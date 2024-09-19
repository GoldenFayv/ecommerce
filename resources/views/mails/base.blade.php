<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', 'Email Notification')</title>

    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f3f4f6;
            font-family: 'Nunito', sans-serif;
            font-size: 14px;
            color: #3b3b3b;
        }

        .email-container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .email-header {
            background-color: #6576ff;
            padding: 30px;
            text-align: center;
            color: #ffffff;
        }

        .email-header h1 {
            font-size: 24px;
            margin: 0;
            font-weight: bold;
        }

        .email-body {
            padding: 30px;
            background-color: #ffffff;
        }

        .email-footer {
            padding: 20px;
            text-align: center;
            background-color: #f9fafb;
            color: #7d7d7d;
            font-size: 12px;
            border-top: 1px solid #e4e4e4;
        }

        .email-footer a {
            color: #6576ff;
            text-decoration: none;
        }

        .social-links {
            padding-top: 15px;
        }

        .social-links a {
            margin: 0 5px;
            display: inline-block;
            height: 40px;
            width: 40px;
            border-radius: 50%;
            background-color: #f3f4f6;
            text-align: center;
            line-height: 40px;
        }

        .social-links img {
            height: 20px;
        }
    </style>
</head>

<body>
    <div class="email-container">
        <!-- Email Header -->
        <div class="email-header">
            <h1>@yield('email-title', 'Project Notification')</h1>
        </div>

        <!-- Email Body -->
        <div class="email-body">
            @yield('content')
        </div>

        <!-- Email Footer -->
        <div class="email-footer">
            <p>&copy; {{ now()->year }} Brand Name Family. All rights reserved.</p>
            <p>Powered by <a href="#">Brand Name</a></p>

            <!-- Social Links -->
            <div class="social-links">
                <a href="#" target="_blank">
                    <img src="https://img.icons8.com/fluent/48/000000/facebook-new.png" alt="Facebook">
                </a>
                <a href="#" target="_blank">
                    <img src="https://img.icons8.com/fluent/48/000000/instagram-new.png" alt="Instagram">
                </a>
                <a href="#" target="_blank">
                    <img src="https://img.icons8.com/fluent/48/000000/twitter.png" alt="Twitter">
                </a>
                <a href="#" target="_blank">
                    <img src="https://img.icons8.com/fluent/48/000000/linkedin.png" alt="LinkedIn">
                </a>
            </div>
        </div>
    </div>
</body>

</html>
