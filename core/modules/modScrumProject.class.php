<?php
/* Copyright (C) 2004-2018  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2018-2019  Nicolas ZABOURI         <info@inovea-conseil.com>
 * Copyright (C) 2019-2020	Frédéric France         <frederic.france@netlogic.fr>
 * Copyright (C) 2020		Maxime Kohlhaas			<maxime@atm-consulting.fr>
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
 * 	\defgroup   scrumproject     Module ScrumProject
 *  \brief      ScrumProject module descriptor.
 *
 *  \file       htdocs/scrumproject/core/modules/modScrumProject.class.php
 *  \ingroup    scrumproject
 *  \brief      Description and activation file for module ScrumProject
 */
include_once DOL_DOCUMENT_ROOT.'/core/modules/DolibarrModules.class.php';

/**
 *  Description and activation class for module ScrumProject
 */
class modScrumProject extends DolibarrModules
{
	/**
	 * Constructor. Define names, constants, directories, boxes, permissions
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		global $langs, $conf;
		$this->db = $db;

		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 104215; // TODO Go on page https://wiki.dolibarr.org/index.php/List_of_modules_id to reserve an id number for your module
		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'scrumproject';
		// Family can be 'base' (core modules),'crm','financial','hr','projects','products','ecm','technic' (transverse modules),'interface' (link with external tools),'other','...'
		// It is used to group modules by family in module setup page
		$this->family = "projects";
		// Module position in the family on 2 digits ('01', '10', '20', ...)
		$this->module_position = '90';
		// Gives the possibility for the module, to provide his own family info and position of this family (Overwrite $this->family and $this->module_position. Avoid this)
		//$this->familyinfo = array('myownfamily' => array('position' => '01', 'label' => $langs->trans("MyOwnFamily")));
		// Module label (no space allowed), used if translation string 'ModuleScrumProjectName' not found (ScrumProject is name of module).
		$this->name = preg_replace('/^mod/i', '', get_class($this));
		// Module description, used if translation string 'ModuleScrumProjectDesc' not found (ScrumProject is name of module).
		$this->description = "ScrumProjectDescription";
		// Used only if file README.md and README-LL.md not found.
		$this->descriptionlong = "ScrumProject description (Long)";
		$this->editor_name = 'ATM Consulting';
		$this->editor_url = 'www.atm-consulting.fr';
		// Possible values for version are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated' or a version string like 'x.y.z'
		$this->version = 'development';
		// Url to the file with your last numberversion of this module
		//$this->url_last_version = 'http://www.example.com/versionmodule.txt';

		// Key used in llx_const table to save module status enabled/disabled (where SCRUMPROJECT is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		$this->picto = 'scrumproject@scrumproject';
		// Define some features supported by module (triggers, login, substitutions, menus, css, etc...)
		$this->module_parts = array(
			// Set this to 1 if module has its own trigger directory (core/triggers)
			'triggers' => 0,
			// Set this to 1 if module has its own login method file (core/login)
			'login' => 0,
			// Set this to 1 if module has its own substitution function file (core/substitutions)
			'substitutions' => 0,
			// Set this to 1 if module has its own menus handler directory (core/menus)
			'menus' => 0,
			// Set this to 1 if module overwrite template dir (core/tpl)
			'tpl' => 0,
			// Set this to 1 if module has its own barcode directory (core/modules/barcode)
			'barcode' => 0,
			// Set this to 1 if module has its own models directory (core/modules/xxx)
			'models' => 1,
			// Set this to 1 if module has its own printing directory (core/modules/printing)
			'printing' => 0,
			// Set this to 1 if module has its own theme directory (theme)
			'theme' => 0,
			// Set this to relative path of css file if module has its own css file
			'css' => array(
				//    '/scrumproject/css/scrumproject.css.php',
			),
			// Set this to relative path of js file if module must load a js on all pages
			'js' => array(
				//   '/scrumproject/js/scrumproject.js.php',
			),
			// Set here all hooks context managed by module. To find available hook context, make a "grep -r '>initHooks(' *" on source code. You can also set hook context to 'all'
			'hooks' => array(
				   'data' => array(
				       'projecttaskcard',
				   ),
			),
			// Set this to 1 if features of module are opened to external users
			'moduleforexternal' => 0,
			'contactelement' => array('scrumsprint' => "ScrumSprint")
		);
		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/scrumproject/temp","/scrumproject/subdir");
		$this->dirs = array("/scrumproject/temp");
		// Config pages. Put here list of php page, stored into scrumproject/admin directory, to use to setup module.
		$this->config_page_url = array("setup.php@scrumproject");
		// Dependencies
		// A condition to hide module
		$this->hidden = false;
		// List of module class names as string that must be enabled if this module is enabled. Example: array('always1'=>'modModuleToEnable1','always2'=>'modModuleToEnable2', 'FR1'=>'modModuleToEnableFR'...)
		$this->depends = array('always1'=>'modProjet');
		$this->requiredby = array(); // List of module class names as string to disable if this one is disabled. Example: array('modModuleToDisable1', ...)
		$this->conflictwith = array(); // List of module class names as string this module is in conflict with. Example: array('modModuleToDisable1', ...)
		$this->langfiles = array("scrumproject@scrumproject");
		$this->phpmin = array(5, 5); // Minimum version of PHP required by module
		$this->need_dolibarr_version = array(12, -3); // Minimum version of Dolibarr required by module
		$this->warnings_activation = array(); // Warning to show when we activate module. array('always'='text') or array('FR'='textfr','ES'='textes'...)
		$this->warnings_activation_ext = array(); // Warning to show when we activate an external module. array('always'='text') or array('FR'='textfr','ES'='textes'...)
		//$this->automatic_activation = array('FR'=>'ScrumProjectWasAutomaticallyActivatedBecauseOfYourCountryChoice');
		//$this->always_enabled = true;								// If true, can't be disabled

		// Constants
		// List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
		// Example: $this->const=array(1 => array('SCRUMPROJECT_MYNEWCONST1', 'chaine', 'myvalue', 'This is a constant to add', 1),
		//                             2 => array('SCRUMPROJECT_MYNEWCONST2', 'chaine', 'myvalue', 'This is another constant to add', 0, 'current', 1)
		// );
		$this->const = array();

		// Some keys to add into the overwriting translation tables
		/*$this->overwrite_translation = array(
			'en_US:ParentCompany'=>'Parent company or reseller',
			'fr_FR:ParentCompany'=>'Maison mère ou revendeur'
		)*/

