<?php
/* Copyright (C) 2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2020	Maxime Kohlhaas		<maxime@atm-consulting.fr>
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
 *   	\file       scrumsprint_card.php
 *		\ingroup    scrumproject
 *		\brief      Page to create/edit/view scrumsprint
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
if (!$res && !empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res = @include $_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php";
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp = empty($_SERVER['SCRIPT_FILENAME']) ? '' : $_SERVER['SCRIPT_FILENAME']; $tmp2 = realpath(__FILE__); $i = strlen($tmp) - 1; $j = strlen($tmp2) - 1;
while ($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i] == $tmp2[$j]) { $i--; $j--; }
if (!$res && $i > 0 && file_exists(substr($tmp, 0, ($i + 1))."/main.inc.php")) $res = @include substr($tmp, 0, ($i + 1))."/main.inc.php";
if (!$res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php")) $res = @include dirname(substr($tmp, 0, ($i + 1)))."/main.inc.php";
// Try main.inc.php using relative path
if (!$res && file_exists("../main.inc.php")) $res = @include "../main.inc.php";
if (!$res && file_exists("../../main.inc.php")) $res = @include "../../main.inc.php";
if (!$res && file_exists("../../../main.inc.php")) $res = @include "../../../main.inc.php";
if (!$res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
dol_include_once('/scrumproject/class/scrumsprint.class.php');
dol_include_once('/scrumproject/lib/scrumproject_scrumsprint.lib.php');
dol_include_once('/advancedkanban/class/advkanban.class.php');


// Load translation files required by the page
$langs->loadLangs(array("scrumproject@scrumproject", "other"));

// Get parameters
$id = GETPOST('id', 'int');
$ref        = GETPOST('ref', 'alpha');
$action = GETPOST('action', 'aZ09');
$confirm    = GETPOST('confirm', 'alpha');
$cancel     = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ?GETPOST('contextpage', 'aZ') : 'scrumsprintcard'; // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');
$fk_kanban = GETPOST('fk_kanban','int');
//$lineid   = GETPOST('lineid', 'int');

// Initialize technical objects
$object = new ScrumSprint($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->scrumproject->dir_output.'/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('scrumsprintcard', 'globalcard')); // Note that conf->hooks_modules contains array

// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');

// Initialize array of search criterias
$search_all = GETPOST("search_all", 'alpha');
$search = array();
foreach ($object->fields as $key => $val)
{
	if (GETPOST('search_'.$key, 'alpha')) $search[$key] = GETPOST('search_'.$key, 'alpha');
}

if (empty($action) && empty($id) && empty($ref)) $action = 'view';

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php'; // Must be include, not include_once.


$permissiontoread = $user->rights->scrumproject->scrumsprint->read;
$permissiontoadd = $user->rights->scrumproject->scrumsprint->write; // Used by the include of actions_addupdatedelete.inc.php and actions_lineupdown.inc.php
$permissiontodelete = $user->rights->scrumproject->scrumsprint->delete || ($permissiontoadd && isset($object->status) && $object->status == $object::STATUS_DRAFT);
$permissionnote = $user->rights->scrumproject->scrumsprint->write; // Used by the include of actions_setnotes.inc.php
$permissiondellink = $user->rights->scrumproject->scrumsprint->write; // Used by the include of actions_dellink.inc.php
$upload_dir = $conf->scrumproject->multidir_output[isset($object->entity) ? $object->entity : 1];

// Security check - Protection if external user
//if ($user->socid > 0) accessforbidden();
//if ($user->socid > 0) $socid = $user->socid;
//$isdraft = (($object->statut == $object::STATUS_DRAFT) ? 1 : 0);
//$result = restrictedArea($user, 'scrumproject', $object->id, '', '', 'fk_soc', 'rowid', $isdraft);

//if (!$permissiontoread) accessforbidden();


/*
 * Actions
 */

