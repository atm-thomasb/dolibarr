# Change Log
All notable changes to this project will be documented in this file.

## [Unreleased]



## Version 1.6
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
