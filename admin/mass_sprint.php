<?php
// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if(! $res && ! empty($_SERVER['CONTEXT_DOCUMENT_ROOT'])) $res = @include $_SERVER['CONTEXT_DOCUMENT_ROOT'].'/main.inc.php';
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME'];
$tmp2 = realpath(__FILE__);
$i = strlen($tmp) - 1;
$j = strlen($tmp2) - 1;
while($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
    $i--;
    $j--;
}
if(! $res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1)).'/main.inc.php')) $res = @include substr($tmp, 0, ($i + 1)).'/main.inc.php';
if(! $res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1))).'/main.inc.php')) $res = @include dirname(substr($tmp, 0, ($i + 1))).'/main.inc.php';
// Try main.inc.php using relative path
if(! $res && file_exists('../../main.inc.php')) $res = @include '../../main.inc.php';
if(! $res && file_exists('../../../main.inc.php')) $res = @include '../../../main.inc.php';
if(! $res) die('Include of main fails');

/**
 * @var DoliDB $db
 * @var User $user
 * @var Translate $langs
 */

// Libraries
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once '../lib/scrumproject.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
dol_include_once('/scrumproject/class/scrumsprint.class.php');
dol_include_once('/scrumproject/lib/scrumproject_scrumsprint.lib.php');

// Translations
$langs->loadLangs(array('errors', 'admin', 'scrumproject@scrumproject'));

// Access control
if(!$user->admin) accessforbidden();

