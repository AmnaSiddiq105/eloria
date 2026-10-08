document.addEventListener("DOMContentLoaded", function () {

    // =====================================================
    // ELEMENTS
    // =====================================================

    const minusBtn = document.getElementById("minusBtn");
    const plusBtn = document.getElementById("plusBtn");
    const quantityInput = document.getElementById("quantity");
    const addToCartBtn = document.getElementById("addToCartBtn");

    const sizeButtons = document.querySelectorAll(".size-btn");


    // =====================================================
    // QUANTITY - MINUS
    // =====================================================

    if (minusBtn && quantityInput) {

        minusBtn.addEventListener("click", function () {

            let quantity = parseInt(quantityInput.value) || 1;

            if (quantity > 1) {
                quantity--;
            }

            quantityInput.value = quantity;

        });

    }


    // =====================================================
    // QUANTITY - PLUS
    // =====================================================

    if (plusBtn && quantityInput) {

        plusBtn.addEventListener("click", function () {

            let quantity = parseInt(quantityInput.value) || 1;

            if (quantity < 10) {
                quantity++;
            }

            quantityInput.value = quantity;

        });

    }


    // =====================================================
    // SIZE SELECTION
    // =====================================================

    sizeButtons.forEach(function (button) {

        button.addEventListener("click", function () {

            sizeButtons.forEach(function (btn) {
                btn.classList.remove("active");
            });

            this.classList.add("active");

        });

    });


    // =====================================================
    // ADD TO CART
    // =====================================================

    if (addToCartBtn) {

        addToCartBtn.addEventListener("click", function () {

            const productId = this.getAttribute("data-product-id");

            const quantity = quantityInput
                ? parseInt(quantityInput.value) || 1
                : 1;


            // ---------------------------------------------
            // GET SELECTED SIZE
            // ---------------------------------------------

            const selectedSize = document.querySelector(
                ".size-btn.active"
            );

            let size = "";

            if (selectedSize) {
                size = selectedSize.getAttribute("data-size") || "";
            }


            // ---------------------------------------------
            // SIZE REQUIRED
            // ---------------------------------------------

            if (sizeButtons.length > 0 && size === "") {

                alert("Please select a size first.");

                return;

            }


            // ---------------------------------------------
            // SAVE ORIGINAL BUTTON
            // ---------------------------------------------

            const originalHTML = addToCartBtn.innerHTML;

            addToCartBtn.disabled = true;

            addToCartBtn.innerHTML = `
                <span>Adding...</span>
                <span>⏳</span>
            `;


            // ---------------------------------------------
            // SEND TO PHP
            // ---------------------------------------------

            const formData = new URLSearchParams();

            formData.append("product_id", productId);
            formData.append("quantity", quantity);
            formData.append("size", size);


            fetch("add_to_cart.php", {

                method: "POST",

                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },

                body: formData.toString()

            })

            .then(function (response) {

                /*
                | Read response as TEXT first.
                | This helps us see PHP errors instead of
                | automatically failing JSON parsing.
                */

                return response.text();

            })

            .then(function (text) {

                console.log("ADD TO CART RESPONSE:");
                console.log(text);


                let data;

                try {

                    data = JSON.parse(text);

                } catch (error) {

                    console.error(
                        "PHP did not return valid JSON:",
                        text
                    );

                    alert(
                        "Something went wrong while adding to cart."
                    );

                    addToCartBtn.innerHTML = originalHTML;

                    addToCartBtn.disabled = false;

                    return;

                }


                // -----------------------------------------
                // SUCCESS
                // -----------------------------------------

                if (data.success) {


                    // Update navbar cart count

                    const cartCount =
                        document.querySelector(".cart-count");


                    if (cartCount) {

                        cartCount.textContent =
                            data.cart_count;

                    }


                    // Change button

                    addToCartBtn.innerHTML = `
                        <span>Added to Cart</span>
                        <span>✓</span>
                    `;


                    // Success message

                    alert(data.message);


                    // Restore button

                    setTimeout(function () {

                        addToCartBtn.innerHTML =
                            originalHTML;

                        addToCartBtn.disabled = false;

                    }, 1200);


                } else {


                    // -------------------------------------
                    // PHP RETURNED AN ERROR
                    // -------------------------------------

                    alert(
                        data.message ||
                        "Unable to add product to cart."
                    );


                    addToCartBtn.innerHTML =
                        originalHTML;

                    addToCartBtn.disabled = false;

                }

            })

            .catch(function (error) {

                console.error(
                    "Add to cart error:",
                    error
                );

                alert(
                    "Something went wrong while adding to cart."
                );

                addToCartBtn.innerHTML =
                    originalHTML;

                addToCartBtn.disabled = false;

            });

        });

    }

});

