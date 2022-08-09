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
elseif ($action === 'getScrumCardData') {
	_actionGetScrumCardData($jsonResponse);
}
elseif ($action === 'splitScrumCard') {
	_actionSplitScrumCard($jsonResponse);
}
elseif ($action === 'dropItemToList') {
	_actionDropItemToList($jsonResponse);
}
elseif ($action === 'changeListOrder') {
	_actionChangeListOrder($jsonResponse);
}
elseif ($action === 'assignMeToCard') {
	_actionAssignUserToCard($jsonResponse);
}
elseif ($action === 'toggleAssignMeToCard') {
	_actionAssignUserToCard($jsonResponse, false, true);
}
elseif ($action === 'removeMeFromCard') {
	_actionRemoveUserToCard($jsonResponse);
}
elseif ($action === 'removeList') {
	_actionRemoveList($jsonResponse);
}
elseif ($action === 'removeCard') {
	_actionRemoveCard($jsonResponse);
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
		$jsonResponse->data = new stdClass();

		$jsonResponse->data->boards = array();


		// All listes stored in databases
		foreach ($kanbanLists as $kanbanList){
			$jsonResponse->data->boards[] = $kanbanList->getKanBanListObjectFormatted();
		}

		$jsonResponse->data->md5Boards = md5(json_encode($jsonResponse->data->boards));

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
function _actionRemoveList($jsonResponse){
	global $user, $langs, $db;

	$jsonResponse->result = 0;

	$data = GETPOST("data", "array");

	// check kanban list data
	if(empty($data['kanban_list_id'])){
		$jsonResponse->msg = 'Need Kanban list Id';
		return false;
	}

	// toDo vérifier le status du kanban aussi

	$kanbanListId = $data['kanban_list_id'];
	$kanbanList = _checkObjectByElement('scrumproject_scrumkanbanlist', $kanbanListId, $jsonResponse);
	/**
	 * @var ScrumKanbanList $kanbanList
	 */
	if(!$kanbanList){
		$jsonResponse->msg = 'Invalid Kanban list load';
		return false;
	}

	if(empty($user->rights->scrumproject->scrumcard->write)){
		$jsonResponse->msg = 'Not enough rights';
		return false;
	}

	if($kanbanList->delete($user) <= 0){
		$jsonResponse->msg = 'Error deleting scrum list : '.$kanbanList->errorsToString();
		return false;
	}

	$jsonResponse->result = 1;
	return true;
}


/**
 * @param JsonResponse $jsonResponse
 * @return bool|void
 */
function _actionRemoveCard($jsonResponse){
	global $user, $langs, $db;

	$jsonResponse->result = 0;

	$data = GETPOST("data", "array");

	// check kanban item data
	if(empty($data['card_id'])){
		$jsonResponse->msg = 'Need Kanban card Id';
		return false;
	}

	// toDo vérifier le status du kanban aussi

	$kanbanCardId = $data['card_id'];
	$kanbanCard = _checkObjectByElement('scrumproject_scrumcard', $kanbanCardId, $jsonResponse);
	/**
	 * @var ScrumCard $kanbanCard
	 */
	if(!$kanbanCard){
		$jsonResponse->msg = 'Invalid Kanban card load';
		return false;
	}

	if(empty($user->rights->scrumproject->scrumcard->write)){
		$jsonResponse->msg = 'Not enough rights';
		return false;
	}

	if($kanbanCard->delete($user) <= 0){
		$jsonResponse->msg = 'Error deleting scrum card : '.$kanbanCard->errorsToString();
		return false;
	}

	$jsonResponse->result = 1;
	return true;
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
		$jsonResponse->data = $scrumCard->getScrumKanBanItemObjectFormatted();
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
function _actionGetScrumCardData($jsonResponse){
	global $db;

	$data = GETPOST("data", "array");

	// check kanban list data
	if(empty($data['id'])){
		$jsonResponse->msg = 'Need scrumcard Id';
		return false;
	}

	$scrumCard = new ScrumCard($db);
	$res = $scrumCard->fetch($data['id']);
	if($res <= 0){
		$jsonResponse->msg = 'Scrumcard fetch error';
		return false;
	}

	$jsonResponse->result = 1;
	$jsonResponse->data = $scrumCard->getScrumKanBanItemObjectStd();
	return true;
}


/**
 * @param JsonResponse $jsonResponse
 * @return bool|void
 */
function _actionDropItemToList($jsonResponse){
	global $langs, $db;

	$data = GETPOST("data", "array");

	if(empty($data['card-id'])){
		$jsonResponse->msg = 'Need card Id';
		return false;
	}

	$scrumCard = new ScrumCard($db);
	if($scrumCard->fetch($data['card-id']) <= 0){
		$jsonResponse->msg = 'Invalid card';
		return false;
	}

	if(empty($data['target-list-id'])){
		$jsonResponse->msg = 'Need target list Id';
		return false;
	}
	$target_fk_scrumkanbanlist = $data['target-list-id'];

	// toDo vérifier le status du kanban aussi

	$kanbanList = _checkObjectByElement('scrumproject_scrumkanbanlist', $target_fk_scrumkanbanlist, $jsonResponse);
	if(!$kanbanList){
		return false;
	}

	if(!empty($data['before-card-id'])){
		$beforeScrumCard = new ScrumCard($db);
		$res = $beforeScrumCard->fetch($data['before-card-id']);
		if($res<=0){
			$jsonResponse->msg = 'Need target list Id';
			return false;
		}


		$newRank = $beforeScrumCard->fk_rank;

		$crumCardsAfter = $db->getRows(
			/* @Lang SQL */
			'SELECT rowid id, fk_rank '
			. ' FROM '.MAIN_DB_PREFIX.$scrumCard->table_element
			. ' WHERE fk_scrum_kanbanlist ='.intval($kanbanList->id)
			. ' AND fk_rank >= '.intval($beforeScrumCard->fk_rank)
			. ' ORDER BY fk_rank ASC'
		);

		if(!empty($crumCardsAfter)){
			$db->begin();
			$error = 0;
			$nextRank = intval($newRank);
			foreach ($crumCardsAfter as $item){
				$nextRank++;
				$sqlUpdate = /* @Lang SQL */
					'UPDATE '.MAIN_DB_PREFIX.$scrumCard->table_element
					. ' SET tms=NOW(), fk_rank = '.$nextRank
					. ' WHERE rowid = '.intval($item->id)
					. ';';

				$resUp = $db->query($sqlUpdate);
				if(!$resUp){
					$error++;
					break;
				}
			}

			if(!empty($error)){
				$db->rollback();
				$jsonResponse->result = 0;
				$jsonResponse->msg = $langs->trans('UpdateError') . ' : ' .$db->error();
				return false;
			}

			// Mise à jour de la card elle même
			$sqlUpdate = /* @Lang SQL */
				' UPDATE '.MAIN_DB_PREFIX.$scrumCard->table_element
				. ' SET  tms=NOW(), fk_rank = '.intval($newRank).', fk_scrum_kanbanlist = '.intval($kanbanList->id)
				. ' WHERE rowid = '.intval($scrumCard->id);

			if($db->query($sqlUpdate)){
				$db->commit();
				$jsonResponse->result = 1;
				return true;
			}
			else{
				$db->rollback();
				$jsonResponse->result = 0;
				$jsonResponse->msg = $langs->trans('UpdateError') . ' : ' .$db->error();
				return false;
			}
		}
		else{
			$jsonResponse->result = 0;
			$jsonResponse->msg = $langs->trans('Card position error');
			return false;
		}
	}
	else{
		$newRank = $kanbanList->getMaxRankOfKanBanListItems() + 1;

		// Mise à jour de la card elle même
		$sqlUpdate = /* @Lang SQL */
			' UPDATE '.MAIN_DB_PREFIX.$scrumCard->table_element
			. ' SET fk_rank = '.intval($newRank).', fk_scrum_kanbanlist = '.intval($kanbanList->id)
			. ' WHERE rowid = '.intval($scrumCard->id);

		if($db->query($sqlUpdate)){
			$jsonResponse->result = 1;
			return true;
		}
		else{
			$jsonResponse->result = 0;
			$jsonResponse->msg = $langs->trans('UpdateError') . ' : ' . $scrumCard->errorsToString();
			return false;
		}
	}
}


/**
 * @param JsonResponse $jsonResponse
 * @return bool|void
 */
function _actionChangeListOrder($jsonResponse){
	global  $langs, $db;

	$data = GETPOST("data", "array");

	// Get kanban
	if(empty($data['fk_kanban'])){
		$jsonResponse->msg = 'Need Kanban Id';
		return false;
	}

	$fk_kanban = $data['fk_kanban'];
	$kanban = _checkObjectByElement('scrumproject_scrumkanban', $fk_kanban, $jsonResponse);
	if(!$kanban){
		return false;
	}

	// Get list
	if(empty($data['list-id'])){
		$jsonResponse->msg = 'Need list Id';
		return false;
	}

	$kanbanListId = $data['list-id'];
	$kanbanList = _checkObjectByElement('scrumproject_scrumkanbanlist', $kanbanListId, $jsonResponse);
	if(!$kanbanList){
		return false;
	}

	/**
	 * @var ScrumKanbanList $kanbanList
	 */
	if($fk_kanban != $kanbanList->fk_scrum_kanban){
		$jsonResponse->msg = 'kanban scope error';
		return false;
	}

	$newRank = 0;
	$obj = $db->getRow('SELECT MAX(fk_rank) maxRank FROM '.MAIN_DB_PREFIX.$kanbanList->table_element . ' WHERE fk_scrum_kanban = '.intval($kanban->id));
	if($obj){
		$newRank = intval($obj->maxRank) + 1;
	}

	if(!empty($data['before-list-id'])) {
		$beforeKanbanList = _checkObjectByElement('scrumproject_scrumkanbanlist', $data['before-list-id'], $jsonResponse);
		if(!$beforeKanbanList){
			return false;
		}

		$newRank = $beforeKanbanList->fk_rank;
	}


	$getListsAfter = $db->getRows(
	/* @Lang SQL */
		'SELECT rowid id, fk_rank '
		. ' FROM '.MAIN_DB_PREFIX.$kanbanList->table_element
		. ' WHERE rowid != '.intval($kanbanList->id)
		. ' AND fk_rank >= '.intval($newRank)
		. ' AND fk_scrum_kanban = '.intval($fk_kanban)
		. ' ORDER BY fk_rank ASC'
	);

	if($getListsAfter===false) {
		$jsonResponse->result = 0;
		$jsonResponse->msg = $langs->trans('Card position query error').$db->error();
		return false;
	}

	$db->begin();
	$error = 0;

	if(!empty($getListsAfter)) {
		$nextRank = intval($newRank);
		foreach ($getListsAfter as $item) {
			$nextRank++;

			$sqlUpdate = /* @Lang SQL */
				'UPDATE ' . MAIN_DB_PREFIX . $kanbanList->table_element
				. ' SET tms=NOW(), fk_rank = ' . $nextRank
				. ' WHERE rowid = ' . intval($item->id);

			$resUp = $db->query($sqlUpdate);
			if (!$resUp) {
				$error++;
				break;
			}
		}

		if (!empty($error)) {
			$db->rollback();
			$jsonResponse->result = 0;
			$jsonResponse->msg = $langs->trans('UpdateError') . ' : ' . $db->error();
			return false;
		}
	}

	// Mise à jour de la liste elle même
	$sqlUpdate = /* @Lang SQL */
		' UPDATE '.MAIN_DB_PREFIX.$kanbanList->table_element
		. ' SET  tms=NOW(), fk_rank = '.intval($newRank)
		. ' WHERE rowid = '.intval($kanbanList->id).';';

	if($db->query($sqlUpdate)){
		$db->commit();
		$jsonResponse->result = 1;
		return true;
	}
	else{
		$db->rollback();
		$jsonResponse->result = 0;
		$jsonResponse->msg = $langs->trans('UpdateError') . ' : ' .$db->error();
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

	$object = scrumProjectGetObjectByElement($elementType, $id);
	if(!$object){
		$jsonResponse->msg = $elementType . ' : ' . $langs->trans('RequireValidExistingElement');
		return false;
	}

	return $object;
}


/**
 * @param JsonResponse $jsonResponse
 * @return bool|CommonObject
 */
function _actionSplitScrumCard($jsonResponse){
	global $langs, $db, $user;


	$data = GETPOST("data", "array");

	if(empty($data['id'])){
		$jsonResponse->msg = 'Need card Id';
		return false;
	}

	$scrumCard = new ScrumCard($db);
	if($scrumCard->fetch($data['id']) <= 0){
		$jsonResponse->msg =  $langs->trans('RequireValidExistingElement');
		return false;
	}

	$errors = 0;

	if(empty($data['form']) || empty($data['form']['new-item-qty-planned'])){
		$jsonResponse->msg =  $langs->trans('RequireValidSplitData');
		return false;
	}

	if(!is_array($data['form']['new-item-qty-planned'])){
		$data['form']['new-item-qty-planned'] = array(
			$data['form']['new-item-qty-planned']
		);
	}

	if(!is_array($data['form']['new-item-label'])){
		$data['form']['new-item-label'] = array(
			$data['form']['new-item-label']
		);
	}

	foreach ($data['form']['new-item-qty-planned'] as $key => $qty){
		$newCardLabel = '';
		if(!empty($data['form']['new-item-label'][$key])){
			$newCardLabel = $data['form']['new-item-label'][$key];
		}

		if(is_array($newCardLabel)){ $newCardLabel = '';}

		$res = $scrumCard->splitCard($qty, $newCardLabel, $user);
		if($res<=0){
			$jsonResponse->msg =  $scrumCard->errorsToString();
			$errors++;
		}
	}



	return $errors==0;
}


/**
 * @param JsonResponse $jsonResponse
 * @param int|bool     $userId
 * @param bool         $toggle if contact already prevent it remove it
 * @return bool|void
 */
function _actionAssignUserToCard($jsonResponse, $userId = false, $toggle = false){
	global  $user, $db, $conf;

	$data = GETPOST("data", "array");


	if($userId === false){
		$userId = $user->id;
		$user->fetch_optionals();
		$typeContact = $user->array_options['options_scrumproject_role'];
	}elseif(empty($userId)){
		$jsonResponse->msg = 'Need user Id';
		return false;
	}else{
		$contactUser = new User($db);
		if($contactUser->fetch($userId) <= 0){
			$jsonResponse->msg = 'Need valid user';
			return false;
		}

		$contactUser->fetch_optionals();
		$typeContact = $contactUser->array_options['options_scrumproject_role'];
	}

	if(empty($typeContact) && !empty($conf->global->SCRUMPROJECT_DEFAULT_KANBAN_CONTACT_CODE)){
		$typeContact = $conf->global->SCRUMPROJECT_DEFAULT_KANBAN_CONTACT_CODE;
	}


	// Get card id
	if(empty($data['card-id'])){
		$jsonResponse->msg = 'Need card Id';
		return false;
	}

	$cardId = $data['card-id'];
	$scrumCard = _checkObjectByElement('scrumproject_scrumcard', $cardId, $jsonResponse);
	if(!$scrumCard){
		return false;
	}

	$gCError = '';

	/**
	 * @var ScrumCard $scrumCard
	 */
	if($scrumCard->fk_element > 0){
		if(!$scrumCard->fetchElementObject()){
			$jsonResponse->msg = 'Error fectching element object';
			return false;
		}
		$typeContactId = $scrumCard::getInternalContactIdFromCode($typeContact, $scrumCard->elementObject);
		if(!$typeContactId){
			$jsonResponse->msg = 'Error contact type '.$typeContact.' not found for '.$scrumCard->elementObject->element;
			return false;
		}

		$jsonResponse->debug = $typeContactId;
		$result = $scrumCard->elementObject->add_contact($userId, $typeContactId,'internal');
		if($result<0){
			$jsonResponse->msg = 'Error adding contact : '.$scrumCard->elementObject->errorsToString();
			return false;
		}

		if($toggle && $result == 0){
			return _actionRemoveUserToCard($jsonResponse, $userId);
		}
	}
	else{
		$typeContactId = $scrumCard::getInternalContactIdFromCode($typeContact, $scrumCard);
		if(!$typeContactId){
			$jsonResponse->msg = 'Error contact type '.$typeContact.' not found for scrum card';
			return false;
		}
		$jsonResponse->debug = $typeContactId;
		$result = $scrumCard->add_contact($userId, $typeContactId,'internal');
		if($result<0){
			$jsonResponse->msg = 'Error adding contact : '.$scrumCard->errorsToString();
			return false;
		}

		if($toggle && $result == 0){
			return _actionRemoveUserToCard($jsonResponse, $userId);
		}
	}



	$jsonResponse->result = 1;
	$jsonResponse->data = $scrumCard->getScrumKanBanItemObjectFormatted();
	return true;
}



/**
 * @param JsonResponse $jsonResponse
 * @param int|bool         $userId
 * @return bool|void
 */
function _actionRemoveUserToCard($jsonResponse, $userId = false){
	global  $user, $db;

	$data = GETPOST("data", "array");


	if($userId === false){
		$userId = $user->id;
	}elseif(empty($userId)){
		$jsonResponse->msg = 'Need user Id';
		return false;
	}else{
		$contactUser = new User($db);
		if($contactUser->fetch($userId) <= 0){
			$jsonResponse->msg = 'Need valid user';
			return false;
		}
	}


	// Get card id
	if(empty($data['card-id'])){
		$jsonResponse->msg = 'Need card Id';
		return false;
	}

	$cardId = $data['card-id'];
	$scrumCard = _checkObjectByElement('scrumproject_scrumcard', $cardId, $jsonResponse);
	if(!$scrumCard){
		return false;
	}

	$gCError = '';

	/**
	 * @var ScrumCard $scrumCard
	 */
	if($scrumCard->fk_element > 0){
		if(!$scrumCard->fetchElementObject()){
			$jsonResponse->msg = 'Error fectching element object';
			return false;
		}

		$TContactUsersAffected = $scrumCard->elementObject->liste_contact(-1,'internal');
		if($TContactUsersAffected == -1){
			$jsonResponse->msg = 'Error removing contact : '.$scrumCard->elementObject->errorsToString();
			return false;
		}

		foreach ($TContactUsersAffected as $contactArray){
			if($contactArray['id'] != $userId){
				continue;
			}

			$result = $scrumCard->elementObject->delete_contact($contactArray['rowid']);
			if($result<0){
				$jsonResponse->msg = 'Error delecting contact : '.$scrumCard->errorsToString();
				return false;
			}
		}
	}
	else{
		$TContactUsersAffected = $scrumCard->liste_contact(-1,'internal');
		if($TContactUsersAffected == -1){
			$jsonResponse->msg = 'Error removing contact : '.$scrumCard->errorsToString();
			return false;
		}

		foreach ($TContactUsersAffected as $contactArray){

			if($contactArray['id'] != $userId){
				continue;
			}

			$result = $scrumCard->delete_contact($contactArray['rowid']);
			if($result<0){
				$jsonResponse->msg = 'Error delecting contact : '.$scrumCard->errorsToString();
				return false;
			}
		}
	}

	$jsonResponse->result = 1;
	$jsonResponse->data = $scrumCard->getScrumKanBanItemObjectFormatted();
	return true;
}
