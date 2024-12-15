# LOSPAÉ - Logiciel de Suivi de la Participation des Étudiants

## Introduction

Le **LO**giciel de **S**uivi de la **Pa**rticipation des **É**tudiants, ou plus simplement LOSPAÉ, est une application Web permettant de gérer une note de participation (comme son nom l'indique) pour des étudiants.

Cette application était à l'origine un logiciel développé en C# disponible uniquement sous Windows, puis est devenu un site Web accessible depuis n'importe quel périphérique à la sortie de sa version 2.0.0.

## Objectif de l'application

L'application permet de gérer une liste d'étudiants, et de leur ajouter ou retirer des points en fonction des critères de notation de la note de participation. Elle permet également un historique des ajouts et retraits de points, et d'afficher le détail pour chaque étudiant. Depuis la version 3.0.0, la liste d'étudiants peut être composée d'une ou plusieurs promotions différentes.

## RGPD

LOSPAÉ fonctionne sans sauvegarde et n'utilise que les données personnelles nécessaires des étudiants (noms, prénoms et adresses e-mail). Toutes les données sont supprimées définitivement des serveurs dès qu'elles ne sont plus utilisées. Cela concerne, sans s'y limiter, les cas suivants :
* L'étudiant quitte la formation avant son terme
* L'étudiant est arrivé au terme de sa formation
* L'étudiant n'a plus aucun enseignant utilisant LOSPAÉ

## Fonctionnalités de l'application

Les fonctionnalités disponibles dans l'application varient suivant le profil des utilisateurs. Une partie commune existe, accessible par l'ensemble des utilisateurs. D'autres fonctionnalités sont propres aux étudiants, aux enseignants et/ou aux administrateurs. Un enseignant peut également être un administrateur, et cumuler les fonctionnalités des deux rôles.

Aucune inscription n'est possible sur LOSPAÉ : tous les comptes doivent être importés puis activés par un administrateur. Des mots de passe temporaires sont alors générés pour ces comptes, et un document PDF est également généré pour permettre de les transmettre aux propriétaires des comptes.

Toutes les données propres à la note de participation (hormis les critères de notation) fonctionnent au trimestre ou au semestre : à l'issue de chacune de ces périodes, les données peuvent être réinitialisées par les enseignants pour permettre de démarrer une nouvelle période sur une base vierge. Les critères de notation sont conservés pendant les réinitialisations, sont propres à chaque enseignant et peuvent être modifiées à tout moment (de façon non-rétroactive) par ces derniers.

Toutes les données de l'application sont mises à jour en temps réel pour l'ensemble des utilisateurs. Pour que les modifications récentes puissent être vues, les utilisateurs doivent rafraichir ou changer de page.

### Fonctionnalités communes à tous les comptes

* Connexion à son compte
* Profil
  * Changement d'adresse e-mail
  * Changement du mot de passe (pas de règles spécifiques imposées)
  * Changement de la photo de profil (JPG/JPEG, PNG, GIF, WEBP)
* Déconnexion de son compte

### Fonctionnalités propres aux étudiants

Les fonctionnalités des étudiants sont dupliquées pour chaque enseignant utilisant LOSPAÉ pour ces derniers. Un système d'onglet apparait alors pour permettre à l'étudiant de visualiser les données liées à un autre enseignant sur les différentes pages du site.

* Suivi de la note
  * Note actuelle
  * Statistiques générales
    * Nombre de modifications apportées à la note
    * Tendance sur les 15 dernières modifications (baisse, hausse, stable)
    * Total des points gagnés ou perdus
* Historique des modifications apportées (de la plus récente à la plus ancienne)
* Critères de notation

### Fonctionnalités propres aux enseignants

Les fonctionnalités des enseignants, hormis ce qui concerne les critères de notation, sont dupliquées pour chaque promotion gérée par l'enseignant. Cette affectation est réalisée par les administrateurs. Un système d'onglet est mis en place pour permettre de visualiser les données des différentes promotions.

* Gestion des critères de notation
  * Ajout d'un nouveau critère
  * Modification d'un critère existant
  * Suppression d'un critère existant
* Statistiques générales
  * Moyenne actuelle de la classe
  * Nombre de modifications apportées aux notes
  * Total des points gagnés ou perdus
* Suivi des notes actuelles des étudiants
* Historique des modifications apportées (de la plus récente à la plus ancienne)
* Modification des notes
  * Modification de la note d'un seul étudiant (pour un seul motif)
  * Modification simultanée des notes (permet de modifier la note d'un ou plusieurs étudiants pour une ou plusieurs raisons d'un seul coup)
  * Réinitialisation des notes d'un ou plusieurs étudiants
* Gestion des étudiants
  * Réinitialisation d'un mot de passe oublié par un étudiant

### Fonctionnalités propres aux administrateurs

Les administrateurs ne peuvent pas accéder aux données liées aux notes. Ils peuvent cependant gérer les données globales du site.

* Gestion des comptes utilisateurs
  * Importation des comptes
  * Attribution des rôles (étudiant, enseignant, administrateur)
  * Affectation à un établissement scolaire et une promotion
  * Activation ou désactivation des comptes
  * Suppression définitive d'un compte et de toutes les données liées
* Gestion des établissements scolaires
  * Ajout d'un nouvel établissement scolaire
  * Suppression d'un établissement scolaire
* Gestion des promotions
  * Ajout d'une promotion
  * Suppression d'une promotion
* Modération des photos de profil : tous les utilisateurs ayant modifié leur photo de profil sont visibles sur cette page, et l'administrateur peut rétablir l'image par défaut d'utilisateurs qui abuseraient de cette fonctionnalité