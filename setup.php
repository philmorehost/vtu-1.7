<?php
// Simple, multi-step PHP installer
// A single file to reduce complexity

session_start();

// --- Configuration ---
$install_lock_file = 'includes/install.lock';
$config_file = 'includes/config.php';

// --- Functions ---

/**
 * Checks if the application is already installed.
 * @return bool
 */
function is_installed() {
    global $install_lock_file;
    return file_exists($install_lock_file);
}

/**
 * Verifies the license key and domain.
 *
 * @param string $license_key
 * @param string $domain_name
 * @return bool
 */
function verify_license($license_key, $domain_name, $license_server_url) {
    $result = ['success' => false, 'http_code' => 0, 'response_body' => ''];

    if (empty($license_server_url) || filter_var($license_server_url, FILTER_VALIDATE_URL) === false) {
        $result['response_body'] = 'License server URL is not a valid URL.';
        return $result;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $license_server_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['key' => $license_key, 'domain' => $domain_name]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $api_response = curl_exec($ch);
    $result['http_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $result['response_body'] = $api_response;

    if (curl_errno($ch)) {
        $result['response_body'] = 'cURL Error: ' . curl_error($ch);
        curl_close($ch);
        return $result;
    }
    curl_close($ch);

    if ($result['http_code'] == 200 && $api_response) {
        $data = json_decode($api_response, true);
        if (isset($data['status']) && $data['status'] == 1) {
            $result['success'] = true;
        }
    }
    return $result;
}

/**
 * Renders the installer page.
 *
 * @param string $title The title of the current step.
 * @param string $content The HTML content of the current step.
 */
function render_page($title, $content) {
    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installation - {$title}</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 700px; margin: 50px auto; background: #fff; padding: 20px 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1, h2 { color: #444; }
        .btn { display: inline-block; padding: 10px 20px; background-color: #007bff; color: #fff; border-radius: 5px; text-decoration: none; border: none; cursor: pointer; }
        .btn:hover { background-color: #0056b3; }
        .btn-disabled { background-color: #ccc; cursor: not-allowed; }
        .error { color: #d8000c; background-color: #ffbaba; border: 1px solid; margin: 10px 0; padding: 15px; border-radius: 5px; }
        .success { color: #4f8a10; background-color: #dff2bf; border: 1px solid; margin: 10px 0; padding: 15px; border-radius: 5px; }
        input[type="text"], input[type="password"] { width: 100%; padding: 8px; margin: 5px 0 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        label { font-weight: bold; }
        .requirements li { list-style: none; padding: 5px 0; }
        .requirements .pass { color: green; }
        .requirements .fail { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Application Installer</h1>
        <h2>{$title}</h2>
        {$content}
    </div>
</body>
</html>
HTML;
}

function run_migrations($pdo, $db_name) {
    try {
        $migration_files = glob('migrations/*.php');
        sort($migration_files);

        foreach ($migration_files as $file) {
            $migration = require($file);
            if (isset($migration['up']) && is_callable($migration['up'])) {
                $migration['up']($pdo);
            }
        }

        // Seeding data
        $stmt = $pdo->query("SELECT id FROM site_settings WHERE id = 1");
        if ($stmt->rowCount() == 0) {
            $pdo->exec("INSERT INTO site_settings (id, site_name) VALUES (1, 'My VTU')");
        }

        return true;
    } catch (PDOException $e) {
        // It's good practice to log the error message.
        // For this installer, we will just return false.
        error_log("Migration Error: " . $e->getMessage());
        return false;
    }
}


// --- Main Logic ---

// If installed, redirect to home page.
if (is_installed()) {
    header("Location: index.php");
    exit;
}

$step = $_GET['step'] ?? '1';

switch ($step) {
    case '1':
        // Face 1: Welcome & License
        $error_message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $license_key = trim($_POST['license_key'] ?? '');
            $domain_name = trim($_POST['domain_name'] ?? '');
            $license_server_url = trim($_POST['license_server_url'] ?? '');

            if (!empty($license_server_url) && basename($license_server_url) !== 'api.php') {
                $license_server_url = rtrim($license_server_url, '/') . '/api.php';
            }

            $verification_result = verify_license($license_key, $domain_name, $license_server_url);

            if ($verification_result['success']) {
                $_SESSION['license_key'] = $license_key;
                $_SESSION['domain_name'] = $domain_name;
                $_SESSION['license_server_url'] = $license_server_url;
                header("Location: setup.php?step=2");
                exit;
            } else {
                $error_message = "<div class='error'>Validation failed. Please check your details and try again.<br><br><strong>Debug Info:</strong><br>HTTP Code: " . htmlspecialchars($verification_result['http_code']) . "<br>Response Body: " . htmlspecialchars($verification_result['response_body']) . "</div>";
            }
        }

        $content = "
            <h3>Welcome</h3>
            {$error_message}
            <p>Welcome to the application installer. This wizard will guide you through the installation process.</p>
            <form action='setup.php?step=1' method='post'>
                <div>
                    <label for='license_server_url'>License Server URL</label>
                    <input type='text' id='license_server_url' name='license_server_url' placeholder='e.g., https://manager.example.com' required>
                    <small>Enter the full URL to your license manager installation.</small>
                </div>
                <div>
                    <label for='license_key'>License Key</label>
                    <input type='text' id='license_key' name='license_key' required>
                </div>
                <div>
                    <label for='domain_name'>Domain Name</label>
                    <input type='text' id='domain_name' name='domain_name' value='{$_SERVER['SERVER_NAME']}' required>
                </div>
                <button type='submit' class='btn'>Verify & Proceed</button>
            </form>
        ";
        render_page('License Verification', $content);
        break;

    case '2':
        // Face 2: Server Requirements
        $requirements = [
            'php_version' => [
                'name' => 'PHP Version >= 7.4',
                'check' => version_compare(PHP_VERSION, '7.4', '>=')
            ],
            'pdo' => [
                'name' => 'PDO Extension',
                'check' => extension_loaded('pdo')
            ],
            'curl' => [
                'name' => 'cURL Extension',
                'check' => extension_loaded('curl')
            ],
            'gd' => [
                'name' => 'GD Extension',
                'check' => extension_loaded('gd')
            ],
             'mysqli' => [
                'name' => 'MySQLi Extension',
                'check' => extension_loaded('mysqli')
            ]
        ];

        $all_requirements_met = true;
        $requirements_html = '<ul class="requirements">';
        foreach ($requirements as $req) {
            $status_class = $req['check'] ? 'pass' : 'fail';
            $requirements_html .= "<li class='{$status_class}'>{$req['name']}</li>";
            if (!$req['check']) {
                $all_requirements_met = false;
            }
        }
        $requirements_html .= '</ul>';

        $proceed_button = $all_requirements_met
            ? '<a href="setup.php?step=3" class="btn">Proceed to Database Setup</a>'
            : '<a href="#" class="btn btn-disabled" onclick="alert(\'Please fix the failed requirements before proceeding.\'); return false;">Proceed</a>';

        $content = "
            <h3>Server Requirements</h3>
            {$requirements_html}
            {$proceed_button}
        ";
        render_page('Server Requirements', $content);
        break;

    case '3':
        // Face 3: Database Setup
        $error_message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $db_host = $_POST['db_host'] ?? '';
            $db_name = $_POST['db_name'] ?? '';
            $db_user = $_POST['db_user'] ?? '';
            $db_pass = $_POST['db_pass'] ?? '';

            // Create config file content
            $config_content = "<?php\n";
            $config_content .= "define('DB_HOST', '{$db_host}');\n";
            $config_content .= "define('DB_NAME', '{$db_name}');\n";
            $config_content .= "define('DB_USER', '{$db_user}');\n";
            $config_content .= "define('DB_PASS', '{$db_pass}');\n";

            // Write config file
            if (file_put_contents($config_file, $config_content)) {
                try {
                    // Test DB connection and run migrations
                    $pdo = new PDO("mysql:host={$db_host}", $db_user, $db_pass);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}`");
                    $pdo->exec("USE `{$db_name}`");

                    if (run_migrations($pdo, $db_name)) {
                        // Database is set up, proceed to admin creation
                        header("Location: setup.php?step=4");
                        exit;
                    } else {
                        $error_message = "<div class='error'>Database migration failed.</div>";
                    }
                } catch (PDOException $e) {
                    $error_message = "<div class='error'>Database connection failed: " . $e->getMessage() . "</div>";
                }
            } else {
                $error_message = "<div class='error'>Could not write to config file. Please check permissions.</div>";
            }
        }

        $content = "
            <h3>Database Configuration</h3>
            {$error_message}
            <p>Please provide your database connection details.</p>
            <form action='setup.php?step=3' method='post'>
                <div><label for='db_host'>Database Host</label><input type='text' id='db_host' name='db_host' value='localhost' required></div>
                <div><label for='db_name'>Database Name</label><input type='text' id='db_name' name='db_name' required></div>
                <div><label for='db_user'>Database User</label><input type='text' id='db_user' name='db_user' required></div>
                <div><label for='db_pass'>Database Password</label><input type='password' id='db_pass' name='db_pass'></div>
                <button type='submit' class='btn'>Save & Install Database</button>
            </form>
        ";
        render_page('Database Setup', $content);
        break;

    case '4':
        // Face 4: Create Admin User
        $error_message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $admin_name = $_POST['admin_name'] ?? '';
            $admin_email = $_POST['admin_email'] ?? '';
            $admin_pass = $_POST['admin_pass'] ?? '';

            if (!empty($admin_name) && !empty($admin_email) && !empty($admin_pass) && filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
                require_once($config_file);
                try {
                    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                    $password_hash = password_hash($admin_pass, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO admins (name, email, password) VALUES (?, ?, ?)");
                    $stmt->execute([$admin_name, $admin_email, $password_hash]);

                    // Create install lock file
                    file_put_contents($install_lock_file, 'installed');

                    header("Location: setup.php?step=5");
                    exit;
                } catch (PDOException $e) {
                    $error_message = "<div class='error'>Database error: " . $e->getMessage() . "</div>";
                }
            } else {
                $error_message = "<div class='error'>Please fill in all fields with valid data.</div>";
            }
        }
        $content = "
            <h3>Create Admin User</h3>
            {$error_message}
            <p>Create your administrator account.</p>
            <form action='setup.php?step=4' method='post'>
                <div><label>Name</label><input type='text' name='admin_name' required></div>
                <div><label>Email</label><input type='email' name='admin_email' required></div>
                <div><label>Password</label><input type='password' name='admin_pass' required></div>
                <button type='submit' class='btn'>Create Admin & Finish</button>
            </form>
        ";
        render_page('Create Admin', $content);
        break;

    case '5':
        // Face 5: Complete
        $content = "
            <div class='success'>Congratulations! The application has been installed successfully.</div>
            <h3>Next Steps</h3>
            <ol>
                <li><strong>Login to your Admin Panel:</strong> Use the credentials you just created to <a href='admin/index.php'>log in to the admin area</a>.</li>
                <li><strong>Configure Site Settings:</strong> Go to the 'Settings' page to set up your site name, payment gateways, and SMTP details for email.</li>
                <li><strong>Set Up API Providers:</strong> In the admin panel, navigate to 'API Manager' to configure the providers for services like airtime, data, etc.</li>
            </ol>
            <p>For security, the installer is now locked. To re-run it, you must delete the <strong>install.lock</strong> file from the <strong>includes</strong> directory.</p>
            <a href='admin/index.php' class='btn'>Login to Admin Panel</a>
        ";
        render_page('Installation Complete', $content);
        break;

    default:
        header("Location: setup.php?step=1");
        exit;
}
?>
