<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta
        http-equiv="X-UA-Compatible"
        content="IE=edge"
    >
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=5, user-scalable=1"
    />
    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <style>
        :root {
            --primary-color: {{ theme_option('primary_color', '#fab528') }};
            --heading-color: {{ theme_option('heading_color', '#000') }};
            --text-color: {{ theme_option('text_color', '#000') }};
            --primary-button-color: {{ theme_option('primary_button_color', '#000') }};
            --primary-button-background-color: {{ theme_option('primary_button_background_color') ?: theme_option('primary_color', '#fab528') }};
            --top-header-background-color: {{ theme_option('top_header_background_color', '#f7f7f7') }};
            --top-header-text-color: {{ theme_option('top_header_text_color') ?: theme_option('header_text_color', '#000') }};
            --middle-header-background-color: {{ theme_option('middle_header_background_color', '#fff') }};
            --middle-header-text-color: {{ theme_option('middle_header_text_color') ?: theme_option('header_text_color', '#000') }};
            --bottom-header-background-color: {{ theme_option('bottom_header_background_color', '#fff') }};
            --bottom-header-text-color: {{ theme_option('bottom_header_text_color') ?: theme_option('header_text_color', '#000') }};
            --header-text-color: {{ theme_option('header_text_color', '#000') }};
            --header-text-secondary-color: {{ BaseHelper::hexToRgba(theme_option('header_text_color', '#000'), 0.5) }};
            --header-deliver-color: {{ BaseHelper::hexToRgba(theme_option('header_deliver_color', '#000'), 0.15) }};
            --footer-text-color: {{ theme_option('footer_text_color', '#555') }};
            --footer-heading-color: {{ theme_option('footer_heading_color', '#555') }};
            --footer-hover-color: {{ theme_option('footer_hover_color', '#fab528') }};
            --footer-border-color: {{ theme_option('footer_border_color', '#dee2e6') }};
        }

        .header-quick-actions {
            align-items: center;
            display: flex;
            flex-shrink: 0;
            gap: 10px;
            margin-right: 16px;
        }

        .header-wishlist,
        .btn-wishlist,
        .wishlist-button,
        .product-wishlist-button,
        a[href$="/wishlist"],
        a[href*="/wishlist?"] {
            display: none !important;
        }

        .header-quick-actions__item {
            align-items: center;
            background: rgba(255, 255, 255, 0.92);
            border: 2px solid rgba(255, 255, 255, 0.7);
            border-radius: 50%;
            color: #9eabb0;
            display: inline-flex;
            height: 46px;
            justify-content: center;
            padding: 0;
            width: 46px;
        }

        .header-quick-actions--guest .header-quick-actions__item,
        .header-quick-actions--guest .header-account-menu__chevron,
        .header-quick-actions--guest .header-account-menu__dropdown {
            display: none !important;
        }

        .header-quick-actions__badge {
            align-items: center;
            background: #e53935;
            border: 2px solid #fff;
            border-radius: 999px;
            color: #fff;
            display: flex;
            font-size: 10px;
            font-weight: 700;
            height: 20px;
            justify-content: center;
            min-width: 20px;
            padding: 0 4px;
            position: absolute;
            right: -4px;
            top: -5px;
        }

        .header-quick-actions__icon {
            font-family: "Segoe UI Emoji", "Apple Color Emoji", sans-serif;
            font-size: 27px;
            line-height: 1;
        }

        .header-quick-actions__item--notifications {
            background: #f7f9fa;
            position: relative;
        }

        .header-quick-actions__item--notifications .header-quick-actions__icon {
            display: inline-flex;
            height: 35px;
            width: 35px;
        }

        .header-quick-actions__item--notifications .header-quick-actions__icon svg {
            display: block;
            height: 100%;
            width: 100%;
        }

        .header-quick-actions__item--premium {
            background: #fff8df;
        }

        .header-quick-actions__item--premium .header-quick-actions__icon {
            align-items: center;
            display: inline-flex;
            height: 38px;
            justify-content: center;
            width: 38px;
        }

        .header-quick-actions__item--premium .header-quick-actions__icon svg {
            height: 100%;
            overflow: visible;
            width: 100%;
        }

        .header-quick-actions__item--account {
            background: #e9f9ed;
        }

        .header-account-menu {
        }

        .header .header-middle .header__center .search-input-rotator {
            flex: 1 1 auto;
            min-width: 0;
            position: relative;
        }

        .header .header-middle .header__center .search-input-rotator .input-search-product {
            height: 100%;
            width: 100%;
        }

        .header-account-menu__trigger {
            align-items: center;
            color: var(--middle-header-text-color);
            display: inline-flex;
            gap: 7px;
        }

        .header-account-menu__trigger:hover {
            color: var(--primary-color);
        }

        .header-account-menu__label {
            color: #1f2933;
            font-size: 14px;
            font-weight: 700;
            white-space: nowrap;
        }

        .header-account-menu__chevron {
            display: inline-flex;
            font-size: 9px;
        }

        .header-account-menu__dropdown {
            background: #fff;
            border: 1px solid #e6e8eb;
            border-radius: 8px;
            box-shadow: 0 10px 28px rgba(20, 30, 40, 0.16);
            min-width: 190px;
            opacity: 0;
            padding: 7px 0;
            position: absolute;
            right: 0;
            top: calc(100% + 8px);
            transform: translateY(5px);
            transition: opacity 0.18s ease, transform 0.18s ease, visibility 0.18s ease;
            visibility: hidden;
            z-index: 1100;
        }

        .header-account-menu__dropdown::before {
            content: "";
            height: 10px;
            left: 0;
            position: absolute;
            right: 0;
            top: -10px;
        }

        .header-account-menu:hover .header-account-menu__dropdown,
        .header-account-menu:focus-within .header-account-menu__dropdown {
            opacity: 1;
            transform: translateY(0);
            visibility: visible;
        }

        .header-account-menu__dropdown a {
            color: #263238;
            display: block;
            font-size: 14px;
            padding: 10px 16px;
            white-space: nowrap;
        }

        .header-account-menu__dropdown a:hover,
        .header-account-menu__dropdown a:focus {
            background: #f3f7f4;
            color: var(--primary-color);
        }

        .header-account-menu__dropdown a:last-child {
            border-top: 1px solid #eceff1;
            color: #d64545;
            margin-top: 4px;
        }

        .header-quick-actions__item:hover {
            color: var(--primary-color);
            transform: translateY(-1px);
        }

        .header-quick-actions__sell {
            align-items: center;
            background: #ffa33b;
            border-radius: 12px;
            color: #fff;
            display: inline-flex;
            font-size: 16px;
            font-weight: 700;
            height: 46px;
            justify-content: center;
            min-width: 145px;
            padding: 0 24px;
        }

        .header-quick-actions__sell:hover {
            background: #f49326;
            color: #fff;
        }

        @media (max-width: 1399px) {
            .header-quick-actions {
                gap: 6px;
                margin-right: 10px;
            }

            .header-quick-actions__item {
                height: 40px;
                width: 40px;
            }

            .header-quick-actions__sell {
                min-width: 120px;
                padding: 0 16px;
            }
        }

        #page-auth .auth-card {
            background-color: #fdfefe !important;
            border-radius: 24px;
            box-shadow: 0 30px 75px rgba(20, 35, 28, 0.3), 0 10px 30px rgba(20, 35, 28, 0.18), 0 2px 8px rgba(20, 35, 28, 0.1);
            overflow: hidden;
        }

        #page-auth .auth-card .card-header,
        #page-auth .auth-card .card-body {
            background-color: #fdfefe !important;
        }

        #page-auth .auth-card .form-control {
            border-radius: 999px;
            min-height: 46px;
        }

        #page-auth .auth-card .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb), 0.14) !important;
        }

        #page-auth .auth-card .btn-auth-submit {
            border-radius: 999px;
            min-height: 46px;
        }

        .header .header-top {
            background-color: #f68b1e !important;
            border-bottom: 0 !important;
            box-shadow: 0 3px 10px rgba(95, 48, 0, 0.2);
            min-height: 64px;
            overflow: hidden;
            padding: 0 !important;
            position: relative;
            z-index: 3;
        }

        .header .header-top.header-top--original {
            min-height: 38px;
            overflow: visible;
            padding: 11px 0 !important;
        }

        .top-ad-slider {
            display: flex;
            overflow: hidden;
            width: 100%;
        }

        .top-customer-ads {
            background: #f68b1e;
            height: 72px;
            overflow: hidden;
            position: relative;
            touch-action: pan-y;
            width: 100%;
        }

        .top-customer-ads__track {
            display: flex;
            height: 100%;
            transition: transform 0.25s ease-out;
            will-change: transform;
        }

        .top-customer-ads__slide {
            flex: 0 0 100%;
            height: 100%;
            min-width: 0;
        }

        .top-customer-ads__slide a,
        .top-customer-ads__slide picture {
            display: block;
            height: 100%;
            width: 100%;
        }

        .top-customer-ads__slide img {
            height: 100%;
            object-fit: cover;
            object-position: center;
            width: 100%;
        }

        .top-customer-ads__arrow {
            align-items: center;
            background: rgba(0, 0, 0, 0.35);
            border: 0;
            border-radius: 50%;
            color: #fff;
            display: flex;
            height: 30px;
            justify-content: center;
            opacity: 0;
            padding: 0;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            transition: opacity 0.2s ease, background-color 0.2s ease;
            width: 30px;
            z-index: 2;
        }

        .top-customer-ads:hover .top-customer-ads__arrow,
        .top-customer-ads__arrow:focus-visible {
            opacity: 1;
        }

        .top-customer-ads__arrow:hover {
            background: rgba(0, 0, 0, 0.65);
        }

        .top-customer-ads__arrow--previous {
            left: 12px;
        }

        .top-customer-ads__arrow--next {
            right: 12px;
        }

        .top-customer-ads__arrow svg {
            fill: currentColor;
            height: 11px;
            width: 11px;
        }

        .top-customer-ads__arrow--previous svg {
            transform: rotate(90deg);
        }

        .top-customer-ads__arrow--next svg {
            transform: rotate(-90deg);
        }

        .top-customer-ads__dots {
            bottom: 5px;
            display: flex;
            gap: 5px;
            justify-content: center;
            left: 50%;
            position: absolute;
            transform: translateX(-50%);
            z-index: 2;
        }

        .top-customer-ads__dot {
            background: rgba(255, 255, 255, 0.55);
            border: 0;
            border-radius: 50%;
            height: 6px;
            padding: 0;
            width: 6px;
        }

        .top-customer-ads__dot.is-active {
            background: #fff;
            transform: scale(1.25);
        }

        .top-ad-slider__track {
            animation: top-ad-slide 32s linear infinite;
            display: flex;
            flex: 0 0 auto;
            width: max-content;
        }

        .top-ad-slider:hover .top-ad-slider__track {
            animation-play-state: paused;
        }

        .top-ad-slider__group {
            align-items: stretch;
            display: flex;
            flex: 0 0 auto;
        }

        .top-ad-slider__item {
            align-items: center;
            border-right: 1px solid rgba(255, 255, 255, 0.22);
            color: #fff;
            display: flex;
            flex: 0 0 auto;
            gap: 10px;
            justify-content: center;
            min-height: 64px;
            padding: 8px 38px;
            white-space: nowrap;
        }

        .top-ad-slider__item:hover {
            color: #fff;
        }

        .top-ad-slider__icon {
            align-items: center;
            display: inline-flex;
            flex: 0 0 auto;
            height: 34px;
            justify-content: center;
            width: 34px;
        }

        .top-ad-slider__icon svg {
            fill: currentColor;
            height: 100%;
            width: 100%;
        }

        .top-ad-slider__copy {
            display: flex;
            flex-direction: column;
            font-size: 17px;
            font-weight: 700;
            line-height: 0.95;
        }

        .top-ad-slider__copy small {
            color: inherit;
            font-size: 13px;
            font-weight: 600;
            margin-top: 4px;
        }

        .top-ad-slider__item--cta {
            background: #e2f5ed;
            color: #111;
            min-width: 220px;
            padding-left: 34px;
            padding-right: 34px;
        }

        .top-ad-slider__item--cta:hover {
            background: #d2ecdf;
            color: #111;
        }

        .top-ad-slider__button {
            background: #111;
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            padding: 10px 22px;
            text-transform: uppercase;
        }

        @keyframes top-ad-slide {
            to {
                transform: translateX(-50%);
            }
        }

        @media (max-width: 767px) {
            .header .header-top:not(.header-top--original) {
                display: block !important;
                min-height: 52px;
            }

            .top-customer-ads {
                height: 54px;
            }

            .top-ad-slider__item {
                min-height: 52px;
                padding: 6px 24px;
            }

            .top-ad-slider__icon {
                height: 27px;
                width: 27px;
            }

            .top-ad-slider__copy {
                font-size: 14px;
            }

            .top-ad-slider__copy small {
                font-size: 11px;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .top-ad-slider__track {
                animation-play-state: paused;
            }
        }

        .header .header-top .header-info > ul > li > a,
        .header .header-top .header-info > ul > li > a span,
        .header .header-top .header-info-right > ul > li,
        .header .header-top .header-info-right > ul > li > span,
        .header .header-top .header-info-right > ul > li > span a {
            color: #fff !important;
            font-weight: 600;
        }

        .header .header-top .header-info > ul > li::before {
            background-color: rgba(255, 255, 255, 0.45) !important;
        }

        .header .header-top .header-info > ul > li > a:hover,
        .header .header-top .header-info > ul > li > a:hover span,
        .header .header-top .header-info-right > ul > li > span a:hover {
            color: #402000 !important;
        }

        .header .header-middle .header__center .form--quick-search {
            background-color: #fff;
            border: 2px solid rgba(255, 255, 255, 0.95);
            border-radius: 27px;
            box-shadow: 0 7px 20px rgba(15, 35, 25, 0.22);
            position: relative;
        }

        .header .header-middle .header__center .search-category-rotator {
            color: #68737a;
            font-size: 14px;
            font-weight: 700;
            left: 20px;
            line-height: 20px;
            max-width: calc(100% - 40px);
            overflow: hidden;
            pointer-events: none;
            position: absolute;
            text-overflow: ellipsis;
            top: 50%;
            transform: translateY(-50%);
            white-space: nowrap;
            z-index: 2;
        }

        .header .header-middle .header__center .search-category-rotator.is-sliding span {
            animation: search-category-slide-up 0.42s ease both;
            display: inline-block;
        }

        .header .header-middle .header__center .search-input-rotator.has-search-value .search-category-rotator,
        .header .header-middle .header__center .search-input-rotator:focus-within .search-category-rotator {
            opacity: 0;
            visibility: hidden;
        }

        @keyframes search-category-slide-up {
            from {
                opacity: 0;
                transform: translateY(16px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .header .header-middle .header__center .search-category-rotator.is-sliding span {
                animation: none;
            }
        }

        .header .header-middle .header__center .form--quick-search .product-category-label,
        .header .header-middle .header__center .form--quick-search input[type="text"] {
            background-color: #fff !important;
        }

        .header .header-middle .header__center .form--quick-search input[type="text"]::placeholder {
            color: #68737a;
            font-weight: 600;
            opacity: 1;
        }

        .header .header-middle .header__center .form--quick-search button {
            background-color: #f68b1e !important;
            min-width: 58px;
        }

        .header .header-middle .header__center .form--quick-search button .svg-icon {
            color: #fff !important;
            font-size: 21px;
        }

        .header .header-middle .header__center .form--quick-search button .svg-icon svg {
            stroke: currentColor;
            stroke-width: 2;
            stroke-linejoin: round;
            filter: drop-shadow(0 2px 2px rgba(70, 32, 0, 0.45));
            transform: scale(1);
        }

        .header .header-middle .header__center .form--quick-search:focus-within {
            border-color: #ffc27a;
            box-shadow: 0 0 0 4px rgba(246, 139, 30, 0.22), 0 9px 24px rgba(15, 35, 25, 0.25);
        }

        .navigation__left {
            position: relative;
            z-index: 10;
        }

        .navigation__left .menu--product-categories {
            background: #f68b1e !important;
            color: #fff !important;
            position: relative;
            z-index: 20;
        }

        .navigation__left .menu--product-categories:hover {
            background: #df7610 !important;
        }

        .navigation__left .menu--product-categories .menu__toggle-title,
        .navigation__left .menu--product-categories .svg-icon {
            color: #fff !important;
        }

        .navigation__left .menu--product-categories svg,
        .navigation__left .menu--product-categories use {
            fill: currentColor;
        }

        .product-inner .product__title,
        .product-inner .product__title a {
            color: #000 !important;
        }

        .product-price {
            color: #000 !important;
            font-size: 18px;
        }

        .product-content-box {
            border-radius: 12px;
            overflow: hidden;
        }

        .product-inner .product-button .add-to-cart-button {
            background: linear-gradient(90deg, #3a7bd5, #5d26c1, #e94057);
            border: 0;
            border-radius: 24px !important;
            color: #fff !important;
            font-weight: 600;
            padding-left: 7px;
            padding-right: 7px;
            text-transform: uppercase;
            transition: all 0.3s ease;
            width: 100%;
        }

        .product-inner .product-button .add-to-cart-button:hover {
            background: linear-gradient(90deg, #e94057, #5d26c1, #3a7bd5);
            transform: scale(1.03);
        }

        .vendor-verified-badge {
            align-items: center;
            background: #1687f8;
            box-shadow: 0 1px 3px rgba(0, 75, 160, 0.3);
            clip-path: polygon(
                50% 0%, 61% 12%, 75% 7%, 81% 22%, 97% 25%, 91% 41%,
                100% 50%, 88% 61%, 93% 75%, 78% 81%, 75% 97%, 59% 91%,
                50% 100%, 39% 88%, 25% 93%, 19% 78%, 3% 75%, 9% 59%,
                0% 50%, 12% 39%, 7% 25%, 22% 19%, 25% 3%, 41% 9%
            );
            color: #fff;
            display: inline-flex;
            flex: 0 0 auto;
            font-family: Arial, sans-serif;
            font-size: 11px;
            font-weight: 700;
            height: 17px;
            justify-content: center;
            line-height: 1;
            margin-left: 5px;
            vertical-align: middle;
            width: 17px;
        }
    </style>

    @php
        Theme::asset()->remove('language-css');
        Theme::asset()
            ->container('footer')
            ->remove('language-public-js');
        Theme::asset()
            ->container('footer')
            ->remove('simple-slider-owl-carousel-css');
        Theme::asset()
            ->container('footer')
            ->remove('simple-slider-owl-carousel-js');
        Theme::asset()
            ->container('footer')
            ->remove('simple-slider-css');
        Theme::asset()
            ->container('footer')
            ->remove('simple-slider-js');
    @endphp

    {!! Theme::header() !!}
    <link rel="stylesheet" href="{{ asset('css/skymarket-product-grid.css') }}?v={{ filemtime(public_path('css/skymarket-product-grid.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/skymarket-responsive.css') }}?v={{ filemtime(public_path('css/skymarket-responsive.css')) }}">
    <script src="{{ asset('js/skymarket-product-images.js') }}?v={{ filemtime(public_path('js/skymarket-product-images.js')) }}" defer></script>
    @if (request()->routeIs('public.index') || request()->boolean('homepage_catalog'))
        <script src="{{ asset('js/skymarket-seller-banner.js') }}?v={{ filemtime(public_path('js/skymarket-seller-banner.js')) }}" defer></script>
    @endif

    <style>
        .section-content.section-content__slider {
            padding: 10px 15px;
        }

        .section-content.section-content__slider.is-hidden-by-search {
            display: none !important;
        }

        .section-content.section-content__slider .section-slides-wrapper .slick-slides-carousel:not(.slick-initialized),
        .section-content.section-content__slider .section-slides-wrapper .slide-item .slide-item__image,
        .section-content.section-content__slider .section-banner-wrapper .banner-medium .banner-item__image {
            background-color: #fff;
            height: 20vw;
            max-height: 280px;
        }

        .section-content.section-content__slider .slide-item__image a,
        .section-content.section-content__slider .slide-item__image picture {
            display: block;
            height: 100%;
            overflow: hidden;
            width: 100%;
        }

        .section-content.section-content__slider .slide-item__image img,
        .section-content.section-content__slider .banner-item__image img {
            display: block !important;
            height: 100% !important;
            max-height: 100% !important;
            max-width: 100% !important;
            object-fit: contain !important;
            object-position: center center !important;
            width: 100% !important;
        }

        @media (max-width: 767px) {
            .section-content.section-content__slider {
                padding: 8px 15px;
            }

            .section-content.section-content__slider .section-slides-wrapper .slick-slides-carousel:not(.slick-initialized),
            .section-content.section-content__slider .section-slides-wrapper .slide-item .slide-item__image {
                height: 32vw;
                max-height: 220px;
            }
        }

        .header .header-bottom .navigation__left .menu--product-categories,
        .header .header-bottom .navigation__left .menu--product-categories .menu__toggle {
            background: #f68b1e !important;
        }

        .header .header-bottom .navigation__left .menu--product-categories:hover,
        .header .header-bottom .navigation__left .menu--product-categories:hover .menu__toggle {
            background: #df7610 !important;
        }

        @media (min-width: 992px) {
            .header .header-bottom .navigation__center > .menu {
                align-items: center;
                display: flex;
                gap: 8px;
            }

            .header .header-bottom .navigation__center > .menu > li {
                margin: 0;
            }

            .header .header-bottom .navigation__center > .menu > li > a {
                align-items: center;
                background: #fff7ef;
                border: 1px solid #f3dcc7;
                border-radius: 999px;
                color: #202428;
                display: inline-flex;
                font-weight: 700;
                min-height: 38px;
                padding: 8px 16px;
                transition: background-color .2s ease, border-color .2s ease, color .2s ease, box-shadow .2s ease;
                white-space: nowrap;
            }

            .header .header-bottom .navigation__center > .menu > li:hover > a,
            .header .header-bottom .navigation__center > .menu > li.current > a {
                background: #f68b1e;
                border-color: #f68b1e;
                box-shadow: 0 4px 12px rgba(246, 139, 30, .22);
                color: #fff;
            }

            .header .header-bottom .navigation__center > .menu > li > a .svg-icon,
            .header .header-bottom .navigation__center > .menu > li > a svg {
                color: inherit;
                fill: currentColor;
            }

            .header .header-bottom .navigation__center > .menu > li > .sub-menu,
            .header .header-bottom .navigation__center > .menu > li > .mega-menu {
                background: #fffaf5;
                border: 1px solid #f3dcc7;
                border-radius: 8px;
                box-shadow: 0 14px 34px rgba(25, 30, 35, .14);
                margin-top: 9px;
                min-width: 250px;
                padding: 8px;
            }

            .header .header-bottom .navigation__center .sub-menu > li {
                border-bottom: 1px solid #f5e7da;
                position: relative;
            }

            .header .header-bottom .navigation__center .sub-menu > li:last-child {
                border-bottom: 0;
            }

            .header .header-bottom .navigation__center .sub-menu > li > a {
                align-items: center;
                border-radius: 6px;
                color: #30353a;
                display: flex;
                font-size: 14px;
                font-weight: 600;
                min-height: 40px;
                padding: 9px 34px 9px 12px;
                transition: background-color .18s ease, color .18s ease, padding-left .18s ease;
            }

            .header .header-bottom .navigation__center .sub-menu > li:hover > a,
            .header .header-bottom .navigation__center .sub-menu > li.current > a {
                background: #fff3e7;
                color: #d96800;
                padding-left: 16px;
            }

            .header .header-bottom .navigation__center .sub-menu > li.menu-item-has-children::after {
                border-right: 2px solid currentColor;
                border-top: 2px solid currentColor;
                color: #8b9196;
                content: '';
                height: 7px;
                pointer-events: none;
                position: absolute;
                right: 15px;
                top: 50%;
                transform: translateY(-50%) rotate(45deg);
                width: 7px;
            }

            .header .header-bottom .navigation__center .sub-menu > li.menu-item-has-children:hover::after {
                color: #f68b1e;
            }

            .header .header-bottom .navigation__center .sub-menu > li > .sub-menu {
                background: #fffaf5;
                border: 1px solid #f3dcc7;
                border-radius: 8px;
                box-shadow: 0 14px 34px rgba(25, 30, 35, .14);
                left: calc(100% + 8px);
                padding: 8px;
                top: -8px;
            }

            .header .header-bottom .navigation__center > .menu > li.menu-item-has-children > a::before {
                background: #fffaf5;
                border-color: #f3dcc7;
                height: 10px;
                top: calc(100% + 9px);
                width: 10px;
            }

            .header .header-bottom .navigation__center .mega-menu h4 {
                color: #202428;
                font-size: 15px;
                margin-bottom: 8px;
            }

            .header .header-bottom .navigation__center .mega-menu .mega-menu__list li a {
                border-radius: 6px;
                color: #4d5358;
                padding: 7px 9px;
                transition: background-color .18s ease, color .18s ease;
            }

            .header .header-bottom .navigation__center .mega-menu .mega-menu__list li a:hover {
                background: #fff3e7;
                color: #d96800;
            }
        }
    </style>
</head>

<body {!! Theme::bodyAttributes() !!}>
    @if (theme_option('preloader_enabled', 'yes') == 'yes')
        {!! Theme::partial('preloader') !!}
    @endif

    {!! Theme::partial('svg-icons') !!}
    {!! apply_filters(THEME_FRONT_BODY, null) !!}

    <header
        class="header header-js-handler"
        data-sticky="{{ theme_option('sticky_header_enabled', 'yes') == 'yes' ? 'true' : 'false' }}"
    >
        @php
            $topAdvertisementSlider = is_plugin_active('simple-slider')
                ? \Botble\SimpleSlider\Models\SimpleSlider::query()
                    ->with('sliderItems')
                    ->where('key', 'top-advertisement-slider')
                    ->where('status', \Botble\Base\Enums\BaseStatusEnum::PUBLISHED)
                    ->first()
                : null;
            $topAdvertisementSlides = $topAdvertisementSlider?->sliderItems ?? collect();
        @endphp
        <div @class([
            'header-top',
            'header-top--original d-none d-lg-block' => $topAdvertisementSlides->isEmpty(),
            'header-content-sticky' =>
                theme_option('sticky_header_content_position', 'middle') == 'top',
        ])>
            @if ($topAdvertisementSlides->isNotEmpty())
                <div class="top-customer-ads" data-top-customer-ads aria-label="{{ __('Customer advertisements') }}">
                    <div class="top-customer-ads__track">
                        @foreach ($topAdvertisementSlides as $slide)
                            <div class="top-customer-ads__slide">
                                @if ($slide->link)<a href="{{ url($slide->link) }}">@endif
                                    <img
                                        src="{{ RvMedia::getImageUrl($slide->image, null, false, RvMedia::getDefaultImage()) }}"
                                        alt="{{ $slide->title ?: __('Advertisement') }}"
                                        @if (! $loop->first) loading="lazy" @endif
                                    />
                                @if ($slide->link)</a>@endif
                            </div>
                        @endforeach
                    </div>
                    @if ($topAdvertisementSlides->count() > 1)
                        <button class="top-customer-ads__arrow top-customer-ads__arrow--previous" type="button" aria-label="{{ __('Previous advertisement') }}">
                            <svg><use href="#svg-icon-chevron-down" xlink:href="#svg-icon-chevron-down"></use></svg>
                        </button>
                        <button class="top-customer-ads__arrow top-customer-ads__arrow--next" type="button" aria-label="{{ __('Next advertisement') }}">
                            <svg><use href="#svg-icon-chevron-down" xlink:href="#svg-icon-chevron-down"></use></svg>
                        </button>
                        <div class="top-customer-ads__dots">
                            @foreach ($topAdvertisementSlides as $slide)
                                <button
                                    @class(['top-customer-ads__dot', 'is-active' => $loop->first])
                                    type="button"
                                    aria-label="{{ __('Show advertisement :number', ['number' => $loop->iteration]) }}"
                                    data-slide-index="{{ $loop->index }}"
                                ></button>
                            @endforeach
                        </div>
                    @endif
                </div>
            @else
                <div class="container-xxxl">
                    <div class="header-wrapper">
                        <div class="row align-items-center">
                            <div class="col-6">
                                <div class="header-info">
                                    {!! Menu::renderMenuLocation('header-navigation', ['view' => 'menu-default']) !!}
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="header-info header-info-right">
                                    <ul>
                                        @if (is_plugin_active('ecommerce'))
                                            @if (auth('customer')->check())
                                                <li><a href="{{ route('customer.overview') }}">{{ auth('customer')->user()->name }}</a></li>
                                                <li><a href="{{ route('customer.logout') }}">{{ __('Logout') }}</a></li>
                                            @else
                                                <li><a href="{{ route('customer.login') }}">{{ __('Login') }}</a></li>
                                                <li><a href="{{ route('customer.register') }}">{{ __('Register') }}</a></li>
                                            @endif
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        <div @class([
            'header-middle',
            'header-content-sticky' =>
                theme_option('sticky_header_content_position', 'middle') == 'middle',
        ])>
            <div class="container-xxxl">
                <div class="header-wrapper">
                    <div class="header-items header__left">
                        @if ($logo = theme_option('logo'))
                            @php
                                $height = theme_option('logo_height', 45);

                                $attributes = [
                                    'style' => sprintf('max-height: %s', is_numeric($height) ? "{$height}px" : $height),
                                    'loading' => false,
                                ];
                            @endphp
                            <div class="logo">
                                <a href="{{ BaseHelper::getHomepageUrl() }}">
                                    {{ RvMedia::image($logo, theme_option('site_title'), attributes: $attributes) }}
                                </a>
                            </div>
                        @endif
                    </div>
                    <div class="header-items header__center">
                        @if (is_plugin_active('ecommerce'))
                            @php
                                $searchParentCategories = \Botble\Ecommerce\Models\ProductCategory::query()
                                    ->where(function ($query) {
                                        $query->whereNull('parent_id')->orWhere('parent_id', 0);
                                    })
                                    ->where('status', \Botble\Base\Enums\BaseStatusEnum::PUBLISHED)
                                    ->orderBy('order')
                                    ->pluck('name')
                                    ->values();
                            @endphp
                            <form
                                class="form--quick-search bb-form-quick-search"
                                role="search"
                                action="{{ route('public.products') }}"
                                data-ajax-url="{{ route('public.ajax.search-products') }}"
                                method="GET"
                            >
                                <div
                                    class="form-group--icon"
                                    style="display: none"
                                >
                                    <div class="product-category-label" style="border-radius: 25px 0 0 25px">
                                        <label for="product-category-select" class="text">{{ __('All Categories') }}</label>
                                        <span class="svg-icon">
                                            <svg>
                                                <use
                                                    href="#svg-icon-chevron-down"
                                                    xlink:href="#svg-icon-chevron-down"
                                                ></use>
                                            </svg>
                                        </span>
                                    </div>
                                    <select
                                        name="categories[]"
                                        class="form-control product-category-select"
                                        id="product-category-select"
                                        data-bb-toggle="init-categories-dropdown"
                                        data-url="{{ route('public.ajax.categories-dropdown') }}"
                                    >
                                        <option value="">{{ __('All Categories') }}</option>
                                    </select>
                                </div>
                                <div class="search-input-rotator">
                                    <input
                                        type="text"
                                        name="q"
                                        class="form-control input-search-product"
                                        placeholder=""
                                        value="{{ BaseHelper::stringify(request()->query('q')) }}"
                                        autocomplete="off"
                                        aria-label="{{ __('Search for products, brands, models and more') }}"
                                        style="border-radius: 25px 0 0 25px"
                                    >
                                    <span
                                        class="search-category-rotator"
                                        data-categories='@json($searchParentCategories)'
                                        aria-hidden="true"
                                    >
                                        <span>{{ $searchParentCategories->isNotEmpty() ? $searchParentCategories->first() : __('Search for products, brands, models & more...') }}</span>
                                    </span>
                                </div>
                                <button
                                    class="btn"
                                    type="submit"
                                    aria-label="Submit"
                                    style="border-radius: 0 25px 25px 0"
                                >
                                    <span class="svg-icon">
                                        <svg>
                                            <use
                                                href="#svg-icon-search"
                                                xlink:href="#svg-icon-search"
                                            ></use>
                                        </svg>
                                    </span>
                                </button>
                                <div class="bb-quick-search-results"></div>
                            </form>
                        @endif
                    </div>
                    <div class="header-items header__right">
                        @if (theme_option('hotline'))
                            <div class="header__extra header-support">
                                <div class="header-box-content">
                                    <span>{{ theme_option('hotline') }}</span>
                                    <p>{{ __('Support 24/7') }}</p>
                                </div>
                            </div>
                        @endif

                        @if (is_plugin_active('ecommerce'))
                            @if (EcommerceHelper::isCompareEnabled())
                                <div class="header__extra header-compare">
                                    <a
                                        class="btn-compare"
                                        href="{{ route('public.compare') }}"
                                    >
                                        <i class="icon-repeat"></i>
                                        <span
                                            class="header-item-counter">{{ Cart::instance('compare')->count() }}</span>
                                    </a>
                                </div>
                            @endif
                            @if (is_plugin_active('marketplace'))
                                @php
                                    $headerCustomer = auth('customer')->user();
                                    $headerIsVendor = $headerCustomer?->is_vendor;
                                    $headerLoginUrl = route('customer.login');
                                    $headerMessageUrl = $headerIsVendor
                                        ? route('marketplace.vendor.messages.index')
                                        : ($headerCustomer ? route('customer.messages.index') : $headerLoginUrl);
                                    $headerUnreadMessages = 0;

                                    if ($headerCustomer) {
                                        $headerUnreadMessages = \Botble\Marketplace\Models\Message::query()
                                            ->when(
                                                $headerIsVendor,
                                                fn ($query) => $query
                                                    ->where('store_id', $headerCustomer->store->id)
                                                    ->where('sender_type', 'customer'),
                                                fn ($query) => $query
                                                    ->where('customer_id', $headerCustomer->id)
                                                    ->where('sender_type', 'vendor')
                                            )
                                            ->whereNull('read_at')
                                            ->count();
                                    }
                                @endphp
                                <nav class="header-quick-actions {{ $headerCustomer ? '' : 'header-quick-actions--guest' }}" aria-label="{{ __('Quick actions') }}">
                                    <a
                                        class="header-quick-actions__item header-quick-actions__item--notifications"
                                        href="{{ $headerMessageUrl }}"
                                        title="{{ __('Notifications') }}"
                                        aria-label="{{ __('Notifications') }}"
                                    >
                                        <span class="header-quick-actions__icon" aria-hidden="true">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9" />
                                                <path d="M10 21h4" />
                                            </svg>
                                        </span>
                                        @if ($headerCustomer)
                                            <span class="header-quick-actions__badge" data-sm-unread data-url="{{ route('public.messaging.unread') }}" style="{{ $headerUnreadMessages ? '' : 'display:none' }}">{{ $headerUnreadMessages > 99 ? '99+' : $headerUnreadMessages }}</span>
                                        @endif
                                    </a>
                                    <a
                                        class="header-quick-actions__item header-quick-actions__item--premium"
                                        href="{{ route('public.vendor-promotion-packages') }}"
                                        title="{{ __('Promotion Packages') }}"
                                        aria-label="{{ __('Promotion Packages') }}"
                                    >
                                        <span class="header-quick-actions__icon" aria-hidden="true">
                                            <svg viewBox="0 0 48 48">
                                                <defs>
                                                    <linearGradient id="premium-icon-gold" x1="8" y1="5" x2="40" y2="43" gradientUnits="userSpaceOnUse">
                                                        <stop stop-color="#fff176" />
                                                        <stop offset=".42" stop-color="#ffc400" />
                                                        <stop offset="1" stop-color="#e69a00" />
                                                    </linearGradient>
                                                </defs>
                                                <circle cx="24" cy="24" r="21" fill="#050505" stroke="url(#premium-icon-gold)" stroke-width="3" />
                                                <circle cx="28" cy="17" r="8" fill="url(#premium-icon-gold)" />
                                                <text x="28" y="21" fill="#050505" font-family="Arial, sans-serif" font-size="12" font-weight="900" text-anchor="middle">$</text>
                                                <path d="M9 28.5h7l7.2 5.1c1.6 1.1 3.8 1 5.2-.3L38 25.2c1.2-1 3-.9 4 .3 1 1.3.8 3.1-.5 4.1L29.2 40c-2 1.7-5 2-7.3.8L9 34.4v-5.9Z" fill="url(#premium-icon-gold)" />
                                                <path d="m19.5 30.9 7.4 3.5c1.5.7 3.3.1 4-1.4.7-1.4.1-3.1-1.3-3.9l-5.7-3.2" fill="none" stroke="#050505" stroke-linecap="round" stroke-width="2.1" />
                                            </svg>
                                        </span>
                                    </a>
                                    <div class="header-account-menu">
                                        <a
                                            class="header-account-menu__trigger"
                                            href="{{ $headerCustomer ? route('customer.overview') : $headerLoginUrl }}"
                                            title="{{ __('Account') }}"
                                            aria-label="{{ __('Account') }}"
                                            aria-haspopup="true"
                                        >
                                            <span class="header-quick-actions__item header-quick-actions__item--account">
                                                <span class="header-quick-actions__icon" aria-hidden="true">👤</span>
                                            </span>
                                            <span class="header-account-menu__label">{{ $headerCustomer ? __('Account') : __('Login') }}</span>
                                            <span class="header-account-menu__chevron svg-icon" aria-hidden="true">
                                                <svg>
                                                    <use href="#svg-icon-chevron-down" xlink:href="#svg-icon-chevron-down"></use>
                                                </svg>
                                            </span>
                                        </a>
                                        <div class="header-account-menu__dropdown">
                                            <a href="{{ $headerMessageUrl }}">{{ __('Messages') }}</a>
                                            <a href="{{ $headerIsVendor && $headerCustomer->store ? $headerCustomer->store->url : route('marketplace.vendor.become-vendor') }}">
                                                {{ __('My Shop') }}
                                            </a>
                                            <a href="{{ $headerIsVendor ? route('marketplace.vendor.dashboard') : ($headerCustomer ? route('customer.overview') : $headerLoginUrl) }}">
                                                {{ __('Statistics') }}
                                            </a>
                                            <a href="{{ $headerIsVendor ? route('marketplace.vendor.settings') : ($headerCustomer ? route('customer.edit-account') : $headerLoginUrl) }}">
                                                {{ __('Settings') }}
                                            </a>
                                            <a href="{{ $headerCustomer ? route('customer.logout') : $headerLoginUrl }}">
                                                {{ $headerCustomer ? __('Logout') : __('Login') }}
                                            </a>
                                        </div>
                                    </div>
                                    <a
                                        class="header-quick-actions__sell"
                                        href="{{ $headerIsVendor ? route('marketplace.vendor.products.create') : route('marketplace.vendor.become-vendor') }}"
                                    >{{ __('START SELLING') }}</a>
                                </nav>
                            @endif
                            @if (EcommerceHelper::isCartEnabled())
                                <div
                                    class="header__extra cart--mini"
                                    role="button"
                                    tabindex="0"
                                >
                                    <div class="header__extra">
                                        <a
                                            class="btn-shopping-cart"
                                            href="{{ route('public.cart') }}"
                                        >
                                            <span class="svg-icon">
                                                <svg>
                                                    <use
                                                        href="#svg-icon-cart"
                                                        xlink:href="#svg-icon-cart"
                                                    ></use>
                                                </svg>
                                            </span>
                                            <span
                                                class="header-item-counter">{{ Cart::instance('cart')->count() }}</span>
                                        </a>
                                        <span class="cart-text">
                                            <span class="cart-title">{{ __('Your Cart') }}</span>
                                            <span class="cart-price-total">
                                                <span class="cart-amount">
                                                    <bdi>
                                                        <span>{{ format_price(Cart::instance('cart')->rawSubTotal() + Cart::instance('cart')->rawTax()) }}</span>
                                                    </bdi>
                                                </span>
                                            </span>
                                        </span>
                                    </div>
                                    <div
                                        class="cart__content"
                                        id="cart-mobile"
                                    >
                                        <div class="backdrop"></div>
                                        <div class="mini-cart-content">
                                            <div class="widget-shopping-cart-content">
                                                {!! Theme::partial('cart-mini.list') !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div @class([
            'header-bottom',
            'header-content-sticky' =>
                theme_option('sticky_header_content_position', 'middle') == 'bottom',
        ])>
            <div class="header-wrapper">
                <nav class="navigation">
                    <div class="container-xxxl">
                        <div class="navigation__left">
                            @if (is_plugin_active('ecommerce') && theme_option('enabled_product_categories_on_header', 'yes') == 'yes')
                                <div class="menu--product-categories">
                                    <div class="menu__toggle">
                                        <span class="svg-icon">
                                            <svg>
                                                <use
                                                    href="#svg-icon-list"
                                                    xlink:href="#svg-icon-list"
                                                ></use>
                                            </svg>
                                        </span>
                                        <span class="menu__toggle-title">{{ __('Shop by Category') }}</span>
                                    </div>
                                    <div
                                        class="menu__content"
                                        data-bb-toggle="init-categories-dropdown"
                                        data-bb-target=".menu--dropdown"
                                        data-url="{{ route('public.ajax.categories-dropdown') }}"
                                    >
                                        <ul class="menu--dropdown"></ul>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div @class(['navigation__center', 'ps-0' => theme_option('enabled_product_categories_on_header', 'yes') != 'yes'])>
                            {!! Menu::renderMenuLocation('main-menu', [
                                'view' => 'menu',
                                'options' => ['class' => 'menu'],
                            ]) !!}
                        </div>
                        <div class="navigation__right">
                            @if (is_plugin_active('ecommerce') && EcommerceHelper::isEnabledCustomerRecentlyViewedProducts())
                                <div
                                    class="header-recently-viewed"
                                    data-url="{{ route('public.ajax.recently-viewed-products') }}"
                                    role="button"
                                >
                                    <h3 class="recently-title">
                                        <span class="svg-icon recent-icon">
                                            <svg>
                                                <use
                                                    href="#svg-icon-refresh"
                                                    xlink:href="#svg-icon-refresh"
                                                ></use>
                                            </svg>
                                        </span>
                                        {{ __('Recently Viewed') }}
                                    </h3>
                                    <div class="recently-viewed-inner container-xxxl">
                                        <div class="recently-viewed-content">
                                            <div class="loading--wrapper">
                                                <div class="loading"></div>
                                            </div>
                                            <div class="recently-viewed-products"></div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </nav>
            </div>
        </div>
        <div
            class="header-mobile header-js-handler"
            data-sticky="{{ theme_option('sticky_header_mobile_enabled', 'yes') == 'yes' ? 'true' : 'false' }}"
        >
            <div class="header-items-mobile header-items-mobile--left">
                <div class="menu-mobile">
                    <div class="menu-box-title">
                        <div
                            class="icon menu-icon toggle--sidebar"
                            href="#menu-mobile"
                        >
                            <span class="svg-icon">
                                <svg>
                                    <use
                                        href="#svg-icon-list"
                                        xlink:href="#svg-icon-list"
                                    ></use>
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="header-items-mobile header-items-mobile--center">
                @if ($logo)
                    <div class="logo">
                        <a href="{{ BaseHelper::getHomepageUrl() }}">
                            {{ RvMedia::image($logo, theme_option('site_title'), attributes: $attributes) }}
                        </a>
                    </div>
                @endif
            </div>
            <div class="header-items-mobile header-items-mobile--right">
                <div class="search-form--mobile search-form--mobile-right search-panel">
                    <a
                        class="open-search-panel toggle--sidebar"
                        href="#search-mobile"
                        title="{{ __('Search') }}"
                    >
                        <span class="svg-icon">
                            <svg>
                                <use
                                    href="#svg-icon-search"
                                    xlink:href="#svg-icon-search"
                                ></use>
                            </svg>
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.search-category-rotator').forEach(function (rotator) {
                var form = rotator.closest('.form--quick-search');
                var inputWrapper = rotator.closest('.search-input-rotator');
                var input = form ? form.querySelector('.input-search-product') : null;
                var label = rotator.querySelector('span');
                var categories = [];

                try {
                    categories = JSON.parse(rotator.dataset.categories || '[]');
                } catch (error) {
                    categories = [];
                }

                if (!inputWrapper || !input || !label || !categories.length) {
                    return;
                }

                var current = 0;
                var updateVisibility = function () {
                    inputWrapper.classList.toggle('has-search-value', input.value.trim().length > 0);
                };

                input.addEventListener('input', updateVisibility);
                updateVisibility();

                if (categories.length < 2) {
                    return;
                }

                window.setInterval(function () {
                    if (document.hidden || document.activeElement === input || input.value.trim()) {
                        return;
                    }

                    current = (current + 1) % categories.length;
                    label.textContent = categories[current];
                    rotator.classList.remove('is-sliding');
                    void rotator.offsetWidth;
                    rotator.classList.add('is-sliding');
                }, 2600);
            });

            var homepageCatalog = document.querySelector('.homepage-products-page');
            var headerSearchForm = document.querySelector('.header .header__center .bb-form-quick-search');

            if (homepageCatalog && headerSearchForm) {
                var headerSearchInput = headerSearchForm.querySelector('.input-search-product');
                var headerCategorySelect = headerSearchForm.querySelector('.product-category-select');
                var catalogForm = homepageCatalog.querySelector('#products-filter-form');
                var catalogListing = homepageCatalog.querySelector('.products-listing');
                var homepageSlider = document.querySelector('.section-content.section-content__slider');
                var liveSearchTimer = null;
                var liveSearchRequest = null;

                var updateHomepageProducts = function () {
                    if (!headerSearchInput || !catalogForm || !catalogListing) {
                        return;
                    }

                    window.clearTimeout(liveSearchTimer);
                    liveSearchTimer = window.setTimeout(function () {
                        var query = headerSearchInput.value.trim();
                        var category = headerCategorySelect ? headerCategorySelect.value : '';
                        var params = new URLSearchParams();

                        if (homepageSlider) {
                            homepageSlider.classList.toggle('is-hidden-by-search', Boolean(query || category));
                        }

                        if (query) {
                            params.set('q', query);
                        }

                        if (category) {
                            params.append('categories[]', category);
                        }

                        params.set('_', Date.now().toString());

                        if (liveSearchRequest) {
                            liveSearchRequest.abort();
                        }

                        liveSearchRequest = new AbortController();
                        catalogListing.classList.add('homepage-products-loading');

                        fetch(catalogForm.action + '?' + params.toString(), {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            signal: liveSearchRequest.signal
                        })
                            .then(function (response) {
                                if (!response.ok) {
                                    throw new Error('Search request failed');
                                }

                                return response.json();
                            })
                            .then(function (response) {
                                if (response.error || typeof response.data !== 'string') {
                                    return;
                                }

                                catalogListing.innerHTML = response.data;

                                var catalogQuery = catalogForm.querySelector('input[name="q"]');
                                if (catalogQuery) {
                                    catalogQuery.value = query;
                                }

                                if (window.MartApp && typeof window.MartApp.lazyLoad === 'function') {
                                    window.MartApp.lazyLoad(catalogListing);
                                }
                            })
                            .catch(function (error) {
                                if (error.name !== 'AbortError') {
                                    console.error(error);
                                }
                            })
                            .finally(function () {
                                catalogListing.classList.remove('homepage-products-loading');
                            });
                    }, 350);
                };

                headerSearchInput.addEventListener('input', updateHomepageProducts);

                if (headerCategorySelect) {
                    headerCategorySelect.addEventListener('change', updateHomepageProducts);
                }

                headerSearchForm.addEventListener('submit', function (event) {
                    event.preventDefault();
                    updateHomepageProducts();
                });
            }

            document.querySelectorAll('[data-top-customer-ads]').forEach(function (slider) {
                var track = slider.querySelector('.top-customer-ads__track');
                var slides = slider.querySelectorAll('.top-customer-ads__slide');
                var dots = slider.querySelectorAll('.top-customer-ads__dot');
                var previous = slider.querySelector('.top-customer-ads__arrow--previous');
                var next = slider.querySelector('.top-customer-ads__arrow--next');
                var current = 0;
                var autoplay = null;
                var pointerStart = null;

                if (!track || slides.length < 2) {
                    return;
                }

                var showSlide = function (index) {
                    current = (index + slides.length) % slides.length;
                    track.style.transform = 'translateX(-' + (current * 100) + '%)';
                    dots.forEach(function (dot, dotIndex) {
                        dot.classList.toggle('is-active', dotIndex === current);
                    });
                };

                var stopAutoplay = function () {
                    if (autoplay) {
                        window.clearInterval(autoplay);
                        autoplay = null;
                    }
                };

                var startAutoplay = function () {
                    stopAutoplay();
                    autoplay = window.setInterval(function () {
                        if (!document.hidden) {
                            showSlide(current + 1);
                        }
                    }, 5000);
                };

                if (previous) {
                    previous.addEventListener('click', function () {
                        showSlide(current - 1);
                        startAutoplay();
                    });
                }

                if (next) {
                    next.addEventListener('click', function () {
                        showSlide(current + 1);
                        startAutoplay();
                    });
                }

                dots.forEach(function (dot) {
                    dot.addEventListener('click', function () {
                        showSlide(Number(dot.dataset.slideIndex));
                        startAutoplay();
                    });
                });

                slider.addEventListener('mouseenter', stopAutoplay);
                slider.addEventListener('mouseleave', startAutoplay);
                slider.addEventListener('pointerdown', function (event) {
                    pointerStart = event.clientX;
                });
                slider.addEventListener('pointerup', function (event) {
                    if (pointerStart === null) {
                        return;
                    }

                    var distance = event.clientX - pointerStart;
                    pointerStart = null;

                    if (Math.abs(distance) > 45) {
                        showSlide(current + (distance < 0 ? 1 : -1));
                        startAutoplay();
                    }
                });

                startAutoplay();
            });
        });
    </script>
