<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Upaya Kloning Cookie Terdeteksi - OptiGuard' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            background: radial-gradient(circle at 50% 15%, #1e1b4b 0%, #090d16 55%, #030712 100%);
            color: #f8fafc;
            font-family: 'Figtree', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
            padding: 20px;
            overflow-x: hidden;
        }
        .bg-ambient {
            position: fixed;
            top: 20%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(225, 29, 72, 0.18) 0%, rgba(99, 102, 241, 0.10) 50%, transparent 70%);
            pointer-events: none;
            z-index: 0;
        }
        .card {
            position: relative;
            z-index: 1;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-top: 1px solid rgba(244, 63, 94, 0.4);
            padding: 36px 28px;
            border-radius: 24px;
            max-width: 460px;
            width: 100%;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7), 0 0 30px rgba(225, 29, 72, 0.15);
            animation: optigateFadeIn 0.35s ease-out;
        }
        @keyframes optigateFadeIn {
            from { opacity: 0; transform: scale(0.96) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
        .optiguard-logo {
            height: 42px;
            width: auto;
            margin: 0 auto 20px;
            display: block;
            filter: brightness(0) invert(1);
            opacity: 0.95;
        }
        .shield-box {
            width: 68px;
            height: 68px;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(244, 63, 94, 0.18), rgba(99, 102, 241, 0.15));
            border: 1px solid rgba(244, 63, 94, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            box-shadow: inset 0 0 20px rgba(244, 63, 94, 0.15), 0 8px 20px -6px rgba(0, 0, 0, 0.4);
        }
        h1 {
            font-size: 21px;
            font-weight: 800;
            margin: 0 0 10px 0;
            color: #ffffff;
            letter-spacing: -0.02em;
        }
        p {
            font-size: 13.5px;
            color: #94a3b8;
            margin: 0 0 22px 0;
            line-height: 1.6;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            background: rgba(3, 7, 18, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.05);
            padding: 12px;
            border-radius: 14px;
            margin-bottom: 24px;
            text-align: left;
        }
        .info-label {
            font-size: 10.5px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.04em;
            display: block;
            margin-bottom: 3px;
        }
        .info-val {
            font-size: 12px;
            font-weight: 700;
        }
        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
            font-weight: 700;
            padding: 13px 24px;
            border-radius: 14px;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.2s ease;
            box-shadow: 0 10px 20px -5px rgba(5, 150, 105, 0.4);
            border: none;
            cursor: pointer;
        }
        .btn:hover {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            transform: translateY(-1px);
            box-shadow: 0 14px 24px -5px rgba(5, 150, 105, 0.5);
        }
        .footer {
            margin-top: 18px;
            font-size: 11px;
            color: #475569;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="bg-ambient"></div>
    <div class="card">
        <img src="{{ $logoUrl ?? \OptiGuard\Laravel\Helpers\Logo::get() }}" alt="OptiGuard Logo" class="optiguard-logo" />

        <div class="shield-box">
            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" stroke="#fb7185" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                <circle cx="12" cy="11" r="2.5" fill="#fb7185"/>
                <path d="M12 13.5V17"/>
            </svg>
        </div>

        <h1>{{ $title ?? 'Upaya Kloning Cookie Terdeteksi' }}</h1>
        <p>{{ $message ?? 'Sesi login Anda telah dibatalkan otomatis oleh sistem keamanan karena terdeteksi upaya penggunaan cookie pada perangkat atau jaringan yang berbeda (Anti-Hijacking Protocol).' }}</p>

        <div class="info-grid">
            <div>
                <span class="info-label">Status Sesi</span>
                <span class="info-val" style="color: #fb7185;">Dibatalkan (Logged Out)</span>
            </div>
            <div>
                <span class="info-label">Protokol Keamanan</span>
                <span class="info-val" style="color: #34d399;">Anti-Hijacking Shield</span>
            </div>
        </div>

        <a href="{{ $buttonUrl ?? '/login' }}" class="btn">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                <polyline points="10 17 15 12 10 7"/>
                <line x1="15" y1="12" x2="3" y2="12"/>
            </svg>
            <span>{{ $buttonText ?? 'Kembali ke Halaman Login' }}</span>
        </a>

        <div class="footer">
            OptiGuard Security Protocol • PT Tata Optima Property
        </div>
    </div>
</body>
</html>
