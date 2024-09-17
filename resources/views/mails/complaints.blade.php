<!DOCTYPE html>
<html>
<head>
    <title>Complaint Submission</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            background-color: #f9f9f9;
        }
        .header {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .content {
            margin-bottom: 20px;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #888;
        }
        .image {
            margin-top: 10px;
        }
        .image img {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            New Complaint Submission
        </div>

        <div class="content">
            <p><strong>From:</strong> {{ $name }} - ({{ $email }})</p>
            <p><strong>Subject:</strong> {{ $subject }}</p>
            <p><strong>Message:</strong></p>
            <p>{{ $message }}</p>

            @if (!empty($images))
                <p><strong>Attached Images:</strong></p>
                @foreach($images as $image)
                    <div class="image">
                        <img src="{{ asset('storage/' . $image) }}" alt="Complaint">
                    </div>
                @endforeach
            @else
                <p><strong>No images attached.</strong></p>
            @endif
        </div>

        <div class="footer">
            This email was automatically generated by the system. Please do not reply to this email.
        </div>
    </div>

</body>
</html>
