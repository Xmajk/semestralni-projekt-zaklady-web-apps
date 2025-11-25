const NAME_LEN = [3,40]
const DESCRIPTION_LEN = [0,1_000]
const PLACE_LEN = [0,100]

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

function validationPrice(value){
    return value.match(/^\d+$/gm)
}
function validationCapacity(value){
    return value.match(/^\d+$/gm)
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
            case 'form-name':
                if(value === ''){
                    errorMessage = 'Název je povinný';
                }else if(value.length<NAME_LEN[0]){
                    errorMessage = 'Uživatelské jménu musí být dlouhé minimálně '+NAME_LEN[0]+' znaků'
                }else if(value.length>NAME_LEN[1]){
                    inputElement.value = value.substring(0,NAME_LEN[1]);
                }
                break;
            case 'form-description':
                if(value.length>DESCRIPTION_LEN[1]){
                    errorMessage = 'Popis nesmí mít více jak '+DESCRIPTION_LEN[1]+' znaků'
                }
                break;
            case 'form-location':
                if(value===''){
                    errorMessage = 'Místo je povinné'
                }else if(value.length<PLACE_LEN[0]){
                    errorMessage = 'Místo musí být dlouhé minimálně '+PLACE_LEN[0]+' znaků'
                }else if(value.length>PLACE_LEN[1]){
                    inputElement.value = value.substring(0,PLACE_LEN[1]);
                }
                break;
            case 'form-price':
                if(value !== '' && value.trim().toLowerCase()!=="zdarma" && !validationPrice(value)){
                    errorMessage = "Cena musí být číslo"
                }
                else if(value.startsWith("0") && validationPrice(value)){
                    inputElement.value = "zdarma";
                }
                break;
            case 'form-capacity':
                if(value !=='' && !validationCapacity(value)){
                    errorMessage = 'Kapacita musí být číslo'
                }
                break;
            case 'form-start-datetime':
                if(value===''){
                    errorMessage = 'Datum a čas konání je povinné'
                }else if(new Date(value)<new Date()){
                    errorMessage = 'Datum a čas konání musí být v budoucnosti'
                }
                break;
            case 'form-registration-deadline':
                if(value===''){
                    errorMessage = 'Datum a čas konce registrace je povinné'
                }else if(new Date(value)<new Date()){
                    errorMessage = 'Datum a čas konce registrace musí být v budoucnosti'
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


const form = document.getElementById('add-event-form');

const fields = [
    document.getElementById('form-name'),
    document.getElementById('form-description'),
    document.getElementById('form-location'),
    document.getElementById('form-price'),
    document.getElementById('form-capacity'),
    document.getElementById('form-start-datetime'),
    document.getElementById('form-registration-deadline')
].filter(el => el != null);

fields.forEach(async input => {
    input.addEventListener('input',async () => {
        await validateField(input);
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
    } else {
        clearGeneralError();
    }
});

function toggleForm() {
    const form = document.getElementById('add-event-form');
    form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
}
const btn = document.getElementById("show-form-btn").addEventListener("click",toggleForm)

const filterInput = document.getElementById('event-filter');
const rows = document.querySelectorAll('tbody tr');

if(filterInput) {
    filterInput.addEventListener('input', function () {
        const filterValue = this.value.toLowerCase().trim();

        rows.forEach(row => {
            // Ujistěte se, že buňka existuje a má atribut 'mark'
            const nameCell = row.querySelector('.filter-element');

            if (nameCell) { // Pokud je to řádek s událostí
                const name = nameCell.textContent.toLowerCase();
                if (name.includes(filterValue)) {
                    row.style.display = ''; // Zobrazit řádek
                } else {
                    row.style.display = 'none'; // Skrýt řádek
                }
            } else if (filterValue === "" && row.querySelector('td[colspan]')) {
                // Zobrazit zprávu "Zatím nebyly vytvořeny..." pokud je filtr prázdný
                row.style.display = '';
            } else if (row.querySelector('td[colspan]')) {
                // Skrýt zprávu "Zatím nebyly vytvořeny..." pokud se filtruje
                row.style.display = 'none';
            }
        });
    });
}