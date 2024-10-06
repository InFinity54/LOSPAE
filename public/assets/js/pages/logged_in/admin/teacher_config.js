const element = $('#promotions')[0];
const choices = new Choices(element, {
    removeItemButton: true,
    loadingText: 'Chargement...',
    noResultsText: 'Aucun résultat trouvé',
    noChoicesText: 'Aucune promotion disponible',
    itemSelectText: 'Cliquez pour sélectionner',
    addItemText: (value) => {
        return `Appuyez sur Entrer pour ajouter la promotion <b>"${value}"</b>`;
    },
    removeItemIconText: () => `Retirer la promotion`,
    removeItemLabelText: (value) => `Retirer la promotion ${value}`
});