		if (!isset($conf->scrumproject) || !isset($conf->scrumproject->enabled)) {
			$conf->scrumproject = new stdClass();
			$conf->scrumproject->enabled = 0;
		}

		// Array to add new pages in new tabs
		$this->tabs = array();
		$this->tabs[] = array('data'=>'scrumsprint@scrumproject:+scrumcardlist:ScrumCardTab:scrumprject@scrumproject:$user->rights->scrumproject->scrumcard->read:/scrumproject/scrumcard_list.php?scrumsprintid=__ID__');  					// To add a new tab identified by code tabname1
		// Example:
		// $this->tabs[] = array('data'=>'objecttype:+tabname1:Title1:mylangfile@scrumproject:$user->rights->scrumproject->read:/scrumproject/mynewtab1.php?id=__ID__');  					// To add a new tab identified by code tabname1
		// $this->tabs[] = array('data'=>'objecttype:+tabname2:SUBSTITUTION_Title2:mylangfile@scrumproject:$user->rights->othermodule->read:/scrumproject/mynewtab2.php?id=__ID__',  	// To add another new tab identified by code tabname2. Label will be result of calling all substitution functions on 'Title2' key.
		// $this->tabs[] = array('data'=>'objecttype:-tabname:NU:conditiontoremove');                                                     										// To remove an existing tab identified by code tabname
		//
		// Where objecttype can be
		// 'categories_x'	  to add a tab in category view (replace 'x' by type of category (0=product, 1=supplier, 2=customer, 3=member)
		// 'contact'          to add a tab in contact view
		// 'contract'         to add a tab in contract view
		// 'group'            to add a tab in group view
		// 'intervention'     to add a tab in intervention view
		// 'invoice'          to add a tab in customer invoice view
		// 'invoice_supplier' to add a tab in supplier invoice view
		// 'member'           to add a tab in fundation member view
		// 'opensurveypoll'	  to add a tab in opensurvey poll view
		// 'order'            to add a tab in customer order view
		// 'order_supplier'   to add a tab in supplier order view
		// 'payment'		  to add a tab in payment view
		// 'payment_supplier' to add a tab in supplier payment view
		// 'product'          to add a tab in product view
		// 'propal'           to add a tab in propal view
		// 'project'          to add a tab in project view
		// 'stock'            to add a tab in stock view
		// 'thirdparty'       to add a tab in third party view
		// 'user'             to add a tab in user view

