document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('add-user-form');
    if (!form) return;

    const fields = [
        document.getElementById('form-username'),
        document.getElementById('form-email'),
        document.getElementById('form-firstname'),
        document.getElementById('form-lastname'),
        document.getElementById('form-bdate'),
        document.getElementById('form-password')
    ].filter(el => el != null);

    const errorDisplay = document.getElementById('form-error');
    const MIN_PASSWORD_LENGTH = 8;
    const MIN_USERNAME_LENGTH = 3;

    function displayGeneralError(message) {
        if (errorDisplay) {
            errorDisplay.textContent = message;
            errorDisplay.className = 'error active';
            errorDisplay.style.display = 'block';
        }
    }
    function clearGeneralError() {
        if (errorDisplay) {
            errorDisplay.textContent = '';
            errorDisplay.className = 'error';
            errorDisplay.style.display = 'none';
        }
    }

    function displayFieldError(inputElement, message) {
        const errorId = 'error-' + inputElement.id.substring(5);
        const errorSpan = document.getElementById(errorId);
        if (errorSpan) {
            errorSpan.textContent = message;
            errorSpan.classList.add('active');
        }
        inputElement.setCustomValidity(message);
    }

    function clearFieldError(inputElement) {
        const errorId = 'error-' + inputElement.id.substring(5);
        const errorSpan = document.getElementById(errorId);

        if (errorSpan) {
            errorSpan.textContent = '';
            errorSpan.classList.remove('active');
        }
        inputElement.setCustomValidity('');
    }

    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    async function validateField(inputElement) {
        const value = inputElement.value.trim();
        const id = inputElement.id;
        let errorMessage = '';

        if (inputElement.hasAttribute('required') && value === '' && id !== 'form-password') {
            errorMessage = 'Toto pole je povinné';
        }

        if (errorMessage === '') {
            switch (id) {
                case 'form-username':
                    if (value === '') {
                        errorMessage = 'Uživatelské jméno je povinné';
                    } else if (value.length > 0 && value.length < MIN_USERNAME_LENGTH) {
                        errorMessage = `Uživatelské jméno musí mít alespoň ${MIN_USERNAME_LENGTH} znaky.`;
                    } else if (await fetchUsernameValidation(value)){
                        errorMessage = 'Uživatelské jméno již existuje';
                    }

                    break;
                case 'form-email':
                    if (value === '') {
                        errorMessage = 'E-mail je povinný';
                    } else if (value.length > 0 && !isValidEmail(value)) {
                        errorMessage = 'Zadejte prosím platnou e-mailovou adresu';
                    }
                    break;
                case 'form-password':
                    if (value === '') {
                        errorMessage = 'Heslo je povinné';
                    } else if (value.length > 0 && value.length < MIN_PASSWORD_LENGTH) {
                        errorMessage = `Heslo musí mít alespoň ${MIN_PASSWORD_LENGTH} znaků`;
                    }
                    break;
                case 'form-firstname':
                case 'form-lastname':
                case 'form-bdate':
                    if (value === '') {
                        errorMessage = 'Toto pole je povinné.';
                    }
                    break;
            }
        }

        if (errorMessage) {
            displayFieldError(inputElement, errorMessage);
            return false;
        }
        clearFieldError(inputElement);
        return true;
    }

    function validateAll() {
        let isFormValid = true;
        fields.forEach(input => {
            if (!validateField(input)) {
                isFormValid = false;
            }
        });
        return isFormValid;
    }

    async function fetchUsernameValidation(username){
        let errorMessage = "";
        const searchParams = new URLSearchParams();
        searchParams.set("username",username);

        let response = await fetch(
            URL_PREFIX+"/admin/username_exists.php?"+searchParams.toString(),
            {
                method:"GET",
                redirect: "manual",
                headers:{},
            }
        )
        return (await response.text())==="true";
    }

    fields.forEach(input => {
        input.addEventListener('input', () => {
            validateField(input);
        });
        input.addEventListener('blur', () => {
            validateField(input);
        });
    });

    form.addEventListener('submit', function(event) {
        const isFormValid = validateAll();

        if (!isFormValid) {
            event.preventDefault();

            const firstInvalid = fields.find(input => input.checkValidity() === false);
            if (firstInvalid) {
                firstInvalid.focus();
            }
            displayGeneralError('Opravte prosím chyby ve formuláři.');
        } else {
            clearGeneralError();
        }
    });
});