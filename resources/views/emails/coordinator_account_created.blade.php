<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Coordinator Account Created</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f9f9f9; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 0 10px rgba(0,0,0,0.05);">
        <!-- Header -->
        <div style="background-color: #2e7d32; padding: 20px; text-align: center;">
        <img src="{{ url('images/Opol-logo-300x287.png') }}" alt="Opol MAO Logo" style="max-width: 120px; margin-bottom: 20px;">


            <h2 style="color: #ffffff; margin: 0;">Opol AgriSys</h2>
            <p style="color: #e0f2f1; margin: 0;">Municipal Agriculture Office – Opol</p>
        </div>

        <!-- Content -->
        <div style="padding: 30px;">
            <h3 style="color: #2e7d32;">Welcome, {{ $coordinator->fname }}!</h3>

            <p>Your coordinator account has been successfully created. Below are your login details:</p>

            <table style="width: 100%; margin-top: 20px; margin-bottom: 20px;">
                <tr>
                    <td style="font-weight: bold;">Username:</td>
                    <td>{{ $coordinator->username }}</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">Password:</td>
                    <td>{{ $password }}</td>
                </tr>
            </table>

            <p><em>Please log in and change your password immediately to secure your account.</em></p>

            <p>Thank you,<br>
            <strong>Municipal Agriculture Office – Opol</strong></p>
        </div>

        <!-- Footer -->
        <div style="background-color: #f1f1f1; padding: 15px; text-align: center; font-size: 12px; color: #777;">
            <p>This is an automated message from the Opol AgriSys system.<br>
            Please do not reply to this email.</p>
            <p>For support, contact <a href="mailto:support@opolagrisys.gov.ph">support@opolagrisys.gov.ph</a></p>
        </div>
    </div>
</body>
</html>