		// Dictionaries
		$this->dictionaries=array(
			'langs'=>'scrumproject@scrumproject',
			// List of tables we want to see into dictonnary editor
			'tabname'=>array(MAIN_DB_PREFIX."c_scrum_stage"),
			// Label of tables
			'tablib'=>array("ScrumCardStage"),
			// Request to select fields
			'tabsql'=>array('SELECT f.rowid, f.code, f.position, f.label, f.picto, f.active FROM '.MAIN_DB_PREFIX.'c_scrum_stage as f'),
			// Sort order
			'tabsqlsort'=>array("position ASC"),
			// List of fields (result of select to show dictionary)
			'tabfield'=>array("code,label,picto,position"),
			// List of fields (list of fields to edit a record)
			'tabfieldvalue'=>array("code,label,picto,position"),
			// List of fields (list of fields for insert)
			'tabfieldinsert'=>array("code,label,picto,position"),
			// Name of columns with primary key (try to always name it 'rowid')
			'tabrowid'=>array("rowid"),
			// Condition to show each dictionary
			'tabcond'=>array($conf->scrumproject->enabled)
		);


		// Boxes/Widgets
		// Add here list of php file(s) stored in scrumproject/core/boxes that contains a class to show a widget.
		$this->boxes = array(
			//  0 => array(
			//      'file' => 'scrumprojectwidget1.php@scrumproject',
			//      'note' => 'Widget provided by ScrumProject',
			//      'enabledbydefaulton' => 'Home',
			//  ),
			//  ...
		);

		// Cronjobs (List of cron jobs entries to add when module is enabled)
		// unit_frequency must be 60 for minute, 3600 for hour, 86400 for day, 604800 for week
		$this->cronjobs = array(
			//  0 => array(
			//      'label' => 'MyJob label',
			//      'jobtype' => 'method',
			//      'class' => '/scrumproject/class/scrumsprint.class.php',
			//      'objectname' => 'ScrumSprint',
			//      'method' => 'doScheduledJob',
			//      'parameters' => '',
			//      'comment' => 'Comment',
			//      'frequency' => 2,
			//      'unitfrequency' => 3600,
			//      'status' => 0,
			//      'test' => '$conf->scrumproject->enabled',
			//      'priority' => 50,
			//  ),
		);
		// Example: $this->cronjobs=array(
		//    0=>array('label'=>'My label', 'jobtype'=>'method', 'class'=>'/dir/class/file.class.php', 'objectname'=>'MyClass', 'method'=>'myMethod', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>2, 'unitfrequency'=>3600, 'status'=>0, 'test'=>'$conf->scrumproject->enabled', 'priority'=>50),
		//    1=>array('label'=>'My label', 'jobtype'=>'command', 'command'=>'', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>1, 'unitfrequency'=>3600*24, 'status'=>0, 'test'=>'$conf->scrumproject->enabled', 'priority'=>50)
		// );

