<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Verify Your Email — NU Clark Events</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style>
        /* Reset */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-rspace: 0pt; mso-table-lspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
        body { margin: 0; padding: 0; width: 100% !important; height: 100% !important; }
        a[x-apple-data-detectors] { color: inherit !important; text-decoration: none !important; }
        @media only screen and (max-width: 600px) {
            .email-container { width: 100% !important; }
            .fluid { max-width: 100% !important; height: auto !important; }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f0f2f5; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <!-- Preheader (hidden text for email preview) -->
    <div style="display:none;font-size:1px;color:#f0f2f5;line-height:1px;max-height:0px;max-width:0px;opacity:0;overflow:hidden;">
        Your NU Clark verification code is {{ $code }}. Enter this code to verify your email address.
    </div>

    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f0f2f5;">
        <tr>
            <td align="center" style="padding: 40px 10px;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="600" class="email-container" style="max-width: 600px; width: 100%;">

                    <!-- Header with NU Blue Gradient -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #002E5D 0%, #003d7a 50%, #004e99 100%); padding: 40px 40px 30px 40px; text-align: center; border-radius: 12px 12px 0 0;">
                            <!--[if mso]>
                            <v:rect xmlns:v="urn:schemas-microsoft-com:vml" fill="true" stroke="false" style="width:600px;height:120px;">
                            <v:fill type="gradient" color="#002E5D" color2="#004e99" angle="135"/>
                            <v:textbox inset="0,0,0,0">
                            <![endif]-->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td align="center" style="padding-bottom: 15px;">
                                        <div style="width: 60px; height: 60px; background-color: rgba(255,255,255,0.15); border-radius: 50%; display: inline-block; line-height: 60px; text-align: center;">
                                            <span style="font-size: 28px; color: #FFD700;">✉</span>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center">
                                        <h1 style="margin: 0; font-size: 22px; font-weight: 700; color: #ffffff; letter-spacing: 0.5px;">
                                            NU Clark Events
                                        </h1>
                                        <p style="margin: 5px 0 0 0; font-size: 13px; color: rgba(255,255,255,0.7); font-weight: 400;">
                                            Email Verification
                                        </p>
                                    </td>
                                </tr>
                            </table>
                            <!--[if mso]></v:textbox></v:rect><![endif]-->
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="background-color: #ffffff; padding: 40px 40px 20px 40px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td style="font-size: 16px; color: #333333; line-height: 1.6;">
                                        <p style="margin: 0 0 15px 0;">
                                            Hi <strong style="color: #002E5D;">{{ $userName }}</strong>,
                                        </p>
                                        <p style="margin: 0 0 25px 0; color: #555555;">
                                            Thank you for registering with NU Clark Events! Please use the verification code below to confirm your email address and activate your account.
                                        </p>
                                    </td>
                                </tr>

                                <!-- OTP Code Box -->
                                <tr>
                                    <td align="center" style="padding: 10px 0 30px 0;">
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0">
                                            <tr>
                                                <td style="background-color: #f8f9fb; border: 2px dashed #002E5D; border-radius: 12px; padding: 25px 50px; text-align: center;">
                                                    <p style="margin: 0 0 8px 0; font-size: 12px; color: #888888; text-transform: uppercase; letter-spacing: 2px; font-weight: 600;">
                                                        Your Verification Code
                                                    </p>
                                                    <p style="margin: 0; font-size: 40px; font-weight: 800; color: #002E5D; letter-spacing: 10px; font-family: 'Courier New', monospace;">
                                                        {{ $code }}
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="font-size: 14px; color: #777777; line-height: 1.6;">
                                        <p style="margin: 0 0 10px 0;">
                                            Enter this code on the verification page to complete your registration. This code is valid for your current session.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Security Notice -->
                    <tr>
                        <td style="background-color: #ffffff; padding: 0 40px 35px 40px;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #fff8e1; border-radius: 8px; border-left: 4px solid #FFD700;">
                                <tr>
                                    <td style="padding: 15px 20px;">
                                        <p style="margin: 0; font-size: 13px; color: #8a6d00; line-height: 1.5;">
                                            <strong>🔒 Security Tip:</strong> Never share this code with anyone. NU Clark Events staff will never ask for your verification code.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fb; padding: 30px 40px; border-radius: 0 0 12px 12px; border-top: 1px solid #e9ecef;">
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td align="center" style="font-size: 13px; color: #999999; line-height: 1.5;">
                                        <p style="margin: 0 0 5px 0; font-weight: 600; color: #002E5D;">
                                            National University — Clark Campus
                                        </p>
                                        <p style="margin: 0 0 5px 0;">
                                            Jose Abad Santos Avenue, Clark Freeport Zone, Pampanga
                                        </p>
                                        <p style="margin: 15px 0 0 0; font-size: 11px; color: #bbbbbb;">
                                            This is an automated email from NU Clark Events. Please do not reply to this message.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
