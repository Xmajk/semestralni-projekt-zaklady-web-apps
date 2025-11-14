function toggleForm() {
    alert("toohle")
    const form = document.getElementById('add-user-form');
    form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
}

document.getElementById("expand-user-form").addEventListener("click",toggleForm)

const filterInput = document.querySelector('.filter-username');
const rows = document.querySelectorAll('tbody tr');

filterInput.addEventListener('input', function () {
    const filterValue = this.value.toLowerCase().trim();

    rows.forEach(row => {
        const usernameCell = row.querySelector('[mark="username"]');
        const username = usernameCell.textContent.toLowerCase();

        if (username.includes(filterValue)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});