/* =========================
   WISHLIST
========================= */

const wishlistButtons = document.querySelectorAll(
    "#wishlistBtn, #wishlistTextBtn"
);


wishlistButtons.forEach(function (button) {

    button.addEventListener("click", function () {

        const clickedButton = this;

        const productId =
            clickedButton.getAttribute(
                "data-product-id"
            );


        if (!productId) {

            console.error(
                "Wishlist product ID missing."
            );

            return;
        }


        clickedButton.disabled = true;


        const formData =
            new URLSearchParams();

        formData.append(
            "product_id",
            productId
        );


        fetch("wishlist_action.php", {

            method: "POST",

            headers: {
                "Content-Type":
                    "application/x-www-form-urlencoded"
            },

            body: formData.toString()

        })

        .then(function (response) {

            return response.text();

        })

        .then(function (text) {

            console.log(
                "WISHLIST RESPONSE:",
                text
            );


            let data;

            try {

                data = JSON.parse(text);

            } catch (error) {

                console.error(
                    "Wishlist returned invalid JSON:",
                    text
                );

                throw error;
            }


            if (data.success === true) {


                /* =========================
                   ADDED
                ========================= */

                if (data.action === "added") {

                    document
                        .querySelectorAll(
                            "#wishlistBtn, #wishlistTextBtn"
                        )
                        .forEach(function (btn) {

                            btn.classList.add(
                                "active"
                            );

                        });


                    const imageButton =
                        document.getElementById(
                            "wishlistBtn"
                        );

                    if (imageButton) {
                        imageButton.textContent = "♥";
                    }


                    const textButton =
                        document.getElementById(
                            "wishlistTextBtn"
                        );

                    if (textButton) {

                        textButton.innerHTML =
                            "♥ Remove from Wishlist";

                    }

                }


                /* =========================
                   REMOVED
                ========================= */

                else if (
                    data.action === "removed"
                ) {

                    document
                        .querySelectorAll(
                            "#wishlistBtn, #wishlistTextBtn"
                        )
                        .forEach(function (btn) {

                            btn.classList.remove(
                                "active"
                            );

                        });


                    const imageButton =
                        document.getElementById(
                            "wishlistBtn"
                        );

                    if (imageButton) {
                        imageButton.textContent = "♡";
                    }


                    const textButton =
                        document.getElementById(
                            "wishlistTextBtn"
                        );

                    if (textButton) {

                        textButton.innerHTML =
                            "♡ Add to Wishlist";

                    }

                }


                /* =========================
                   NAVBAR COUNT
                ========================= */

                const wishlistCount =
                    document.querySelector(
                        ".wishlist-count"
                    );


                if (wishlistCount) {

                    wishlistCount.textContent =
                        data.wishlist_count;

                }

            }

            else {

                alert(
                    data.message ||
                    "Unable to update wishlist."
                );

            }

        })

        .catch(function (error) {

            console.error(
                "Wishlist error:",
                error
            );

            alert(
                "Something went wrong while updating wishlist."
            );

        })

        .finally(function () {

            clickedButton.disabled = false;

        });

    });

});