<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'CloudSaviour Notification' }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap');
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            line-height: 1.6;
            color: #0f172a;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #ffffff;
            padding-bottom: 40px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
        }
        .main-card {
            margin: 40px 20px;
            border: 1px solid #0f172a;
            border-radius: 0px; /* Sharp corners for premium look */
            overflow: hidden;
            box-shadow: 10px 10px 0px rgba(15, 23, 42, 0.05);
        }
        .header {
            padding: 40px 32px;
            background-color: #ffffff;
            border-bottom: 1px solid #0f172a;
            text-align: left;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: 800;
            letter-spacing: -0.025em;
            text-transform: uppercase;
            color: #0f172a;
        }
        .content {
            padding: 40px 32px;
        }
        .content h2 {
            margin-top: 0;
            font-size: 24px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.02em;
        }
        .footer {
            padding: 32px;
            text-align: left;
            font-size: 11px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            border-top: 1px solid #f1f5f9;
        }
        .button {
            display: inline-block;
            padding: 14px 28px;
            background-color: #0f172a;
            color: #ffffff !important;
            text-decoration: none;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.2em;
            margin-top: 32px;
            border: 1px solid #0f172a;
            transition: all 0.2s ease;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            background: #f1f5f9;
            color: #0f172a;
            margin-bottom: 16px;
            border: 1px solid #0f172a;
        }
        .highlight {
            color: #0f172a;
            font-weight: 700;
            border-bottom: 1px solid #0f172a;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 24px 0;
        }
        .data-table th {
            text-align: left;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: #64748b;
            padding-bottom: 8px;
            border-bottom: 1px solid #f1f5f9;
        }
        .data-table td {
            padding: 12px 0;
            font-size: 14px;
            color: #0f172a;
            border-bottom: 1px solid #f1f5f9;
        }
        .type-mono {
            font-family: 'JetBrains Mono', 'Courier New', monospace;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="main-card">
                <div class="header">
                    <h1>CloudSaviour</h1>
                </div>
                <div class="content">
                    @yield('content')
                    <div style="margin-top: 40px;">
                        <a href="{{ config('app.url') }}/dashboard" class="button">Access Dashboard</a>
                    </div>
                </div>
                <div class="footer">
                    &copy; {{ date('Y') }} CLOUDSAVIOUR SYSTEM INTERFACE<br>
                    <span style="display: block; margin-top: 8px;">Automated intelligence alert. configured via workspace settings.</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
