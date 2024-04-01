$("#csvfile").dropify({
    messages: {
        'default': 'Glissez-déposez un fichier ou cliquez ici',
        'replace': 'Glissez-déposez un fichier ou cliquez ici pour remplacer le fichier actuel',
        'remove':  'Supprimer',
        'error':   'Une erreur est survenue.'
    },
    error: {
        'fileSize': 'La taille du fichier est trop grande ({{ value }} max).',
        'fileExtension': 'Ce type de fichier n\'est pas autorisé. Types autorisés : {{ value }}.'
    }
});