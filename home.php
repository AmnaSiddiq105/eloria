<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_name = $_SESSION['user_name'] ?? 'Beautiful';

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>ÉLORIA | A Little Luxury</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap"
          rel="stylesheet">

    <link rel="stylesheet" href="assets/css/home.css">

    <style>

        /* =========================================================
           ÉLORIA HOME PAGE
        ========================================================= */

        :root {
            --cream: #faf7f4;
            --soft-pink: #f4e4e5;
            --rose: #d7a8ad;
            --dark: #21191c;
            --muted: #76686b;
            --white: #ffffff;
            --border: rgba(33, 25, 28, .10);
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            background: var(--cream);
            color: var(--dark);
            overflow-x: hidden;
        }


        /* =========================================================
           LOADER
        ========================================================= */

        .page-loader {
            position: fixed;
            inset: 0;
            z-index: 99999;
            background: #f9f2f1;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            transition: opacity .7s ease, visibility .7s ease;
        }

        .page-loader.loader-hidden {
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
        }

        .loader-content {
            width: 260px;
            text-align: center;
        }

        .loader-logo {
            font-family: "Cormorant Garamond", serif;
            font-size: 42px;
            letter-spacing: 9px;
            color: #271c20;
            margin-left: 9px;
            opacity: 0;
            transform: translateY(8px);
            animation: loaderLogoIn .8s ease .1s forwards;
        }

        .loader-line {
            width: 120px;
            height: 2px;
            background: rgba(39, 28, 32, .12);
            margin: 25px auto 0;
            overflow: hidden;
            position: relative;
        }

        .loader-line span {
            display: block;
            width: 100%;
            height: 100%;
            background: #271c20;
            transform: scaleX(0);
            transform-origin: left center;
            animation: loaderLine 1.6s cubic-bezier(.4,0,.2,1) .25s forwards;
        }

        .loader-content p {
            margin: 16px 0 0;
            font-family: "Montserrat", sans-serif;
            font-size: 8px;
            letter-spacing: 2.5px;
            color: #76686b;
            opacity: 0;
            animation: loaderTextIn .8s ease .35s forwards;
        }

        @keyframes loaderLine {
            from {
                transform: scaleX(0);
            }
            to {
                transform: scaleX(1);
            }
        }

        @keyframes loaderLogoIn {
            from {
                opacity: 0;
                transform: translateY(8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes loaderTextIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }


        /* =========================================================
           HERO
        ========================================================= */


/* =========================================================
   ÉLORIA — SOFT LUXURY HERO
========================================================= */

.hero {
    position: relative;
    min-height: 720px;
    height: calc(100vh - 78px);
    max-height: 900px;

    overflow: hidden;

    display: flex;
    align-items: center;

    background: #ead9d8;
}


/* ---------------------------------------------------------
   BLURRED BACKGROUND
--------------------------------------------------------- */
/* 
.hero-background {
    position: absolute;
    inset: -35px;

    background-image:
        url("https://images.unsplash.com/photo-1617038220319-276d3cfab638?auto=format&fit=crop&w=2200&q=90");

    background-size: cover;
    background-position: center;

    filter: blur(8px);

    transform: scale(1.08);

    opacity: .78;

    animation: luxuryZoom 12s ease-in-out infinite alternate;
}


@keyframes luxuryZoom {

    from {
        transform: scale(1.08);
    }

    to {
        transform: scale(1.14);
    }

} */
.hero-background {
    position: absolute;
    inset: -35px;

    background-image:
        linear-gradient(
            90deg,
            rgba(48, 32, 36, .68) 0%,
            rgba(48, 32, 36, .38) 35%,
            rgba(48, 32, 36, .08) 75%
        ),
        url("https://images.unsplash.com/photo-1485968579580-b6d095142e6e?auto=format&fit=crop&w=2200&q=90");

    background-size: cover;
    background-position: center 30%;

    filter: blur(5px);

    transform: scale(1.07);

    opacity: .86;

    animation: luxuryZoom 14s ease-in-out infinite alternate;
}

/* ---------------------------------------------------------
   SOFT OVERLAY
--------------------------------------------------------- */

.hero-overlay {
    position: absolute;
    inset: 0;

    background:
        linear-gradient(
            90deg,
            rgba(38, 25, 29, .82) 0%,
            rgba(38, 25, 29, .58) 30%,
            rgba(38, 25, 29, .20) 68%,
            rgba(38, 25, 29, .12) 100%
        );

    z-index: 1;
}


/* ---------------------------------------------------------
   LIGHT GLOW
--------------------------------------------------------- */

.hero-glow {
    position: absolute;

    border-radius: 50%;

    filter: blur(70px);

    pointer-events: none;

    z-index: 1;
}


.hero-glow-one {
    width: 300px;
    height: 300px;

    right: 8%;
    top: 10%;

    background: rgba(255, 220, 224, .30);
}


.hero-glow-two {
    width: 220px;
    height: 220px;

    right: 30%;
    bottom: 8%;

    background: rgba(255, 239, 232, .18);
}


/* ---------------------------------------------------------
   HERO CONTENT
--------------------------------------------------------- */

.hero-content {
    position: relative;

    z-index: 3;

    width: 100%;
    max-width: 820px;

    padding-left: 9%;
    padding-right: 30px;

    color: #fff;

    animation: heroReveal 1s ease .25s both;
}


@keyframes heroReveal {

    from {
        opacity: 0;
        transform: translateY(35px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }

}


/* ---------------------------------------------------------
   TOP SMALL LINE
--------------------------------------------------------- */

.hero-small-line {

    display: flex;
    align-items: center;

    gap: 13px;

    margin-bottom: 24px;

    font-family: "Montserrat", sans-serif;

    font-size: 8px;
    letter-spacing: 3.5px;

    font-weight: 500;

    color: rgba(255,255,255,.72);
}


.hero-small-line span {

    width: 28px;
    height: 1px;

    background: rgba(255,255,255,.55);

}


/* ---------------------------------------------------------
   LABEL
--------------------------------------------------------- */

.hero-label {

    display: block;

    margin-bottom: 19px;

    font-family: "Montserrat", sans-serif;

    font-size: 10px;

    letter-spacing: 4px;

    font-weight: 500;

    color: rgba(255,255,255,.90);

}


/* ---------------------------------------------------------
   MAIN HEADING
--------------------------------------------------------- */

.hero h1 {

    margin: 0;

    font-family: "Cormorant Garamond", serif;

    font-size: clamp(68px, 8vw, 118px);

    line-height: .78;

    letter-spacing: -3px;

    font-weight: 400;

    text-shadow:
        0 8px 35px rgba(0,0,0,.12);
}


.hero h1 em {

    color: #f2d9dc;

    font-weight: 400;

}


/* ---------------------------------------------------------
   DESCRIPTION
--------------------------------------------------------- */

.hero p {

    max-width: 490px;

    margin: 32px 0 34px;

    font-family: "Montserrat", sans-serif;

    font-size: 12px;

    line-height: 2;

    font-weight: 300;

    color: rgba(255,255,255,.80);

}


/* ---------------------------------------------------------
   BUTTONS
--------------------------------------------------------- */

.hero-buttons {

    display: flex;

    align-items: center;

    gap: 28px;

    flex-wrap: wrap;
}


/* PRIMARY */

.primary-btn {

    display: inline-flex;

    align-items: center;

    justify-content: space-between;

    gap: 35px;

    min-width: 205px;

    padding: 16px 20px;

    background: rgba(255,255,255,.96);

    color: #291d21;

    text-decoration: none;

    font-family: "Montserrat", sans-serif;

    font-size: 10px;

    letter-spacing: 1.2px;

    font-weight: 600;

    transition:
        transform .35s ease,
        background .35s ease,
        box-shadow .35s ease;
}


.primary-btn b {

    font-size: 17px;

    font-weight: 400;

    transition: transform .3s ease;
}


.primary-btn:hover {

    transform: translateY(-4px);

    background: #f3dfe1;

    box-shadow:
        0 14px 35px rgba(0,0,0,.15);
}


.primary-btn:hover b {

    transform: translateX(6px);
}


/* STORY LINK */

.text-btn {

    display: inline-flex;

    align-items: center;

    gap: 10px;

    padding-bottom: 7px;

    border-bottom: 1px solid rgba(255,255,255,.65);

    color: white;

    text-decoration: none;

    font-family: "Montserrat", sans-serif;

    font-size: 10px;

    letter-spacing: 1.3px;

    transition: .3s ease;
}


.text-btn:hover {

    border-color: white;

    letter-spacing: 2px;
}


.text-btn span {

    font-size: 14px;

}


/* ---------------------------------------------------------
   SIDE TEXT
--------------------------------------------------------- */

.hero-side-text {

    position: absolute;

    right: 25px;
    top: 50%;

    z-index: 3;

    transform:
        translateY(-50%)
        rotate(90deg);

    transform-origin: center;

    font-family: "Montserrat", sans-serif;

    font-size: 8px;

    letter-spacing: 4px;

    color: rgba(255,255,255,.55);

}


/* ---------------------------------------------------------
   SCROLL
--------------------------------------------------------- */

.hero-scroll {

    position: absolute;

    left: 9%;
    bottom: 30px;

    z-index: 4;

    display: flex;

    align-items: center;

    gap: 14px;

    color: rgba(255,255,255,.70);
}


.scroll-line {

    width: 38px;

    height: 1px;

    overflow: hidden;

    background: rgba(255,255,255,.25);
}


.scroll-line span {

    display: block;

    width: 100%;
    height: 100%;

    background: white;

    animation: scrollLine 2s ease-in-out infinite;
}


@keyframes scrollLine {

    0% {
        transform: translateX(-100%);
    }

    50% {
        transform: translateX(0);
    }

    100% {
        transform: translateX(100%);
    }

}


.hero-scroll small {

    font-family: "Montserrat", sans-serif;

    font-size: 7px;

    letter-spacing: 3px;
}


/* =========================================================
   TABLET
========================================================= */

@media (max-width: 900px) {

    .hero {

        min-height: 680px;

        height: 85vh;

    }

    .hero-content {

        padding-left: 7%;

    }

    .hero h1 {

        font-size: clamp(62px, 10vw, 90px);

    }

}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 600px) {

    .hero {

        min-height: 700px;

        height: calc(100vh - 65px);

    }


    .hero-background {

        background-position: 62% center;

        filter: blur(10px);

        opacity: .72;

    }


    .hero-overlay {

        background:
            linear-gradient(
                180deg,
                rgba(38,25,29,.72),
                rgba(38,25,29,.56) 50%,
                rgba(38,25,29,.78)
            );

    }


    .hero-content {

        padding:
            40px 7%
            90px;

    }


    .hero-small-line {

        font-size: 7px;

        letter-spacing: 2.5px;

    }


    .hero-label {

        font-size: 8px;

        letter-spacing: 3px;

    }


    .hero h1 {

        font-size: 65px;

        line-height: .82;

        letter-spacing: -2px;

    }


    .hero p {

        font-size: 11px;

        line-height: 1.9;

        max-width: 340px;

        margin: 27px 0;

    }


    .hero-buttons {

        gap: 18px;

    }


    .primary-btn {

        min-width: 190px;

        padding: 15px 17px;

    }


    .hero-side-text {

        display: none;

    }


    .hero-scroll {

        left: 7%;

        bottom: 25px;

    }

}


/* =========================================================
   SMALL MOBILE
========================================================= */

@media (max-width: 380px) {

    .hero h1 {

        font-size: 55px;

    }

    .hero-content {

        padding-left: 6%;

        padding-right: 6%;

    }

    .hero-label {

        letter-spacing: 2px;

    }

}


        /* =========================================================
           COMMON
        ========================================================= */

        .section {
            padding: 115px 8%;
        }

        .intro {
            display: grid;
            grid-template-columns: .7fr 1.3fr;
            gap: 80px;
            align-items: start;
            background: #fffaf8;
        }

        .intro-label,
        .section-heading span,
        .feature-content > span,
        .newsletter-inner > span {
            font-family: "Montserrat", sans-serif;
            font-size: 10px;
            letter-spacing: 3px;
            font-weight: 600;
        }

        .intro-label {
            color: #9e777d;
        }

        .intro-content {
            max-width: 850px;
        }

        .intro-content h2 {
            margin: 0 0 25px;

            font-family: "Cormorant Garamond", serif;
            font-size: clamp(48px, 5vw, 76px);
            font-weight: 500;
            line-height: .95;
        }

        .intro-content h2 em {
            color: #b8878e;
        }

        .intro-content p {
            max-width: 680px;
            margin: 0;

            font-family: "Montserrat", sans-serif;
            color: var(--muted);
            font-size: 14px;
            line-height: 2;
            font-weight: 300;
        }


        /* =========================================================
           CATEGORIES
        ========================================================= */

        .categories {
            background: #f6efed;
        }

        .section-heading {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 45px;
        }

        .section-heading h2 {
            margin: 8px 0 0;

            font-family: "Cormorant Garamond", serif;
            font-size: clamp(45px, 5vw, 70px);
            line-height: .95;
            font-weight: 500;
        }

        .section-heading h2 em {
            color: #b8878e;
        }

        .section-heading > a {
            text-decoration: none;
            color: var(--dark);

            font-family: "Montserrat", sans-serif;
            font-size: 10px;
            font-weight: 600;
            letter-spacing: 1px;

            border-bottom: 1px solid #21191c;
            padding-bottom: 7px;

            transition: .3s ease;
        }

        .section-heading > a:hover {
            letter-spacing: 2px;
        }

        .category-grid {
            display: grid;
            grid-template-columns: 1.2fr .9fr .9fr;
            gap: 18px;
        }

        .category-card {
            position: relative;
            min-height: 570px;
            overflow: hidden;
            display: flex;
            align-items: flex-end;

            text-decoration: none;
            color: white;

            background-size: cover;
            background-position: center;

            transition: .5s ease;
        }

        .category-one {
            background-image:
                url("https://images.unsplash.com/photo-1617038220319-276d3cfab638?auto=format&fit=crop&w=1200&q=85");
        }

        .category-two {
            background-image:
                url("https://images.unsplash.com/photo-1611652022419-a9419f74343d?auto=format&fit=crop&w=1000&q=85");
        }

        .category-three {
            background-image:
                url("https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?auto=format&fit=crop&w=1000&q=85");
        }

        .category-card:hover {
            transform: translateY(-7px);
        }

        .category-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background: linear-gradient(
                to top,
                rgba(20,14,16,.75),
                rgba(20,14,16,.05) 65%
            );
        }

        .category-card:hover {
            background-position: center 48%;
        }

        .category-info {
            position: relative;
            z-index: 2;
            padding: 35px;
        }

        .category-info > span {
            font-family: "Montserrat", sans-serif;
            font-size: 10px;
            letter-spacing: 2px;
            opacity: .8;
        }

        .category-info h3 {
            margin: 10px 0 7px;

            font-family: "Cormorant Garamond", serif;
            font-size: 38px;
            font-weight: 500;
        }

        .category-info p {
            margin: 0;

            font-family: "Montserrat", sans-serif;
            font-size: 10px;
            letter-spacing: 1px;
            opacity: .8;
        }


        /* =========================================================
           PRODUCT EDIT
        ========================================================= */

        .product-edit {
            padding: 120px 8%;
            background: #fffaf8;
        }

        .product-edit-heading {
            text-align: center;
            margin-bottom: 55px;
        }

        .product-edit-heading span {
            font-family: "Montserrat", sans-serif;
            font-size: 9px;
            letter-spacing: 4px;
            color: #a07b80;
        }

        .product-edit-heading h2 {
            margin: 12px 0 0;

            font-family: "Cormorant Garamond", serif;
            font-size: clamp(48px, 5vw, 72px);
            font-weight: 500;
            line-height: .95;
        }

        .product-edit-heading h2 em {
            color: #b8878e;
        }

        .edit-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }

        .edit-card {
            text-decoration: none;
            color: var(--dark);
        }

        .edit-image {
            position: relative;
            height: 430px;
            overflow: hidden;
            background: #eee3e0;
        }

        .edit-image img {
            width: 100%;
            height: 100%;
            display: block;
            object-fit: cover;

            transition: transform .7s ease;
        }

        .edit-card:hover .edit-image img {
            transform: scale(1.06);
        }

        .edit-number {
            position: absolute;
            top: 18px;
            left: 18px;

            width: 35px;
            height: 35px;

            display: flex;
            align-items: center;
            justify-content: center;

            background: rgba(255,255,255,.9);

            font-family: "Montserrat", sans-serif;
            font-size: 9px;
        }

        .edit-info {
            padding: 18px 2px;
        }

        .edit-info h3 {
            margin: 0 0 5px;

            font-family: "Cormorant Garamond", serif;
            font-size: 25px;
            font-weight: 600;
        }

        .edit-info p {
            margin: 0;

            font-family: "Montserrat", sans-serif;
            font-size: 10px;
            letter-spacing: 1px;
            color: #8b7c7f;
        }


        /* =========================================================
           FEATURE
        ========================================================= */

        .feature-section {
            min-height: 690px;

            display: grid;
            grid-template-columns: 1.15fr .85fr;

            background: #eadcda;
        }

        .feature-image {
            min-height: 690px;

            background-image:
                url("https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?auto=format&fit=crop&w=1500&q=90");

            background-size: cover;
            background-position: center;
        }

        .feature-content {
            padding: 90px 11%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .feature-content > span {
            color: #947074;
        }

        .feature-content h2 {
            margin: 18px 0 25px;

            font-family: "Cormorant Garamond", serif;
            font-size: clamp(50px, 5vw, 75px);
            font-weight: 500;
            line-height: .9;
        }

        .feature-content h2 em {
            color: #a8767d;
        }

        .feature-content p {
            max-width: 390px;

            font-family: "Montserrat", sans-serif;
            font-size: 13px;
            line-height: 2;
            color: #76676a;
            font-weight: 300;
        }

        .dark-btn {
            width: max-content;
            margin-top: 25px;

            display: inline-flex;
            align-items: center;
            gap: 30px;

            padding: 17px 23px;

            background: #261d20;
            color: white;
            text-decoration: none;

            font-family: "Montserrat", sans-serif;
            font-size: 10px;
            letter-spacing: 1px;

            transition: .35s ease;
        }

        .dark-btn span {
            transition: .3s ease;
        }

        .dark-btn:hover {
            transform: translateY(-3px);
            background: #8f666c;
        }

        .dark-btn:hover span {
            transform: translateX(5px);
        }


        /* =========================================================
           MINI FEATURES
        ========================================================= */

        .mini-features {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1px;
            padding-top: 95px;
            padding-bottom: 95px;
            background: #fffaf8;
        }

        .mini-feature {
            text-align: center;
            padding: 30px 50px;
        }

        .mini-icon {
            width: 52px;
            height: 52px;

            margin: 0 auto 20px;

            border: 1px solid rgba(90,65,69,.18);
            border-radius: 50%;

            display: flex;
            align-items: center;
            justify-content: center;

            font-size: 20px;
            color: #a8757c;
        }

        .mini-feature h3 {
            margin: 0 0 12px;

            font-family: "Cormorant Garamond", serif;
            font-size: 27px;
            font-weight: 600;
        }

        .mini-feature p {
            margin: auto;

            max-width: 230px;

            font-family: "Montserrat", sans-serif;
            font-size: 11px;
            line-height: 1.9;
            color: #85777a;
        }


        /* =========================================================
           NEWSLETTER
        ========================================================= */

        .newsletter {
            position: relative;
            overflow: hidden;

            padding: 120px 8%;

            background:
                linear-gradient(
                    rgba(35,25,28,.78),
                    rgba(35,25,28,.78)
                ),
                url("https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?auto=format&fit=crop&w=1800&q=85");

            background-size: cover;
            background-position: center;
        }

        .newsletter::after {
            content: "É";
            position: absolute;
            right: 3%;
            bottom: -100px;

            font-family: "Cormorant Garamond", serif;
            font-size: 400px;
            line-height: 1;

            color: rgba(255,255,255,.035);
        }

        .newsletter-inner {
            position: relative;
            z-index: 2;

            max-width: 750px;
            margin: auto;
            text-align: center;

            color: white;
        }

        .newsletter-inner > span {
            color: #dfc0c4;
        }

        .newsletter-inner h2 {
            margin: 18px 0;

            font-family: "Cormorant Garamond", serif;
            font-size: clamp(50px, 6vw, 80px);
            font-weight: 500;
            line-height: .9;
        }

        .newsletter-inner p {
            max-width: 500px;
            margin: 0 auto 35px;

            font-family: "Montserrat", sans-serif;
            font-size: 12px;
            line-height: 1.9;
            font-weight: 300;

            color: rgba(255,255,255,.75);
        }

        .newsletter-form {
            max-width: 520px;
            margin: auto;

            display: flex;

            border-bottom: 1px solid rgba(255,255,255,.45);
        }

        .newsletter-form input {
            flex: 1;

            border: 0;
            outline: 0;
            background: transparent;

            padding: 17px 5px;

            color: white;

            font-family: "Montserrat", sans-serif;
            font-size: 11px;
        }

        .newsletter-form input::placeholder {
            color: rgba(255,255,255,.65);
        }

        .newsletter-form button {
            border: 0;
            background: transparent;
            color: white;

            padding: 0 5px;

            font-family: "Montserrat", sans-serif;
            font-size: 10px;
            letter-spacing: 1px;

            cursor: pointer;
        }

        .newsletter-form button span {
            margin-left: 12px;
        }

        #newsletterMessage {
            display: block;
            margin-top: 15px;

            font-family: "Montserrat", sans-serif;
            font-size: 10px;
            color: #e7c9cc;
        }


        /* =========================================================
           SCROLL REVEAL
        ========================================================= */

        .section,
        .product-edit,
        .feature-section,
        .mini-features,
        .newsletter {
            animation: reveal .8s ease both;
        }

        @keyframes reveal {
            from {
                opacity: 0;
                transform: translateY(25px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }


        /* =========================================================
           RESPONSIVE
        ========================================================= */

        @media (max-width: 1000px) {

            .category-grid {
                grid-template-columns: 1fr 1fr;
            }

            .category-one {
                grid-column: span 2;
            }

            .edit-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .feature-section {
                grid-template-columns: 1fr;
            }

            .feature-image {
                min-height: 550px;
            }

            .mini-features {
                grid-template-columns: 1fr;
            }

        }


        @media (max-width: 700px) {

            .hero {
                min-height: 720px;
            }

            .hero-background {
                background-position: 62% center;
            }

            .hero-content {
                padding: 80px 7%;
            }

            .hero h1 {
                font-size: 59px;
            }

            .hero p {
                font-size: 12px;
            }

            .hero-scroll {
                left: 7%;
                bottom: 25px;
            }

            .section {
                padding: 75px 6%;
            }

            .intro {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .intro-content h2 {
                font-size: 52px;
            }

            .section-heading {
                align-items: flex-start;
                gap: 25px;
                flex-direction: column;
            }

            .category-grid {
                grid-template-columns: 1fr;
            }

            .category-one {
                grid-column: auto;
            }

            .category-card {
                min-height: 480px;
            }

            .product-edit {
                padding: 80px 6%;
            }

            .edit-grid {
                grid-template-columns: 1fr 1fr;
                gap: 12px;
            }

            .edit-image {
                height: 300px;
            }

            .edit-info h3 {
                font-size: 21px;
            }

            .feature-image {
                min-height: 450px;
            }

            .feature-content {
                padding: 75px 7%;
            }

            .mini-feature {
                padding: 25px;
            }

            .newsletter {
                padding: 80px 6%;
            }

            .newsletter-form {
                display: block;
                border: 0;
            }

            .newsletter-form input {
                width: 100%;
                border-bottom: 1px solid rgba(255,255,255,.45);
                margin-bottom: 15px;
            }

            .newsletter-form button {
                padding: 12px 0;
            }

        }


        @media (max-width: 450px) {

            .loader-logo {
                font-size: 34px;
            }

            .hero h1 {
                font-size: 52px;
            }

            .hero-buttons {
                gap: 18px;
            }

            .primary-btn {
                padding: 14px 18px;
            }

            .category-info {
                padding: 25px;
            }

            .category-info h3 {
                font-size: 32px;
            }

            .edit-grid {
                grid-template-columns: 1fr;
            }

            .edit-image {
                height: 420px;
            }

            .newsletter-inner h2 {
                font-size: 50px;
            }

        }

        .explore-collection-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 14px;

    padding: 15px 30px;

    background: linear-gradient(
        135deg,
        #65485f,
        #806176
    );

    color: #ffffff;
    text-decoration: none;

    font-family: 'Montserrat', sans-serif;
    font-size: 11px;
    font-weight: 500;

    letter-spacing: 2px;
    text-transform: uppercase;

    border-radius: 8px;

    border: 1px solid rgba(255,255,255,0.25);

    box-shadow:
        0 10px 25px rgba(101, 72, 95, 0.18);

    transition:
        transform 0.35s ease,
        box-shadow 0.35s ease,
        background 0.35s ease;
}

.explore-collection-btn span {
    font-size: 18px;
    transition: transform 0.3s ease;
}

.explore-collection-btn:hover {
    background: linear-gradient(
        135deg,
        #51394c,
        #6d5065
    );

    transform: translateY(-3px);

    box-shadow:
        0 15px 30px rgba(101, 72, 95, 0.28);
}

.explore-collection-btn:hover span {
    transform: translateX(5px);
}

.explore-collection-btn:active {
    transform: translateY(-1px);
}

    </style>

</head>

<body>


<!-- =========================================================
     PAGE LOADER
========================================================= -->

<!-- PAGE LOADER -->
<div id="pageLoader" class="page-loader">

    <div class="loader-content">

        <div class="loader-logo">ÉLORIA.</div>

        <div class="loader-line">
            <span></span>
        </div>

        <p>LOADING YOUR LITTLE LUXURY...</p>

    </div>

</div>


<?php include "includes/navbar.php"; ?>


<!-- =========================================================
     HERO
========================================================= -->
<!-- =========================================================
     HERO / LUXURY BANNER
========================================================= -->

<section class="hero">

    <!-- Soft blurred luxury background -->
    <div class="hero-background"></div>

    <!-- Elegant overlay -->
    <div class="hero-overlay"></div>

    <!-- Decorative glow -->
    <div class="hero-glow hero-glow-one"></div>
    <div class="hero-glow hero-glow-two"></div>


    <div class="hero-content">

        <div class="hero-small-line">
            <span></span>
            ÉLORIA COLLECTION
            <span></span>
        </div>

        <span class="hero-label">
            A LITTLE LUXURY, JUST FOR YOU
        </span>

        <h1>
            Beauty in
            <br>
            <em>every detail.</em>
        </h1>

        <p>
            Discover delicate pieces and timeless little
            luxuries, thoughtfully chosen to make your
            everyday moments feel extraordinary.
        </p>

        <div class="hero-buttons">

           
            
<a href="shop.php" class="explore-collection-btn">
    Explore Collection
    <span>→</span>
</a>
            <a href="about.php" class="text-btn">
                Our Story
                <span>↗</span>
            </a>

        </div>

    </div>


    <!-- Side decorative text -->
    <div class="hero-side-text">
        ÉLORIA — 2026
    </div>


    <!-- Bottom scroll -->
    <div class="hero-scroll">

        <div class="scroll-line">
            <span></span>
        </div>

        <small>
            SCROLL TO DISCOVER
        </small>

    </div>

</section>


<!-- =========================================================
     INTRO
========================================================= -->

<!-- INTRO / LITTLE THINGS SECTION -->
<section class="intro section">

    <div class="intro-text">
        <div class="intro-label">✦ ÉLORIA ESSENTIALS</div>

        <div class="intro-content">
            <h2>
                Little things can make
                <em>a big difference.</em>
            </h2>

            <p>
                At ÉLORIA, we believe beauty lives in the details.
                Every collection is thoughtfully selected to bring
                elegance, confidence and a little joy into your
                everyday life.
            </p>
        </div>
    </div>

   <div class="intro-image">
    <img 
        src="https://assets.vogue.com/photos/646f73611d0fae915c027948/1:1/w_1000,h_1000,c_limit/VBIC-520-voguebus-story.jpg"
        alt="ÉLORIA Luxury Collection"
    >
</div>

</section>



<!-- =========================================================
     CATEGORIES
========================================================= -->

<section class="categories section">

    <div class="section-heading">

        <div>

            <span>
                EXPLORE
            </span>

            <h2>
                Find your
                <em>favorite.</em>
            </h2>

        </div>

        <a href="shop.php">
            View all →
        </a>

    </div>


    <div class="category-grid">


        <a href="shop.php?category=new"
           class="category-card category-one">

            <div class="category-info">

                <span>
                    01
                </span>

                <h3>
                    New Arrivals
                </h3>

                <p>
                    Freshly added favorites
                </p>

            </div>

        </a>


        <a href="shop.php?category=best"
           class="category-card category-two">

            <div class="category-info">

                <span>
                    02
                </span>

                <h3>
                    Best Sellers
                </h3>

                <p>
                    Loved by ÉLORIA girls
                </p>

            </div>

        </a>


        <a href="shop.php?category=essentials"
           class="category-card category-three">

            <div class="category-info">

                <span>
                    03
                </span>

                <h3>
                    Essentials
                </h3>

                <p>
                    Everyday elegance
                </p>

            </div>

        </a>

    </div>

</section>


<!-- =========================================================
     ÉLORIA EDIT / PRODUCTS
========================================================= -->

<section class="product-edit">

    <div class="product-edit-heading">

        <span>
            A LITTLE SOMETHING BEAUTIFUL
        </span>

        <h2>
            The ÉLORIA
            <em>edit.</em>
        </h2>

    </div>


    <div class="edit-grid">


        <a href="shop.php?category=new"
           class="edit-card">

            <div class="edit-image">

                <img
                    src="https://images.unsplash.com/photo-1617038220319-276d3cfab638?auto=format&fit=crop&w=900&q=85"
                    alt="ÉLORIA Rose Collection"
                >

                <span class="edit-number">
                    01
                </span>

            </div>

            <div class="edit-info">

                <h3>
                    Rose Petal
                </h3>

                <p>
                    SOFT • FEMININE • TIMELESS
                </p>

            </div>

        </a>


        <a href="shop.php?category=best"
           class="edit-card">

            <div class="edit-image">

                <img
                    src="https://images.unsplash.com/photo-1611652022419-a9419f74343d?auto=format&fit=crop&w=900&q=85"
                    alt="ÉLORIA Pearl Collection"
                >

                <span class="edit-number">
                    02
                </span>

            </div>

            <div class="edit-info">

                <h3>
                    Pearl Grace
                </h3>

                <p>
                    ELEGANT • DELICATE • CLASSIC
                </p>

            </div>

        </a>


        <a href="shop.php?category=essentials"
           class="edit-card">

            <div class="edit-image">

                <img
                    src="https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?auto=format&fit=crop&w=900&q=85"
                    alt="ÉLORIA Essentials"
                >

                <span class="edit-number">
                    03
                </span>

            </div>

            <div class="edit-info">

                <h3>
                    Daily Rose
                </h3>

                <p>
                    EASY • EVERYDAY • BEAUTIFUL
                </p>

            </div>

        </a>


        <a href="shop.php?category=accessories"
           class="edit-card">

            <div class="edit-image">

                <img
                    src="https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?auto=format&fit=crop&w=900&q=85"
                    alt="ÉLORIA Accessories"
                >

                <span class="edit-number">
                    04
                </span>

            </div>

            <div class="edit-info">

                <h3>
                    Pearl Charm
                </h3>

                <p>
                    DELICATE • POLISHED • LOVELY
                </p>

            </div>

        </a>


    </div>

</section>


<!-- =========================================================
     FEATURE
========================================================= -->

<section class="feature-section">

    <div class="feature-image"></div>

    <div class="feature-content">

        <span>
            THE ÉLORIA EDIT
        </span>

        <h2>
            Elegance doesn't
            <em>have to be loud.</em>
        </h2>

        <p>
            Soft details. Thoughtful choices.
            Pieces designed to become part of
            your story — not just another thing
            in your wardrobe.
        </p>

        <a href="shop.php" class="dark-btn">

            Discover the edit

            <span>
                →
            </span>

        </a>

    </div>

</section>


<!-- =========================================================
     MINI FEATURES
========================================================= -->

<section class="mini-features section">


    <div class="mini-feature">

        <div class="mini-icon">
            ♡
        </div>

        <h3>
            Curated With Love
        </h3>

        <p>
            Every piece is selected
            with care and intention.
        </p>

    </div>


    <div class="mini-feature">

        <div class="mini-icon">
            ✦
        </div>

        <h3>
            Made To Feel Special
        </h3>

        <p>
            Beautiful details that
            elevate everyday moments.
        </p>

    </div>


    <div class="mini-feature">

        <div class="mini-icon">
            ◇
        </div>

        <h3>
            A Personal Touch
        </h3>

        <p>
            Your ÉLORIA experience
            is designed around you.
        </p>

    </div>


</section>


<!-- =========================================================
     NEWSLETTER
========================================================= -->

<section class="newsletter">

    <div class="newsletter-inner">

        <span>
            JOIN THE ÉLORIA WORLD
        </span>

        <h2>
            Something beautiful
            <br>
            is waiting for you.
        </h2>

        <p>
            Be the first to discover new collections,
            special pieces and ÉLORIA stories.
        </p>

        <form class="newsletter-form"
              onsubmit="return handleNewsletter(event)">

            <input
                type="email"
                id="newsletterEmail"
                placeholder="Your email address"
                required
            >

            <button type="submit">

                Join us

                <span>
                    →
                </span>

            </button>

        </form>

        <small id="newsletterMessage"></small>

    </div>

</section>


<?php include "includes/footer.php"; ?>


<script>

    /* =========================================================
       NEWSLETTER DEMO
    ========================================================= */

    function handleNewsletter(event) {

        event.preventDefault();

        const email =
            document.getElementById("newsletterEmail").value;

        const message =
            document.getElementById("newsletterMessage");

        if (!email) {
            return false;
        }

        message.textContent =
            "Thank you for joining the ÉLORIA world ♡";

        document.getElementById("newsletterEmail").value = "";

        return false;
    }


    /* =========================================================
       IMAGE FALLBACK
    ========================================================= */

    document.querySelectorAll("img").forEach(function(img) {

        img.addEventListener("error", function() {

            this.style.display = "none";

            const parent = this.parentElement;

            if (parent) {
                parent.style.background =
                    "linear-gradient(135deg, #f3e4e4, #ead8d8)";
            }

        });

    });


    /* =========================================================
       SMALL SCROLL REVEAL
    ========================================================= */

    const revealItems = document.querySelectorAll(
        ".category-card, .edit-card, .mini-feature"
    );

    const observer = new IntersectionObserver(
        function(entries) {

            entries.forEach(function(entry) {

                if (entry.isIntersecting) {

                    entry.target.style.opacity = "1";
                    entry.target.style.transform = "translateY(0)";

                    observer.unobserve(entry.target);

                }

            });

        },
        {
            threshold: 0.12
        }
    );


    revealItems.forEach(function(item) {

        item.style.opacity = "0";
        item.style.transform = "translateY(25px)";
        item.style.transition =
            "opacity .7s ease, transform .7s ease";

        observer.observe(item);

    });

</script>
<script>
(function () {
    function hideEloriaLoader() {
        const loader = document.getElementById("pageLoader");

        if (!loader) return;

        // Give the logo + line animation enough time to be visible.
        setTimeout(function () {
            loader.classList.add("loader-hidden");

            setTimeout(function () {
                loader.remove();
            }, 750);
        }, 1900);
    }

    if (document.readyState === "complete") {
        hideEloriaLoader();
    } else {
        window.addEventListener("load", hideEloriaLoader, { once: true });
    }
})();
</script>

</body>
</html>