$parameters = array();
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook))
{
	$error = 0;

	$backurlforlist = dol_buildpath('/scrumproject/scrumsprint_list.php', 1);

	if (empty($backtopage) || ($cancel && empty($id))) {
		if (empty($backtopage) || ($cancel && strpos($backtopage, '__ID__'))) {
			if (empty($id) && (($action != 'add' && $action != 'create') || $cancel)) $backtopage = $backurlforlist;
			else $backtopage = dol_buildpath('/scrumproject/scrumsprint_card.php', 1).'?id='.($id > 0 ? $id : '__ID__');
		}
	}

	$triggermodname = 'SCRUMPROJECT_SCRUMSPRINT_MODIFY'; // Name of trigger action code to execute when we modify record

	// Actions cancel, add, update, update_extras, confirm_validate, confirm_delete, confirm_deleteline, confirm_clone, confirm_close, confirm_setdraft, confirm_reopen
	include DOL_DOCUMENT_ROOT.'/core/actions_addupdatedelete.inc.php';

	if($action == 'confirm_setpending' && $confirm == 'yes') {
		$object->setStatusCommon($user, $object::STATUS_PENDING, 0, 'SCRUMSPRINT_PENDING');
	}
	if($action == 'confirm_setdone' && $confirm == 'yes') {
		$object->setStatusCommon($user, $object::STATUS_DONE, 0, 'SCRUMSPRINT_DONE');
	}
	if ($action == 'confirm_create_kanban'){

		$newkanban = New AdvKanban($db);
		$newkanban->status = AdvKanban::STATUS_DRAFT;
		$newkanban->label = $object->label;
		$res = $newkanban->create($user);

		if($res>0){

			$object->fk_advkanban = $newkanban->id;
			if($object->update($user)<0){
				setEventMessage('ErrorAssignKanbanToSprint', 'error');
			}

			//Create KanbanList on Clone
			if ($fk_kanban > 0){

				$kanbanList = New AdvKanbanList($db);

				//Récupération des AdvKanbanList
				$TkanbanList = $kanbanList->fetchAll('','',0,0,array('t.fk_advkanban' => $fk_kanban));
				$TnewKanbanList = $kanbanList->fetchAll('','',0,0,array('t.fk_advkanban' => $newkanban->id));

				//Delete list done & keep backlog
				if (!empty($TnewKanbanList)){
					foreach ($TnewKanbanList as $newKanbanList){

						// ne pas delete backlog car contient potentiellement des card
						if($newKanbanList->ref_code == 'backlog'){

							// mise a jour du rank du backlog depuis lancien
							foreach ($TkanbanList as $fromKanbanList){
								if($fromKanbanList->ref_code == 'backlog') {
									$newKanbanList->fk_rank = $fromKanbanList->fk_rank;
									$newKanbanList->label = $fromKanbanList->label;
									$newKanbanList->description = $fromKanbanList->description;
									$newKanbanList->note_public = $fromKanbanList->note_public;
									$newKanbanList->note_private = $fromKanbanList->note_private;

									$newKanbanList->update($user);
									break;
								}
							}
							continue;
						}

						$resultDel = $newKanbanList->delete($user, false, true);
						if ($resultDel < 0) {
							setEventMessages($newKanbanList->error, $newKanbanList->errors, 'errors');
						}
					}
				}

				//Create new kanbanList
				if (!empty($TkanbanList)){
					foreach ($TkanbanList as $newKanbanList){
						// ne pas creer le backlog car gardé précedament
						if($newKanbanList->ref_code == 'backlog'){
							continue;
						}

						$newKanbanList->id = 0;
						$newKanbanList->fk_advkanban = $newkanban->id;

						$resultCreate = $newKanbanList->create($user);
						if ($resultCreate < 0) {
							setEventMessages($newKanbanList->error, $newKanbanList->errors, 'errors');
						}
					}
				}

				if($object->autoCreateMissingListForKanban($user)<0){
					if(empty($object->error) && empty($object->errors)){
						setEventMessages($langs->trans('ErrorAutoCreateMissingListForKanban'), array(), 'errors');
					} else {
						setEventMessages($object->error, $object->errors, 'errors');
					}
				}

				if($object->createAdvKanbanCardsInAdvKanban($user)<0){
					if(empty($object->error) && empty($object->errors)){
						setEventMessages($langs->trans('ErrorCreateAdvKanbanCardsInAdvKanban'), array(), 'errors');
					} else {
						setEventMessages($object->error, $object->errors, 'errors');
					}
				}
			}
			//Redirect to kanban
			if ($newkanban->id > 0){
				header('Location: ' . dol_buildpath('/advancedkanban/advkanban_view.php', 1) . '?id=' . $newkanban->id);
				exit;
			}

		}else{
			setEventMessage($object->errorsToString(), 'errors');
		}
	}


	if($action == 'refreshQuantities'){
		$res = $object->refreshQuantities($user, true);
		if($res>0){
			setEventMessage($langs->trans('Updated'));
		}else{
			setEventMessage($object->errorsToString(), 'errors');
		}
	}

	if($action == 'refreshVelocity'){
		$res = $object->refreshVelocity($user, true);
		if($res>0){
			setEventMessage($langs->trans('Updated'));
		}else{
			setEventMessage($object->errorsToString(), 'errors');
		}
	}


	// Actions when linking object each other
	include DOL_DOCUMENT_ROOT.'/core/actions_dellink.inc.php';

	// Actions when printing a doc from card
	include DOL_DOCUMENT_ROOT.'/core/actions_printing.inc.php';

	// Action to build doc
	include DOL_DOCUMENT_ROOT.'/core/actions_builddoc.inc.php';

	if ($action == 'set_thirdparty' && $permissiontoadd)
	{
		$object->setValueFrom('fk_soc', GETPOST('fk_soc', 'int'), '', '', 'date', '', $user, $triggermodname);
	}
	if ($action == 'classin' && $permissiontoadd)
	{
		$object->setProject(GETPOST('projectid', 'int'));
	}

	// Actions to send emails
	$triggersendname = 'SCRUMPROJECT_SCRUMSPRINT_SENTBYMAIL';
	$autocopy = 'MAIN_MAIL_AUTOCOPY_SCRUMSPRINT_TO';
	$trackid = 'scrumsprint'.$object->id;
	include DOL_DOCUMENT_ROOT.'/core/actions_sendmails.inc.php';
}




