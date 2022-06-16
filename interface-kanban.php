<?php


//if (! defined('NOREQUIREDB'))              define('NOREQUIREDB', '1');				// Do not create database handler $db
//if (! defined('NOREQUIREUSER'))            define('NOREQUIREUSER', '1');				// Do not load object $user
//if (! defined('NOREQUIRESOC'))             define('NOREQUIRESOC', '1');				// Do not load object $mysoc
//if (! defined('NOREQUIRETRAN'))            define('NOREQUIRETRAN', '1');				// Do not load object $langs
//if (! defined('NOSCANGETFORINJECTION'))    define('NOSCANGETFORINJECTION', '1');		// Do not check injection attack on GET parameters
//if (! defined('NOSCANPOSTFORINJECTION'))   define('NOSCANPOSTFORINJECTION', '1');		// Do not check injection attack on POST parameters
//if (! defined('NOCSRFCHECK'))              define('NOCSRFCHECK', '1');				// Do not check CSRF attack (test on referer + on token if option MAIN_SECURITY_CSRF_WITH_TOKEN is on).
//if (! defined('NOTOKENRENEWAL'))           define('NOTOKENRENEWAL', '1');				// Do not roll the Anti CSRF token (used if MAIN_SECURITY_CSRF_WITH_TOKEN is on)
//if (! defined('NOSTYLECHECK'))             define('NOSTYLECHECK', '1');				// Do not check style html tag into posted data
if (! defined('NOREQUIREMENU')) define('NOREQUIREMENU', '1');				// If there is no need to load and show top and left menu
if (! defined('NOREQUIREHTML')) define('NOREQUIREHTML', '1');				// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX')) define('NOREQUIREAJAX', '1');       	  	// Do not load ajax.lib.php library
//if (! defined("NOLOGIN"))                  define("NOLOGIN", '1');					// If this page is public (can be called outside logged session). This include the NOIPCHECK too.
//if (! defined('NOIPCHECK'))                define('NOIPCHECK', '1');					// Do not check IP defined into conf $dolibarr_main_restrict_ip
//if (! defined("MAIN_LANG_DEFAULT"))        define('MAIN_LANG_DEFAULT', 'auto');					// Force lang to a particular value
//if (! defined("MAIN_AUTHENTICATION_MODE")) define('MAIN_AUTHENTICATION_MODE', 'aloginmodule');	// Force authentication handler
//if (! defined("NOREDIRECTBYMAINTOLOGIN"))  define('NOREDIRECTBYMAINTOLOGIN', 1);		// The main.inc.php does not make a redirect if not logged, instead show simple error message
//if (! defined("FORCECSP"))                 define('FORCECSP', 'none');				// Disable all Content Security Policies
//if (! defined('CSRFCHECK_WITH_TOKEN'))     define('CSRFCHECK_WITH_TOKEN', '1');		// Force use of CSRF protection with tokens even for GET
//if (! defined('NOBROWSERNOTIF'))     		 define('NOBROWSERNOTIF', '1');				// Disable browser notification


$sapi_type = php_sapi_name();
$script_file = basename(__FILE__);
$path = dirname(__FILE__) . '/';

// Include and load Dolibarr environment variables
$res = 0;
if (!$res && file_exists($path . "main.inc.php")) $res = @include($path . "main.inc.php");
if (!$res && file_exists($path . "../main.inc.php")) $res = @include($path . "../main.inc.php");
if (!$res && file_exists($path . "../../main.inc.php")) $res = @include($path . "../../main.inc.php");
if (!$res && file_exists($path . "../../../main.inc.php")) $res = @include($path . "../../../main.inc.php");
if (!$res) die("Include of master fails");

require_once __DIR__ . '/lib/scrumproject.lib.php';
require_once __DIR__ . '/class/jsonResponse.class.php';
require_once __DIR__ . '/class/scrumkanbanlist.class.php';
require_once __DIR__ . '/class/scrumcard.class.php';
if (!class_exists('Validate')) { require_once DOL_DOCUMENT_ROOT . '/core/class/validate.class.php'; }

global $langs, $db, $hookmanager, $user, $mysoc;
/**
 * @var DoliDB $db
 */
$hookmanager->initHooks('scrumkanbaninterface');

// Load traductions files requiredby by page
$langs->loadLangs(array("scrumproject@scrumproject","scrumkanban@scrumproject", "other", 'main'));

$action = GETPOST('action');

// Security check
if (empty($conf->scrumproject->enabled)) accessforbidden('Module not enabled');

$jsonResponse = new JsonResponse();


if ($action === 'addKanbanList') {
	_actionAddList($jsonResponse);
}
elseif ($action === 'getAllBoards') {
	_actionGetAllBoards($jsonResponse);
}
elseif ($action === 'getAllItemToList') {
	_actionAddItemToList($jsonResponse);
}
else{
	$jsonResponse->msg = 'Action not found';
}

print $jsonResponse->getJsonResponse();

$db->close();    // Close $db database opened handler

/**
 * @param JsonResponse $jsonResponse
 * @return bool|void
 */
