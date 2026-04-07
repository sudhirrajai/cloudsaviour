<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to CloudSaviour</title>
</head>
<body style="margin: 0; padding: 0; background-color: #020508; color: #e2eeff; font-family: 'JetBrains Mono', monospace;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #020508; padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="100%" max-width="600" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #020508; border: 1px solid rgba(59, 130, 246, 0.1); border-radius: 16px; overflow: hidden;">
                    <!-- HEADER -->
                    <tr>
                        <td align="center" style="padding: 40px; background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(139, 92, 246, 0.1));">
                            <div style="font-size: 24px; font-weight: 800; color: #e2eeff; letter-spacing: -1px;">CloudSaviour</div>
                        </td>
                    </tr>
                    <!-- CONTENT -->
                    <tr>
                        <td style="padding: 40px; line-height: 1.6;">
                            <div style="font-size: 28px; font-weight: 700; margin-bottom: 24px; color: #3b82f6;">The wait is over.</div>
                            <p style="margin: 0 0 16px;">We’ve been working hard in stealth, and today we’re excited to give you early access to CloudSaviour. Your account has been prepared and is ready for use.</p>
                            
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background: rgba(59, 130, 246, 0.05); border: 1px solid rgba(59, 130, 246, 0.2); border-radius: 12px; margin: 32px 0;">
                                <tr>
                                    <td style="padding: 24px;">
                                        <div style="margin-bottom: 16px;">
                                            <div style="color: #4a6080; text-transform: uppercase; letter-spacing: 1px; font-size: 11px; margin-bottom: 4px;">LOGIN EMAIL</div>
                                            <div style="font-size: 16px; color: #e2eeff; font-weight: 500;">{{ $email }}</div>
                                        </div>
                                        <div>
                                            <div style="color: #4a6080; text-transform: uppercase; letter-spacing: 1px; font-size: 11px; margin-bottom: 4px;">TEMPORARY PASSWORD</div>
                                            <div style="font-size: 16px; color: #e2eeff; font-weight: 500; font-family: monospace; letter-spacing: 1px;">{{ $password }}</div>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0 0 24px;">For your security, please change your password immediately after your first login.</p>

                            <a href="{{ config('app.url') }}/login" style="display: inline-block; background: #3b82f6; color: #ffffff !important; text-decoration: none; padding: 16px 32px; border-radius: 8px; font-weight: 700; font-size: 14px;">Access your Dashboard →</a>
                        </td>
                    </tr>
                    <!-- FOOTER -->
                    <tr>
                        <td align="center" style="padding: 40px; border-top: 1px solid rgba(59, 130, 246, 0.1); color: #4a6080; font-size: 12px;">
                            &copy; 2025 CloudSaviour by VMCORE. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