/*
 * View
 *
 * Put here all code to build page
 */

$form = new Form($db);
$formfile = new FormFile($db);
$formproject = new FormProjets($db);

$title = $langs->trans("ScrumSprint");
$help_url = '';
llxHeader('', $title, $help_url);


// Part to create
if ($action == 'create')
{
	print load_fiche_titre($langs->trans("NewObject", $langs->transnoentitiesnoconv("ScrumSprint")), '', $object->picto);

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="add">';
	if ($backtopage) print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	if ($backtopageforcancel) print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';

	print dol_get_fiche_head(array(), '');

	// Set some default values
	//if (! GETPOSTISSET('fieldname')) $_POST['fieldname'] = 'myvalue';

	print '<table class="border centpercent tableforfieldcreate">'."\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_add.tpl.php';

	// Other attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_add.tpl.php';

	print '</table>'."\n";

	print dol_get_fiche_end();

	print '<div class="center">';
	print '<input type="submit" class="button" name="add" value="'.dol_escape_htmltag($langs->trans("Create")).'">';
	print '&nbsp; ';
	print '<input type="'.($backtopage ? "submit" : "button").'" class="button button-cancel" name="cancel" value="'.dol_escape_htmltag($langs->trans("Cancel")).'"'.($backtopage ? '' : ' onclick="javascript:history.go(-1)"').'>'; // Cancel for create does not post form if we don't know the backtopage
	print '</div>';

	print '</form>';
}

// Part to edit record
if (($id || $ref) && $action == 'edit')
{
	print load_fiche_titre($langs->trans("ScrumSprint"), '', $object->picto);

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.newToken().'">';
	print '<input type="hidden" name="action" value="update">';
	print '<input type="hidden" name="id" value="'.$object->id.'">';
	if ($backtopage) print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
	if ($backtopageforcancel) print '<input type="hidden" name="backtopageforcancel" value="'.$backtopageforcancel.'">';

	print dol_get_fiche_head();

	print '<table class="border centpercent tableforfieldedit">'."\n";

	// Common attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_edit.tpl.php';

	// Other attributes
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_edit.tpl.php';

	print '</table>';

	print dol_get_fiche_end();

	print '<div class="center"><input type="submit" class="button button-save" name="save" value="'.$langs->trans("Save").'">';
	print ' &nbsp; <input type="submit" class="button button-cancel" name="cancel" value="'.$langs->trans("Cancel").'">';
	print '</div>';

	print '</form>';
}

