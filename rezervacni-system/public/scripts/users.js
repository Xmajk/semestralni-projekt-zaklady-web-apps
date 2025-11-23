function toggleForm() {
    let visibilityclass = "novisible"
    const form = document.getElementById('add-user-form');
    if(form.classList.contains(visibilityclass)){
        form.classList.remove(visibilityclass)
    }else{
        form.classList.add(visibilityclass)
    }
}

document.getElementById("expand-user-form").addEventListener("click",toggleForm)

const filterInput = document.querySelector('.filter-username');
const rows = document.querySelectorAll('tbody tr');

filterInput.addEventListener('input', function () {
    const filterValue = this.value.toLowerCase().trim();

    rows.forEach(row => {
        const usernameCell = row.querySelector('.username-col');
        const username = usernameCell.textContent.toLowerCase();

        if (username.includes(filterValue)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});