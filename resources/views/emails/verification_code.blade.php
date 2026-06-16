<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Code</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        @keyframes fadeSlideIn {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.85); }
            to   { opacity: 1; transform: scale(1); }
        }
        @keyframes pulse {
            0%, 100% { box-shadow: 0 0 0 0 rgba(24, 95, 165, 0.18); }
            50%       { box-shadow: 0 0 0 10px rgba(24, 95, 165, 0); }
        }
        @keyframes checkPop {
            0%   { transform: scale(0) rotate(-10deg); opacity: 0; }
            70%  { transform: scale(1.18) rotate(2deg); opacity: 1; }
            100% { transform: scale(1) rotate(0deg); opacity: 1; }
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f4f8;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .email-card {
            max-width: 460px;
            width: 100%;
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            animation: fadeSlideIn 0.6s cubic-bezier(.22,.68,0,1.2) both;
        }

        /* ── Header ── */
        .card-header {
            background: linear-gradient(135deg, #185FA5 0%, #378ADD 100%);
            padding: 2rem;
            text-align: center;
        }
        .header-icon {
            width: 56px; height: 56px;
            background: rgba(255,255,255,0.18);
            border-radius: 16px;
            margin: 0 auto 1rem;
            display: flex; align-items: center; justify-content: center;
            animation: scaleIn 0.5s 0.2s cubic-bezier(.22,.68,0,1.2) both;
        }
        .header-icon i { font-size: 28px; color: #fff; }
        .header-subtitle {
            font-size: 13px;
            color: rgba(255,255,255,0.75);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-weight: 500;
        }
        .header-title {
            margin-top: 6px;
            font-size: 22px;
            font-weight: 500;
            color: #fff;
        }

        /* ── Body ── */
        .card-body {
            padding: 2rem;
            animation: fadeSlideIn 0.5s 0.25s both;
        }
        .intro-text {
            font-size: 15px;
            color: #64748b;
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }

        /* ── Code box ── */
        .code-box {
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 1.5rem;
            text-align: center;
            margin-bottom: 1.5rem;
            animation: scaleIn 0.5s 0.35s cubic-bezier(.22,.68,0,1.2) both;
        }
        .code-label {
            font-size: 12px;
            color: #94a3b8;
            letter-spacing: 0.07em;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .code-value {
            font-size: 38px;
            font-weight: 600;
            letter-spacing: 0.22em;
            color: #185FA5;
            font-variant-numeric: tabular-nums;
            margin-bottom: 14px;
            display: inline-block;
            animation: pulse 2.4s 0.8s ease-in-out infinite;
        }
        .code-actions {
            display: flex;
            gap: 8px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-copy {
            display: inline-flex; align-items: center; gap: 6px;
            font-size: 13px; font-weight: 500;
            background: #185FA5; color: #fff;
            border: none; border-radius: 8px;
            padding: 7px 16px; cursor: pointer;
            transition: transform 0.15s, opacity 0.15s;
        }
        .btn-copy:hover { opacity: 0.88; }
        .btn-copy:active { transform: scale(0.96); }

        .copied-badge {
            display: none;
            margin-top: 10px;
            font-size: 13px;
            color: #0F6E56;
            animation: checkPop 0.4s cubic-bezier(.22,.68,0,1.2) both;
        }

        /* ── Expiry notice ── */
        .expiry-notice {
            display: flex; align-items: flex-start; gap: 10px;
            background: #EBF4FF;
            border-radius: 8px;
            padding: 12px 14px;
            margin-bottom: 1.5rem;
            animation: fadeSlideIn 0.5s 0.45s both;
        }
        .expiry-notice i { font-size: 16px; color: #185FA5; margin-top: 1px; flex-shrink: 0; }
        .expiry-notice p { font-size: 13px; color: #0C447C; line-height: 1.55; }

        /* ── Footer notes ── */
        .footer-notes {
            border-top: 1px solid #e2e8f0;
            padding-top: 1.25rem;
            animation: fadeSlideIn 0.5s 0.55s both;
        }
        .footer-notes .note {
            display: flex; align-items: center; gap: 8px;
            margin-bottom: 8px;
        }
        .footer-notes .note i { font-size: 15px; color: #94a3b8; }
        .footer-notes .note p { font-size: 12px; color: #94a3b8; }

        /* ── Card footer ── */
        .card-footer {
            padding: 1rem 2rem;
            border-top: 1px solid #e2e8f0;
            display: flex; align-items: center; justify-content: space-between;
            animation: fadeSlideIn 0.5s 0.6s both;
        }
        .card-footer p { font-size: 12px; color: #94a3b8; }
        .card-footer .links { display: flex; gap: 12px; }
        .card-footer .links a { font-size: 12px; color: #94a3b8; text-decoration: none; }
        .card-footer .links a:hover { color: #185FA5; }
    </style>
</head>
<body>

<div class="email-card">

    {{-- Header --}}
    <div class="card-header">
        <div class="header-icon">
            <i class="ti ti-shield-check"></i>
        </div>
        <p class="header-subtitle">Security verification</p>
        <h2 class="header-title">Confirm your email</h2>
    </div>

    {{-- Body --}}
    <div class="card-body">

        <p class="intro-text">
            Hello! We received a request to verify your email address.
            Use the code below to complete the process.
        </p>

        {{-- Code box --}}
        <div class="code-box">
            <p class="code-label">Your code</p>
            <div class="code-value" id="code-display">{{ $code }}</div>

            <div class="code-actions">
                <button class="btn-copy" onclick="handleCopy()">
                    <i class="ti ti-copy"></i>
                    <span id="copy-label">Copy code</span>
                </button>
            </div>

            <div class="copied-badge" id="copied-badge">
                <i class="ti ti-circle-check" style="vertical-align:-2px; margin-right:4px;"></i>
                Code copied to clipboard!
            </div>
        </div>

        {{-- Expiry --}}
        <div class="expiry-notice">
            <i class="ti ti-clock"></i>
            <p>This code expires in <strong>5 minutes</strong>. Do not share it with anyone.</p>
        </div>

        {{-- Notes --}}
        <div class="footer-notes">
            <div class="note">
                <i class="ti ti-info-circle"></i>
                <p>If you didn't request this, you can safely ignore this email.</p>
            </div>
            <div class="note">
                <i class="ti ti-lock"></i>
                <p>Never share your verification code with anyone.</p>
            </div>
        </div>

    </div>

    {{-- Card footer --}}
    <div class="card-footer">
        <p>© {{ date('Y') }} Intelligent Peer-to-Peer Car Rental Platform. All rights reserved.</p>
        <div class="links">
            <a href="#">Privacy</a>
            <a href="#">Help</a>
        </div>
    </div>

</div>

<script>
    let copyTimeout = null;

    function handleCopy() {
    const code = document.getElementById('code-display').textContent.trim();

    // Try modern API first, fallback to execCommand
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(code).then(showCopied).catch(fallbackCopy);
    } else {
        fallbackCopy();
    }

    function fallbackCopy() {
        const textarea = document.createElement('textarea');
        textarea.value = code;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.focus();
        textarea.select();
        try {
            document.execCommand('copy');
            showCopied();
        } catch (err) {
            alert('Code: ' + code); // Last resort
        }
        document.body.removeChild(textarea);
    }

    function showCopied() {
        const badge = document.getElementById('copied-badge');
        const label = document.getElementById('copy-label');

        badge.style.display = 'block';
        badge.style.animation = 'none';
        void badge.offsetWidth;
        badge.style.animation = 'checkPop 0.4s cubic-bezier(.22,.68,0,1.2) both';

        label.textContent = 'Copied!';
        clearTimeout(copyTimeout);
        copyTimeout = setTimeout(() => {
            badge.style.display = 'none';
            label.textContent = 'Copy code';
        }, 2200);
    }
}
</script>

</body>
</html>
