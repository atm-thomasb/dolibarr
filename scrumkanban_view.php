<?php
/* Copyright (C) 2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2022 John Botella <john.botella@atm-consulting.fr>
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
 *   	\file       scrumkanban_card.php
 *		\ingroup    scrumproject
 *		\brief      Page to create/edit/view scrumkanban
 */

//if (! defined('NOREQUIREDB'))              define('NOREQUIREDB', '1');				// Do not create database handler $db
//if (! defined('NOREQUIREUSER'))            define('NOREQUIREUSER', '1');				// Do not load object $user
//if (! defined('NOREQUIRESOC'))             define('NOREQUIRESOC', '1');				// Do not load object $mysoc
//if (! defined('NOREQUIRETRAN'))            define('NOREQUIRETRAN', '1');				// Do not load object $langs
//if (! defined('NOSCANGETFORINJECTION'))    define('NOSCANGETFORINJECTION', '1');		// Do not check injection attack on GET parameters
//if (! defined('NOSCANPOSTFORINJECTION'))   define('NOSCANPOSTFORINJECTION', '1');		// Do not check injection attack on POST parameters
//if (! defined('NOCSRFCHECK'))              define('NOCSRFCHECK', '1');				// Do not check CSRF attack (test on referer + on token).
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
//if (! defined('NOSESSION'))     		     define('NOSESSION', '1');				    // Disable session

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

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
dol_include_once('/scrumproject/class/scrumkanban.class.php');
dol_include_once('/scrumproject/lib/scrumproject_scrumkanban.lib.php');

// Load translation files required by the page
$langs->loadLangs(array("scrumproject@scrumproject","scrumkanban@scrumproject", "other"));

// Get parameters
$id = GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$lineid   = GETPOST('lineid', 'int');

$action = GETPOST('action', 'aZ09');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel', 'aZ09');
$contextpage = GETPOST('contextpage', 'aZ') ? GETPOST('contextpage', 'aZ') : 'scrumkanbancard'; // To manage different context of search
$backtopage = GETPOST('backtopage', 'alpha');
$backtopageforcancel = GETPOST('backtopageforcancel', 'alpha');
$dol_openinpopup = GETPOST('dol_openinpopup', 'aZ09');

$permissiontoadd = $user->rights->scrumproject->scrumsprint->write;

// Initialize technical objects
$object = new ScrumKanban($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction = $conf->scrumproject->dir_output.'/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('scrumkanbancard', 'globalcard')); // Note that conf->hooks_modules contains array

// Load object
$accessForbiden = true;
if ($id>0) {
	$ret = $object->fetch($id);
	if ($ret > 0) {
		$object->fetch_thirdparty();
		$id = $object->id;
		$accessForbiden = false;
	} else {
		if (empty($object->error) && !count($object->errors)) {
			if ($ret < 0) {	// if $ret == 0, it means not found.
				setEventMessages('Fetch on object (type '.get_class($object).') return an error without filling $object->error nor $object->errors', null, 'errors');
			}
		} else {
			setEventMessages($object->error, $object->errors, 'errors');
		}
		$action = '';
	}
}


if($accessForbiden || !$permissiontoadd){
	accessForbidden();
}


// Fetch optionals attributes and labels
$extrafields->fetch_name_optionals_label($object->table_element);

$search_array_options = $extrafields->getOptionalsFromPost($object->table_element, '', 'search_');
// Load translation files required by the page
$langs->load("externalsite");
$head = '<link rel="icon" type="image/png" href="'.dol_buildpath('scrumproject/img/object_scrumkanban.png', 1).'" />';
$head.= '<meta name="viewport" content="width=device-width, initial-scale=1" />';
$arrayofjs = array(
	'scrumproject/vendors/jkanban/dist/jkanban.js'
);
$arrayofcss = array(
	'scrumproject/css/kanban.css',
	'scrumproject/vendors/jkanban/dist/jkanban.css'
);
top_htmlhead($head,  $object->ref . ' - ' . $object->label, 0, 0, $arrayofjs, $arrayofcss);

$confToJs = array(
	'MAIN_MAX_DECIMALS_TOT' => $conf->global->MAIN_MAX_DECIMALS_TOT,
	'MAIN_MAX_DECIMALS_UNIT' => $conf->global->MAIN_MAX_DECIMALS_UNIT,
	'interface_kanban_url' => dol_buildpath('scrumproject/scripts/interface-kanban.php',1),
	'interface_liveupdate_url' => dol_buildpath('scrumproject/scripts/interface-liveupdate.php',1),
	'js_url' => dol_buildpath('scrumproject/js/scrumkanban.js',1),
	'fk_kanban' => $object->id
);

$jsLangs = array(
	'NewList' => $langs->trans('NewList'),
	'NewCard' => $langs->trans('NewCard')
);

?>
<body id="mainbody" class="scrumkanban-page">
<header class="kanban-header" role="banner">
	<nav class="navigation" role="navigation">
		<?php
		$title = $object->getNomUrl(0) . ' <span class="kanban-title__label">'.$object->label.'</span>';
		$newcardbutton = '';
		$newcardbutton .= dolGetButtonTitle($langs->trans('New'), '', 'fa fa-plus-circle', '', 'addkanbancol', $permissiontoadd);


		print_barre_liste($title, '', $_SERVER["PHP_SELF"], '', '', '', '', '', '', 'object_'.$object->picto, 0, $newcardbutton, '', 0, 0, 0, 1);

		?>
	</nav>
</header>
<div id="scrum-kanban"></div>
<script>


	jQuery(function ($) {
		let config = <?php print json_encode($confToJs) ?>;

		// Chargement de la librairie js
		let advps_script_to_load = document.createElement('script')
		advps_script_to_load.setAttribute('src', config.js_url);
		advps_script_to_load.setAttribute('id', 'advance-product-search-script-load');
		document.body.appendChild(advps_script_to_load);
		// now wait for it to load...
		advps_script_to_load.onload = () => {
			// script has loaded, you can now use it safely
			// Apply conf to AdvancedProductSearch object
			scrumKanban.init(config, <?php print json_encode($jsLangs) ?>);
		};
	});



</script>

</body>

<?php
$db->close();