function _actionAddList($jsonResponse){
	global $user, $langs, $db;

	$data = GETPOST("data", "array");
	$validate = new Validate($db, $langs);

	if(empty($data['fk_kanban'])){
		$jsonResponse->msg = 'Need Kanban Id';
		return false;
	}

	$fk_kanban = $data['fk_kanban'];

	if(!$validate->isNumeric($fk_kanban)){
		$jsonResponse->msg = $validate->error;
		return false;
	}

	/**
	 * @var ScrumKanban $kanban
	 */
	$kanban = scrumProjectGetObjectByElement('scrumproject_scrumkanban', $fk_kanban);
	if(!$kanban){
		$jsonResponse->msg = $langs->trans('RequireValidExistingElement');
		return false;
	}


	$kanbanList = new ScrumKanbanList($db);
	$kanbanList->fk_scrum_kanban = $kanban->id;


	$kanbanList->fk_rank = 0;
	$obj = $db->getRow('SELECT MAX(fk_rank) maxRank FROM '.MAIN_DB_PREFIX.$kanbanList->table_element . ' WHERE fk_scrum_kanban = '.intval($kanban->id));
	if($obj){
		$kanbanList->fk_rank = intval($obj->maxRank) + 1;
	}

	if(!empty($data['label'])){
		$kanbanList->label = $data['label'];
	}else{
		$kanbanList->label = $langs->trans('NewList');
	}

	foreach ($kanbanList->fields as $field => $value) {
		if (!empty($val['validate'])
			&& is_callable(array($kanbanList, 'validateField'))
			&& !$kanbanList->validateField($kanbanList->fields, $field, $kanbanList->{$field})
		) {
			$jsonResponse->msg = $kanbanList->errorsToString();
			$jsonResponse->result = 0;
			return false;
		}
	}


	if($kanbanList->create($user) > 0){
		$jsonResponse->msg = $langs->trans('Created');
		$jsonResponse->result = 1;

		$jsonResponse->data = $kanbanList->getKanBanListObjectFormatted();

		return true;
	}
	else{
		$jsonResponse->result = 0;
		$jsonResponse->msg = $langs->trans('CreateError') . ' : ' . $kanbanList->errorsToString();
		return false;
	}
}

/**
 * @param JsonResponse $jsonResponse
 * @return bool|void
 */
function _actionGetAllBoards($jsonResponse){
	global $user, $langs, $db;

	$data = GETPOST("data", "array");
	$validate = new Validate($db, $langs);

	if(empty($data['fk_kanban'])){
		$jsonResponse->msg = 'Need Kanban Id';
		return false;
	}

	$fk_kanban = $data['fk_kanban'];
	$kanban = _checkObjectByElement('scrumproject_scrumkanban', $fk_kanban, $jsonResponse);
	if(!$kanban){
		return false;
	}

	$staticKanbanList = new ScrumKanbanList($db);
	$kanbanLists = $staticKanbanList->fetchAll('ASC', 'fk_rank', 0, 0, array('fk_scrum_kanban' => intval($kanban->id)));

	/**
	 * @var ScrumKanbanList[] $kanbanLists
	 */

	if(is_array($kanbanLists)){
		$jsonResponse->result = 1;
		$jsonResponse->data = array();
		foreach ($kanbanLists as $kanbanList){
			$jsonResponse->data[] = $kanbanList->getKanBanListObjectFormatted();
		}

		return true;
	}
	else{
		$jsonResponse->result = 0;
		$jsonResponse->msg = $langs->trans('CreateError') . ' : ' . $staticKanbanList->errorsToString();
		return false;
	}
}


/**
 * @param JsonResponse $jsonResponse
 * @return bool|void
 */
function _actionAddItemToList($jsonResponse){
	global $user, $langs, $db;

	$data = GETPOST("data", "array");

	// check kanban list data
	if(empty($data['fk_kanbanlist'])){
		$jsonResponse->msg = 'Need Kanbanlist Id';
		return false;
	}

	// toDo vérifier le status du kanban aussi

	$fk_kanbanlist = $data['fk_kanbanlist'];
	$kanbanList = _checkObjectByElement('scrumproject_scrumkanbanlist', $fk_kanbanlist, $jsonResponse);
	if(!$kanbanList){
		return false;
	}

	$scrumCard = new ScrumCard($db);
	$scrumCard->fk_scrum_kanbanlist = $kanbanList->id;


	$scrumCard->fk_rank = $kanbanList->getMaxRankOfKanBanListItems() + 1;


	if(!empty($data['label'])){
		$scrumCard->label = $data['label'];
	}else{
		$scrumCard->label = $langs->trans('NewCard');
	}

	foreach ($scrumCard->fields as $field => $value) {
		if (!empty($val['validate'])
			&& is_callable(array($scrumCard, 'validateField'))
			&& !$scrumCard->validateField($scrumCard->fields, $field, $kanbanList->{$field})
		) {
			$jsonResponse->msg = $scrumCard->errorsToString();
			$jsonResponse->result = 0;
			return false;
		}
	}


	if($scrumCard->create($user) > 0){
		$jsonResponse->msg = $langs->trans('Created');
		$jsonResponse->result = 1;
		$jsonResponse->data = $scrumCard->getKanBanItemObjectFormatted();
		return true;
	}
	else{
		$jsonResponse->result = 0;
		$jsonResponse->msg = $langs->trans('CreateError') . ' : ' . $kanbanList->errorsToString();
		return false;
	}
}


/**
 * @param JsonResponse $jsonResponse
 * @return bool|CommonObject
 */
function _checkObjectByElement($elementType, $id, $jsonResponse){
	global $langs, $db;

	$validate = new Validate($db, $langs);

	if(!$validate->isNumeric($id)){
		$jsonResponse->msg = $validate->error;
		return false;
	}

	$kanbanlist = scrumProjectGetObjectByElement($elementType, $id);
	if(!$kanbanlist){
		$jsonResponse->msg = $elementType . ' : ' . $langs->trans('RequireValidExistingElement');
		return false;
	}

	return $kanbanlist;
}
