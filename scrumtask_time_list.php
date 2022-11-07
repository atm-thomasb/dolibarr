<?php
/* Copyright (C) 2007-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) ---Put here your own copyright and developer email---
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 *  \file       scrumtask_note.php
 *  \ingroup    scrumproject
 *  \brief      Tab for notes on ScrumTask
 */

//if (! defined('NOREQUIREDB'))              define('NOREQUIREDB', '1');				// Do not create database handler $db
//if (! defined('NOREQUIREUSER'))            define('NOREQUIREUSER', '1');				// Do not load object $user
//if (! defined('NOREQUIRESOC'))             define('NOREQUIRESOC', '1');				// Do not load object $mysoc
//if (! defined('NOREQUIRETRAN'))            define('NOREQUIRETRAN', '1');				// Do not load object $langs
//if (! defined('NOSCANGETFORINJECTION'))    define('NOSCANGETFORINJECTION', '1');		// Do not check injection attack on GET parameters
//if (! defined('NOSCANPOSTFORINJECTION'))   define('NOSCANPOSTFORINJECTION', '1');		// Do not check injection attack on POST parameters
//if (! defined('NOCSRFCHECK'))              define('NOCSRFCHECK', '1');				// Do not check CSRF attack (test on referer + on token if option MAIN_SECURITY_CSRF_WITH_TOKEN is on).
//if (! defined('NOTOKENRENEWAL'))           define('NOTOKENRENEWAL', '1');				// Do not roll the Anti CSRF token (used if MAIN_SECURITY_CSRF_WITH_TOKEN is on)
//if (! defined('NOSTYLECHECK'))             define('NOSTYLECHECK', '1');				// Do not check style html tag into posted data
//if (! defined('NOREQUIREMENU'))            define('NOREQUIREMENU', '1');				// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))            define('NOREQUIREHTML', '1');				// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))            define('NOREQUIREAJAX', '1');       	  	// Do not load ajax.lib.php library
//if (! defined("NOLOGIN"))                  define("NOLOGIN", '1');					// If this page is public (can be called outside logged session). This include the NOIPCHECK too.
//if (! defined('NOIPCHECK'))                define('NOIPCHECK', '1');					// Do not check IP defined into conf $dolibarr_main_restrict_ip
//if (! defined("MAIN_LANG_DEFAULT"))        define('MAIN_LANG_DEFAULT', 'auto');					// Force lang to a particular value
//if (! defined("MAIN_AUTHENTICATION_MODE")) define('MAIN_AUTHENTICATION_MODE', 'aloginmodule');	// Force authentication handler
//if (! defined("NOREDIRECTBYMAINTOLOGIN"))  define('NOREDIRECTBYMAINTOLOGIN', 1);		// The main.inc.php does not make a redirect if not logged, instead show simple error message
//if (! defined("FORCECSP"))                 define('FORCECSP', 'none');				// Disable all Content Security Policies
//if (! defined('CSRFCHECK_WITH_TOKEN'))     define('CSRFCHECK_WITH_TOKEN', '1');		// Force use of CSRF protection with tokens even for GET
//if (! defined('NOBROWSERNOTIF'))     		 define('NOBROWSERNOTIF', '1');				// Disable browser notification

// Load Dolibarr environment
$res = 0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) {
	$res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
}
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) {
	$i--; $j--;
}
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) {
	$res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
}
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) {
	$res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
}
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) {
	$res = @include "../main.inc.php";
}
if (!$res && file_exists("../../main.inc.php")) {
	$res = @include "../../main.inc.php";
}
if (!$res && file_exists("../../../main.inc.php")) {
	$res = @include "../../../main.inc.php";
}
if (!$res) {
	die("Include of main fails");
}

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
require_once __DIR__ . '/class/scrumtask.class.php';
require_once __DIR__ . '/class/scrumuserstory.class.php';
require_once __DIR__ . '/class/scrumuserstorysprint.class.php';
require_once __DIR__ . '/lib/scrumproject_scrumtask.lib.php';

// Load translation files required by the page
$langs->loadLangs(array("scrumproject@scrumproject", "companies"));

