# LOSPAÉ - Logiciel de Suivi de la Participation des Étudiants

## Introduction

Le LOgiciel de Suivi de la Participation des Étudiants est une application Web permettant de gérer une note de participation (comme son nom l'indique) pour des étudiants.

Cette application était à l'origine un logiciel disponible uniquement sous Windows, puis est devenu un site Web accessible depuis n'importe quel périphérique à la sortie de sa version 2.0.0.

## Objectif de l'application

L'application permet de gérer une liste d'étudiants, et de leur ajouter ou retirer des points en fonction des critères de notation de la note de participation. Elle permet également un historique des ajouts et retraits de points, et d'afficher le détail pour chaque étudiant. Depuis la version 3.0.0, la liste d'étudiants peut être composée d'une ou plusieurs promotions différentes.

## Fonctionnalités actuellement disponibles

* Authentification des utilisateurs
  * Chaque utilisateur dispose de ces propres droits d'accès : étudiant, enseignant, administrateur
  * Les droits d'enseignant et d'administrateur sont cumulables
  * Un administrateur peut gérer les utilisateurs et les critères de notation
* Suivi des notes des étudiants : les étudiants peuvent, depuis leur compte, visualiser l'état actuel de leurs notes ainsi que les modifications qui y ont été apportées par leurs enseignants
* Gestion des notes des étudiants pour l'enseignant
  * Possibilité de gérer plusieurs promotions dans un ou plusieurs établissements
  * Possibilité de modifier la note d'un ou plusieurs étudiants pour un ou plusieurs motifs simultanément
  * Possibilité d'annuler une modification en cas d'erreur (restitution des points enlevés ou retrait des points ajoutés le cas échéant)
  * Possibilité de visualiser l'historique des modifications apportées à l'ensemble des étudiants gérés
 * Gestion des critères : chaque enseignant dispose de ces propres critères de notation, qu'il peut gérer à tout moment

## Évolution de l'application

Quelques évolutions sont planifiées pour l'application, dans le but d'améliorer LOSPAÉ, notamment en raison des modifications apportées par la version 3.0.0 et l'ajout de la gestion multi-promotions/multi-enseignants. Parmis celles prévues :
* Suivi de la classe par l'enseignant par onglets (un par promotion) pour faciliter la lecture
