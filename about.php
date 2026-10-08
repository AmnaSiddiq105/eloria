<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_name = $_SESSION['user_name'];

?>

<!DOCTYPE html>

<html lang="en">

<head>


<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>ÉLORIA | About Us</title>

<!-- ÉLORIA FONTS -->

<link rel="preconnect"
      href="https://fonts.googleapis.com">

<link rel="preconnect"
      href="https://fonts.gstatic.com"
      crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Montserrat:wght@300;400;500;600;700&display=swap"
      rel="stylesheet">


<!-- HOME THEME -->

<link rel="stylesheet"
      href="assets/css/home.css">


<!-- ABOUT CSS -->

<link rel="stylesheet"
      href="assets/css/about.css">


<style>

    /* =====================================================
       ÉLORIA ABOUT PAGE
       HOME THEME MATCH
    ===================================================== */

    * {
        box-sizing: border-box;
    }


    body {
        overflow-x: hidden;
    }


    /* =====================================================
       HERO
    ===================================================== */

    .about-page-hero {

        position: relative;

        width: 100%;

        min-height: 610px;

        display: flex;

        align-items: center;

        justify-content: center;

        overflow: hidden;

        background:
            linear-gradient(
                135deg,
                #f4e8eb,
                #eee5f2,
                #f8eee9
            );

    }


    /* SOFT IMAGE */

    .about-page-hero-image {

        position: absolute;

        inset: 0;

        background-image:
            url("https://images.unsplash.com/photo-1483985988355-763728e1935b?auto=format&fit=crop&w=1800&q=85");

        background-size: cover;

        background-position: center 35%;

        background-repeat: no-repeat;

        transform: scale(1.03);

        filter: blur(2px);

    }


    /* HOME STYLE LIGHT OVERLAY */

    .about-page-hero::after {

        content: "";

        position: absolute;

        inset: 0;

        background:

            linear-gradient(
                90deg,
                rgba(247,236,238,0.82),
                rgba(245,235,242,0.55),
                rgba(250,241,235,0.78)
            );

    }


    .about-page-hero-content {

        position: relative;

        z-index: 2;

        width: 90%;

        max-width: 850px;

        text-align: center;

        padding: 60px 20px;

    }


    .about-page-hero-content .small-title {

        display: inline-block;

        margin-bottom: 18px;

        font-family: "Montserrat", sans-serif;

        font-size: 11px;

        letter-spacing: 5px;

        font-weight: 600;

        color: #6d5664;

    }


    .about-page-hero-content h1 {

        margin: 0;

        font-family: "Cormorant Garamond", serif;

        font-size: clamp(58px, 8vw, 100px);

        line-height: .88;

        font-weight: 500;

        color: #342633;

    }


    .about-page-hero-content h1 em {

        font-weight: 400;

        color: #76556d;

    }


    .about-page-hero-content p {

        max-width: 600px;

        margin: 28px auto 0;

        font-family: "Montserrat", sans-serif;

        font-size: 13px;

        line-height: 1.9;

        font-weight: 400;

        color: #66545f;

    }



    /* =====================================================
       ABOUT INTRO
    ===================================================== */

    .about-main {

        position: relative;

        padding: 110px 8%;

        background:
            linear-gradient(
                135deg,
                #fffafa,
                #f9f3f7
            );

    }


    .about-main-grid {

        max-width: 1200px;

        margin: auto;

        display: grid;

        grid-template-columns: 0.9fr 1.1fr;

        gap: 90px;

        align-items: center;

    }


    /* IMAGE */

    .about-main-image {

        width: 100%;

        height: 540px;

        border-radius: 4px;

        overflow: hidden;

        background-image:
            url("https://images.unsplash.com/photo-1496747611176-843222e1e57c?auto=format&fit=crop&w=1000&q=85");

        background-size: cover;

        background-position: center;

        background-repeat: no-repeat;

        box-shadow:
            0 25px 60px rgba(75,48,65,0.13);

    }


    .about-main-content .label {

        display: block;

        margin-bottom: 18px;

        font-family: "Montserrat", sans-serif;

        font-size: 10px;

        letter-spacing: 4px;

        color: #987789;

        font-weight: 600;

    }


    .about-main-content h2 {

        margin: 0 0 25px;

        font-family: "Cormorant Garamond", serif;

        font-size: clamp(45px, 5vw, 68px);

        line-height: .95;

        font-weight: 500;

        color: #342633;

    }


    .about-main-content h2 em {

        color: #8b637b;

        font-weight: 400;

    }


    .about-main-content p {

        margin: 0 0 18px;

        max-width: 590px;

        font-family: "Montserrat", sans-serif;

        font-size: 13px;

        line-height: 2;

        color: #665962;

    }



    /* =====================================================
       OUR STORY
    ===================================================== */

    .our-story {

        position: relative;

        padding: 120px 8%;

        background:
            linear-gradient(
                135deg,
                #eee5f1,
                #f7e9ed
            );

        overflow: hidden;

    }


    .our-story::before {

        content: "";

        position: absolute;

        width: 450px;

        height: 450px;

        right: -220px;

        top: -180px;

        border-radius: 50%;

        background: rgba(255,255,255,.35);

        filter: blur(20px);

    }


    .story-inner {

        position: relative;

        z-index: 1;

        max-width: 1150px;

        margin: auto;

    }


    .story-heading {

        max-width: 750px;

        margin-bottom: 65px;

    }


    .story-heading span {

        font-family: "Montserrat", sans-serif;

        font-size: 10px;

        letter-spacing: 4px;

        font-weight: 600;

        color: #806477;

    }


    .story-heading h2 {

        margin: 18px 0 0;

        font-family: "Cormorant Garamond", serif;

        font-size: clamp(48px, 6vw, 76px);

        line-height: .95;

        font-weight: 500;

        color: #342633;

    }


    .story-heading h2 em {

        color: #855e76;

        font-weight: 400;

    }


    .story-grid {

        display: grid;

        grid-template-columns: 1fr 1fr;

        gap: 55px;

        align-items: center;

    }


    .story-image {

        width: 100%;

        height: 480px;

        border-radius: 5px;

        overflow: hidden;

        background-image:
            url("https://images.unsplash.com/photo-1485230895905-ec40ba36b9bc?auto=format&fit=crop&w=1000&q=85");

        background-size: cover;

        background-position: center;

        background-repeat: no-repeat;

        box-shadow:
            0 25px 60px rgba(71,43,65,.12);

        transition: transform .7s ease;

    }


    .story-image:hover {

        transform: scale(1.015);

    }


    .story-text {

        padding: 10px 5px;

    }


    .story-text .story-number {

        font-family: "Cormorant Garamond", serif;

        font-size: 55px;

        color: rgba(112,77,99,.30);

        line-height: 1;

        margin-bottom: 15px;

    }


    .story-text h3 {

        margin: 0 0 20px;

        font-family: "Cormorant Garamond", serif;

        font-size: 38px;

        font-weight: 500;

        color: #3d2d39;

    }


    .story-text p {

        font-family: "Montserrat", sans-serif;

        font-size: 13px;

        line-height: 2;

        color: #675862;

        margin-bottom: 18px;

    }



    /* =====================================================
       VALUES
    ===================================================== */

    .about-beliefs {

        padding: 110px 8%;

        background: #fffafa;

    }


    .belief-heading {

        text-align: center;

        max-width: 700px;

        margin: 0 auto 65px;

    }


    .belief-heading span {

        font-family: "Montserrat", sans-serif;

        font-size: 10px;

        letter-spacing: 4px;

        color: #987789;

        font-weight: 600;

    }


    .belief-heading h2 {

        margin: 18px 0 0;

        font-family: "Cormorant Garamond", serif;

        font-size: clamp(45px, 5vw, 68px);

        line-height: .95;

        font-weight: 500;

        color: #342633;

    }


    .belief-heading h2 em {

        color: #8b637b;

        font-weight: 400;

    }


    .belief-grid {

        max-width: 1100px;

        margin: auto;

        display: grid;

        grid-template-columns: repeat(3,1fr);

        gap: 25px;

    }


    .belief-card {

        position: relative;

        padding: 42px 32px;

        min-height: 270px;

        background:

            linear-gradient(
                145deg,
                #fff,
                #f9f0f5
            );

        border: 1px solid rgba(145,108,130,.12);

        border-radius: 5px;

        transition:
            transform .35s ease,
            box-shadow .35s ease;

    }


    .belief-card:hover {

        transform: translateY(-8px);

        box-shadow:
            0 22px 45px rgba(73,45,65,.10);

    }


    .belief-number {

        font-family: "Montserrat", sans-serif;

        font-size: 10px;

        letter-spacing: 2px;

        color: #ad8c9e;

    }


    .belief-icon {

        margin: 20px 0 15px;

        font-family: "Cormorant Garamond", serif;

        font-size: 38px;

        color: #936c82;

    }


    .belief-card h3 {

        margin: 0 0 12px;

        font-family: "Cormorant Garamond", serif;

        font-size: 29px;

        font-weight: 600;

        color: #3b2b38;

    }


    .belief-card p {

        margin: 0;

        font-family: "Montserrat", sans-serif;

        font-size: 12px;

        line-height: 1.9;

        color: #70616a;

    }



    /* =====================================================
       PHILOSOPHY
    ===================================================== */

    .about-philosophy {

        position: relative;

        min-height: 500px;

        display: flex;

        align-items: center;

        justify-content: center;

        overflow: hidden;

        background:

            linear-gradient(
                90deg,
                rgba(53,36,48,.75),
                rgba(82,58,75,.50),
                rgba(53,36,48,.72)
            ),

            url("https://images.unsplash.com/photo-1490481651871-ab68de25d43d?auto=format&fit=crop&w=1800&q=85")
            center / cover no-repeat;

    }


    .philosophy-content {

        position: relative;

        z-index: 2;

        max-width: 760px;

        padding: 60px 25px;

        text-align: center;

    }


    .philosophy-content span {

        font-family: "Montserrat", sans-serif;

        font-size: 10px;

        letter-spacing: 4px;

        color: rgba(255,255,255,.85);

    }


    .philosophy-content h2 {

        margin: 20px 0;

        font-family: "Cormorant Garamond", serif;

        font-size: clamp(48px, 6vw, 78px);

        line-height: .95;

        font-weight: 500;

        color: #fff;

    }


    .philosophy-content h2 em {

        font-weight: 400;

    }


    .philosophy-content p {

        max-width: 590px;

        margin: auto;

        font-family: "Montserrat", sans-serif;

        font-size: 13px;

        line-height: 1.9;

        color: rgba(255,255,255,.88);

    }



    /* =====================================================
       FINAL CTA
    ===================================================== */

    .about-final {

        padding: 105px 25px;

        text-align: center;

        background:
            linear-gradient(
                135deg,
                #f8e9ee,
                #eee6f3
            );

    }


    .about-final span {

        font-family: "Montserrat", sans-serif;

        font-size: 10px;

        letter-spacing: 4px;

        color: #806477;

        font-weight: 600;

    }


    .about-final h2 {

        margin: 18px 0 15px;

        font-family: "Cormorant Garamond", serif;

        font-size: clamp(48px, 6vw, 76px);

        line-height: .95;

        font-weight: 500;

        color: #342633;

    }


    .about-final h2 em {

        color: #855e76;

        font-weight: 400;

    }


    .about-final p {

        max-width: 550px;

        margin: 0 auto 30px;

        font-family: "Montserrat", sans-serif;

        font-size: 13px;

        line-height: 1.9;

        color: #675862;

    }


    .about-final-btn {

        display: inline-flex;

        align-items: center;

        gap: 14px;

        padding: 14px 27px;

        border: 1px solid #76556d;

        color: #76556d;

        text-decoration: none;

        font-family: "Montserrat", sans-serif;

        font-size: 11px;

        letter-spacing: 1px;

        transition: all .35s ease;

    }


    .about-final-btn:hover {

        background: #76556d;

        color: white;

        transform: translateY(-3px);

    }



    /* =====================================================
       RESPONSIVE
    ===================================================== */

    @media (max-width: 900px) {

        .about-main-grid {

            grid-template-columns: 1fr;

            gap: 50px;

        }


        .about-main-image {

            height: 500px;

        }


        .story-grid {

            grid-template-columns: 1fr;

            gap: 45px;

        }


        .story-image {

            height: 480px;

        }


        .belief-grid {

            grid-template-columns: 1fr;

            max-width: 600px;

        }

    }


    @media (max-width: 600px) {

        .about-page-hero {

            min-height: 530px;

        }


        .about-page-hero-content {

            padding: 40px 20px;

        }


        .about-page-hero-content h1 {

            font-size: 57px;

        }


        .about-page-hero-content .small-title {

            font-size: 9px;

            letter-spacing: 3px;

        }


        .about-page-hero-content p {

            font-size: 12px;

        }


        .about-main {

            padding: 75px 22px;

        }


        .about-main-image {

            height: 400px;

        }


        .about-main-grid {

            gap: 40px;

        }


        .about-main-content h2 {

            font-size: 48px;

        }


        .our-story {

            padding: 75px 22px;

        }


        .story-heading {

            margin-bottom: 45px;

        }


        .story-heading h2 {

            font-size: 50px;

        }


        .story-image {

            height: 390px;

        }


        .story-text h3 {

            font-size: 34px;

        }


        .about-beliefs {

            padding: 75px 22px;

        }


        .belief-heading h2 {

            font-size: 49px;

        }


        .belief-card {

            min-height: auto;

        }


        .about-philosophy {

            min-height: 470px;

        }


        .philosophy-content h2 {

            font-size: 52px;

        }


        .about-final {

            padding: 75px 22px;

        }


        .about-final h2 {

            font-size: 52px;

        }

    }

