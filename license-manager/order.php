<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase License</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f4f4; margin: 0; padding: 0; }
        .container { max-width: 700px; margin: 50px auto; background: #fff; padding: 20px 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #444; }
        .btn { display: inline-block; padding: 10px 20px; background-color: #007bff; color: #fff; border-radius: 5px; text-decoration: none; border: none; cursor: pointer; }
        .btn:hover { background-color: #0056b3; }
        input[type="text"], input[type="email"] { width: 100%; padding: 8px; margin: 5px 0 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        label { font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Purchase a License</h1>
        <p>To use this script, you need a valid license key for your domain.</p>
        <form action="https://paystack.com/pay/your_payment_link" method="POST">
            <div>
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div>
                <label for="domain">Domain Name</label>
                <input type="text" id="domain" name="domain" value="<?= htmlspecialchars($_GET['domain'] ?? '') ?>" required>
            </div>
            <!-- Paystack will handle the rest -->
            <button type="submit" class="btn">Proceed to Payment</button>
        </form>
    </div>
</body>
</html>
