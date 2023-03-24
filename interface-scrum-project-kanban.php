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
require_once __DIR__ . '/class/scrumsprint.class.php';
dol_include_once('/advancedkanban/class/advkanbancard.class.php');
dol_include_once('/advancedkanban/class/advKanbanTools.class.php');
if (!class_exists('Validate')) { require_once DOL_DOCUMENT_ROOT . '/core/class/validate.class.php'; }

global $langs, $db, $hookmanager, $user, $mysoc;
/**
 * @var DoliDB $db
 */
$hookmanager->initHooks('scrumkanbaninterface');

// Load traductions files requiredby by page
$langs->loadLangs(array("scrumproject@scrumproject","advancedkanban@advancedkanban", "other", 'main'));

$action = GETPOST('action');

// Security check
if (empty($conf->scrumproject->enabled)) accessforbidden('Module not enabled');

$jsonResponse = new ScrumProject\JsonResponse();

// TODO : ajouter des droits et une vérification plus rigoureuse actuellement il n'y a pas de droit sur le kanban il faut peut-être en ajouter
if (empty($user->rights->scrumproject->scrumsprint->read)) {
    $jsonResponse->msg = $langs->trans('NotEnoughRights');
    $jsonResponse->result = 0;
}
elseif ($action === 'getSprintResumeData') {
	_actionGetSprintResumeData($jsonResponse);
}
elseif ($action === 'getSprintInfo') {
	_actionGetSprintInfos($jsonResponse);
}
else{
	$jsonResponse->msg = 'Action not found';
}

print $jsonResponse->getJsonResponse();

$db->close();    // Close $db database opened handler




/**
 * @param ScrumProject\JsonResponse $jsonResponse
 * @return bool|void
 */
function _actionGetSprintInfos($jsonResponse){
	global $user, $langs, $db;

	$data = GETPOST("data", "array");

	if (empty($user->rights->scrumproject->scrumsprint->read)) {
		$jsonResponse->msg = $langs->trans('NotEnoughRights');
		$jsonResponse->result = 0;
		return false;
	}

	if(empty($data['fk_kanban'])){
		$jsonResponse->msg = 'Need Kanban Id';
		return false;
	}

	$fk_kanban = $data['fk_kanban'];
	$kanban = _checkObjectByElement('advancedkanban_advkanban', $fk_kanban, $jsonResponse);
	/** @var AdvKanban $kanban */
	if(!$kanban){
		return false;
	}

	$scrumSprint = ScrumSprint::getScrumSprintFromKanban($fk_kanban);
	if(!$scrumSprint){
		return false;
	}

	$jsonResponse->data = new stdClass();
	$jsonResponse->data->sprintInfos = new stdClass();
	$jsonResponse->data->sprintInfos->date_start = $scrumSprint->showOutputFieldQuick('date_start');
	$jsonResponse->data->sprintInfos->date_end = $scrumSprint->showOutputFieldQuick('date_end');
	$jsonResponse->data->sprintInfos->qty_velocity = $scrumSprint->showOutputFieldQuick('qty_velocity');
	$jsonResponse->data->sprintInfos->qty_planned = $scrumSprint->showOutputFieldQuick('qty_planned');
	$jsonResponse->data->sprintInfos->qty_done = $scrumSprint->showOutputFieldQuick('qty_done');
	$jsonResponse->data->sprintInfos->qty_consumed = $scrumSprint->showOutputFieldQuick('qty_consumed');
	$jsonResponse->data->sprintInfos->qty_us_planned_done = $scrumSprint->calcUsPlannedInList('done');

	return true;
}

/**
 * @param ScrumProject\JsonResponse $jsonResponse
 * @return bool|void
 */
function _actionGetSprintResumeData($jsonResponse){
	global $user, $langs, $db;

	if (empty($user->rights->scrumproject->scrumsprint->read)) {
		$jsonResponse->msg = $langs->trans('NotEnoughRights');
		$jsonResponse->result = 0;
		return false;
	}

	$jsonResponse->result = 0;

	$data = GETPOST("data", "array");

	if(empty($data['fk_kanban'])){
		$jsonResponse->msg = 'Need Kanban Id';
		return false;
	}

	$fk_kanban = $data['fk_kanban'];
	$kanban = _checkObjectByElement('advancedkanban_advkanban', $fk_kanban, $jsonResponse);
	/** @var AdvKanban $kanban */
	if(!$kanban){
		return false;
	}

	$sprint = ScrumSprint::getScrumSprintFromKanban($fk_kanban);
	if(!$sprint){
		return false;
	}


	$jsonResponse->data = new stdClass();
	$jsonResponse->data->html = $sprint->displayUsersProgress();


	$jsonResponse->result = 1;
	return true;
}


/**
 * @param ScrumProject\JsonResponse $jsonResponse
 * @return bool|CommonObject
 */
function _checkObjectByElement($elementType, $id, $jsonResponse){
	global $langs, $db;

	$validate = new Validate($db, $langs);

	if(!$validate->isNumeric($id)){
		$jsonResponse->msg = $validate->error;
		return false;
	}

	$object = scrumProjectGetObjectByElement($elementType, $id);
	if(!$object){
		$jsonResponse->msg = $elementType . ' : ' . $langs->trans('RequireValidExistingElement');
		return false;
	}

	return $object;
}