</style>


</head>

<body>

<!-- =====================================================
     PAGE LOADER
===================================================== -->

<div class="page-loader">

<div class="loader-logo">
    ÉLORIA
</div>

<div class="loader-line">
    <span></span>
</div>


</div>

<!-- =====================================================
     NAVBAR
===================================================== -->

<?php include "includes/navbar.php"; ?>

<!-- =====================================================
     HERO
===================================================== -->

<section class="about-page-hero">


<div class="about-page-hero-image"></div>


<div class="about-page-hero-content">

    <span class="small-title">
        ABOUT ÉLORIA
    </span>


    <h1>

        A little luxury,

        <br>

        <em>made personal.</em>

    </h1>


    <p>

        Discover the story behind ÉLORIA —
        a space created for beautiful pieces,
        quiet confidence and little moments
        that deserve to feel special.

    </p>

</div>


</section>

<!-- =====================================================
     ABOUT ÉLORIA
===================================================== -->

<section class="about-main">

<div class="about-main-grid">


    <div class="about-main-image"></div>


    <div class="about-main-content">

        <span class="label">
            WHO WE ARE
        </span>


        <h2>

            Welcome to

            <br>

            <em>ÉLORIA.</em>

        </h2>


        <p>

            ÉLORIA is more than an online collection.
            It is a carefully created space where
            elegance meets individuality.

        </p>


        <p>

            We believe the things you choose to wear,
            keep and love should feel like a reflection
            of who you are.

        </p>


        <p>

            From the first moment you discover a piece
            to the moment it becomes part of your life,
            every detail is designed to feel thoughtful,
            beautiful and effortlessly special.

        </p>

    </div>

