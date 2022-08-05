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
require_once __DIR__ . '/class/scrumuserstory.class.php';
require_once __DIR__ . '/class/scrumuserstorysprint.class.php';

if (!class_exists('Validate')) { require_once DOL_DOCUMENT_ROOT . '/core/class/validate.class.php'; }

global $langs, $db, $hookmanager, $user, $mysoc;
/**
 * @var DoliDB $db
 */
$hookmanager->initHooks('scrumuserstorysprintplanwizardinterface');

// Load traductions files requiredby by page
$langs->loadLangs(array("scrumproject@scrumproject","scrumkanban@scrumproject", "other", 'main'));

$action = GETPOST('action');

// Security check
if (empty($conf->scrumproject->enabled)) accessforbidden('Module not enabled');

$jsonResponse = new JsonResponse();


if ($action === 'get-sprint-autocompletion') {
	$ignoreSprint = array();
	$fk_scrum_user_story = GETPOSTINT('fk_scrum_user_story');
	if($fk_scrum_user_story > 0){
		$ignoreSprint = $db->getRows('SELECT fk_scrum_sprint FROM  '.MAIN_DB_PREFIX.'scrumproject_scrumuserstorysprint WHERE fk_scrum_user_story = '.intval($fk_scrum_user_story));
	}

	_getAutocompletionForSprint($jsonResponse, GETPOST('term'), time(), $ignoreSprint);
}
elseif ($action === 'add-us-planned-to-sprint') {
	_actionAddScrumUserStoryPlanned($jsonResponse);
}
elseif ($action === 'delete-us-planned') {
	_actionRemoveUserStorySprint($jsonResponse);
}
elseif ($action === 'get-user-story-infos') {
	_actionGetScrumUserStorySprintInfo($jsonResponse);
}
else{
	$jsonResponse->msg = 'Action not found';
}

print $jsonResponse->getJsonResponse();

$db->close();    // Close $db database opened handler


/**
 * @param JsonResponse $jsonResponse
 * @param string $search Search terms which will be matched against the client ref and the leaser ref
 * @param int $minDateEnd
 * @param array $notSprint list id of
 * @return array|string  Returns a sequential array of objects with 2 props: "id" (ID of the contract) and "text" (concatenated client / leaser refs)
 *                       If the SQL query fails, return the SQL itself.
 */
function _getAutocompletionForSprint(JsonResponse $jsonResponse, string $search, int $minDateEnd = 0, $notSprint = array()): bool {
	/** @var DoliDB $db */
	global $db, $conf, $langs;

	if(!class_exists('ScrumSprint')){
		require_once __DIR__ . '/class/scrumsprint.class.php';
	}

	$sprintStatic = new ScrumSprint($db);

	if(!is_array($notSprint)){
		$notSprint = array();
	}

	$sql = /** @lang SQL */
		'SELECT s.rowid     AS "id",'
		. '     s.label AS "text", '
		. '     g.nom AS "GroupName" '
		. ' FROM '.MAIN_DB_PREFIX.$sprintStatic->table_element.' s'
		. ' LEFT JOIN '.MAIN_DB_PREFIX.'usergroup g ON (s.fk_team = g.rowid)'
		. ' WHERE 1 = 1 ';

	if(!empty($notSprint)){
		$sql.= ' AND s.rowid NOT IN ('. implode(',', $notSprint).') ';
	}

	if($minDateEnd > 0){
		$sql.= ' AND s.date_end >= "'. $db->idate($minDateEnd).'" ';
	}

	if(!empty($search)){
		$sql.= natural_search(['s.label', 's.ref'], $search);
	}


	$sql.= ' ORDER BY s.date_start ASC, s.label ASC';

	if(!empty($search)){
		$sql.= ' LIMIT 10;';
	}

	$TRow = $db->getRows($sql);
	if (!$TRow) {
		$jsonResponse->data = ['errors' => $db->lasterror(), 'sql' => $db->lastqueryerror()];
		return false;
	}

	$jsonResponse->data = ['rows' => []];
	foreach ($TRow as $obj) {

		$sprint = new ScrumSprint($db);
		$sprint->fetch($obj->id);

		$item = new stdClass();
		$item->id =  $sprint->id;
		$item->text = $sprint->label.' - '.$obj->GroupName.' - '.dol_print_date($sprint->date_start, "%d/%m/%Y").' '.$langs->trans('to').' '.dol_print_date($sprint->date_end, "%d/%m/%Y");

		$item->sprintQtyAvailable = $sprint->getQtyAvailable();
		$item->html_sprintQtyAvailable = $sprint->getQtyAvailableBadge();


		$jsonResponse->data['rows'][] = $item;
	}
	return true;
}



/**
 * @param JsonResponse $jsonResponse
 * @return bool|void
 */