// Part to show record
if ($object->id > 0 && (empty($action) || ($action != 'edit' && $action != 'create')))
{
	$res = $object->fetch_optionals();

	$head = scrumsprintPrepareHead($object);
	print dol_get_fiche_head($head, 'card', $langs->trans("ScrumSprint"), -1, $object->picto);

	$formconfirm = '';

	// Create Kanban Confirmation
	if ($action == 'createkanban') {

		$kanbanObject = New AdvKanban($db);
		$Tkanban = $kanbanObject->fetchAll();
		$TkanbanLabel = array();
		if (!empty($Tkanban)){
			foreach ($Tkanban as $fk_kanban => $kanban){
				$TkanbanLabel[$fk_kanban] = $kanban->ref.' - '.$kanban->label;
			}
		}
		$SelectKanban = Form::selectarray('fk_kanban',$TkanbanLabel,'','Structure Classique',0,0,'',0,0,0,'','minwidth200');


		// Create an array for form
		$formquestion = array(
			array('type' => 'other', 'name' => 'fk_kanban', 'label' => $langs->trans('SelectKanbanClone'), 'value' => $SelectKanban),
		);
		$formconfirm = $form->formconfirm($_SERVER['PHP_SELF'] . '?id=' . $object->id, $langs->trans('CreateNewAdvKanban'), $langs->trans('ConfirmCreateAsk', $object->ref), 'confirm_create_kanban', $formquestion, 'yes', 1);
	}

	// Confirmation to delete
	if ($action == 'delete') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('DeleteScrumSprint'), $langs->trans('ConfirmDeleteObject'), 'confirm_delete', '', 0, 1);
	}
	// Confirmation to delete line
	if ($action == 'deleteline') {
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id.'&lineid='.$lineid, $langs->trans('DeleteLine'), $langs->trans('ConfirmDeleteLine'), 'confirm_deleteline', '', 0, 1);
	}
	// Clone confirmation
	if ($action == 'clone') {
		// Create an array for form
		$formquestion = array();
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('ToClone'), $langs->trans('ConfirmCloneAsk', $object->ref), 'confirm_clone', $formquestion, 'yes', 1);
	}

	// Confirmation of action xxxx
	if ($action == 'xxx')
	{
		$formquestion = array();
		/*
		$forcecombo=0;
		if ($conf->browser->name == 'ie') $forcecombo = 1;	// There is a bug in IE10 that make combo inside popup crazy
		$formquestion = array(
			// 'text' => $langs->trans("ConfirmClone"),
			// array('type' => 'checkbox', 'name' => 'clone_content', 'label' => $langs->trans("CloneMainAttributes"), 'value' => 1),
			// array('type' => 'checkbox', 'name' => 'update_prices', 'label' => $langs->trans("PuttingPricesUpToDate"), 'value' => 1),
			// array('type' => 'other',    'name' => 'idwarehouse',   'label' => $langs->trans("SelectWarehouseForStockDecrease"), 'value' => $formproduct->selectWarehouses(GETPOST('idwarehouse')?GETPOST('idwarehouse'):'ifone', 'idwarehouse', '', 1, 0, 0, '', 0, $forcecombo))
		);
		*/
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('XXX'), $text, 'confirm_xxx', $formquestion, 0, 1, 220);
	}

	// Call Hook formConfirm
	$parameters = array('formConfirm' => $formconfirm, 'lineid' => $lineid);
	$reshook = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
	if (empty($reshook)) $formconfirm .= $hookmanager->resPrint;
	elseif ($reshook > 0) $formconfirm = $hookmanager->resPrint;

	// Print form confirm
	print $formconfirm;


	// Object card
	// ------------------------------------------------------------
	$linkback = '<a href="'.dol_buildpath('/scrumproject/scrumsprint_list.php', 1).'?restore_lastsearch_values=1'.(!empty($socid) ? '&socid='.$socid : '').'">'.$langs->trans("BackToList").'</a>';

	$morehtmlref = '<div class="refidno">';
	if(!empty($object->label)) $morehtmlref.= $object->label . '<br>';
	$morehtmlref.= $object->showOutputField($object->fields['fk_team'], 'fk_team', $object->fk_team);
	$morehtmlref .= '</div>';

	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);

	print '<div class="fichecenter">';
	print '<div class="fichehalfleft">';
	print '<div class="underbanner clearboth"></div>';
	print '<table class="border centpercent tableforfield">'."\n";

	// Common attributes
	$keyforbreak='qty_velocity';	// We change column just before this field
	unset($object->fields['fk_team']);
	unset($object->fields['label']);
	include DOL_DOCUMENT_ROOT.'/core/tpl/commonfields_view.tpl.php';

	// Other attributes. Fields from hook formObjectOptions and Extrafields.
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_view.tpl.php';

	print '</table>';
	print '</div>';
	print '</div>';

	print '<div class="clearboth"></div>';


	/*
	 * Ajoute le kanban dans la fiche
	 */
	$jsData = new stdClass();
	$jsData->kanbansGetNomUrls = array();
	$jsData->langs = new stdClass();
	$jsData->langs->kanban = $langs->trans("kanban");

	$scrumKanbanStatic = new AdvKanban($db);
	$TAdvKanbans = $scrumKanbanStatic->fetchAll( '', '',0,  0, array('fk_scrum_sprint' => $object->id));
	if(is_array($TAdvKanbans) && !empty($TAdvKanbans)){

		foreach ($TAdvKanbans as $scrumKanban){
			$jsData->kanbansGetNomUrls[]=$scrumKanban->getNomUrl(1);
		}

		?>
		<script>
			$(document).ready(function () {
				if($('.field_date_end').length) {
					let jsData = <?php print json_encode($jsData); ?>;
					let kanbanTableRow = '<tr class="kanban-tr"><td class="kanban-title">' + jsData.langs.kanban + '</td><td>' + jsData.kanbansGetNomUrls.join(", ") + '</td></tr>';
					$(kanbanTableRow).insertAfter('.field_date_end');
				}
			});
		</script>
		<?php
	}

	print dol_get_fiche_end();


	// Buttons for actions

	if ($action != 'presend' && $action != 'editline') {
		print '<div class="tabsAction">'."\n";
		$parameters = array();
		$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
		if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

		if (empty($reshook))
		{

			// Create Kanban
			$kanban = $object->getKanbanId();
			if (empty($kanban)) {
				print dolGetButtonAction($langs->trans('CreateNewAdvKanban'), '', 'default', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=createkanban&object=scrumsprint&token='.newToken(), '', $permissiontoadd);
			}

			// Send
			if (empty($user->socid)) {
				print dolGetButtonAction($langs->trans('SendMail'), '', 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=presend&mode=init#formmailbeforetitle&token='.newToken());
			}

			// Modify
			print dolGetButtonAction($langs->trans('Modify'), '', 'default', $_SERVER["PHP_SELF"].'?id='.$object->id.'&action=edit', '', $permissiontoadd);

			// When draft
			if ($object->status == $object::STATUS_DRAFT) {
				print dolGetButtonAction($langs->trans('Validate'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=confirm_validate&confirm=yes&token='.newToken(), '', $permissiontoadd);
			}

			// When valid
			if ($object->status == $object::STATUS_VALIDATED) {
				print dolGetButtonAction($langs->trans('SetToDraft'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=confirm_setdraft&confirm=yes&token='.newToken(), '', $permissiontoadd);
				print dolGetButtonAction($langs->trans('SetToPending'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=confirm_setpending&confirm=yes&token='.newToken(), '', $permissiontoadd);
			}

			// When pending
			if($object->status == $object::STATUS_PENDING) {
				print dolGetButtonAction($langs->trans('SetBackToValid'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=confirm_reopen&confirm=yes&token='.newToken(), '', $permissiontoadd);
				print dolGetButtonAction($langs->trans('SetToDone'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=confirm_setdone&confirm=yes&token='.newToken(), '', $permissiontoadd);
			}

			// When done
			if($object->status == $object::STATUS_DONE) {
				print dolGetButtonAction($langs->trans('SetBackToPending'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=confirm_setpending&confirm=yes&token='.newToken(), '', $permissiontoadd);
			}

			// Clone
			print dolGetButtonAction($langs->trans('ToClone'), '', 'default', $_SERVER['PHP_SELF'].'?id='.$object->id.'&socid='.$object->socid.'&action=clone&object=scrumsprint&token='.newToken(), '', $permissiontoadd);

			// Delete (need delete permission, or if draft, just need create/modify permission)
			print dolGetButtonAction($langs->trans('Delete'), '', 'delete', $_SERVER['PHP_SELF'].'?id='.$object->id.'&action=delete&token='.newToken(), '', $permissiontodelete || ($object->status == $object::STATUS_DRAFT && $permissiontoadd));

			print '<div style="clear:both; margin-top: 5px;"></div>';

			if ($object->status == $object::STATUS_DRAFT) {
				print dolGetButtonAction($langs->trans('RefreshVelocity'), '', 'default', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=refreshVelocity&token='.newToken());
			}

			print dolGetButtonAction($langs->trans('RefreshTimes'), '', 'default', $_SERVER['PHP_SELF'] . '?id=' . $object->id . '&action=refreshQuantities&token='.newToken());


		}
		print '</div>'."\n";
	}


	// Select mail models is same action as presend
	if (GETPOST('modelselected')) {
		$action = 'presend';
	}

	if ($action != 'presend')
	{
		print '<div class="fichecenter"><div class="fichehalfleft">';
		print '<a name="builddoc"></a>'; // ancre

		$includedocgeneration = 0;

		// Documents
		if ($includedocgeneration) {
			$objref = dol_sanitizeFileName($object->ref);
			$relativepath = $objref.'/'.$objref.'.pdf';
			$filedir = $conf->scrumproject->dir_output.'/'.$object->element.'/'.$objref;
			$urlsource = $_SERVER["PHP_SELF"]."?id=".$object->id;
			$genallowed = $user->rights->scrumproject->scrumsprint->read; // If you can read, you can build the PDF to read content
			$delallowed = $user->rights->scrumproject->scrumsprint->write; // If you can create/edit, you can remove a file on card
			print $formfile->showdocuments('scrumproject:ScrumSprint', $object->element.'/'.$objref, $filedir, $urlsource, $genallowed, $delallowed, $object->model_pdf, 1, 0, 0, 28, 0, '', '', '', $langs->defaultlang);
		}

		// Show links to link elements
		$linktoelem = $form->showLinkToObjectBlock($object, null, array('scrumsprint'));
		$somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);


		print '</div><div class="fichehalfright"><div class="ficheaddleft">';

		$MAXEVENT = 10;

		$morehtmlright = '<a href="'.dol_buildpath('/scrumproject/scrumsprint_agenda.php', 1).'?id='.$object->id.'">';
		$morehtmlright .= $langs->trans("SeeAll");
		$morehtmlright .= '</a>';

		// List of actions on element
		include_once DOL_DOCUMENT_ROOT.'/core/class/html.formactions.class.php';
		$formactions = new FormActions($db);
		$somethingshown = $formactions->showactions($object, $object->element.'@'.$object->module, (is_object($object->thirdparty) ? $object->thirdparty->id : 0), 1, '', $MAXEVENT, '', $morehtmlright);

		print '</div></div></div>';
	}

	//Select mail models is same action as presend
	if (GETPOST('modelselected')) $action = 'presend';

	// Presend form
	$modelmail = 'scrumsprint';
	$defaulttopic = 'InformationMessage';
	$diroutput = $conf->scrumproject->dir_output;
	$trackid = 'scrumsprint'.$object->id;

	include DOL_DOCUMENT_ROOT.'/core/tpl/card_presend.tpl.php';
}

// End of page
llxFooter();
$db->close();
