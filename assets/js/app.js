document.documentElement.classList.add("js");

document.addEventListener("DOMContentLoaded", () => {
    const navToggle = document.querySelector(".nav-toggle");
    const navigation = document.querySelector(".nav");

    if (navToggle && navigation) {
        navToggle.addEventListener("click", () => {
            const isOpen = navigation.classList.toggle("is-open");
            navToggle.setAttribute("aria-expanded", String(isOpen));
        });

        navigation.addEventListener("click", (event) => {
            if (event.target.closest("a")) {
                navigation.classList.remove("is-open");
                navToggle.setAttribute("aria-expanded", "false");
            }
        });

        document.addEventListener("keydown", (event) => {
            if (event.key === "Escape") {
                navigation.classList.remove("is-open");
                navToggle.setAttribute("aria-expanded", "false");
                navToggle.focus();
            }
        });
    }

    const revealElements = document.querySelectorAll("[data-reveal]");

    if ("IntersectionObserver" in window) {
        const revealObserver = new IntersectionObserver(
            (entries, observer) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add("is-visible");
                        observer.unobserve(entry.target);
                    }
                });
            },
            { rootMargin: "0px 0px -40px", threshold: 0.08 }
        );

        revealElements.forEach((element) => revealObserver.observe(element));
    } else {
        revealElements.forEach((element) => element.classList.add("is-visible"));
    }

    const commentField = document.querySelector("#comentario");
    const characterCount = document.querySelector("[data-character-count]");

    if (commentField && characterCount) {
        const updateCount = () => {
            characterCount.textContent = String(commentField.value.length);
        };

        commentField.addEventListener("input", updateCount);
        updateCount();
    }

    const contactForm = document.querySelector("[data-contact-form]");

    if (contactForm) {
        contactForm.addEventListener("submit", (event) => {
            const requiredFields = contactForm.querySelectorAll("[required]");
            let firstInvalid = null;

            requiredFields.forEach((field) => {
                const isValid = field.checkValidity();
                field.setAttribute("aria-invalid", String(!isValid));

                if (!isValid && !firstInvalid) {
                    firstInvalid = field;
                }
            });

            if (firstInvalid) {
                event.preventDefault();
                firstInvalid.focus();
                firstInvalid.reportValidity();
            }
        });

        contactForm.querySelectorAll("[required]").forEach((field) => {
            field.addEventListener("input", () => {
                if (field.checkValidity()) {
                    field.removeAttribute("aria-invalid");
                }
            });
        });
    }

    document.querySelectorAll("[data-alert-close]").forEach((button) => {
        button.addEventListener("click", () => {
            button.closest("[data-alert]")?.remove();
        });
    });
});