// Parameters
$action = GETPOST('action', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');

//Sprint object
$object = new ScrumSprint($db);

/*
 * Actions
 */
if($action == 'mass_create'){
    $Tteam = GETPOST('fk_team','array');
    $str_date_start = GETPOST('date_start','alpha');
    $str_date_end = GETPOST('date_end','alpha');
    $numsprint = GETPOST('numsprint','int');
    $startsprintweek = GETPOST('startsprintweek','int');
    $endsprintweek = GETPOST('endsprintweek','int');
    $sprintduration = GETPOST('sprintduration','int');

    //Créé les sprints pour chaque team
    //Récupère les dates au format désiré pour le traitement
    $dateStart = dol_mktime(
        0,
        0,
        0,
        GETPOST('date_startmonth', 'int'),
        GETPOST('date_startday', 'int'),
        GETPOST('date_startyear', 'int')
    );

    $dateEnd = dol_mktime(
        0,
        0,
        0,
        GETPOST('date_endmonth', 'int'),
        GETPOST('date_endday', 'int'),
        GETPOST('date_endyear', 'int')
    );

    //On défini la date de référence actuelle pour le premier sprint créé
    $dateCur = $dateStart;

    //Tant que la date de début du sprint est inférieur à la date de fin sélectionnée pour la génération des sprints, alors on continue la création
    //Utiliser pour le nombre de sprint créé
    $nbsprint = 0;

    while($dateCur < $dateEnd) {
        foreach($Tteam as $team) {
            $sprint = new ScrumSprint($db);

            //Récupère le nom de l'équipe
            $scrum_team = new UserGroup($db);
            $resTeam = $scrum_team->fetch($team);
            if (!$resTeam) {
                //Ne trouve pas de groupe utilisateur
                setEventMessage('ScrumMassSprintFetchGroupUserError','errors');
                header('Location: '.DOL_URL_ROOT.'/custom/scrumproject/admin/mass_sprint.php', 1);
                exit;
            }

            //Conversion du numéro du jours de la semaine en jours (string)
            $startDay = array(
                    0 => 'sunday',
                    1 => 'monday',
                    2 => 'tuesday',
                    3 => 'wednesday',
                    4 => 'thursday',
                    5 => 'friday',
                    6 => 'saturday'
            );
            $enDay = array(
                    0 => 'sunday',
                    1 => 'monday',
                    2 => 'tuesday',
                    3 => 'wednesday',
                    4 => 'thursday',
                    5 => 'friday',
                    6 => 'saturday'
            );

            //On vérifie si le jours de la semaine(Lundi, Mardi...) correspond au jours sélectionné, sinon on prends le suivant
            $dateS = (date('w', $dateCur) == $startsprintweek) ? $dateCur : strtotime('next '.$startDay[$startsprintweek], $dateCur);
            $dateE = dol_time_plus_duree($dateCur, $sprintduration, 'w');
            $dateE = (date('w', $dateE) == $endsprintweek) ? $dateE : strtotime('previous '.$enDay[$endsprintweek], $dateE);

            //On définit le sprint
            $sprint->fk_team = $team;
            $sprint->date_start = $dateS;
            $sprint->date_end = $dateE;
            $sprint->label = 'Sprint '.$numsprint.' '.$scrum_team->name;

            $resSprint = $sprint->create($user);
            if ($resSprint > 0) {
                //Nombre de sprint créé
                $nbsprint++;
            } else {
                //Ne peut pas créer de sprint
                setEventMessage('ScrumMassSprintCreateError','errors');
                header('Location: '.DOL_URL_ROOT.'/custom/scrumproject/admin/mass_sprint.php', 1);
                exit;
            }
        }
        //Mise à jour des dates selon la fin du sprint actuel
        $dateCur = dol_time_plus_duree($dateCur, $sprintduration, 'w');

        //Numéro du sprint incrémenté
        $numsprint++;
    }
    //Redirection vers la liste des sprints
    header('Location: '.DOL_URL_ROOT.'/custom/scrumproject/scrumsprint_list.php?search_status=draft');
    setEventMessage($langs->trans('ScrumMassSprintCreated',$nbsprint));
    exit;
}

/*
 * View
 */
$page_name = 'ScrumMassSprint';
llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="'.($backtopage ? $backtopage : DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.$langs->trans('BackToModuleList').'</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'tools');

// Configuration header
$head = scrumprojectAdminPrepareHead();
print dol_get_fiche_head($head, 'mass_sprint', 'ScrumMassSprint', -1, 'scrumproject@scrumproject');

//Content
$form = new Form($db);
$formother = new FormOther($db);

$title = $langs->trans('ScrumSprint');
$help_url = '';



print '<div class="clearboth"></div>';
print '<div class="fichecenter">';

print '<div class="fichehalfleft">';

print '<fieldset>';
print '<legend>'.$langs->trans('ScrumMassSprintParameters').'</legend>';

print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
print '<input type="hidden" name="token" value="'.newToken().'">';
print '<input type="hidden" name="action" value="mass_create">';
if($backtopage) print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
if($backtopageforcancel) print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';

print '<table>';

print '<tr>';
print '<td>'.$langs->trans($object->fields['fk_team']['label']).'</td>';
print '<td>'.$object->showInputField($object->fields['fk_team'], 'fk_team', '', 'multiple', '', '', 'minwidth500').'</td>';
print '</tr>';

print '<tr>';
print '<td>'.$langs->trans($object->fields['date_start']['label']).'</td>';
print '<td>'.$object->showInputField($object->fields['date_start'], 'date_start', '', '', '', '', 0).'</td>';
print '</tr>';

print '<tr>';
print '<td>'.$langs->trans($object->fields['date_end']['label']).'</td>';
print '<td>'.$object->showInputField($object->fields['date_end'], 'date_end', '', '', '', '', 0).'</td>';
print '</tr>';

print '<tr>';
print '<td>'.$langs->trans('ScrumMassSprintNumber').'</td>';
print '<td><input type="number" name="numsprint">'.'</td>';
print '</tr>';

print '<tr>';
print '<td>'.$langs->trans('ScrumMassSprintStartSprintWeek').'</td>';
print '<td>'.$formother->select_dayofweek('1', 'startsprintweek', 0).'</td>';
print '</tr>';

print '<tr>';
print '<td>'.$langs->trans('ScrumMassSprintEnSprintWeek').'</td>';
print '<td>'.$formother->select_dayofweek('5', 'endsprintweek', 0).'</td>';
print '</tr>';

print '<tr>';
print '<td>'.$langs->trans('ScrumMassSprintDuration').'</td>';
print '<td><input type="number" name="sprintduration">'.'</td>';
print '</tr>';

print '</table>';

print '<div class="center">';
print '<input type="submit" class="button" name="add" value="'.dol_escape_htmltag($langs->trans('Create')).'">';
print '&nbsp; ';
print '<input type="'.($backtopage ? 'submit' : 'button').'" class="button button-cancel" name="cancel" value="'.dol_escape_htmltag($langs->trans('Cancel')).'"'.($backtopage ? '' : ' onclick="javascript:history.go(-1)"').'>'; // Cancel for create does not post form if we don't know the backtopage
print '</div>';

print '</form>';

print '</fieldset>';


print '</div>';

print '<div class="fichehalfright">';
print '<fieldset>';
print '<legend>'.$langs->trans('ScrumMassSprintParametersHelp').'</legend>';
print '<p>'.$langs->trans('ScrumMassSprintParametersHelpText').'</p>';
print '</fieldset>';
print '</div>';

print '</div>'; // close fichecenter



?>

<script>
    jQuery(document).ready(
        function() {
            $('#fk_team').attr('name','fk_team[]');
        }
    );
</script>

<?php
//Page end
print dol_get_fiche_end(-1);
llxFooter();
$db->close();

