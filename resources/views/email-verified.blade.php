<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verified - BYAHERO</title>
    <meta http-equiv="refresh" content="10;url=http://localhost:3000/login">
    <style>
        * {
            box-sizing: border-box;
        }

        html, body {
            margin: 0;
            min-height: 100%;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }

        body {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            overflow-x: hidden;
            background: white;
        }

        /* Grid Background */
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background-image: 
                linear-gradient(to right, #e2e8f0 1px, transparent 1px),
                linear-gradient(to bottom, #e2e8f0 1px, transparent 1px);
            background-size: 4rem 4rem;
            mask-image: radial-gradient(ellipse 80% 70% at 50% 20%, black 70%, transparent 100%);
            -webkit-mask-image: radial-gradient(ellipse 80% 70% at 50% 20%, black 70%, transparent 100%);
            pointer-events: none;
        }

        /* Cyan gradient glow effect */
        body::after {
            content: "";
            position: fixed;
            inset: 0;
            background: 
                radial-gradient(ellipse at top right, rgba(6, 182, 212, 0.1), transparent 50%),
                radial-gradient(ellipse at bottom left, rgba(6, 182, 212, 0.08), transparent 50%);
            pointer-events: none;
        }

        .bg-car {
            position: fixed;
            width: 170px;
            height: 170px;
            opacity: 0.08;
            color: #06b6d4;
            pointer-events: none;
        }

        .bg-car.left {
            top: 48px;
            left: 28px;
        }

        .bg-car.right {
            right: 28px;
            bottom: 48px;
        }

        .shell {
            position: relative;
            z-index: 20;
            width: 100%;
            max-width: 1440px;
        }

        .card {
            display: grid;
            grid-template-columns: 0.95fr 1.05fr;
            overflow: hidden;
            border: 2px solid rgba(6, 182, 212, 0.6);
            border-radius: 32px;
            background: white;
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.05), 0 0 0 1px rgba(6, 182, 212, 0.1);
        }

        .left-panel,
        .right-panel {
            padding: 40px;
        }

        .left-panel {
            position: relative;
            border-right: 1px solid #e2e8f0;
            background: linear-gradient(135deg, #f0fdfa 0%, white 100%);
        }

        .left-panel::before {
            content: "";
            position: absolute;
            inset: 0 0 auto 0;
            height: 128px;
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.1), transparent 60%);
            pointer-events: none;
        }

        .chip {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            border-radius: 999px;
            border: 1px solid rgba(6, 182, 212, 0.5);
            background: rgba(6, 182, 212, 0.1);
            color: #0891b2;
            font-size: 14px;
            font-weight: 500;
        }

        .hero {
            position: relative;
            max-width: 520px;
            margin-top: 32px;
        }

        .hero h1 {
            margin: 0;
            font-size: clamp(3rem, 5vw, 5rem);
            line-height: 0.95;
            font-weight: 900;
            letter-spacing: -0.04em;
        }

        .hero h1 .accent {
            background: linear-gradient(135deg, #0891b2, #06b6d4);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .hero p {
            margin: 20px 0 0;
            color: #64748b;
            font-size: 16px;
            line-height: 1.9;
        }

        .stack {
            margin-top: 40px;
            display: grid;
            gap: 16px;
        }

        .step-card,
        .info-card,
        .status-card,
        .redirect-card {
            border: 1px solid rgba(6, 182, 212, 0.5);
            background: white;
            border-radius: 16px;
            transition: all 0.2s ease;
        }

        .step-card:hover,
        .info-card:hover {
            border-color: #06b6d4;
            box-shadow: 0 4px 12px rgba(6, 182, 212, 0.1);
        }

        .step-card {
            display: flex;
            gap: 16px;
            align-items: flex-start;
            padding: 18px 20px;
        }

        .step-icon {
            flex: 0 0 44px;
            width: 44px;
            height: 44px;
            display: grid;
            place-items: center;
            border-radius: 12px;
            border: 1px solid rgba(6, 182, 212, 0.5);
            background: rgba(6, 182, 212, 0.1);
            color: #0891b2;
            font-weight: 700;
            font-size: 18px;
        }

        .step-title {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
        }

        .step-copy {
            margin: 6px 0 0;
            color: #64748b;
            font-size: 14px;
            line-height: 1.65;
        }

        .info-card {
            margin-top: 40px;
            display: flex;
            gap: 14px;
            padding: 20px;
        }

        .section-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
        }

        .section-head h2 {
            margin: 0;
            font-size: 48px;
            line-height: 0.95;
            letter-spacing: -0.04em;
            font-weight: 900;
            background: linear-gradient(135deg, #0f172a, #334155);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .section-head p {
            margin: 16px 0 0;
            color: #64748b;
            font-size: 16px;
            line-height: 1.8;
        }

        .status-meta {
            text-align: right;
        }

        .status-meta .label {
            font-size: 12px;
            letter-spacing: 0.24em;
            text-transform: uppercase;
            color: #94a3b8;
        }

        .status-meta .value {
            margin-top: 10px;
            color: #0891b2;
            font-size: 30px;
            font-weight: 700;
        }

        .progress {
            margin-top: 28px;
            height: 8px;
            overflow: hidden;
            border-radius: 999px;
            background: #e2e8f0;
        }

        .progress > div {
            width: 100%;
            height: 100%;
            border-radius: 999px;
            background: linear-gradient(90deg, #0891b2, #06b6d4);
        }

        .status-card {
            margin-top: 28px;
            padding: 28px;
            background: linear-gradient(135deg, #f0fdfa 0%, white 100%);
            border: 1px solid rgba(6, 182, 212, 0.5);
        }

        .status-top {
            display: flex;
            gap: 18px;
            align-items: center;
        }

        .success-orb {
            width: 76px;
            height: 76px;
            border-radius: 20px;
            display: grid;
            place-items: center;
            background: rgba(6, 182, 212, 0.1);
            border: 1px solid rgba(6, 182, 212, 0.5);
            color: #0891b2;
            box-shadow: 0 0 20px rgba(6, 182, 212, 0.1);
        }

        .eyebrow {
            margin: 0;
            color: #64748b;
            font-size: 12px;
            letter-spacing: 0.24em;
            text-transform: uppercase;
        }

        .status-title {
            margin: 10px 0 0;
            font-size: 28px;
            line-height: 1.05;
            font-weight: 800;
            color: #0f172a;
        }

        .status-copy {
            margin: 12px 0 0;
            color: #475569;
            font-size: 15px;
            line-height: 1.85;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            margin-top: 22px;
        }

        .feature {
            padding: 14px 18px;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            background: white;
            color: #334155;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .feature:hover {
            border-color: #06b6d4;
            box-shadow: 0 2px 8px rgba(6, 182, 212, 0.1);
        }

        .feature span {
            color: #0891b2;
            margin-right: 10px;
            font-weight: 700;
        }

        .redirect-card {
            margin-top: 18px;
            padding: 16px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
        }

        .redirect-copy {
            color: #475569;
            font-size: 14px;
        }

        .redirect-copy strong {
            color: #0891b2;
        }

        .actions {
            margin-top: 28px;
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 48px;
            padding: 0 28px;
            border-radius: 999px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .button:hover {
            transform: translateY(-1px);
        }

        .button-primary {
            color: white;
            background: linear-gradient(135deg, #0891b2, #06b6d4);
            box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
        }

        .button-primary:hover {
            background: linear-gradient(135deg, #0e7a9a, #0891b2);
            box-shadow: 0 6px 16px rgba(6, 182, 212, 0.4);
        }

        .button-secondary {
            color: #334155;
            border: 1px solid #cbd5e1;
            background: white;
        }

        .button-secondary:hover {
            border-color: #06b6d4;
            background: #f0fdfa;
            color: #0891b2;
        }

        .support {
            margin-top: 18px;
            color: #64748b;
            font-size: 13px;
        }

        .support a {
            color: #0891b2;
            text-decoration: none;
            font-weight: 600;
        }

        .support a:hover {
            text-decoration: underline;
        }

        .mobile-brand {
            display: none;
            margin-bottom: 28px;
        }

        .mobile-brand .chip {
            margin-bottom: 20px;
        }

        @media (max-width: 1024px) {
            .card {
                grid-template-columns: 1fr;
            }

            .left-panel {
                display: none;
            }

            .mobile-brand {
                display: block;
            }
        }

        @media (max-width: 640px) {
            body {
                padding: 14px;
            }

            .right-panel {
                padding: 24px;
            }

            .section-head h2 {
                font-size: 38px;
            }

            .features {
                grid-template-columns: 1fr;
            }

            .actions {
                flex-direction: column;
            }

            .button {
                width: 100%;
            }

            .status-top {
                align-items: flex-start;
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <svg class="bg-car left" viewBox="0 0 64 64" fill="none" aria-hidden="true">
        <path d="M15 18h34l5 16v16a4 4 0 0 1-4 4h-4a6 6 0 1 1-12 0H30a6 6 0 1 1-12 0h-4a4 4 0 0 1-4-4V34l5-16Z" stroke="currentColor" stroke-width="3" />
        <path d="M21 18l3-7h16l3 7" stroke="currentColor" stroke-width="3" />
        <circle cx="22" cy="46" r="5" fill="currentColor" />
        <circle cx="42" cy="46" r="5" fill="currentColor" />
    </svg>

    <svg class="bg-car right" viewBox="0 0 64 64" fill="none" aria-hidden="true">
        <path d="M15 18h34l5 16v16a4 4 0 0 1-4 4h-4a6 6 0 1 1-12 0H30a6 6 0 1 1-12 0h-4a4 4 0 0 1-4-4V34l5-16Z" stroke="currentColor" stroke-width="3" />
        <path d="M21 18l3-7h16l3 7" stroke="currentColor" stroke-width="3" />
        <circle cx="22" cy="46" r="5" fill="currentColor" />
        <circle cx="42" cy="46" r="5" fill="currentColor" />
    </svg>

    <div class="shell">
        <div class="card">
            <section class="left-panel">
                <div class="chip">
                    <span>✓</span>
                    Email confirmed
                </div>

                <div class="hero">
                    <h1><span class="accent">Welcome to</span><br>Byahero</h1>
                    <p>Your account is fully verified. You can now sign in, browse rental-ready vehicles, and manage your bookings from the same streamlined BYAHERO experience.</p>
                </div>

                <div class="stack">
                    <div class="step-card">
                        <div class="step-icon">✓</div>
                        <div>
                            <p class="step-title">Email verified</p>
                            <p class="step-copy">Your account activation is complete and your email address is now trusted.</p>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-icon">→</div>
                        <div>
                            <p class="step-title">Login unlocked</p>
                            <p class="step-copy">You can sign in immediately and continue with your car rental journey.</p>
                        </div>
                    </div>
                    <div class="step-card">
                        <div class="step-icon">★</div>
                        <div>
                            <p class="step-title">Ready to book</p>
                            <p class="step-copy">Browse fleet listings, manage bookings, and update your profile anytime.</p>
                        </div>
                    </div>
                </div>

                <div class="info-card">
                    <div class="step-icon">⚡</div>
                    <div>
                        <p class="step-title" style="font-size:16px;">Verification complete</p>
                        <p class="step-copy">Redirecting you to the login page automatically so you can sign in right away.</p>
                    </div>
                </div>
            </section>

            <section class="right-panel">
                <div class="mobile-brand">
                    <div class="chip">
                        <span>✓</span>
                        Email confirmed
                    </div>
                </div>

                <div class="section-head">
                    <div>
                        <h2>Email Verified</h2>
                        <p>Your BYAHERO account is now active and ready for secure sign-in.</p>
                    </div>
                    <div class="status-meta">
                        <div class="label">Status</div>
                        <div class="value">Done</div>
                    </div>
                </div>

                <div class="progress">
                    <div></div>
                </div>

                <div class="status-card">
                    <div class="status-top">
                        <div class="success-orb">
                            <svg width="34" height="34" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                <path d="m5 12 5 5L20 7" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div>
                            <p class="eyebrow">Verification success</p>
                            <h3 class="status-title">Email verified successfully</h3>
                            <p class="status-copy">Your email has been confirmed. You can now log in to your account and access the full renter dashboard.</p>
                        </div>
                    </div>

                    <div class="features">
                        <div class="feature"><span>✓</span>Instant account access</div>
                        <div class="feature"><span>✓</span>Browse and rent cars</div>
                    </div>

                    <div class="redirect-card">
                        <div class="redirect-copy">
                            Redirecting to login in <strong id="countdown">10</strong> seconds.
                        </div>
                    </div>

                    <div class="actions">
                        <a href="http://localhost:3000/login" class="button button-primary">Go to Login</a>
                        <a href="http://localhost:3000/" class="button button-secondary">Back to Home</a>
                    </div>

                    <p class="support">
                        Need help instead?
                        <a href="http://localhost:3000/support">Contact support</a>
                    </p>
                </div>
            </section>
        </div>
    </div>

    <script>
        let countdown = 10;
        const countdownElement = document.getElementById("countdown");

        const timer = setInterval(() => {
            countdown -= 1;
            if (countdownElement) {
                countdownElement.textContent = String(countdown);
            }

            if (countdown <= 0) {
                clearInterval(timer);
                window.location.href = "http://localhost:3000/login";
            }
        }, 1000);
    </script>
</body>
</html>