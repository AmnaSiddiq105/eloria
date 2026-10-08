function togglePassword(inputId, button) {

    const input = document.getElementById(inputId);

    if (!input) {
        return;
    }

    if (input.type === "password") {

        input.type = "text";
        button.textContent = "Hide";

    } else {

        input.type = "password";
        button.textContent = "Show";

    }
}


// Smooth page loading

document.addEventListener("DOMContentLoaded", function () {

    document.body.classList.add("page-loaded");

});


// Prevent accidental double submission

const forms = document.querySelectorAll("form");

forms.forEach(function (form) {

    form.addEventListener("submit", function () {

        const button = form.querySelector(
            'button[type="submit"]'
        );

        if (button) {

            button.classList.add("loading");

            setTimeout(function () {

                button.classList.remove("loading");

            }, 3000);

        }

    });

});