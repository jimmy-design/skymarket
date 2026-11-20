@php Theme::layout('full-width'); @endphp

<style>
    body#page-auth {
        background: #fff8e8;
        min-height: 100vh;
    }

    #page-auth #main-content {
        min-height: 100vh;
        padding: 12px;
    }

    #page-auth #main-content > .container,
    #page-auth #main-content .container:has(> .row.justify-content-center.py-5) {
        max-width: none;
        padding: 0;
        width: 100%;
    }

    #page-auth .container > .row.justify-content-center.py-5 {
        align-items: stretch;
        background: linear-gradient(125deg, #fffdf5 0%, #fff0c6 100%);
        border: 1px solid rgba(196, 137, 45, .14);
        border-radius: 28px;
        box-shadow: 0 20px 70px rgba(117, 83, 28, .12);
        display: flex;
        margin: 0;
        min-height: calc(100vh - 24px);
        overflow: hidden;
        padding: 0 !important;
    }

    #page-auth .container > .row.justify-content-center.py-5 > [class*="col-"] {
        display: flex;
        flex: 0 0 50%;
        max-width: 50%;
        padding: 0;
    }

    #page-auth .auth-card {
        align-self: center;
        background: transparent !important;
        border-radius: 0;
        box-shadow: none;
        margin: 0 auto;
        max-width: 500px;
        overflow: visible;
        padding: 42px clamp(28px, 7vw, 92px);
        width: 100%;
    }

    #page-auth .auth-card__banner { display: none !important; }
    #page-auth .auth-card__header-icon { display: none !important; }
    #page-auth .auth-card .card-header,
    #page-auth .auth-card .card-body {
        background: transparent !important;
        border: 0;
        padding: 0 !important;
    }

    #page-auth .auth-card__header { margin-bottom: 28px; }
    #page-auth .auth-card__header-title {
        color: #27251f;
        font-size: clamp(28px, 3vw, 38px);
        font-weight: 600;
        letter-spacing: -.045em;
        margin-bottom: 8px !important;
    }
    #page-auth .auth-card__header-description { color: #777064; margin: 0; }
    #page-auth .auth-card .form-label { color: #625c50; font-size: 13px; font-weight: 500; }
    #page-auth .auth-card .form-control {
        background: rgba(255,255,255,.84);
        border: 1px solid rgba(120, 99, 61, .12);
        border-radius: 14px;
        min-height: 50px;
    }
    #page-auth .auth-card .form-control:focus {
        border-color: #e6ad38;
        box-shadow: 0 0 0 3px rgba(230,173,56,.17) !important;
    }
    #page-auth .auth-card .btn-auth-submit {
        background: linear-gradient(100deg, #ffdc6a, #ffc94d);
        border: 0;
        border-radius: 14px;
        color: #34280d;
        font-weight: 600;
        min-height: 52px;
        transition: transform .2s ease, box-shadow .2s ease;
        width: 100%;
    }
    #page-auth .auth-card .btn-auth-submit:hover {
        box-shadow: 0 10px 24px rgba(218, 159, 37, .22);
        transform: translateY(-1px);
    }
    #page-auth .auth-card a { color: #9b6b13; }
    #page-auth .auth-card .auth-visual {
        background-color: #d8d4c9;
        background-image: linear-gradient(180deg, rgba(32,28,21,.02), rgba(32,28,21,.10)), url('https://images.unsplash.com/photo-1521737711867-e3b97375f902?auto=format&fit=crop&w=1600&q=85');
        background-position: center;
        background-size: cover;
        border-radius: 24px;
        min-height: min(84vh, 780px);
        overflow: hidden;
        position: relative;
        width: 100%;
    }
    #page-auth .auth-visual__note {
        background: rgba(255,255,255,.92);
        border: 1px solid rgba(255,255,255,.65);
        border-radius: 16px;
        bottom: 30px;
        box-shadow: 0 14px 40px rgba(30, 25, 15, .17);
        color: #39352d;
        left: 30px;
        max-width: 270px;
        padding: 16px 18px;
        position: absolute;
    }
    #page-auth .auth-visual__note strong { display: block; font-size: 14px; margin-bottom: 4px; }
    #page-auth .auth-visual__note span { color: #777064; font-size: 12px; }
    #page-auth .auth-visual__tag {
        background: #ffdc6a;
        border-radius: 12px;
        color: #48370f;
        font-size: 12px;
        left: 28px;
        padding: 11px 15px;
        position: absolute;
        top: 28px;
    }

    @media (max-width: 767px) {
        #page-auth #main-content { padding: 8px; }
        #page-auth .container > .row.justify-content-center.py-5 {
            border-radius: 22px;
            min-height: calc(100vh - 16px);
        }
        #page-auth .container > .row.justify-content-center.py-5 > [class*="col-"] {
            flex-basis: 100%;
            max-width: 100%;
        }
        #page-auth .auth-card { max-width: 540px; padding: 42px 28px; }
        #page-auth .auth-visual { display: none; }
    }
</style>

{!! $form->renderForm() !!}

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const row = document.querySelector('#page-auth .container > .row.justify-content-center.py-5');
        const column = row && row.querySelector('[class*="col-"]');
        if (!row || !column || row.querySelector('.auth-visual')) return;

        const visual = document.createElement('aside');
        visual.className = 'auth-visual';
        visual.setAttribute('aria-hidden', 'true');
        visual.innerHTML = '<div class="auth-visual__tag">A better way to shop and sell</div><div class="auth-visual__note"><strong>Everything you need, in one place</strong><span>Discover trusted products and connect with the people behind them.</span></div>';
        const visualColumn = document.createElement('div');
        visualColumn.className = column.className;
        visualColumn.setAttribute('aria-hidden', 'true');
        visualColumn.appendChild(visual);
        row.appendChild(visualColumn);
    });
</script>
