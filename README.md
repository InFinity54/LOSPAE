# LOSPAÉ - Logiciel de Suivi de la Participation des Étudiants

## Objectif de l'application

L'application permet de gérer une liste d'étudiants, et de leur ajouter ou retirer des points en fonction des critères de notation de la note de participation. Elle permet également un historique des ajouts et retraits, et d'afficher le détail pour chaque étudiant.

## Fonctionnalités

* Modifier la note d'un étudiant (choisi via une liste déroulante), en fonction du comportement de ce dernier en cours
* Afficher les notes actuelles de l'ensemble des étudiants
* Importer une nouvelle liste d'étudiants (via fichier CSV)

### Fichier CSV d'importation

Pour importer une nouvelle liste d'étudiants, le fichier CSV devra respecter le format suivant.

| Nom  | Prenom    | Spec |
|------|-----------|------|
| UN   | Étudiant  | SISR |
| DEUX | Étudiante | SLAM |

ATTENTION : Importer une nouvelle liste d'étudiants supprimera toutes les données de l'application. Les étudiants précédemment enregistrés, ainsi que le suivi de leur note, sera définitivement supprimé.