</div>
```

</section>

<!-- =====================================================
     OUR STORY
===================================================== -->

<section class="our-story">


<div class="story-inner">


    <div class="story-heading">

        <span>
            OUR STORY
        </span>


        <h2>

            It started with a love for

            <em>beautiful little things.</em>

        </h2>

    </div>



    <div class="story-grid">


        <div class="story-image"></div>


        <div class="story-text">

            <div class="story-number">
                01
            </div>


            <h3>
                The beginning.
            </h3>


            <p>

                ÉLORIA began with a simple thought:
                everyday life deserves a little beauty.

            </p>


            <p>

                We wanted to create a collection where
                every piece could bring a little confidence,
                elegance and happiness into someone's day.

            </p>


            <p>

                What started as an appreciation for
                beautiful details became ÉLORIA —
                a place where style feels personal,
                soft and timeless.

            </p>

        </div>

    </div>

</div>
```

</section>

<!-- =====================================================
     WHAT WE BELIEVE
===================================================== -->

<section class="about-beliefs">


<div class="belief-heading">

    <span>
        OUR VALUES
    </span>


    <h2>

        What makes ÉLORIA

        <em>different.</em>

    </h2>

</div>



<div class="belief-grid">


    <div class="belief-card">

        <div class="belief-number">
            01
        </div>


        <div class="belief-icon">
            ♡
        </div>


        <h3>
            Curated With Love
        </h3>


        <p>

            Every piece is selected thoughtfully,
            with beauty, quality and individuality
            in mind.

        </p>

    </div>



    <div class="belief-card">

        <div class="belief-number">
            02
        </div>


        <div class="belief-icon">
            ✦
        </div>


        <h3>
            Timeless Elegance
        </h3>


        <p>

            We love pieces that remain beautiful
            beyond seasons and trends.

        </p>

    </div>



    <div class="belief-card">

        <div class="belief-number">
            03
        </div>


        <div class="belief-icon">
            ◇
        </div>


        <h3>
            Be Beautifully You
        </h3>


        <p>

            Your style should feel like you.
            ÉLORIA is here to help you express
            it naturally.

        </p>

    </div>


</div>


</section>

<!-- =====================================================
     PHILOSOPHY
===================================================== -->

<section class="about-philosophy">


<div class="philosophy-content">

    <span>
        OUR PHILOSOPHY
    </span>


    <h2>

        Beauty is not always

        <br>

        <em>something grand.</em>

    </h2>


    <p>

        Sometimes it is a small detail,
        a favorite piece, a confident feeling,
        or simply something that makes you smile
        when you look in the mirror.

    </p>

</div>


</section>

<!-- =====================================================
     FINAL SECTION
===================================================== -->

<section class="about-final">

<span>
    YOUR ÉLORIA JOURNEY
</span>


<h2>

    Find something

    <br>

    <em>beautiful.</em>

</h2>


<p>

    Explore our collection and discover
    pieces created to become part of
    your own beautiful moments.

</p>


<a href="shop.php"
   class="about-final-btn">

    Explore Collection

    <span>
        →
    </span>

</a>


</section>

<!-- =====================================================
     FOOTER
===================================================== -->

<?php include "includes/footer.php"; ?>

<!-- HOME JS -->

<script src="assets/js/home.js"></script>

</body>

</html>


 