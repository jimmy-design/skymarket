@php Theme::layout('full-width'); @endphp

<style>
    #page-auth .container > .row.justify-content-center.py-5 {
        padding-top: 20px !important;
    }

    #page-auth .auth-card__banner {
        display: block;
        height: 82px !important;
        margin: 18px auto 0 !important;
        max-width: 220px !important;
        object-fit: contain;
        width: auto !important;
    }

    #page-auth .auth-card__header-icon {
        display: none !important;
    }

    #page-auth .auth-card__header .gap-3 {
        gap: 0 !important;
    }
</style>

{!! $form->renderForm() !!}
