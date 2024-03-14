# LOSPAÉ - Logiciel de Suivi de la Participation des Étudiants

## Objectif de l'application

L'application permet de gérer une liste d'étudiants, et de leur ajouter ou retirer des points en fonction des critères de notation de la note de participation. Elle permet également un historique des ajouts et retraits, et d'afficher le détail pour chaque étudiant.

## Fonctionnalités

* Authentification des utilisateurs
  * Chaque utilisateur dispose de ces propres droits d'accès : étudiant, enseignant, administrateur
  * Les droits d'enseignant et d'administrateur sont cumulables
  * Un administrateur peut gérer les utilisateurs et les critères de notation
* Suivi des notes des étudiants : écran permettant d'afficher les notes actuelles de chacun des étudiants gérés par l'enseignant
* Historique des modifications des notes
  * Les étudiants accèdent à l'historique de leur note exclusivement
  * Les enseignants accèdent à l'historique de leurs étudiants exclusivement
  * Un enseignant peut annuler une opération s'il le souhaite
