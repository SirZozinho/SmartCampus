function clearSearch() {
    // Efface la valeur de la barre de recherche
    const searchBar = document.getElementById('searchBar');
    if (searchBar) {
        searchBar.value = '';
    }

    // // Réinitialise tous les champs de type 'select' dans le formulaire
    // const selects = document.querySelectorAll('form select');
    // selects.forEach(select => {
    //     select.value = 'all'; // Réinitialise à "all" pour les filtres d'étage et d'état
    // });

    // Soumet le formulaire après la réinitialisation
    const form = document.querySelector('form');
    if (form) {
        form.submit();
    }
}
