$("#avatar").dropify({
    messages: {
        'default': 'Glissez-déposez un fichier ou cliquez ici',
        'replace': 'Glissez-déposez un fichier ou cliquez ici pour remplacer le fichier actuel',
        'remove':  'Supprimer',
        'error':   'Une erreur est survenue.'
    },
    error: {
        'fileSize': 'La taille du fichier est trop grande ({{ value }} max).',
        'minWidth': 'La largeur de l\'image est trop petite ({{ value }} px min).',
        'maxWidth': 'La largeur de l\'image est trop grande ({{ value }} px max).',
        'minHeight': 'La hauteur de l\'image est trop petite ({{ value }} px min).',
        'maxHeight': 'La hauteur de l\'image est trop grande ({{ value }} px max).',
        'fileExtension': 'Ce type de fichier n\'est pas autorisé. Types autorisés : {{ value }}.'
    }
});