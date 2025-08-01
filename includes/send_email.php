<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require_once 'db.php';

function send_email($to, $subject, $body) {
    global $pdo;

    try {
        $stmt = $pdo->query("SELECT * FROM site_settings LIMIT 1");
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);

        if (empty($settings['smtp_host']) || empty($settings['smtp_port']) || empty($settings['smtp_user']) || empty($settings['smtp_pass']) || empty($settings['smtp_from_email']) || empty($settings['smtp_from_name'])) {
            $error_message = "SMTP settings are not configured properly.";
            error_log("Email Error: " . $error_message);
            return false;
        }

        $mail = new PHPMailer(true);

        // Server settings
        $mail->SMTPDebug = 0; // Set to 0 for production, 2 for debugging
        $mail->isSMTP();
        $mail->Host       = $settings['smtp_host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $settings['smtp_user'];
        $mail->Password   = $settings['smtp_pass'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = (int)$settings['smtp_port'];

        // Recipients
        $mail->setFrom($settings['smtp_from_email'], $settings['smtp_from_name']);
        $mail->addAddress($to);

        // Content
        $template_path = __DIR__ . '/email_template.php';
        if (file_exists($template_path)) {
            $template = file_get_contents($template_path);
        } else {
            // Fallback template if file doesn't exist
            $template = '<!DOCTYPE html><html><head><title>{subject}</title></head><body><div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px;"><h2>{site_name}</h2><div>{body}</div><p>&copy; {year} {site_name}. All rights reserved.</p></div></body></html>';
        }
        
        $template = str_replace('{subject}', $subject, $template);
        $template = str_replace('{site_name}', $settings['site_name'] ?? 'VTU Platform', $template);
        $template = str_replace('{body}', $body, $template);
        $template = str_replace('{year}', date('Y'), $template);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $template;

        $mail->send();
        return true;
    } catch (Exception $e) {
        $error_message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        error_log("Email Error: " . $error_message);
        return false;
    }
}
?>
