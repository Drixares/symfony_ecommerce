
const editButton = document.getElementById('editButton');
const modal = document.getElementById('editProfileModal');
const closeModalButton = document.getElementById('closeModalButton');

// Open the modal when the edit button is clicked
editButton.addEventListener('click', function () {
    modal.classList.remove('hidden'); // Show the modal
});

// Close the modal when the close button is clicked
closeModalButton.addEventListener('click', function () {
    modal.classList.add('hidden'); // Hide the modal
});

// Close the modal when clicking outside the modal
modal.addEventListener('click', function (event) {
    if (event.target === modal) {
        modal.classList.add('hidden'); // Hide the modal
    }
});

