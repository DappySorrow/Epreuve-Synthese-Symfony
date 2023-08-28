$(document).ready(() => {

    $('.telephone').mask('000-000-0000', { placeholder: '___-___-____' });

    $('.codePostal').mask('AZB ZBZ',
        {
            placeholder: '___ ___',
            translation: {
                A: { pattern: /[AaBbCcEeGgHhJjKkLlMmNnPpRrSsTtVvXxYy]/ },
                B: { pattern: /[AaBbCcEeGgHhJjKkLlMmNnPpRrSsTtVvWwXxYyZz]/ },
                Z: { pattern: /[0-9]/ }
            }
        });

    const registrationForm = document.querySelectorAll('.needs-validation-register');

    addValidationToForm(registrationForm);

});

function addValidationToForm(forms) {
    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }

                form.classList.add('was-validated')
            }, false)
        });
}