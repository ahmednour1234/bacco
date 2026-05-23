@php
    $locale = app()->getLocale();
    $isAr   = $locale === 'ar';
    $dir    = $isAr ? 'rtl' : 'ltr';
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 — Unauthorized | QIMTA</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --green: #006A3B;
            --dark:  #111111;
            --gray:  #666666;
            --border:#e0e0e0;
            --cream: #f5f4f0;
        }
        body {
            font-family: 'Cairo', sans-serif;
            background: #fff;
            color: var(--dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 24px;
            text-align: center;
        }
        [dir=rtl] body { font-family: 'Cairo', sans-serif; }

        /* ── 403 giant text ── */
        .err-bg {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 32px;
            user-select: none;
        }
        .err-num {
            font-size: clamp(120px, 20vw, 220px);
            font-weight: 900;
            color: #f0efec;
            line-height: 1;
            letter-spacing: -4px;
            position: relative;
            z-index: 0;
        }

        /* ── Lock card illustration ── */
        .err-card {
            position: absolute;
            z-index: 1;
            background: #fff;
            border: 1.5px dashed var(--border);
            border-radius: 10px;
            padding: 20px 28px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            width: 180px;
            box-shadow: 0 4px 24px rgba(0,0,0,.07);
        }
        .err-line {
            height: 6px;
            border-radius: 3px;
            background: var(--green);
            opacity: .55;
            width: 100%;
        }
        .err-line.short { width: 55%; }
        .err-line.mid   { width: 80%; }

        /* Lock icon */
        .err-lock {
            width: 44px;
            height: 44px;
            border: 2px dashed #e05252;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #e05252;
            flex-shrink: 0;
        }
        .err-lock svg { width: 20px; height: 20px; }

        .err-line-faint {
            width: 100%;
            height: 5px;
            border-radius: 3px;
            background: #e8e6e1;
        }

        /* ── Copy ── */
        .err-h1 {
            font-size: clamp(28px, 5vw, 46px);
            font-weight: 800;
            color: var(--dark);
            margin-bottom: 6px;
        }
        .err-ar {
            font-size: clamp(16px, 2.5vw, 22px);
            font-weight: 700;
            color: var(--gray);
            margin-bottom: 20px;
        }
        .err-sub {
            font-size: 15px;
            color: var(--gray);
            line-height: 1.75;
            max-width: 440px;
            margin: 0 auto 36px;
        }

        /* ── Buttons ── */
        .err-btns { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; margin-bottom: 40px; }
        .btn-green {
            background: var(--green);
            color: #fff;
            border: none;
            padding: 13px 28px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            font-family: 'Cairo', sans-serif;
            transition: background .2s;
        }
        .btn-green:hover { background: #005530; }
        .btn-green-outline {
            background: transparent;
            color: var(--green);
            border: 2px solid var(--green);
            padding: 12px 28px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            font-family: 'Cairo', sans-serif;
            transition: background .2s, color .2s;
        }
        .btn-green-outline:hover { background: var(--green); color: #fff; }

        /* ── Dashboard link ── */
        .err-dash {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: #aaa;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color .2s;
        }
        .err-dash:hover { color: var(--green); }
        .err-dash svg { width: 14px; height: 14px; }
    </style>
</head>
<body>

    {{-- Illustrated 403 --}}
    <div class="err-bg">
        <span class="err-num">403</span>
        <div class="err-card">
            <div class="err-line mid"></div>
            <div class="err-lock">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
            </div>
            <div class="err-line-faint"></div>
            <div class="err-line short"></div>
            <div class="err-line mid" style="opacity:.3"></div>
        </div>
    </div>

    <h1 class="err-h1">Access Denied</h1>
    <span class="err-ar">عذراً، ليس لديك صلاحية الوصول</span>

    <p class="err-sub">
        You don't have permission to access this resource. Please log in with an authorized account or contact your administrator.
    </p>

    <div class="err-btns">
        <a href="{{ url('/') }}" class="btn-green">Go to Homepage</a>
        <a href="javascript:history.back()" class="btn-green-outline">Go Back</a>
    </div>

    <a href="{{ url('/') }}" class="err-dash">
        Return to Intelligence Dashboard
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
    </a>

</body>
</html>
