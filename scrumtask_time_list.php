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
dol_include_once('/scrumproject/class/scrumtask.class.php');
dol_include_once('/scrumproject/lib/scrumproject_scrumtask.lib.php');

// Load translation files required by the page
$langs->loadLangs(array("scrumproject@scrumproject", "companies"));

// Get parameters
$id = GETPOST('id', 'int');
$ref        = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');
$cancel     = GETPOST('cancel', 'aZ09');
$backtopage = GETPOST('backtopage', 'alpha');

// Initialize technical objects
$object = new ScrumTask($db);
$projectstatic = new Project($db);
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




}


/*
 * View
 */

$formother = new FormOther($db);
$formproject = new FormProjets($db);
$form = new Form($db);

//$help_url='EN:Customers_Orders|FR:Commandes_Clients|ES:Pedidos de clientes';
$help_url = '';
llxHeader('', $langs->trans('ScrumTask'), $help_url);

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

		print '<input type="hidden" name="taskid" value="'.$id.'">';

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
		print '<input type="date" value="'.date('Y-m-d').'" name="date" />';
		print '<input type="time" value="'.date('H:i').'" name="time"  />';

		print '</td>';

		// Contributor
		print '<td class="maxwidthonsmartphone nowraponall">';
		$contactsofproject = $projectstatic->getListContactId('internal');
		if (count($contactsofproject) > 0) {
			print img_object('', 'user', 'class="hideonsmartphone"');
			if (in_array($user->id, $contactsofproject)) {
				$userid = $user->id;
			} else {
				$userid = $contactsofproject[0];
			}

			if ($projectstatic->public) {
				$contactsofproject = array();
			}
			print $form->select_dolusers((GETPOST('userid', 'int') ? GETPOST('userid', 'int') : $userid), 'userid', 0, '', 0, '', $contactsofproject, 0, 0, 0, '', 0, $langs->trans("ResourceNotAssignedToProject"), 'maxwidth250');
		} else {
			if ($nboftasks) {
				print img_error($langs->trans('FirstAddRessourceToAllocateTime')).' '.$langs->trans('FirstAddRessourceToAllocateTime');
			}
		}
		print '</td>';

		// Note
		print '<td>';
		print '<textarea name="timespent_note" class="maxwidth100onsmartphone" rows="'.ROWS_2.'">'.($_POST['timespent_note'] ? $_POST['timespent_note'] : '').'</textarea>';
		print '</td>';

		// Duration - Time spent
		print '<td class="nowraponall">';
		print '<input type="time" value="" name="timespent" step="900"  />';
		print '</td>';

		// Progress declared
		print '<td class="nowrap">';
		print $formother->select_percent(GETPOST('progress') ?GETPOST('progress') : $object->progress, 'progress', 0, 5, 0, 100, 1);
		print '</td>';


		print '<td class="center">';
		$form->buttonsSaveCancel();
		print '<input type="submit" name="save" class="button buttongen marginleftonly margintoponlyshort marginbottomonlyshort button-add" value="'.$langs->trans("Add").'">';
		print '</td></tr>';

		print '</table>';
		print '</div>';
	}



	print '</div>';

	print dol_get_fiche_end();
}

// End of page
llxFooter();
$db->close();
