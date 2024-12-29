const tabButtons = document.querySelectorAll('.tab-button');
const tabContents = document.querySelectorAll('.tab-content');
const tableBody = document.getElementById('table-body');

document.addEventListener('click', (e) => {
    if (e.target.classList.contains('tab-button') && !e.target.classList.contains('active')) {
        document.querySelector('.tab-button.active').classList.remove('active');
        document.querySelector('.tab-content:not(.hidden)').classList.add('hidden');
        document.getElementById("tab-" + e.target.dataset.tab).classList.remove('hidden');
        e.target.classList.add('active');
    }
})
tableBody.addEventListener('click', (e) => {
    let element = e.target;

    while (element && element !== tableBody && !element.classList.contains('user-cart-row')) {
        element = element.parentElement;
    }

    if (element !== tableBody) {
        console.log(element.dataset.cart);
        toggleDetails(element.dataset.cart);
    }
})


function toggleDetails(cartId) {
    const detailsRow = document.getElementById(cartId);
    detailsRow.classList.toggle('hidden');
}