		// Permissions provided by this module
		$this->rights = array();
		$r = 0;
		// Add here entries to declare new permissions
		/* BEGIN MODULEBUILDER PERMISSIONS */
		$this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
		$this->rights[$r][1] = 'Read scrum sprints'; // Permission label
		$this->rights[$r][4] = 'scrumsprint'; // In php code, permission will be checked by test if ($user->rights->scrumproject->level1->level2)
		$this->rights[$r][5] = 'read'; // In php code, permission will be checked by test if ($user->rights->scrumproject->level1->level2)
		$r++;
		$this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
		$this->rights[$r][1] = 'Create/Update scrum sprints'; // Permission label
		$this->rights[$r][4] = 'scrumsprint'; // In php code, permission will be checked by test if ($user->rights->scrumproject->level1->level2)
		$this->rights[$r][5] = 'write'; // In php code, permission will be checked by test if ($user->rights->scrumproject->level1->level2)
		$r++;
		$this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
		$this->rights[$r][1] = 'Delete scrum sprints'; // Permission label
		$this->rights[$r][4] = 'scrumsprint'; // In php code, permission will be checked by test if ($user->rights->scrumproject->level1->level2)
		$this->rights[$r][5] = 'delete'; // In php code, permission will be checked by test if ($user->rights->scrumproject->level1->level2)
		$r++;
		$this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
		$this->rights[$r][1] = 'Read scrum cards'; // Permission label
		$this->rights[$r][4] = 'scrumcard'; // In php code, permission will be checked by test if ($user->rights->scrumproject->level1->level2)
		$this->rights[$r][5] = 'read'; // In php code, permission will be checked by test if ($user->rights->scrumproject->level1->level2)
		$r++;
		$this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
		$this->rights[$r][1] = 'Create/Update scrum cards'; // Permission label
		$this->rights[$r][4] = 'scrumcard'; // In php code, permission will be checked by test if ($user->rights->scrumproject->level1->level2)
		$this->rights[$r][5] = 'write'; // In php code, permission will be checked by test if ($user->rights->scrumproject->level1->level2)
		$r++;
		$this->rights[$r][0] = $this->numero + $r; // Permission id (must not be already used)
		$this->rights[$r][1] = 'Delete scrum cards'; // Permission label
		$this->rights[$r][4] = 'scrumcard'; // In php code, permission will be checked by test if ($user->rights->scrumproject->level1->level2)
		$this->rights[$r][5] = 'delete'; // In php code, permission will be checked by test if ($user->rights->scrumproject->level1->level2)
		$r++;
		/* END MODULEBUILDER PERMISSIONS */

		// Main menu entries to add
		$this->menu = array();
		$r = 0;
		// Add here entries to declare new menus
		/* BEGIN MODULEBUILDER TOPMENU */
		/*$this->menu[$r++] = array(
			'fk_menu'=>'', // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'top', // This is a Top menu entry
			'titre'=>'ModuleScrumProjectName',
			'mainmenu'=>'scrumproject',
			'leftmenu'=>'',
			'url'=>'/scrumproject/scrumprojectindex.php',
			'langs'=>'scrumproject@scrumproject', // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000 + $r,
			'enabled'=>'$conf->scrumproject->enabled', // Define condition to show or hide menu entry. Use '$conf->scrumproject->enabled' if entry must be visible if module is enabled.
			'perms'=>'1', // Use 'perms'=>'$user->rights->scrumproject->scrumsprint->read' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2, // 0=Menu for internal users, 1=external users, 2=both
		);*/
		/* END MODULEBUILDER TOPMENU */
		/* BEGIN MODULEBUILDER LEFTMENU SCRUMSPRINT
		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=scrumproject',      // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',                          // This is a Top menu entry
			'titre'=>'ScrumSprint',
			'mainmenu'=>'scrumproject',
			'leftmenu'=>'scrumsprint',
			'url'=>'/scrumproject/scrumprojectindex.php',
			'langs'=>'scrumproject@scrumproject',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000+$r,
			'enabled'=>'$conf->scrumproject->enabled',  // Define condition to show or hide menu entry. Use '$conf->scrumproject->enabled' if entry must be visible if module is enabled.
			'perms'=>'$user->rights->scrumproject->scrumsprint->read',			                // Use 'perms'=>'$user->rights->scrumproject->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2,				                // 0=Menu for internal users, 1=external users, 2=both
		);
		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=scrumproject,fk_leftmenu=scrumsprint',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',			                // This is a Left menu entry
			'titre'=>'List_ScrumSprint',
			'mainmenu'=>'scrumproject',
			'leftmenu'=>'scrumproject_scrumsprint_list',
			'url'=>'/scrumproject/scrumsprint_list.php',
			'langs'=>'scrumproject@scrumproject',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000+$r,
			'enabled'=>'$conf->scrumproject->enabled',  // Define condition to show or hide menu entry. Use '$conf->scrumproject->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'perms'=>'$user->rights->scrumproject->scrumsprint->read',			                // Use 'perms'=>'$user->rights->scrumproject->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2,				                // 0=Menu for internal users, 1=external users, 2=both
		);
		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=scrumproject,fk_leftmenu=scrumsprint',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',			                // This is a Left menu entry
			'titre'=>'New_ScrumSprint',
			'mainmenu'=>'scrumproject',
			'leftmenu'=>'scrumproject_scrumsprint_new',
			'url'=>'/scrumproject/scrumsprint_card.php?action=create',
			'langs'=>'scrumproject@scrumproject',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1000+$r,
			'enabled'=>'$conf->scrumproject->enabled',  // Define condition to show or hide menu entry. Use '$conf->scrumproject->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'perms'=>'$user->rights->scrumproject->scrumsprint->write',			                // Use 'perms'=>'$user->rights->scrumproject->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2,				                // 0=Menu for internal users, 1=external users, 2=both
		);
		*/

