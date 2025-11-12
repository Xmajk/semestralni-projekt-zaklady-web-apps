// ===============================================
// VALIDACE FORMULÁŘE PRO PŘIDÁNÍ UŽIVATELE (Real-time, pod inputem)
// ===============================================

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
    // MINIMÁLNÍ DÉLKA HESLA MUSÍ ODPOVÍDAT SERVERU (8 znaků)
    const MIN_PASSWORD_LENGTH = 8;
    const MIN_USERNAME_LENGTH = 3;

    // Funkce pro zobrazení obecné chyby (pro submit)
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

    // Funkce pro zobrazení chyby pod konkrétním inputem
    function displayFieldError(inputElement, message) {
        const errorId = 'error-' + inputElement.id.substring(5);
        const errorSpan = document.getElementById(errorId);

        if (errorSpan) {
            errorSpan.textContent = message;
            errorSpan.classList.add('active');
        }
        // Nastavíme custom validity pro fallback (např. při submitu)
        inputElement.setCustomValidity(message);
    }

    // Funkce pro vyčištění chyby pod konkrétním inputem
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

    // Centrální funkce pro validaci jednoho pole
    function validateField(inputElement) {
        clearFieldError(inputElement);
        const value = inputElement.value.trim();
        const id = inputElement.id;
        let errorMessage = '';

        // Kontrola povinnosti (HTML 'required' je fallback)
        if (inputElement.hasAttribute('required') && value === '' && id !== 'form-password') {
            errorMessage = 'Toto pole je povinné.';
        }

        // Specifická kontrola podle pole
        if (errorMessage === '') {
            switch (id) {
                case 'form-username':
                    if (value === '') {
                        errorMessage = 'Uživatelské jméno je povinné.';
                    } else if (value.length > 0 && value.length < MIN_USERNAME_LENGTH) {
                        errorMessage = `Uživatelské jméno musí mít alespoň ${MIN_USERNAME_LENGTH} znaky.`;
                    }
                    break;
                case 'form-email':
                    if (value === '') {
                        errorMessage = 'E-mail je povinný.';
                    } else if (value.length > 0 && !isValidEmail(value)) {
                        errorMessage = 'Zadejte prosím platnou e-mailovou adresu.';
                    }
                    break;
                case 'form-password':
                    if (value === '') {
                        errorMessage = 'Heslo je povinné.';
                    } else if (value.length > 0 && value.length < MIN_PASSWORD_LENGTH) {
                        errorMessage = `Heslo musí mít alespoň ${MIN_PASSWORD_LENGTH} znaků.`;
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

        return true;
    }

    // Funkce pro validaci všech polí (používá se při submitu)
    function validateAll() {
        let isFormValid = true;
        fields.forEach(input => {
            // Musíme volat validateField, abychom vynutili kontrolu
            if (!validateField(input)) {
                isFormValid = false;
            }
        });
        return isFormValid;
    }

    // 1. Nastavení real-time validace na událost 'input'
    fields.forEach(input => {
        // Kontrola při psaní
        input.addEventListener('input', () => {
            validateField(input);
            clearGeneralError();
        });
        // Kontrola při opuštění pole
        input.addEventListener('blur', () => {
            validateField(input);
        });
    });

    // 2. Nastavení finální validace při odeslání formuláře
    form.addEventListener('submit', function(event) {
        const isFormValid = validateAll();

        if (!isFormValid) {
            event.preventDefault();

            // Najdeme první nevalidní pole a na něj zaostříme
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