# Change Log
All notable changes to this project will be documented in this file.

## [Unreleased]

- NEW : Add substitution *30/11/2022* - 1.22.0
- NEW : Add model email possibility *30/11/2022* - 1.21.0
- NEW : Kanban, add addTime on menu list Task *30/11/2022* - 1.20.0

## Version 1.19

- FIX : Hours qty conversion to time display  *24/11/2022* - 1.19.1
- NEW : Add total on kanban sprint dashboard  *24/11/2022* - 1.19.0
- NEW : Ux improvement allow card scroll on drag  *24/11/2022* - 1.18.0
- NEW : Create task in kanban just after US *24/11/2022* - 1.17.0
- FIX : Fatal error missing lib *21/11/2022* - 1.16.4
- FIX : css img card position *21/11/2022* - 1.16.3
- FIX : missing backport V16 function  *14/11/2022* - 1.16.2
- FIX : Time display error  *09/11/2022* - 1.16.1
- NEW : Add Kanban sprint dashBoard *09/11/2022* - 1.16.0
- FIX : Database col rename for llx_scrumproject_scrumsprintuser *09/11/2022* - 1.15.0  
  Change qty_availablity -> qty_availability  
  Change availablity_rate -> availability_rate 

## Version 1.14

- FIX : Suppréssion du bouton afficher kanban et ajout getnomurl du scrumkanban sous date de fin - *25/11/2022* - 1.14.10
- FIX : Pagination ScrumSprint page *25/11/2022* - 1.14.9
- FIX : filter soc and thirdparty *09/11/2022* - 1.14.8
- FIX : update time on kanban  *09/11/2022* - 1.14.7
- FIX : remove statut card on scrumcard_card.php *09/11/2022* - 1.14.6
- FIX : label on US planned *09/11/2022* - 1.14.5
- FIX : move butNewKanban to first position *09/11/2022* - 1.14.4
- FIX : Sprint and Kanban times updates *09/11/2022* - 1.14.3
- FIX : Assign user to right element or card and update context menu *04/11/2022* - 1.14.2

- FIX : Contact tab display for scrum task and scrum Us *26/10/2022* - 1.14.1
- NEW : Option for kanban status display *26/10/2022* - 1.14.0
- FIX : Backport du fiw sql des longueurs de colonnes element_element *26/10/2022* - 1.13.0
- FIX : Ajout d'une tâche provenant d'une User Story découpée dans la même colonne *12/10/2022* - 1.12.2
- NEW : Ajout de tag/catégorie dans le left menu *22/09/2022* - 1.12.1
- NEW : Ajout de l'édition des tags sur une card du kanban *20/09/2022* - 1.12.0
- NEW : Ajout de tag aux cards du kanban *14/09/2022* - 1.11.0
- NEW : Ajout dispo/velo des dev à la création d'un sprint + extrafield scrumproject_velocity_rate*13/09/2022* - 1.10.0 
- FIX : Live edit update for user velocity *13/09/2022* - 1.9.1
- NEW : Ajout d'une option pour permettre l'affichage du menu gauche du kanban *13/09/2022* - 1.9.0
- NEW : Mise à jour de l'affichage page sprint card *12/09/2022* - 1.8.0
- FIX : Caché le status de la tache sur une carte vide *12/09/2022* - 1.7.1
- NEW : Kanban drop card status *12/10/2022* - 1.7.0

## Version 1.6

 - FIX : - Transformer les heures afficher au format numérique en format heure sur us card - *03/11/2022)* - 1.6.4  
 - FIX :  - *27/10/2022)* - 1.6.3 
     - badge sur onglet dispo RH sur fiche sprint (ajouter nombre de lignes associées à l'onglet) + MAJ dol banner comme les autres onglets
     - avoir un onglet us planifiées sur la fiche sprint
     - ordre des champs de recherche de la list des planifiées à fixer
     - le temps passé ne se recalcule pas suite suppression temps  -> Si on supprime un temps passé depuis la kanban onglet temps consommé d'une tâche SCRUM (carte) il n'est pas mis à jour sur la tache scrum. Le sprint une fois recalculé ne prend pas non plus en compte cette modification.
     - Arrondi manquant sur les chiffres (ne doit pas dépendre de la conf dolibarr)
     - Transformer les heures afficher au format numérique en format heure : 2,5 -> 02:30 (2h30)
   
 - FIX : Calcule des lignes d'un découpage de carte à chaque clic - *11/10/2022* - 1.6.2
 - FIX : multiple - *29/09/2022* - 1.6.1
 - Le masque de numérotation dans l'admin ne marche pas
 - Les flèches de navigation font perdre le fk_sprint etc                                     (sur tab dispo rh)
 - au moment de la transformation de taĉhe en US, les chiffres sont arrondies (4 au lieu de 3.5 et 5.25 devient 6L11                                                                                (dans projet créer une tache puis scrum boarsd … )
 - La validation d'un sprint ne fonctionne pas (sans doute lié au problème de numérotation de la conf)
 - La suppression d'US depuis leur card ne fonctionne pas

- NEW : Ajout de massaction pour la création de sprint *07/09/2022* - 1.6.0
- NEW : Ajout Création d'un kanban depuis sprint card avec clonage de la structure *07/09/2022* - 1.5.0
- NEW : Ajout de massaction de planification pour les US et les US planif *29/08/2022* - 1.4.0
- NEW : Add Tab Kanban to Sprint card page *17/08/2022* - 1.3.0

## Version 1.2

- FIX : update view planif US *02/09/2022* -1.2.4
- FIX : Update menu *17/08/2022* - 1.2.3
- FIX : Object element name *05/08/2022* - 1.2.1
- NEW : Planning interface for user stories *05/08/2022* - 1.2.0

## Version 1.1

- NEW : Split dialog for kanban card of type User story or Scrum task *28/07/2022* - 1.1.0

## Version 1.0 [2020-12-25]

- FIX : Suppression des champs qté réalisée et quantité prévue à la création d'un sprint *21/07/2022* - 1.0.9
- FIX : Update visuel de la liste US planifiée *21/07/2022* - 1.0.8
- FIX : SQL : Renvoi la liste de Tâches scrum de la fiche US planifiée concernée *21/07/2022* - 1.0.7
- FIX : Fiche create et modify US planifiée et Tache Scrum : Suppression champ statut *21/07/2022* - 1.0.6
- FIX : Formulaire U.S. et U.S. Planifiée type html champ description *21/07/2022* - 1.0.5
- FIX : Modification langs Qty consommée *21/07/2022* - 1.0.4
- FIX : scrumuserstorysprint status BTN  *03/05/2022* - 1.0.3
- FIX : sql query for scrum quntity summary  *03/05/2022* - 1.0.2
- FIX : elements names *03/05/2022* - 1.0.1
- Manage sprints
- Start a scrum project management module
