<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{subject}</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: linear-gradient(135deg, #e6f0ff 0%, #f8faff 100%);
            margin: 0;
            padding: 0;
            color: #222;
        }
        .container {
            width: 100%;
            max-width: 520px;
            margin: 40px auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 8px 28px rgba(60,100,200,0.12), 0 1.5px 6px rgba(0,0,0,0.03);
            overflow: hidden;
            border: 1px solid #e8eaf6;
        }
        .header {
            background: linear-gradient(90deg, #3b82f6 0%, #6366f1 100%);
            padding: 32px 28px 20px 28px;
            text-align: center;
            color: #fff;
            position: relative;
        }
        .header::after {
            content: '';
            display: block;
            margin: 18px auto 0 auto;
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #fff 0%, #a7ffeb 85%);
            border-radius: 2px;
        }
        .header h2 {
            margin: 0;
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .content {
            padding: 32px 28px 18px 28px;
            color: #333;
            font-size: 1.08rem;
        }
        .content p {
            margin: 0 0 16px 0;
        }
        .content a.button {
            display: inline-block;
            margin: 20px 0 10px 0;
            padding: 12px 32px;
            background: linear-gradient(90deg, #3b82f6 0%, #6366f1 100%);
            color: #fff !important;
            font-weight: 600;
            text-decoration: none;
            border-radius: 6px;
            box-shadow: 0 1px 4px rgba(60,100,200,0.12);
            transition: background 0.3s;
        }
        .content a.button:hover {
            background: linear-gradient(90deg, #6366f1 0%, #3b82f6 100%);
        }
        .footer {
            background: #f8faff;
            padding: 18px;
            text-align: center;
            font-size: 13px;
            color: #7a869a;
            border-top: 1px solid #e8eaf6;
        }
        @media (max-width: 600px) {
            .container {
                margin: 8px;
                border-radius: 12px;
            }
            .header, .content {
                padding: 20px 12px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>{site_name}</h2>
        </div>
        <div class="content">
            {body}
            <div style="text-align:center;">
                <a href="{site_url}" class="button">Visit Our Site</a>
            </div>
        </div>
        <div class="footer">
            <p>&copy; {year} {site_name}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>