function _actionAddScrumUserStoryPlanned($jsonResponse){
	global $user, $langs, $db;

	$data = GETPOST("data", "array");

	$userStory = new ScrumUserStory($db);
	if($userStory->fetch($data['fk_scrum_user_story']) <= 0){
		$jsonResponse->msg = 'Need valid fk_scrum_user_story';
		return false;
	}

	$sprint = new ScrumSprint($db);
	if($sprint->fetch($data['fk_scrum_sprint']) <= 0){
		$jsonResponse->msg = 'Need valid fk_scrum_sprint';
		return false;
	}


//	// recherche d'une plannification déja effectuée
//	$resSearch = $db->getRow('SELECT COUNT(rowid) nb FROM '.MAIN_DB_PREFIX.'scrumproject_scrumuserstorysprint WHERE fk_scrum_sprint = '.intval($data['fk_scrum_sprint']).' AND fk_scrum_user_story = '.intval($data['fk_scrum_user_story']));
//	if($resSearch){
//		if($resSearch->nb > 0){
//			$jsonResponse->msg = $langs->trans("UserStoryAlreadyPlannedForThisSprint");
//			return false;
//		}
//	}

	$userStorySprint = new ScrumUserStorySprint($db);
	$userStorySprint->fk_scrum_user_story = intval($data['fk_scrum_user_story']);
	$userStorySprint->fk_scrum_sprint = intval($data['fk_scrum_sprint']);
	$userStorySprint->qty_planned = isset($data['qty_planned'])?doubleval($data['qty_planned']):0;
	$userStorySprint->label = isset($data['label'])?$data['label']:$userStory->label;
	$userStorySprint->business_value = isset($data['business_value'])?doubleval($data['business_value']):0;

	if($userStorySprint->create($user) > 0){
		$jsonResponse->result = 1;

		$jsonResponse->data = new stdClass(); // Todo : ajouter au besoins des infos de retours
		$jsonResponse->data->id = $userStorySprint->id;


		// pour des données a jour je recharge l'object
		$scrumSprint = _checkObjectByElement('scrumproject_scrumsprint', $userStorySprint->fk_scrum_sprint, $jsonResponse);
		/** @var $scrumSprint ScrumSprint */
		$jsonResponse->data->sprintInfos = new stdClass();
		$jsonResponse->data->sprintInfos->id =$scrumSprint->id;
		$jsonResponse->data->sprintInfos->qtyAvailable = $scrumSprint->getQtyAvailable();
		$jsonResponse->data->sprintInfos->html_qtyAvailable = $scrumSprint->getQtyAvailableBadge();



		return true;
	}
	else{
		$jsonResponse->result = 0;
		$jsonResponse->msg = $langs->trans('CreateError') . ' : ' . $userStorySprint->errorsToString().' '.$userStorySprint->ref;
		return false;
	}
}



/**
 * @param JsonResponse $jsonResponse
 * @return bool|void
 */
function _actionRemoveUserStorySprint($jsonResponse){
	global $user, $langs, $db;

	$jsonResponse->result = 0;

	$data = GETPOST("data", "array");

	// check kanban item data
	if(empty($data['fk_scrum_user_story_sprint'])){
		$jsonResponse->msg = 'Need user story sprint id';
		return false;
	}


	$srumUserStorySprintId = $data['fk_scrum_user_story_sprint'];
	$srumUserStorySprint = _checkObjectByElement('scrumproject_scrumuserstorysprint', $srumUserStorySprintId, $jsonResponse);
	/** @var ScrumUserStorySprint $srumUserStorySprint */
	if(!$srumUserStorySprint){
		$jsonResponse->msg = 'Invalid scrum user story sprint load';
		return false;
	}

	if(empty($user->rights->scrumproject->scrumuserstorysprint->delete)){
		$jsonResponse->msg = 'Not enough rights';
		return false;
	}


	if(!$srumUserStorySprint->canBeDeleted()){
		$jsonResponse->msg = 'Cant be deleted : Foreign key exists';
		return false;
	}

	$scrumSprint = _checkObjectByElement('scrumproject_scrumsprint', $srumUserStorySprint->fk_scrum_sprint, $jsonResponse);

	if($srumUserStorySprint->delete($user) <= 0){
		$jsonResponse->msg = 'Error deleting scrum user story : '.$srumUserStorySprint->errorsToString();
		return false;
	}

	/**
	 * @var $scrumSprint ScrumSprint
	 */
	$jsonResponse->data = new stdClass();
	$jsonResponse->data->sprintInfos = new stdClass();
	$jsonResponse->data->sprintInfos->id = $scrumSprint->id;
	$jsonResponse->data->sprintInfos->qtyAvailable = $scrumSprint->getQtyAvailable();
	$jsonResponse->data->sprintInfos->html_qtyAvailable = $scrumSprint->getQtyAvailableBadge();



	$jsonResponse->result = 1;
	return true;
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

	$object = scrumProjectGetObjectByElement($elementType, $id);
	if(!$object){
		$jsonResponse->msg = $elementType . ' : ' . $langs->trans('RequireValidExistingElement');
		return false;
	}

	return $object;
}

/**
 * @param JsonResponse $jsonResponse
 * @return bool|void
 */
function _actionGetScrumUserStorySprintInfo($jsonResponse){
	global $db;

	$data = GETPOST("data", "array");

	// check kanban list data
	if(empty($data['id'])){
		$jsonResponse->msg = 'Need scrum user story Id';
		return false;
	}

	$scrumUserStory = new ScrumUserStory($db);
	$res = $scrumUserStory->fetch($data['id']);
	if($res <= 0){
		$jsonResponse->msg = 'ScrumUserStory fetch error';
		return false;
	}


	$jsonResponse->result = 1;

	$jsonResponse->data = $scrumUserStory->jsonSerialize();


	if(!empty($data['fk_scrumsprint'])){
		$scrumsprint = scrumProjectGetObjectByElement('scrumproject_scrumsprint', $data['fk_scrumsprint']);
		if(!$scrumsprint){
			$jsonResponse->data->sprintInfos = new stdClass();
			$jsonResponse->data->sprintInfos->qtyAvailable =  $scrumsprint->getQtyAvailable();
			$jsonResponse->data->sprintInfos->html_qtyAvailable =  $scrumsprint->getQtyAvailableBadge();
		}
	}



	return true;
}
