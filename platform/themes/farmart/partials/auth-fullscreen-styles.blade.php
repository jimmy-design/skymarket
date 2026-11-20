<style>
        html, body.auth-fullscreen-page { height: 100%; margin: 0; }
        body.auth-fullscreen-page { background: #fff8e9; color: #28251f; font-family: inherit; overflow: hidden; }
        body.auth-fullscreen-page > *:not(.auth-screen) { display: none !important; }
        body.auth-fullscreen-page .auth-screen { align-items: stretch; background: linear-gradient(145deg, #e8e9e9 0%, #f5f0df 48%, #fff0c9 100%); border-radius: 32px; display: grid; gap: 18px; grid-template-columns: minmax(0, .88fr) minmax(0, 1.12fr); height: calc(100vh - 20px); margin: 10px; min-height: 0; padding: 10px; }
        body.auth-fullscreen-page .auth-screen__form { --auth-content-width: min(100%, 430px); align-self: center; justify-self: center; max-width: 540px; padding: 20px 42px; width: 100%; }
        body.auth-fullscreen-page .auth-brand { align-items: center; border: 1px solid rgba(50,50,50,.25); border-radius: 999px; color: #28251f; display: inline-flex; font-size: 14px; font-weight: 550; letter-spacing: -.02em; margin-bottom: 44px; padding: 7px 16px; text-decoration: none; }
        body.auth-fullscreen-page .auth-brand img { max-height: 38px; max-width: 150px; object-fit: contain; }
        body.auth-fullscreen-page .auth-card { background: transparent !important; border: 0; border-radius: 0; box-shadow: none; margin: 0; overflow: visible; }
        body.auth-fullscreen-page .auth-screen__form > .container { margin: 0 auto; max-width: var(--auth-content-width); padding-left: 0; padding-right: 0; width: var(--auth-content-width); }
        body.auth-fullscreen-page .auth-screen__form .container > .row { margin-left: 0 !important; margin-right: 0 !important; width: 100% !important; }
        body.auth-fullscreen-page .auth-screen__form .container > .row.justify-content-center.py-5 { padding-bottom: 0 !important; padding-top: 0 !important; }
        body.auth-fullscreen-page .auth-screen__form .container > .row > .col-xl-6 { flex: 0 0 100% !important; max-width: 100% !important; padding-left: 0 !important; padding-right: 0 !important; width: 100% !important; }
        body.auth-fullscreen-page .auth-screen__form .auth-card { max-width: none !important; width: 100% !important; }
        body.auth-fullscreen-page .auth-card__banner, body.auth-fullscreen-page .auth-card__header-icon { display: none !important; }
        body.auth-fullscreen-page .auth-card .card-header, body.auth-fullscreen-page .auth-card .card-body { background: transparent !important; border: 0; padding: 0 !important; }
        body.auth-fullscreen-page .auth-card__header { margin-bottom: 20px; }
        body.auth-fullscreen-page .auth-card__header-title { color: #29261f; font-size: clamp(27px, 2.6vw, 34px); font-weight: 600; letter-spacing: -.05em; margin-bottom: 5px !important; }
        body.auth-fullscreen-page .auth-card__header-description { color: #777064; margin: 0; }
        body.auth-fullscreen-page .auth-card .form-label { color: #625c50; font-size: 12px; font-weight: 500; margin-bottom: 4px; }
        body.auth-fullscreen-page .auth-card .form-group { margin-bottom: 11px !important; }
        body.auth-fullscreen-page .auth-card form, body.auth-fullscreen-page .auth-card .form-group, body.auth-fullscreen-page .auth-card .position-relative { max-width: 100% !important; width: 100% !important; }
        body.auth-fullscreen-page .auth-card form > * { box-sizing: border-box; max-width: 100% !important; width: 100% !important; }
        body.auth-fullscreen-page .auth-card .form-control { background: rgba(255,255,255,.86); border: 1px solid rgba(120,99,61,.12); border-radius: 22px; font-size: 13px; min-height: 42px; max-width: 100% !important; padding: 9px 16px; width: 100% !important; }
        body.auth-fullscreen-page .auth-card .form-control:focus { border-color: #e4ae3e; box-shadow: 0 0 0 3px rgba(228,174,62,.17) !important; }
        body.auth-fullscreen-page .auth-card .btn-auth-submit { background: linear-gradient(100deg, #ffdc68 0%, #ffc74c 100%); border: 0; border-radius: 24px; color: #35290e; font-size: 14px; font-weight: 650; min-height: 46px; width: 100%; }
        body.auth-fullscreen-page .auth-screen__form--login .auth-card__header { text-align: center; }
        body.auth-fullscreen-page .auth-screen__form--login .auth-card form > :has(a[href*="register"]) { display: none !important; }
        body.auth-fullscreen-page .auth-screen__form--login .card-social-login-container { display: none !important; }
        body.auth-fullscreen-page .auth-social-buttons { display: flex; gap: 10px; margin: 12px auto 0; max-width: var(--auth-content-width); width: var(--auth-content-width); }
        body.auth-fullscreen-page .auth-social-button { align-items: center; background: rgba(255,255,255,.42); border: 1px solid rgba(92,77,49,.22); border-radius: 999px; color: #302d27 !important; display: inline-flex; flex: 1 1 0; font-size: 12px; font-weight: 500; gap: 8px; justify-content: center; min-height: 42px; padding: 7px 12px; text-decoration: none; transition: background .15s ease, border-color .15s ease; }
        body.auth-fullscreen-page .auth-social-button:hover { background: rgba(255,255,255,.9); border-color: rgba(92,77,49,.38); }
        body.auth-fullscreen-page .auth-social-button svg { flex: 0 0 17px; height: 17px; width: 17px; }
        body.auth-fullscreen-page .auth-social-button.is-unavailable { cursor: default; opacity: .72; }
        body.auth-fullscreen-page .auth-register-link { color: #777064; font-size: 12px; margin: 14px 0 0; text-align: center; }
        body.auth-fullscreen-page .auth-register-link a { font-weight: 600; }
        body.auth-register-page .auth-social-caption { color: #777064; font-size: 12px; margin: 10px 0 -4px; text-align: center; }
        body.auth-fullscreen-page .auth-screen__form--login .card-social-login-container { margin-top: 15px; }
        body.auth-fullscreen-page .auth-screen__form--login .card-social-login-container .social-login-text { display: none; }
        body.auth-fullscreen-page .auth-screen__form--login .card-social-login-content { display: flex; gap: 10px; }
        body.auth-fullscreen-page .auth-screen__form--login .card-social-login-content .sl-button { align-items: center; background: rgba(255,255,255,.52); border: 1px solid rgba(92,77,49,.2); border-radius: 999px; box-shadow: none; color: #302d27; display: flex; flex: 1 1 0; justify-content: center; margin: 0 !important; min-height: 44px; padding: 4px 12px; text-decoration: none; }
        body.auth-fullscreen-page .auth-screen__form--login .card-social-login-content .sl-button:hover { background: #fff; border-color: rgba(92,77,49,.38); }
        body.auth-fullscreen-page .auth-screen__form--login .card-social-login-content .sl-button-svg-container { padding: 4px; }
        body.auth-fullscreen-page .auth-screen__form--login .card-social-login-content .sl-button-label-container { font-size: 12px; line-height: 1.2; margin: 0 4px; padding: 4px; }
        body.auth-fullscreen-page .auth-screen__form--login .card-social-login-content svg { height: 19px; width: 19px; }
        body.auth-fullscreen-page .auth-card a { color: #976610; }
        body.auth-fullscreen-page .auth-screen__legal { color: #8d8679; font-size: 11px; margin: 20px 0 0; }
        body.auth-fullscreen-page .auth-screen__visual { background-color: #77766f; background-image: url('https://images.unsplash.com/photo-1521737711867-e3b97375f902?auto=format&fit=crop&w=1800&q=90'); background-position: center; background-size: cover; border-radius: 30px; height: calc(100vh - 24px); min-height: 0; overflow: hidden; position: relative; }
        body.auth-fullscreen-page .auth-screen__visual-wash { background: linear-gradient(145deg, rgba(255,191,72,.08), transparent 44%, rgba(31,28,23,.34)); inset: 0; position: absolute; }
        body.auth-fullscreen-page .auth-screen__badge { align-items: center; background: #ffdc68; border-radius: 12px; color: #49370e; display: flex; font-size: 12px; gap: 9px; left: 24px; padding: 11px 14px; position: absolute; top: 24px; }
        body.auth-fullscreen-page .auth-screen__badge-dot { background: #77550d; border-radius: 50%; height: 7px; width: 7px; }
        body.auth-fullscreen-page .auth-screen__quote { backdrop-filter: blur(12px); background: rgba(255,255,255,.91); border: 1px solid rgba(255,255,255,.7); border-radius: 16px; bottom: 24px; box-shadow: 0 18px 46px rgba(30,25,15,.2); display: grid; gap: 5px; left: 24px; max-width: 310px; padding: 16px 18px; position: absolute; }
        body.auth-fullscreen-page .auth-screen__quote strong { color: #343027; font-size: 16px; }
        body.auth-fullscreen-page .auth-screen__quote span { color: #716a5f; font-size: 13px; line-height: 1.55; }
        @media (max-width: 900px) { body.auth-fullscreen-page .auth-screen { display: flex; height: calc(100vh - 16px); justify-content: center; margin: 8px; padding: 8px; } body.auth-fullscreen-page .auth-screen__form { max-width: 560px; padding: 18px 24px; } body.auth-fullscreen-page .auth-screen__visual { display: none; } }
        @media (max-width: 640px) { body.auth-fullscreen-page .auth-screen { height: calc(100dvh - 16px); margin: 8px; padding: 8px; } body.auth-fullscreen-page .auth-screen__form { max-width: 520px; padding: 14px 18px; } body.auth-fullscreen-page .auth-brand { margin-bottom: 16px; } }
        @media (max-height: 860px) {
            body.auth-fullscreen-page .auth-screen__form { padding-bottom: 10px; padding-top: 10px; }
            body.auth-fullscreen-page .auth-brand { margin-bottom: 20px; }
            body.auth-fullscreen-page .auth-card__header { margin-bottom: 12px; }
            body.auth-fullscreen-page .auth-card .form-group { margin-bottom: 7px !important; }
            body.auth-fullscreen-page .auth-card .form-control { min-height: 38px; }
            body.auth-fullscreen-page .auth-card .btn-auth-submit { min-height: 42px; }
            body.auth-fullscreen-page .auth-social-buttons { margin-top: 8px; }
            body.auth-fullscreen-page .auth-register-link { margin-top: 9px; }
            body.auth-fullscreen-page .auth-screen__legal { margin-top: 9px; }
        }
            body.auth-register-page .auth-screen__form { --auth-content-width: min(100%, 540px); max-width: 640px; }
        body.auth-register-page .auth-brand { margin-bottom: 18px; }
        body.auth-register-page .auth-card__header { margin-bottom: 13px; text-align: center; }
        body.auth-register-page .auth-card__header-title { font-size: clamp(25px, 2.3vw, 31px); }
        body.auth-register-page .auth-card form { column-gap: 18px; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); row-gap: 2px; width: 100%; }
        body.auth-register-page .auth-card form > * { grid-column: span 1; min-width: 0; }
        body.auth-register-page .auth-card form .form-control { max-width: 100%; }
        body.auth-register-page .auth-card form > .form-group,
        body.auth-register-page .auth-card form > .mb-3 { margin-bottom: 8px !important; min-width: 0; }
        body.auth-register-page .auth-card form > :has([name="agree_terms_and_policy"]),
        body.auth-register-page .auth-card form > :has(.btn-auth-submit),
        body.auth-register-page .auth-card form > :has(a[href*="login"]),
        body.auth-register-page .auth-card form > :has(button[type="submit"]),
        body.auth-register-page .auth-card form > :has(input[type="submit"]) { grid-column: 1 / -1; }
        body.auth-register-page .auth-card form .form-check { align-items: flex-start; display: flex; gap: 7px; margin: 0; }
        body.auth-register-page .auth-card form .form-check-label { font-size: 11px; line-height: 1.35; }
        body.auth-register-page .auth-card .btn-auth-submit { border-radius: 24px; min-height: 42px; }
        body.auth-register-page .auth-card form > :has(a[href*="login"]) { display: none !important; }
        body.auth-register-page .card-social-login-container { display: none !important; }
        @media (max-width: 800px) { body.auth-register-page .auth-screen__form { padding-left: 8px; padding-right: 8px; } }
