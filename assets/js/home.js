document.addEventListener("DOMContentLoaded", function () {

    /* ================================
       PAGE LOADER
    ================================= */

    const loader = document.querySelector(".page-loader");

    if (loader) {

        setTimeout(function () {

            loader.classList.add("hide");

            setTimeout(function () {
                loader.style.display = "none";
            }, 700);

        }, 1200);
    }


    /* ================================
       MOBILE MENU
    ================================= */

    const menuToggle = document.querySelector(".menu-toggle");
    const mobileMenu = document.querySelector(".mobile-menu");

    if (menuToggle && mobileMenu) {

        menuToggle.addEventListener("click", function () {

            mobileMenu.classList.toggle("active");
            menuToggle.classList.toggle("active");

        });

    }


    /* ================================
       NEWSLETTER
    ================================= */

    const newsletterForm = document.querySelector(".newsletter-form");
    const newsletterMessage =
        document.getElementById("newsletterMessage");

    if (newsletterForm) {

        newsletterForm.addEventListener("submit", function (e) {

            e.preventDefault();

            if (newsletterMessage) {

                newsletterMessage.textContent =
                    "Thank you — welcome to the ÉLORIA world. ♡";

                newsletterMessage.style.opacity = "1";

            }

            newsletterForm.reset();

        });

    }


    /* ================================
       SCROLL REVEAL
    ================================= */

    const revealElements =
        document.querySelectorAll(
            ".section, .feature-section, .mini-feature, .newsletter-inner"
        );

    if ("IntersectionObserver" in window) {

        const observer = new IntersectionObserver(
            function (entries, observer) {

                entries.forEach(function (entry) {

                    if (entry.isIntersecting) {

                        entry.target.classList.add("visible");

                        observer.unobserve(entry.target);

                    }

                });

            },
            {
                threshold: 0.12
            }
        );

        revealElements.forEach(function (element) {

            element.classList.add("reveal");

            observer.observe(element);

        });

    } else {

        revealElements.forEach(function (element) {

            element.classList.add("visible");

        });

    }

});