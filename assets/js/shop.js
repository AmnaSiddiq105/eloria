document.addEventListener("DOMContentLoaded", function () {

    /* =========================================
       MOBILE MENU
    ========================================= */

    const menuToggle = document.getElementById("menuToggle");
    const mobileMenu = document.getElementById("mobileMenu");

    if (menuToggle && mobileMenu) {

        menuToggle.addEventListener("click", function () {
            mobileMenu.classList.toggle("show");
        });

    }


    /* =========================================
       WISHLIST BUTTONS
    ========================================= */

    const wishlistButtons =
        document.querySelectorAll(".wishlist-btn");


    wishlistButtons.forEach(function (button) {

        button.addEventListener("click", function (event) {

            event.preventDefault();
            event.stopPropagation();


            /* Prevent double click */

            if (button.dataset.loading === "true") {
                return;
            }

            button.dataset.loading = "true";


            const productId =
                button.getAttribute("data-product-id");


            if (!productId) {

                button.dataset.loading = "false";

                return;
            }


            /* Product image */

            const productCard =
                button.closest(".product-card");

            let productImage = null;

            if (productCard) {

                productImage =
                    productCard.querySelector(".product-image img");

            }


            /* Send request */

            const formData = new FormData();

            formData.append("product_id", productId);


            fetch("wishlist_action.php", {

                method: "POST",

                body: formData,

                credentials: "same-origin"

            })

            .then(function (response) {

                return response.text();

            })

            .then(function (text) {

                console.log("WISHLIST RESPONSE:", text);


                let data;

                try {

                    data = JSON.parse(text);

                } catch (error) {

                    console.error(
                        "Wishlist returned invalid JSON:",
                        text
                    );

                    showWishlistMessage(
                        "Something went wrong.",
                        "error"
                    );

                    return;
                }


                if (!data.success) {

                    showWishlistMessage(
                        data.message || "Unable to update wishlist.",
                        "error"
                    );

                    return;
                }


                /* =====================================
                   ADDED
                ===================================== */

                if (data.action === "added") {

                    button.classList.add("active");

                    button.innerHTML = "♥";


                    /* Flying animation */

                    if (productImage) {

                        createWishlistFlight(
                            productImage
                        );

                    }


                    /* Navbar count */

                    updateWishlistCount(
                        data.wishlist_count
                    );


                    showWishlistMessage(
                        "Added to wishlist ♡",
                        "success"
                    );

                }


                /* =====================================
                   REMOVED
                ===================================== */

                else if (data.action === "removed") {

                    button.classList.remove("active");

                    button.innerHTML = "♡";


                    updateWishlistCount(
                        data.wishlist_count
                    );


                    showWishlistMessage(
                        "Removed from wishlist",
                        "success"
                    );

                }

            })

            .catch(function (error) {

                console.error(
                    "Wishlist error:",
                    error
                );

                showWishlistMessage(
                    "Something went wrong.",
                    "error"
                );

            })

            .finally(function () {

                button.dataset.loading = "false";

            });

        });

    });


    /* =========================================
       UPDATE NAVBAR COUNT
    ========================================= */

    function updateWishlistCount(count) {

        const countElement =
            document.getElementById("wishlistCount");

        if (!countElement) {

            console.warn(
                "wishlistCount element not found."
            );

            return;
        }


        countElement.textContent = count;


        /* Count pop */

        countElement.classList.remove("pop");

        void countElement.offsetWidth;

        countElement.classList.add("pop");


        /* Navbar heart pulse */

        const wishlistNav =
            document.querySelector(".wishlist-nav");

        if (wishlistNav) {

            wishlistNav.classList.remove("heart-pulse");

            void wishlistNav.offsetWidth;

            wishlistNav.classList.add("heart-pulse");

        }

    }


    /* =========================================
       FLY PRODUCT IMAGE TO NAVBAR HEART
    ========================================= */

    function createWishlistFlight(image) {

        const wishlistNav =
            document.querySelector(".wishlist-nav");


        if (!wishlistNav) {

            console.warn(
                "Navbar wishlist element not found."
            );

            return;
        }


        const startRect =
            image.getBoundingClientRect();

        const targetRect =
            wishlistNav.getBoundingClientRect();


        /* Create flying element */

        const flying =
            document.createElement("div");

        flying.className =
            "wishlist-flying-item";


        const flyingImage =
            document.createElement("img");

        flyingImage.src =
            image.currentSrc ||
            image.src;


        flyingImage.alt = "";


        flying.appendChild(
            flyingImage
        );


        document.body.appendChild(
            flying
        );


        /* Start position */

        const startX =
            startRect.left +
            startRect.width / 2 -
            37.5;

        const startY =
            startRect.top +
            startRect.height / 2 -
            37.5;


        flying.style.left =
            startX + "px";

        flying.style.top =
            startY + "px";


        flying.style.transform =
            "scale(1)";

        flying.style.opacity =
            "1";


        /* Force browser paint */

        flying.getBoundingClientRect();


        /* Target position */

        const targetX =
            targetRect.left +
            targetRect.width / 2 -
            37.5;

        const targetY =
            targetRect.top +
            targetRect.height / 2 -
            37.5;


        requestAnimationFrame(function () {

            flying.style.left =
                targetX + "px";

            flying.style.top =
                targetY + "px";

            flying.style.width =
                "25px";

            flying.style.height =
                "25px";

            flying.style.opacity =
                "0.25";

            flying.style.transform =
                "scale(0.35)";

        });


        /* Remove after animation */

        setTimeout(function () {

            flying.remove();


            /* Pulse navbar heart */

            wishlistNav.classList.remove(
                "heart-pulse"
            );

            void wishlistNav.offsetWidth;

            wishlistNav.classList.add(
                "heart-pulse"
            );

        }, 800);

    }


    /* =========================================
       NOTIFICATION
    ========================================= */

    function showWishlistMessage(
        message,
        type
    ) {

        let notification =
            document.querySelector(
                ".wishlist-notification"
            );


        if (!notification) {

            notification =
                document.createElement("div");

            notification.className =
                "wishlist-notification";

            document.body.appendChild(
                notification
            );

        }


        notification.textContent =
            message;


        notification.classList.remove(
            "success",
            "error",
            "show"
        );


        notification.classList.add(
            type,
            "show"
        );


        setTimeout(function () {

            notification.classList.remove(
                "show"
            );

        }, 2200);

    }

});