// Get parameters
$id = GETPOST('id', 'int');
$ref        = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');
$cancel     = GETPOST('cancel', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');


$search_day = GETPOST('search_day', 'int');
$search_month = GETPOST('search_month', 'int');
$search_year = GETPOST('search_year', 'int');
$search_datehour = '';
$search_datewithhour = '';
$search_note = GETPOST('search_note', 'alpha');
$search_duration = GETPOST('search_duration', 'int');
$search_value = GETPOST('search_value', 'int');
$search_task_ref = GETPOST('search_task_ref', 'alpha');
$search_task_label = GETPOST('search_task_label', 'alpha');
$search_user = GETPOST('search_user', 'int');
$search_valuebilled = GETPOST('search_valuebilled', 'int');

$limit = GETPOST('limit', 'int') ?GETPOST('limit', 'int') : $conf->liste_limit;
$sortfield = GETPOST('sortfield', 'aZ09comma');
$sortorder = GETPOST('sortorder', 'aZ09comma');
$page = GETPOSTISSET('pageplusone') ? (GETPOST('pageplusone') - 1) : GETPOST("page", 'int');
if (empty($page) || $page == -1) {
	$page = 0;
}		// If $page is not defined, or '' or -1
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (!$sortfield) {
	$sortfield = 'ptt.task_date,ptt.task_datehour,ptt.rowid';
}
if (!$sortorder) {
	$sortorder = 'DESC,DESC,DESC';
}


// Initialize technical objects
$object = new ScrumTask($db);
$userStorySprint = new ScrumUserStorySprint($db);
$userStory = new ScrumUserStory($db);
$projectstatic = new Project($db);
$userstatic = new User($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->scrumproject->dir_output.'/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('scrumtasktime', 'globalcard')); // Note that conf->hooks_modules contains array
// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be include, not include_once  // Must be include, not include_once. Include fetch and fetch_thirdparty but not fetch_optionals
if ($id > 0 || !empty($ref)) {
	$upload_dir = $conf->scrumproject->multidir_output[!empty($object->entity) ? $object->entity : $conf->entity]."/".$object->id;
}

if($userStorySprint->fetch($object->fk_scrum_user_story_sprint) <= 0 || $userStory->fetch($userStorySprint->fk_scrum_user_story) <= 0)
{
	print dol_print_error($db);
	exit;
}

// Security check (enable the most restrictive one)
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//$isdraft = (($object->status == $object::STATUS_DRAFT) ? 1 : 0);
//restrictedArea($user, $object->element, $object->id, $object->table_element, '', 'fk_soc', 'rowid', $isdraft);
//if (empty($conf->scrumproject->enabled)) accessforbidden();
//if (!$permissiontoread) accessforbidden();


/*
 * Actions
 */

$reshook = $hookmanager->executeHooks('doActions', array(), $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) {
	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}
if (empty($reshook)) {


	include DOL_DOCUMENT_ROOT.'/core/actions_changeselectedfields.inc.php';

// Purge search criteria
	if (GETPOST('button_removefilter_x', 'alpha') || GETPOST('button_removefilter.x', 'alpha') || GETPOST('button_removefilter', 'alpha')) { // All tests are required to be compatible with all browsers
		$search_day = '';
		$search_month = '';
		$search_year = '';
		$search_date = '';
		$search_datehour = '';
		$search_datewithhour = '';
		$search_note = '';
		$search_duration = '';
		$search_value = '';
		$search_date_creation = '';
		$search_date_update = '';
		$search_task_ref = '';
		$search_task_label = '';
		$search_user = 0;
		$search_valuebilled = '';
		$toselect = '';
		$search_array_options = array();
		$action = '';
	}

	if($action == 'addtimetotask'){
		$timespent = GETPOST('timespent');

		if(!empty($timespent)){
			$TTimeExplode = explode(':', $timespent);
			$TTimeExplode = array_map('intval', $TTimeExplode);
			$minuteSpent = !empty($TTimeExplode[1]) ? $TTimeExplode[1] : 0;
			$hourSpent = $TTimeExplode[0] * 60;
			$timespent = ($hourSpent + $minuteSpent) * 60;
		}

		$progress = GETPOST('progress', 'int');
		// TODO : il faut convertir la progression en ratio d'avancement en fonction du ratio de la tache scrum vis à vis de la tâche projet

		$date = strtotime(GETPOST('date').' '.GETPOST('time').':00');
		$userid = GETPOST('userid', 'int');
		$note = GETPOST('timespent_note', 'restricthtml');

		$action = '';
		$res = $object->addTimeSpend($user, $userid, $timespent, $progress, $date, $note);

		if($res > 0){
			setEventMessage($langs->trans('TimeConsumedAdded'));
			header('Location: ' .$_SERVER["PHP_SELF"].'?id=' . $id);
		}else{
			setEventMessage($langs->trans('Error'). ' ' . $res . ' ' . $object->errorsToString(), 'errors');
		}
	}



}


/*
 * View
 */

$formother = new FormOther($db);
$formproject = new FormProjets($db);
$form = new Form($db);

//$help_url='EN:Customers_Orders|FR:Commandes_Clients|ES:Pedidos de clientes';
$help_url = '';
$arrayofcss = array('/scrumproject/css/scrumproject.css');
llxHeader('', $langs->trans('ScrumTask'), $help_url ,'', 0, 0, '', $arrayofcss);

if ($id > 0 || !empty($ref)) {
	$object->fetch_thirdparty();


	$head = scrumtaskPrepareHead($object);

	print dol_get_fiche_head($head, 'scrumsprinttime', '', -1, $object->picto);

	// Object card
	// ------------------------------------------------------------
	$linkback = '<a href="'.dol_buildpath('/scrumproject/scrumtask_list.php', 1).'?restore_lastsearch_values=1'.(!empty($socid) ? '&socid='.$socid : '').'">'.$langs->trans("BackToList").'</a>';

	$morehtmlref = '<div class="refidno">';

	// TODO : Ajouter les infos de la tâche liée à la scrum user story

	 $morehtmlref .= '</div>';


	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);


	print '<div class="fichecenter">';
	print '<div class="underbanner clearboth"></div>';




	/*
		 * Form to add a new line of time spent
		 */
	if ($user->rights->projet->lire) {
		print '<!-- table to add time spent -->'."\n";

		print '<form action="'.$_SERVER["PHP_SELF"].'" method="POST">';

		print '<input type="hidden" name="id" value="'.$object->id.'">';
		print '<input type="hidden" name="token" value="'.newToken().'">';

		print '<div class="div-table-responsive-no-min">'; // You can use div-table-responsive-no-min if you dont need reserved height for your table
		print '<table class="noborder nohover centpercent">';

		print '<tr class="liste_titre">';
		print '<td>'.$langs->trans("Date").'</td>';
		print '<td>'.$langs->trans("By").'</td>';
		print '<td>'.$langs->trans("Note").'</td>';
		print '<td>'.$langs->trans("NewTimeSpent").'</td>';
		print '<td>'.$langs->trans("ProgressDeclared").'</td>';
		print '<td></td>';

		print "</tr>\n";

		print '<tr class="oddeven nohover">';

		// Date
		print '<td class="maxwidthonsmartphone">';
		$newdate = '';
		print '<input required type="date" value="'.date('Y-m-d').'" name="date" />';
		print '<input type="time" value="'.date('H:i').'" name="time"  />';

		print '</td>';

		// Contributor
		print '<td class="maxwidthonsmartphone nowraponall">';
		print img_object('', 'user', 'class="hideonsmartphone"');
		$userid = $user->id;
		print $form->select_dolusers((GETPOST('userid', 'int') ? GETPOST('userid', 'int') : $userid), 'userid', 0, '', 0, '', '', 0, 0, 0, 'AND employee = 1 AND statut = 1', 0, $langs->trans("ResourceNotAssignedToProject"), 'maxwidth250');

		print '</td>';

		// Note
		print '<td>';
		print '<textarea name="timespent_note" class="maxwidth100onsmartphone" rows="'.ROWS_2.'">'.($_POST['timespent_note'] ? $_POST['timespent_note'] : '').'</textarea>';
		print '</td>';

		// Duration - Time spent
		print '<td class="nowraponall">';
		print '<input required type="time" value="00:00" name="timespent"  />';
		print '</td>';

		// Progress declared
		print '<td class="nowrap">';
		print '<input required style="vertical-align:middle;" type="range" id="progress" name="progress" min="0" max="100" step="5" oninput="this.nextElementSibling.value = this.value + \'%\'" value="'.GETPOST('progress', 'int').'"><output style="vertical-align:middle;" >'.GETPOST('progress', 'int').'</output>';
		print '</td>';


		print '<td class="center">';
		$form->buttonsSaveCancel();
		print '<button type="submit" name="action" class="button buttongen marginleftonly margintoponlyshort marginbottomonlyshort button-add" value="addtimetotask">'.$langs->trans("Add").'</button>';
		print '</td></tr>';

		print '</table>';
		print '</div>';


		print '</form>';





		/*
		 *	List of time spent
		 */
		$tasks = array();

		// Definition of fields for list
		$arrayfields = array();
		$arrayfields['ptt.task_date'] = array('label'=>$langs->trans("Date"), 'checked'=>1);
		if ((empty($id) && empty($ref)) || !empty($projectidforalltimes)) {	// Not a dedicated task
			$arrayfields['ptt.task_ref'] = array('label'=>$langs->trans("RefTask"), 'checked'=>1);
			$arrayfields['ptt.task_label'] = array('label'=>$langs->trans("LabelTask"), 'checked'=>1);
		}
		$arrayfields['author'] = array('label'=>$langs->trans("By"), 'checked'=>1);
		$arrayfields['ptt.note'] = array('label'=>$langs->trans("Note"), 'checked'=>1);
		$arrayfields['ptt.task_duration'] = array('label'=>$langs->trans("Duration"), 'checked'=>1);
		$arrayfields['value'] = array('label'=>$langs->trans("Value"), 'checked'=>1, 'enabled'=>(empty($conf->salaries->enabled) ? 0 : 1));
		$arrayfields['valuebilled'] = array('label'=>$langs->trans("Billed"), 'checked'=>1, 'enabled'=>(((!empty($conf->global->PROJECT_HIDE_TASKS) || empty($conf->global->PROJECT_BILL_TIME_SPENT)) ? 0 : 1) && $projectstatic->usage_bill_time));
		// Extra fields
		include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_array_fields.tpl.php';

		$arrayfields = dol_sort_array($arrayfields, 'position');

		$sql = /** @lang MySQL */
			"SELECT ptt.rowid, ptt.fk_task, ptt.task_date, ptt.task_datehour, ptt.task_date_withhour, ptt.task_duration, ptt.fk_user, ptt.note, ptt.thm,"
			." pt.ref, pt.label, pt.fk_projet,"
			." u.lastname, u.firstname, u.login, u.photo, u.statut as user_status"
			." FROM ".MAIN_DB_PREFIX."projet_task_time  ptt "
			." JOIN ".MAIN_DB_PREFIX."scrumproject_scrumtask_projet_task_time  stt ON (stt.fk_projet_task_time = ptt.rowid)"
			." JOIN ".MAIN_DB_PREFIX."projet_task pt ON (ptt.fk_task = pt.rowid) "
			." JOIN  ".MAIN_DB_PREFIX."user u ON (ptt.fk_user = u.rowid)"
			." WHERE  stt.fk_scrumproject_scrumtask = ".$object->id ;


		if ($search_note) {
			$sql .= natural_search('ptt.note', $search_note);
		}
		if ($search_task_ref) {
			$sql .= natural_search('pt.ref', $search_task_ref);
		}
		if ($search_task_label) {
			$sql .= natural_search('pt.label', $search_task_label);
		}
		if ($search_user > 0) {
			$sql .= natural_search('ptt.fk_user', $search_user, 2);
		}

//
		$sql .= dolSqlDateFilter('ptt.task_datehour', $search_day, $search_month, $search_year);

		$childids = $user->getAllChildIds(1);

		$sql .= $db->order($sortfield, $sortorder);

		// Count total nb of records
		$nbtotalofrecords = '';
		if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST)) {
			$resql = $db->query($sql);

			if (! $resql) {
				dol_print_error($db);
				exit;
			}

			$nbtotalofrecords = $db->num_rows($resql);
			if (($page * $limit) > $nbtotalofrecords) {	// if total of record found is smaller than page * limit, goto and load page 0
				$page = 0;
				$offset = 0;
			}
		}
		// if total of record found is smaller than limit, no need to do paging and to restart another select with limits set.
		if (is_numeric($nbtotalofrecords) && $limit > $nbtotalofrecords) {
			$num = $nbtotalofrecords;
		} else {
			$sql .= $db->plimit($limit + 1, $offset);

			$resql = $db->query($sql);
			if (!$resql) {
				dol_print_error($db);
				exit;
			}

			$num = $db->num_rows($resql);
		}

		if ($num >= 0) {
			if (!empty($projectidforalltimes)) {
				print '<!-- List of time spent for project -->'."\n";

				$title = $langs->trans("ListTaskTimeUserProject");

				print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, $massactionbutton, $num, $nbtotalofrecords, 'clock', 0, $linktocreatetime, '', $limit, 0, 0, 1);
			} else {
				print '<!-- List of time spent -->'."\n";

				$title = $langs->trans("ListTaskTimeForTask");

				print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, $massactionbutton, $num, $nbtotalofrecords, 'clock', 0, $linktocreatetime, '', $limit, 0, 0, 1);
			}

			$i = 0;
			while ($i < $num) {
				$row = $db->fetch_object($resql);
				$tasks[$i] = $row;
				$i++;
			}
			$db->free($resql);
		} else {
			dol_print_error($db);
		}


		$moreforfilter = '';

		$parameters = array();
		$reshook = $hookmanager->executeHooks('printFieldPreListTitle', $parameters); // Note that $action and $object may have been modified by hook
		if (empty($reshook)) {
			$moreforfilter .= $hookmanager->resPrint;
		} else {
			$moreforfilter = $hookmanager->resPrint;
		}

		if (!empty($moreforfilter)) {
			print '<div class="liste_titre liste_titre_bydiv centpercent">';
			print $moreforfilter;
			print '</div>';
		}

		$varpage = empty($contextpage) ? $_SERVER["PHP_SELF"] : $contextpage;
		$selectedfields = $form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage); // This also change content of $arrayfields


		print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';

		print '<input type="hidden" name="token" value="'.newToken().'">';
		print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
		print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
		print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';

		print '<input type="hidden" name="id" value="'.$id.'">';

		print '<input type="hidden" name="page_y" value="">';
		print '<div class="div-table-responsive">';
		print '<table class="tagtable nobottomiftotal liste'.($moreforfilter ? " listwithfilterbefore" : "").'">'."\n";

		// Fields title search
		print '<tr class="liste_titre_filter">';
		// Date
		if (!empty($arrayfields['ptt.task_date']['checked'])) {
			print '<td class="liste_titre">';
			if (!empty($conf->global->MAIN_LIST_FILTER_ON_DAY)) {
				print '<input class="flat valignmiddle" type="text" size="1" maxlength="2" name="search_day" value="'.$search_day.'">';
			}
			print '<input class="flat valignmiddle" type="text" size="1" maxlength="2" name="search_month" value="'.$search_month.'">';
			$formother->select_year($search_year, 'search_year', 1, 20, 5);
			print '</td>';
		}
		if (!empty($allprojectforuser)) {
			print '<td></td>';
		}
		// Task
		if ((empty($id) && empty($ref)) || !empty($projectidforalltimes)) {	// Not a dedicated task
			if (!empty($arrayfields['ptt.task_ref']['checked'])) {
				print '<td class="liste_titre"><input type="text" class="flat maxwidth100" name="search_task_ref" value="'.dol_escape_htmltag($search_task_ref).'"></td>';
			}
			if (!empty($arrayfields['ptt.task_label']['checked'])) {
				print '<td class="liste_titre"><input type="text" class="flat maxwidth100" name="search_task_label" value="'.dol_escape_htmltag($search_task_label).'"></td>';
			}
		}
		// Author
		if (!empty($arrayfields['author']['checked'])) {
			print '<td class="liste_titre">'.$form->select_dolusers(($search_user > 0 ? $search_user : -1), 'search_user', 1, null, 0, '', '', 0, 0, 0, '', 0, '', 'maxwidth250').'</td>';
		}
		// Note
		if (!empty($arrayfields['ptt.note']['checked'])) {
			print '<td class="liste_titre"><input type="text" class="flat maxwidth100" name="search_note" value="'.dol_escape_htmltag($search_note).'"></td>';
		}
		// Duration
		if (!empty($arrayfields['ptt.task_duration']['checked'])) {
			print '<td class="liste_titre right"></td>';
		}
		// Value in main currency
		if (!empty($arrayfields['value']['checked'])) {
			print '<td class="liste_titre"></td>';
		}
		// Value billed
		if (!empty($arrayfields['valuebilled']['checked'])) {
			print '<td class="liste_titre center">'.$form->selectyesno('search_valuebilled', $search_valuebilled, 1, false, 1).'</td>';
		}

		/*
		// Extra fields
		include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_input.tpl.php';
		*/
		// Fields from hook
		$parameters = array('arrayfields'=>$arrayfields);
		$reshook = $hookmanager->executeHooks('printFieldListOption', $parameters); // Note that $action and $object may have been modified by hook
		print $hookmanager->resPrint;
		// Action column
		print '<td class="liste_titre center">';
		$searchpicto = $form->showFilterButtons();
		print $searchpicto;
		print '</td>';
		print '</tr>'."\n";

		print '<tr class="liste_titre">';
		if (!empty($arrayfields['ptt.task_date']['checked'])) {
			print_liste_field_titre($arrayfields['ptt.task_date']['label'], $_SERVER['PHP_SELF'], 't.task_date,t.task_datehour,t.rowid', '', $param, '', $sortfield, $sortorder);
		}
		if (!empty($allprojectforuser)) {
			print_liste_field_titre("Project", $_SERVER['PHP_SELF'], '', '', $param, '', $sortfield, $sortorder);
		}
		if ((empty($id) && empty($ref)) || !empty($projectidforalltimes)) {	// Not a dedicated task
			if (!empty($arrayfields['ptt.task_ref']['checked'])) {
				print_liste_field_titre($arrayfields['ptt.task_ref']['label'], $_SERVER['PHP_SELF'], 'pt.ref', '', $param, '', $sortfield, $sortorder);
			}
			if (!empty($arrayfields['ptt.task_label']['checked'])) {
				print_liste_field_titre($arrayfields['ptt.task_label']['label'], $_SERVER['PHP_SELF'], 'pt.label', '', $param, '', $sortfield, $sortorder);
			}
		}
		if (!empty($arrayfields['author']['checked'])) {
			print_liste_field_titre($arrayfields['author']['label'], $_SERVER['PHP_SELF'], '', '', $param, '', $sortfield, $sortorder);
		}
		if (!empty($arrayfields['ptt.note']['checked'])) {
			print_liste_field_titre($arrayfields['ptt.note']['label'], $_SERVER['PHP_SELF'], 't.note', '', $param, '', $sortfield, $sortorder);
		}
		if (!empty($arrayfields['ptt.task_duration']['checked'])) {
			print_liste_field_titre($arrayfields['ptt.task_duration']['label'], $_SERVER['PHP_SELF'], 't.task_duration', '', $param, '', $sortfield, $sortorder, 'right ');
		}
		if (!empty($arrayfields['value']['checked'])) {
			print_liste_field_titre($arrayfields['value']['label'], $_SERVER['PHP_SELF'], '', '', $param, '', $sortfield, $sortorder, 'right ');
		}
		if (!empty($arrayfields['valuebilled']['checked'])) {
			print_liste_field_titre($arrayfields['valuebilled']['label'], $_SERVER['PHP_SELF'], 'il.total_ht', '', $param, '', $sortfield, $sortorder, 'center ', $langs->trans("SelectLinesOfTimeSpentToInvoice"));
		}
		/*
		// Extra fields
		include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_title.tpl.php';
		*/
		// Hook fields
		$parameters = array('arrayfields'=>$arrayfields, 'param'=>$param, 'sortfield'=>$sortfield, 'sortorder'=>$sortorder);
		$reshook = $hookmanager->executeHooks('printFieldListTitle', $parameters); // Note that $action and $object may have been modified by hook
		print $hookmanager->resPrint;
		print_liste_field_titre($selectedfields, $_SERVER["PHP_SELF"], "", '', '', 'width="80"', $sortfield, $sortorder, 'center maxwidthsearch ');
		print "</tr>\n";

		$tasktmp = new Task($db);

		$i = 0;

		$total = 0;
		$totalvalue = 0;
		$totalarray = array();
		foreach ($tasks as $task_time) {
			if ($i >= $limit) {
				break;
			}

			$date1 = $db->jdate($task_time->task_date);
			$date2 = $db->jdate($task_time->task_datehour);

			print '<tr class="oddeven">';

			// Date
			if (!empty($arrayfields['ptt.task_date']['checked'])) {
				print '<td class="nowrap">';
				if ($action == 'editline' && $_GET['lineid'] == $task_time->rowid) {
					if (empty($task_time->task_date_withhour)) {
						print $form->selectDate(($date2 ? $date2 : $date1), 'timeline', 3, 3, 2, "timespent_date", 1, 0);
					} else {
						print $form->selectDate(($date2 ? $date2 : $date1), 'timeline', 1, 1, 2, "timespent_date", 1, 0);
					}
				} else {
					print dol_print_date(($date2 ? $date2 : $date1), ($task_time->task_date_withhour ? 'dayhour' : 'day'));
				}
				print '</td>';
				if (!$i) {
					$totalarray['nbfield']++;
				}
			}

			// Project ref
			if (!empty($allprojectforuser)) {
				print '<td class="nowraponall">';
				if (empty($conf->cache['project'][$task_time->fk_projet])) {
					$tmpproject = new Project($db);
					$tmpproject->fetch($task_time->fk_projet);
					$conf->cache['project'][$task_time->fk_projet] = $tmpproject;
				} else {
					$tmpproject = $conf->cache['project'][$task_time->fk_projet];
				}
				print $tmpproject->getNomUrl(1);
				print '</td>';
				if (!$i) {
					$totalarray['nbfield']++;
				}
			}

			// Task ref
			if (!empty($arrayfields['ptt.task_ref']['checked'])) {
				if ((empty($id) && empty($ref)) || !empty($projectidforalltimes)) {   // Not a dedicated task
					print '<td class="nowrap">';
					if ($action == 'editline' && $_GET['lineid'] == $task_time->rowid) {
						$formproject->selectTasks(-1, GETPOST('taskid', 'int') ? GETPOST('taskid', 'int') : $task_time->fk_task, 'taskid', 0, 0, 1, 1, 0, 0, 'maxwidth300', $projectstatic->id, '');
					} else {
						$tasktmp->id = $task_time->fk_task;
						$tasktmp->ref = $task_time->ref;
						$tasktmp->label = $task_time->label;
						print $tasktmp->getNomUrl(1, 'withproject', 'time');
					}
					print '</td>';
					if (!$i) {
						$totalarray['nbfield']++;
					}
				}
			} elseif ($action !== 'createtime') {
				print '<input type="hidden" name="taskid" value="'.$id.'">';
			}

			// Task label
			if (!empty($arrayfields['ptt.task_label']['checked'])) {
				if ((empty($id) && empty($ref)) || !empty($projectidforalltimes)) {	// Not a dedicated task
					print '<td class="nowrap tdoverflowmax300" title="'.dol_escape_htmltag($task_time->label).'">';
					print dol_escape_htmltag($task_time->label);
					print '</td>';
					if (!$i) {
						$totalarray['nbfield']++;
					}
				}
			}

			// By User
			if (!empty($arrayfields['author']['checked'])) {
				print '<td class="tdoverflowmax100">';
				if ($action == 'editline' && $_GET['lineid'] == $task_time->rowid) {
					if (empty($object->id)) {
						$object->fetch($id);
					}
					$contactsoftask = $object->getListContactId('internal');
					if (!in_array($task_time->fk_user, $contactsoftask)) {
						$contactsoftask[] = $task_time->fk_user;
					}
					if (count($contactsoftask) > 0) {
						print img_object('', 'user', 'class="hideonsmartphone"');
						print $form->select_dolusers($task_time->fk_user, 'userid_line', 0, '', 0, '', $contactsoftask, '0', 0, 0, '', 0, '', 'maxwidth200');
					} else {
						print img_error($langs->trans('FirstAddRessourceToAllocateTime')).$langs->trans('FirstAddRessourceToAllocateTime');
					}
				} else {
					$userstatic->id = $task_time->fk_user;
					$userstatic->lastname = $task_time->lastname;
					$userstatic->firstname = $task_time->firstname;
					$userstatic->photo = $task_time->photo;
					$userstatic->statut = $task_time->user_status;
					print $userstatic->getNomUrl(-1);
				}
				print '</td>';
				if (!$i) {
					$totalarray['nbfield']++;
				}
			}

			// Note
			if (!empty($arrayfields['ptt.note']['checked'])) {
				print '<td class="small">';
				if ($action == 'editline' && $_GET['lineid'] == $task_time->rowid) {
					print '<textarea name="timespent_note_line" width="95%" rows="'.ROWS_2.'">'.$task_time->note.'</textarea>';
				} else {
					print dol_nl2br($task_time->note);
				}
				print '</td>';
				if (!$i) {
					$totalarray['nbfield']++;
				}
			} elseif ($action == 'editline' && $_GET['lineid'] == $task_time->rowid) {
				print '<input type="hidden" name="timespent_note_line" value="'.$task_time->note.'">';
			}

			// Time spent
			if (!empty($arrayfields['ptt.task_duration']['checked'])) {
				print '<td class="right nowraponall">';
				if ($action == 'editline' && $_GET['lineid'] == $task_time->rowid) {
					print '<input type="hidden" name="old_duration" value="'.$task_time->task_duration.'">';
					print $form->select_duration('new_duration', $task_time->task_duration, 0, 'text');
				} else {
					print convertSecondToTime($task_time->task_duration, 'allhourmin');
				}
				print '</td>';
				if (!$i) {
					$totalarray['nbfield']++;
				}
				if (!$i) {
					$totalarray['pos'][$totalarray['nbfield']] = 't.task_duration';
				}
				$totalarray['val']['t.task_duration'] += $task_time->task_duration;
				if (!$i) {
					$totalarray['totaldurationfield'] = $totalarray['nbfield'];
				}
				$totalarray['totalduration'] += $task_time->task_duration;
			}

			// Value spent
			if (!empty($arrayfields['value']['checked'])) {
				$langs->load("salaries");

				print '<td class="nowraponall right">';
				$value = price2num($task_time->thm * $task_time->task_duration / 3600, 'MT', 1);
				print '<span class="amount" title="'.$langs->trans("THM").': '.price($task_time->thm).'">';
				print price($value, 1, $langs, 1, -1, -1, $conf->currency);
				print '</span>';
				print '</td>';
				if (!$i) {
					$totalarray['nbfield']++;
				}
				if (!$i) {
					$totalarray['pos'][$totalarray['nbfield']] = 'value';
				}
				$totalarray['val']['value'] += $value;
				if (!$i) {
					$totalarray['totalvaluefield'] = $totalarray['nbfield'];
				}
				$totalarray['totalvalue'] += $value;
			}


			/*
			// Extra fields
			include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_print_fields.tpl.php';
			*/

			// Fields from hook
			$parameters = array('arrayfields'=>$arrayfields, 'obj'=>$task_time, 'i'=>$i, 'totalarray'=>&$totalarray);
			$reshook = $hookmanager->executeHooks('printFieldListValue', $parameters); // Note that $action and $object may have been modified by hook
			print $hookmanager->resPrint;

			// Action column
			print '<td class="center nowraponall">';
			if ($user->rights->projet->lire || $user->rights->projet->all->creer) {	 // Read project and enter time consumed on assigned tasks
				if (in_array($task_time->fk_user, $childids) || $user->rights->projet->all->creer) {

					$tooltip = dol_escape_htmltag($langs->trans('WillRedirectYouToTaskPageForAction'));

					print '<a class="reposition editfielda" title="'.$tooltip.'" href="'.dol_buildpath('projet/tasks/time.php', 1).'?id='.$task_time->fk_task.'&action=editline&token='.newToken().'&lineid='.$task_time->rowid.$param.'&tab=timespent" >';
					print img_edit();
					print '</a>';

					print '&nbsp;';
					print '<a class="reposition paddingleft" title="'.$tooltip.'"  href="'.dol_buildpath('projet/tasks/time.php', 1).'?id='.$task_time->fk_task.'&action=deleteline&token='.newToken().'&lineid='.$task_time->rowid.$param.'&tab=timespent">';
					print img_delete('default', 'class="pictodelete paddingleft"');
					print '</a>';

				}
			}
			print '</td>';
			if (!$i) {
				$totalarray['nbfield']++;
			}

			print "</tr>\n";


			$i++;
		}

		// Show total line

		if (isset($totalarray['totaldurationfield']) || isset($totalarray['totalvaluefield'])) {
			print '<tr class="liste_total">';
			$i = 0;
			while ($i < $totalarray['nbfield']) {
				$i++;
				if ($i == 1) {
					if ($num < $limit && empty($offset)) {
						print '<td class="left">'.$langs->trans("Total").'</td>';
					} else {
						print '<td class="left">'.$langs->trans("Totalforthispage").'</td>';
					}
				} elseif ($totalarray['totaldurationfield'] == $i) {
					print '<td class="right">'.convertSecondToTime($totalarray['totalduration'], 'allhourmin').'</td>';
				} elseif ($totalarray['totalvaluefield'] == $i) {
					print '<td class="right">'.price($totalarray['totalvalue']).'</td>';
					//} elseif ($totalarray['totalvaluebilledfield'] == $i) { print '<td class="center">'.price($totalarray['totalvaluebilled']).'</td>';
				} else {
					print '<td></td>';
				}
			}
			print '</tr>';
		}

		if (!count($tasks)) {
			$totalnboffields = 1;
			foreach ($arrayfields as $value) {
				if ($value['checked']) {
					$totalnboffields++;
				}
			}
			print '<tr class="oddeven"><td colspan="'.$totalnboffields.'">';
			print '<span class="opacitymedium">'.$langs->trans("None").'</span>';
			print '</td></tr>';
		}


		print "</table>";
		print '</div>';
		print '</form>';


	}



	print '</div>';

	print dol_get_fiche_end();
}

// End of page
llxFooter();
$db->close();
