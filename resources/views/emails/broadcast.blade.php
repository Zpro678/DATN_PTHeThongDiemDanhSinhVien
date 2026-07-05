<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectStr }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
            background-color: #f8fafc;
            color: #334155;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: none;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f8fafc;
            padding: 40px 0;
        }
        .main {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 600px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            border: 1px solid #e2e8f0;
        }
        .header {
            background-color: #2563eb;
            padding: 24px 32px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        .content {
            padding: 32px;
            background-color: #ffffff;
        }
        .content p {
            margin-top: 0;
            margin-bottom: 16px;
            font-size: 16px;
            color: #475569;
        }
        .footer {
            background-color: #f1f5f9;
            padding: 24px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        .footer p {
            margin: 0;
            font-size: 14px;
            color: #64748b;
        }
        @media only screen and (max-width: 620px) {
            .main { width: 100% !important; border-radius: 0 !important; border: none !important; box-shadow: none !important; }
            .wrapper { padding: 0 !important; }
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="main">
            <div class="header">
                <h1>{{ config('app.name', 'Attendia Tech') }}</h1>
            </div>
            <div class="content">
                {!! $content !!}
            </div>
            <div class="footer">
                <p>Email này được gửi từ hệ thống <strong>{{ config('app.name', 'Attendia Tech') }}</strong>.</p>
                <p>Vui lòng không trả lời trực tiếp email này.</p>
            </div>
        </div>
    </div>
</body>
</html>
