<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
</head>
<body style="margin:0;padding:24px;background:#f4f6fb;font-family:Arial,sans-serif;color:#0f172a;">
    <div style="max-width:640px;margin:0 auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:18px;overflow:hidden;">
        <div style="padding:24px;background:linear-gradient(135deg,#0f172a,#2446d8);color:#ffffff;">
            <p style="margin:0;font-size:12px;font-weight:700;letter-spacing:0.18em;text-transform:uppercase;">EasyClubSport</p>
            <h1 style="margin:12px 0 0;font-size:28px;line-height:1.2;">{{ $headline }}</h1>
        </div>

        <div style="padding:24px;">
            @foreach ($lines as $line)
                <p style="margin:0 0 14px;font-size:15px;line-height:1.7;color:#334155;">{{ $line }}</p>
            @endforeach

            @if ($actionLabel && $actionUrl)
                <p style="margin:24px 0 0;">
                    <a href="{{ $actionUrl }}" style="display:inline-block;padding:12px 20px;border-radius:999px;background:#2446d8;color:#ffffff;text-decoration:none;font-weight:700;">
                        {{ $actionLabel }}
                    </a>
                </p>
            @endif
        </div>
    </div>
</body>
</html>