        $this->menu[$r++]=array(
            // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'fk_menu'=>'fk_mainmenu=project',
            // This is a Left menu entry
            'type'=>'left',
            'titre'=>'ScrumSprint',
            'mainmenu'=>'project',
            'leftmenu'=>'scrumproject_scrumsprint',
            'url'=>'/scrumproject/scrumsprint_list.php',
            // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'langs'=>'scrumproject@scrumproject',
            'position'=>1100+$r,
            // Define condition to show or hide menu entry. Use '$conf->scrumproject->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'enabled'=>'$conf->scrumproject->enabled',
            // Use 'perms'=>'$user->rights->scrumproject->level1->level2' if you want your menu with a permission rules
            'perms'=>'$user->rights->scrumproject->scrumsprint->read',
            'target'=>'',
            // 0=Menu for internal users, 1=external users, 2=both
            'user'=>2,
        );
        $this->menu[$r++]=array(
            // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
            'fk_menu'=>'fk_mainmenu=project,fk_leftmenu=scrumproject_scrumsprint',
            // This is a Left menu entry
            'type'=>'left',
            'titre'=>'NewScrumSprint',
            'mainmenu'=>'project',
            'leftmenu'=>'scrumproject_scrumsprintnew',
            'url'=>'/scrumproject/scrumsprint_card.php?action=create',
            // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
            'langs'=>'scrumproject@scrumproject',
            'position'=>1100+$r,
            // Define condition to show or hide menu entry. Use '$conf->scrumproject->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
            'enabled'=>'$conf->scrumproject->enabled',
            // Use 'perms'=>'$user->rights->scrumproject->level1->level2' if you want your menu with a permission rules
            'perms'=>'$user->rights->scrumproject->scrumsprint->write',
            'target'=>'',
            // 0=Menu for internal users, 1=external users, 2=both
            'user'=>2
        );
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=project,fk_leftmenu=scrumproject_scrumsprint',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'List',
			'mainmenu'=>'project',
			'leftmenu'=>'scrumproject_scrumsprintlist',
			'url'=>'/scrumproject/scrumsprint_list.php',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'scrumproject@scrumproject',
			'position'=>1100+$r,
			// Define condition to show or hide menu entry. Use '$conf->scrumproject->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->scrumproject->enabled',
			// Use 'perms'=>'$user->rights->scrumproject->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->scrumproject->scrumsprint->read',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>2,
		);
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=project,fk_leftmenu=scrumproject_scrumsprintlist',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'StatusScrumSprintDraft',
			'mainmenu'=>'project',
			'leftmenu'=>'scrumproject_scrumsprintlist0',
			'url'=>'/scrumproject/scrumsprint_list.php?search_status=0',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'scrumproject@scrumproject',
			'position'=>1100+$r,
			// Define condition to show or hide menu entry. Use '$conf->scrumproject->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->scrumproject->enabled',
			// Use 'perms'=>'$user->rights->scrumproject->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->scrumproject->scrumsprint->read',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>2,
		);
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=project,fk_leftmenu=scrumproject_scrumsprintlist',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'StatusScrumSprintValid',
			'mainmenu'=>'project',
			'leftmenu'=>'scrumproject_scrumsprintlist1',
			'url'=>'/scrumproject/scrumsprint_list.php?search_status=1',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'scrumproject@scrumproject',
			'position'=>1100+$r,
			// Define condition to show or hide menu entry. Use '$conf->scrumproject->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->scrumproject->enabled',
			// Use 'perms'=>'$user->rights->scrumproject->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->scrumproject->scrumsprint->read',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>2,
		);
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=project,fk_leftmenu=scrumproject_scrumsprintlist',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'StatusScrumSprintPending',
			'mainmenu'=>'project',
			'leftmenu'=>'scrumproject_scrumsprintlist2',
			'url'=>'/scrumproject/scrumsprint_list.php?search_status=2',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'scrumproject@scrumproject',
			'position'=>1100+$r,
			// Define condition to show or hide menu entry. Use '$conf->scrumproject->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->scrumproject->enabled',
			// Use 'perms'=>'$user->rights->scrumproject->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->scrumproject->scrumsprint->read',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>2,
		);
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=project,fk_leftmenu=scrumproject_scrumsprintlist',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'StatusScrumSprintDone',
			'mainmenu'=>'project',
			'leftmenu'=>'scrumproject_scrumsprintlist3',
			'url'=>'/scrumproject/scrumsprint_list.php?search_status=3',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'scrumproject@scrumproject',
			'position'=>1100+$r,
			// Define condition to show or hide menu entry. Use '$conf->scrumproject->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->scrumproject->enabled',
			// Use 'perms'=>'$user->rights->scrumproject->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->scrumproject->scrumsprint->read',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>2,
		);

		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=project',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'ScrumCard',
			'mainmenu'=>'project',
			'leftmenu'=>'scrumproject_scrumcard',
			'url'=>'/scrumproject/scrumcard_list.php',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'scrumproject@scrumproject',
			'position'=>1100+$r,
			// Define condition to show or hide menu entry. Use '$conf->scrumproject->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->scrumproject->enabled',
			// Use 'perms'=>'$user->rights->scrumproject->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->scrumproject->scrumcard->read',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>2,
		);
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=project,fk_leftmenu=scrumproject_scrumcard',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'NewScrumCard',
			'mainmenu'=>'project',
			'leftmenu'=>'scrumproject_scrumcardnew',
			'url'=>'/scrumproject/scrumcard_card.php?action=create',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'scrumproject@scrumproject',
			'position'=>1100+$r,
			// Define condition to show or hide menu entry. Use '$conf->scrumproject->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->scrumproject->enabled',
			// Use 'perms'=>'$user->rights->scrumproject->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->scrumproject->scrumcard->write',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>2
		);
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=project,fk_leftmenu=scrumproject_scrumcard',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'List',
			'mainmenu'=>'project',
			'leftmenu'=>'scrumproject_scrumcardlist',
			'url'=>'/scrumproject/scrumcard_list.php',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'scrumproject@scrumproject',
			'position'=>1100+$r,
			// Define condition to show or hide menu entry. Use '$conf->scrumproject->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->scrumproject->enabled',
			// Use 'perms'=>'$user->rights->scrumproject->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->scrumproject->scrumcard->read',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>2,
		);
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=project,fk_leftmenu=scrumproject_scrumcardlist',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'StatusScrumCardDraft',
			'mainmenu'=>'project',
			'leftmenu'=>'scrumproject_scrumcardlist0',
			'url'=>'/scrumproject/scrumcard_list.php?search_status=0',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'scrumproject@scrumproject',
			'position'=>1100+$r,
			// Define condition to show or hide menu entry. Use '$conf->scrumproject->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->scrumproject->enabled',
			// Use 'perms'=>'$user->rights->scrumproject->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->scrumproject->scrumcard->read',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>2,
		);
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=project,fk_leftmenu=scrumproject_scrumcardlist',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'StatusScrumCardReady',
			'mainmenu'=>'project',
			'leftmenu'=>'scrumproject_scrumcardlist1',
			'url'=>'/scrumproject/scrumcard_list.php?search_status=1',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'scrumproject@scrumproject',
			'position'=>1100+$r,
			// Define condition to show or hide menu entry. Use '$conf->scrumproject->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->scrumproject->enabled',
			// Use 'perms'=>'$user->rights->scrumproject->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->scrumproject->scrumcard->read',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>2,
		);
		$this->menu[$r++]=array(
			// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'fk_menu'=>'fk_mainmenu=project,fk_leftmenu=scrumproject_scrumcardlist',
			// This is a Left menu entry
			'type'=>'left',
			'titre'=>'StatusScrumCardDone',
			'mainmenu'=>'project',
			'leftmenu'=>'scrumproject_scrumcardlist2',
			'url'=>'/scrumproject/scrumcard_list.php?search_status=2',
			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'langs'=>'scrumproject@scrumproject',
			'position'=>1100+$r,
			// Define condition to show or hide menu entry. Use '$conf->scrumproject->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'enabled'=>'$conf->scrumproject->enabled',
			// Use 'perms'=>'$user->rights->scrumproject->level1->level2' if you want your menu with a permission rules
			'perms'=>'$user->rights->scrumproject->scrumcard->read',
			'target'=>'',
			// 0=Menu for internal users, 1=external users, 2=both
			'user'=>2,
		);

		/* END MODULEBUILDER LEFTMENU SCRUMSPRINT */
		// Exports profiles provided by this module
		$r = 1;
		/* BEGIN MODULEBUILDER EXPORT SCRUMSPRINT */
		/*
		$langs->load("scrumproject@scrumproject");
		$this->export_code[$r]=$this->rights_class.'_'.$r;
		$this->export_label[$r]='ScrumSprintLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
		$this->export_icon[$r]='scrumsprint@scrumproject';
		// Define $this->export_fields_array, $this->export_TypeFields_array and $this->export_entities_array
		$keyforclass = 'ScrumSprint'; $keyforclassfile='/scrumproject/class/scrumsprint.class.php'; $keyforelement='scrumsprint@scrumproject';
		include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		//$this->export_fields_array[$r]['t.fieldtoadd']='FieldToAdd'; $this->export_TypeFields_array[$r]['t.fieldtoadd']='Text';
		//unset($this->export_fields_array[$r]['t.fieldtoremove']);
		//$keyforclass = 'ScrumSprintLine'; $keyforclassfile='/scrumproject/class/scrumsprint.class.php'; $keyforelement='scrumsprintline@scrumproject'; $keyforalias='tl';
		//include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		$keyforselect='scrumsprint'; $keyforaliasextra='extra'; $keyforelement='scrumsprint@scrumproject';
		include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		//$keyforselect='scrumsprintline'; $keyforaliasextra='extraline'; $keyforelement='scrumsprintline@scrumproject';
		//include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		//$this->export_dependencies_array[$r] = array('scrumsprintline'=>array('tl.rowid','tl.ref')); // To force to activate one or several fields if we select some fields that need same (like to select a unique key if we ask a field of a child to avoid the DISTINCT to discard them, or for computed field than need several other fields)
		//$this->export_special_array[$r] = array('t.field'=>'...');
		//$this->export_examplevalues_array[$r] = array('t.field'=>'Example');
		//$this->export_help_array[$r] = array('t.field'=>'FieldDescHelp');
		$this->export_sql_start[$r]='SELECT DISTINCT ';
		$this->export_sql_end[$r]  =' FROM '.MAIN_DB_PREFIX.'scrumsprint as t';
		//$this->export_sql_end[$r]  =' LEFT JOIN '.MAIN_DB_PREFIX.'scrumsprint_line as tl ON tl.fk_scrumsprint = t.rowid';
		$this->export_sql_end[$r] .=' WHERE 1 = 1';
		$this->export_sql_end[$r] .=' AND t.entity IN ('.getEntity('scrumsprint').')';
		$r++; */
		/* END MODULEBUILDER EXPORT SCRUMSPRINT */

		// Imports profiles provided by this module
		$r = 1;
		/* BEGIN MODULEBUILDER IMPORT SCRUMSPRINT */
		/*
		 $langs->load("scrumproject@scrumproject");
		 $this->export_code[$r]=$this->rights_class.'_'.$r;
		 $this->export_label[$r]='ScrumSprintLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
		 $this->export_icon[$r]='scrumsprint@scrumproject';
		 $keyforclass = 'ScrumSprint'; $keyforclassfile='/scrumproject/class/scrumsprint.class.php'; $keyforelement='scrumsprint@scrumproject';
		 include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		 $keyforselect='scrumsprint'; $keyforaliasextra='extra'; $keyforelement='scrumsprint@scrumproject';
		 include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		 //$this->export_dependencies_array[$r]=array('mysubobject'=>'ts.rowid', 't.myfield'=>array('t.myfield2','t.myfield3')); // To force to activate one or several fields if we select some fields that need same (like to select a unique key if we ask a field of a child to avoid the DISTINCT to discard them, or for computed field than need several other fields)
		 $this->export_sql_start[$r]='SELECT DISTINCT ';
		 $this->export_sql_end[$r]  =' FROM '.MAIN_DB_PREFIX.'scrumsprint as t';
		 $this->export_sql_end[$r] .=' WHERE 1 = 1';
		 $this->export_sql_end[$r] .=' AND t.entity IN ('.getEntity('scrumsprint').')';
		 $r++; */
		/* END MODULEBUILDER IMPORT SCRUMSPRINT */
	}

	/**
	 *  Function called when module is enabled.
	 *  The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *  It also creates data directories
	 *
	 *  @param      string  $options    Options when enabling module ('', 'noboxes')
	 *  @return     int             	1 if OK, 0 if KO
	 */
	public function init($options = '')
	{
		global $conf, $langs;

		$result = $this->_load_tables('/scrumproject/sql/');
		if ($result < 0) return -1; // Do not activate module if error 'not allowed' returned when loading module SQL queries (the _load_table run sql with run_sql with the error allowed parameter set to 'default')

		// Create extrafields during init
		include_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
		$extrafields = new ExtraFields($this->db);
		$result1=$extrafields->addExtraField('scrumproject_velocity', "ScrumProjectUserVelocity", 'double', 1000,  '24,8', 'user',   0, 0, '', '', 1, '', 1, 0, '', '', 'scrumproject@scrumproject', '$conf->scrumproject->enabled');
		$result2=$extrafields->addExtraField('scrumproject_role', "ScrumProjectUserRole", 'sellist', 1010, '', 'user',      0, 0, '', array('options' => array("c_type_contact:libelle:rowid::active=1 AND element='scrumproject' AND source='internal'" => null)), 1, '', 1, 0, '', '', 'scrumproject@scrumproject', '$conf->scrumproject->enabled');
		//$result3=$extrafields->addExtraField('scrumproject_myattr3', "New Attr 3 label", 'varchar', 1, 10, 'bank_account', 0, 0, '', '', 1, '', 0, 0, '', '', 'scrumproject@scrumproject', '$conf->scrumproject->enabled');
		//$result4=$extrafields->addExtraField('scrumproject_myattr4', "New Attr 4 label", 'select',  1,  3, 'thirdparty',   0, 1, '', array('options'=>array('code1'=>'Val1','code2'=>'Val2','code3'=>'Val3')), 1,'', 0, 0, '', '', 'scrumproject@scrumproject', '$conf->scrumproject->enabled');
		//$result5=$extrafields->addExtraField('scrumproject_myattr5', "New Attr 5 label", 'text',    1, 10, 'user',         0, 0, '', '', 1, '', 0, 0, '', '', 'scrumproject@scrumproject', '$conf->scrumproject->enabled');

		// Permissions
		$this->remove($options);

		$sql = array();

		return $this->_init($sql, $options);
	}

	/**
	 *  Function called when module is disabled.
	 *  Remove from database constants, boxes and permissions from Dolibarr database.
	 *  Data directories are not deleted
	 *
	 *  @param      string	$options    Options when enabling module ('', 'noboxes')
	 *  @return     int                 1 if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		$sql = array();
		return $this->_remove($sql, $options);
	}
}
