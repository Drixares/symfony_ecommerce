// Sélection des éléments nécessaires
const addProductButton = document.getElementById('addProductButton');
const popup = document.getElementById('addProductPopup');
const closePopupButton = document.getElementById('closePopupButton');

// Fonction pour afficher le pop-up
addProductButton.addEventListener('click', () => {
    popup.classList.remove('hidden');
});

// Fonction pour cacher le pop-up
closePopupButton.addEventListener('click', () => {
    popup.classList.add('hidden');
});

// Fermer le pop-up si on clique en dehors du contenu
popup.addEventListener('click', (e) => {
    if (e.target === popup) {
        popup.classList.add('hidden');
    }
});