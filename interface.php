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
	_getAutocompletionForSprint($jsonResponse, GETPOST('term'));
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
 * @return array|string  Returns a sequential array of objects with 2 props: "id" (ID of the contract) and "text" (concatenated client / leaser refs)
 *                       If the SQL query fails, return the SQL itself.
 */
function _getAutocompletionForSprint(JsonResponse $jsonResponse, string $search, int $minDateEnd = 0): bool {
	/** @var DoliDB $db */
	global $db, $conf, $langs;

	if(!class_exists('ScrumSprint')){
		require_once __DIR__ . '/class/scrumsprint.class.php';
	}

	$sprintStatic = new ScrumSprint($db);

	$sql = /** @lang SQL */
		'SELECT s.rowid     AS "id",'
		. '     s.label AS "text", '
		. '     g.nom AS "GroupName" '
		. ' FROM '.MAIN_DB_PREFIX.$sprintStatic->table_element.' s'
		. ' LEFT JOIN '.MAIN_DB_PREFIX.'usergroup g ON (s.fk_team = g.rowid)'
		. ' WHERE 1 = 1 ';

	if($minDateEnd > 0){
		$sql.= ' AND s.date_start >= '. $minDateEnd.' ';
	}

	$sql.= natural_search('s.label', $search);
	$sql.= ' ORDER BY s.date_start ASC, s.label ASC;';
	$TRow = $db->getRows($sql);
	if (!$TRow) {
		$jsonResponse->data = ['errors' => $db->lasterror(), 'sql' => $db->lastqueryerror()];
		return false;
	}
	foreach ($TRow as $obj) {

		$sprint = new ScrumSprint($db);
		$sprint->fetch($obj->id);

		$item = new stdClass();
		$item->id =  $sprint->id;
		$item->text = $sprint->label.' - '.$obj->GroupName.' - '.dol_print_date($sprint->date_start).' '.$langs->trans('to').' '.dol_print_date($sprint->date_end);

		$TRow[] = $item;
	}
	$jsonResponse->data = ['rows' => $TRow];
	return true;
}
