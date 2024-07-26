<?php
/* Copyright (C) 2017  Laurent Destailleur <eldy@users.sourceforge.net>
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
 * \file        class/scrumsprint.class.php
 * \ingroup     scrumproject
 * \brief       This file is a CRUD class file for ScrumSprint (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
require_once __DIR__ . '/commonObjectQuickTools.trait.php';
//require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
//require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

/**
 * Class for ScrumSprint
 */
class ScrumSprint extends CommonObject
{
	use CommonObjectQuickTools;

//	/**
//	 * @var string ID of module.
//	 */
//	public $module = 'scrumproject'; // already included in $this->element

	/**
	 * @var string ID to identify managed object.
	 */
	public $element = 'scrumproject_scrumsprint';

	/**
	 * @var string Name of table without prefix where object is stored. This is also the key used for extrafields management.
	 */
	public $table_element = 'scrumproject_scrumsprint';

	/**
	 * @var int  Does this object support multicompany module ?
	 * 0=No test on entity, 1=Test with field entity, 'field@table'=Test with link by field@table
	 */
	public $ismultientitymanaged = 1;

	/**
	 * @var int  Does object support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 1;

	/**
	 * @var string String with name of icon for scrumsprint. Must be the part after the 'object_' into object_scrumsprint.png
	 */
	public $picto = 'scrumsprint@scrumproject';


	const STATUS_DRAFT = 0;
	const STATUS_VALIDATED = 1;
	const STATUS_PENDING = 2;
	const STATUS_DONE = 3;


	/**
	 *  'type' if the field format ('integer', 'integer:ObjectClass:PathToClass[:AddCreateButtonOrNot[:Filter]]', 'varchar(x)', 'double(24,8)', 'real', 'price', 'text', 'text:none', 'html', 'date', 'datetime', 'timestamp', 'duration', 'mail', 'phone', 'url', 'password')
	 *         Note: Filter can be a string like "(t.ref:like:'SO-%') or (t.date_creation:<:'20160101') or (t.nature:is:NULL)"
	 *  'label' the translation key.
	 *  'picto' is code of a picto to show before value in forms
	 *  'enabled' is a condition when the field must be managed (Example: 1 or '$conf->global->MY_SETUP_PARAM)
	 *  'position' is the sort order of field.
	 *  'notnull' is set to 1 if not null in database. Set to -1 if we must set data to null if empty ('' or 0).
	 *  'visible' says if field is visible in list (Examples: 0=Not visible, 1=Visible on list and create/update/view forms, 2=Visible on list only, 3=Visible on create/update/view form only (not list), 4=Visible on list and update/view form only (not create). 5=Visible on list and view only (not create/not update). Using a negative value means field is not shown by default on list but can be selected for viewing)
	 *  'noteditable' says if field is not editable (1 or 0)
	 *  'default' is a default value for creation (can still be overwrote by the Setup of Default Values if field is editable in creation form). Note: If default is set to '(PROV)' and field is 'ref', the default value will be set to '(PROVid)' where id is rowid when a new record is created.
	 *  'index' if we want an index in database.
	 *  'foreignkey'=>'tablename.field' if the field is a foreign key (it is recommanded to name the field fk_...).
	 *  'searchall' is 1 if we want to search in this field when making a search from the quick search button.
	 *  'isameasure' must be set to 1 if you want to have a total on list for this field. Field type must be summable like integer or double(24,8).
	 *  'css' and 'cssview' and 'csslist' is the CSS style to use on field. 'css' is used in creation and update. 'cssview' is used in view mode. 'csslist' is used for columns in lists. For example: 'maxwidth200', 'wordbreak', 'tdoverflowmax200'
	 *  'help' is a 'TranslationString' to use to show a tooltip on field. You can also use 'TranslationString:keyfortooltiponlick' for a tooltip on click.
	 *  'showoncombobox' if value of the field must be visible into the label of the combobox that list record
	 *  'disabled' is 1 if we want to have the field locked by a 'disabled' attribute. In most cases, this is never set into the definition of $fields into class, but is set dynamically by some part of code.
	 *  'arraykeyval' to set list of value if type is a list of predefined values. For example: array("0"=>"Draft","1"=>"Active","-1"=>"Cancel")
	 *  'autofocusoncreate' to have field having the focus on a create form. Only 1 field should have this property set to 1.
	 *  'comment' is not used. You can store here any text of your choice. It is not used by application.
	 *
	 *  Note: To have value dynamic, you can set value to 0 in definition and edit the value on the fly into the constructor.
	 */

	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	public $fields=array(
		'rowid' => array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>'1', 'position'=>1, 'notnull'=>1, 'visible'=>0, 'noteditable'=>'1', 'index'=>1, 'css'=>'left', 'comment'=>"Id"),
		'ref' => array('type'=>'varchar(128)', 'label'=>'Ref', 'enabled'=>'1', 'position'=>10, 'notnull'=>1, 'visible'=>4, 'noteditable'=>'1', 'default'=>'(PROV)', 'index'=>1, 'searchall'=>1, 'showoncombobox'=>'1', 'comment'=>"Reference of object"),
		'entity' => array('type'=>'integer', 'label'=>'Entity', 'enabled'=>'1', 'position'=>20, 'notnull'=>1, 'visible'=>0, 'default'=>'1', 'index'=>1,),
		'fk_team' => array('type'=>'integer:UserGroup:user/class/usergroup.class.php', 'label'=>'SprintTeam', 'enabled'=>'1', 'position'=>20, 'notnull'=>1, 'visible'=>1, 'foreignkey'=>'usergroup.rowid',),
		'fk_advkanban' => array('type'=>'integer:AdvKanban:advancedkanban/class/advkanban.class.php', 'label'=>'Kanban', 'enabled'=>'isModEnabled("advancedkanban")', 'position'=>20, 'notnull'=>0, 'visible'=>5, 'foreignkey'=>'advancedkanban_advkanban.rowid',),
		'label' => array('type'=>'varchar(255)', 'label'=>'SprintLabel', 'enabled'=>'1', 'position'=>30, 'notnull'=>0, 'visible'=>1, 'searchall'=>1, 'css'=>'minwidth300', 'showoncombobox'=>'1',),
		'date_start' => array('type'=>'date', 'label'=>'DateStart', 'enabled'=>'1', 'position'=>35, 'notnull'=>1, 'visible'=>1,'showoncombobox'=>'1',),
		'date_end' => array('type'=>'date', 'label'=>'DateEnd', 'enabled'=>'1', 'position'=>40, 'notnull'=>1, 'visible'=>1,'showoncombobox'=>'1',),
		'description' => array('type'=>'html', 'label'=>'Description', 'enabled'=>'1', 'position'=>60, 'notnull'=>0, 'visible'=>3,),
		'note_public' => array('type'=>'html', 'label'=>'NotePublic', 'enabled'=>'1', 'position'=>61, 'notnull'=>0, 'visible'=>0,),
		'note_private' => array('type'=>'html', 'label'=>'NotePrivate', 'enabled'=>'1', 'position'=>62, 'notnull'=>0, 'visible'=>0,),
		'qty_velocity' => array('type'=>'real', 'label'=>'QtyVelocity', 'enabled'=>'1', 'position'=>100, 'notnull'=>1, 'visible'=>1, 'default'=>'0', 'isameasure'=>'1', 'css'=>'maxwidth75imp',),
		'qty_planned' => array('type'=>'real', 'label'=>'QtyPlanned', 'enabled'=>'1', 'position'=>105, 'notnull'=>0, 'visible'=>4, 'noteditable'=>'1', 'default'=>'0', 'isameasure'=>'1', 'css'=>'maxwidth75imp',),
		'qty_done' => array('type'=>'real', 'label'=>'QtyDone', 'enabled'=>'1', 'position'=>110, 'notnull'=>0, 'visible'=>4, 'noteditable'=>'1', 'default'=>'0', 'isameasure'=>'1', 'css'=>'maxwidth75imp',),
		'qty_consumed' => array('type'=>'real', 'label'=>'QtyConsumed', 'enabled'=>'1', 'position'=>120, 'notnull'=>0, 'visible'=>5, 'noteditable'=>'1', 'default'=>'0', 'isameasure'=>'1', 'css'=>'maxwidth75imp',),
		'date_creation' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>'1', 'position'=>500, 'notnull'=>1, 'visible'=>-2,),
		'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>'1', 'position'=>501, 'notnull'=>0, 'visible'=>-2,),
		'fk_user_creat' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserAuthor', 'enabled'=>'1', 'position'=>510, 'notnull'=>1, 'visible'=>-2, 'foreignkey'=>'user.rowid',),
		'fk_user_modif' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserModif', 'enabled'=>'1', 'position'=>511, 'notnull'=>-1, 'visible'=>-2, 'foreignkey'=>'user.rowid',),
		'import_key' => array('type'=>'varchar(14)', 'label'=>'ImportId', 'enabled'=>'1', 'position'=>1000, 'notnull'=>-1, 'visible'=>-2,),
		'status' => array('type'=>'smallint', 'label'=>'Status', 'enabled'=>'1', 'position'=>1000, 'notnull'=>1, 'visible'=>2, 'index'=>1, 'arrayofkeyval'=>array('0'=>'StatusScrumSprintDraft', '1'=>'StatusScrumSprintValid', '2'=>'StatusScrumSprintPending', '3'=>'StatusScrumSprintDone'),),
	);
	public $rowid;
	public $ref;
	public $entity;
	public $fk_team;
	public $fk_advkanban;
	public $label;
	public $date_start;
	public $date_end;
	public $description;
	public $note_public;
	public $note_private;
	public $qty_velocity;
	public $qty_planned;
	public $qty_consumed;
	public $qty_done;
	public $date_creation;
	public $tms;
	public $fk_user_creat;
	public $fk_user_modif;
	public $import_key;
	public $status;


	// If this object has a subtable with lines

	// /**
	//  * @var string    Name of subtable line
	//  */
	// public $table_element_line = 'scrumproject_scrumsprintline';

	// /**
	//  * @var string    Field with ID of parent key if this object has a parent
	//  */
	// public $fk_element = 'fk_scrumsprint';

	// /**
	//  * @var string    Name of subtable class that manage subtable lines
	//  */
	// public $class_element_line = 'ScrumSprintline';

	// /**
	//  * @var array	List of child tables. To test if we can delete object.
	//  */
	// protected $childtables = array();

	// /**
	//  * @var array    List of child tables. To know object to delete on cascade.
	//  *               If name matches '@ClassNAme:FilePathClass;ParentFkFieldName' it will
	//  *               call method deleteByParentField(parentId, ParentFkFieldName) to fetch and delete child object
	//  */
	// protected $childtablesoncascade = array('scrumproject_scrumsprintdet');

	// /**
	//  * @var ScrumSprintLine[]     Array of subtable lines
	//  */
	// public $lines = array();



	/**
	 * Constructor
	 *
	 * @param DoliDb $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		global $conf, $langs;

		$this->db = $db;

		if (!getDolGlobalString('MAIN_SHOW_TECHNICAL_ID') && isset($this->fields['rowid'])) $this->fields['rowid']['visible'] = 0;
		//if (empty(isModEnabled('multicompany')) && isset($this->fields['entity'])) $this->fields['entity']['enabled'] = 0;

		// Example to show how to set values of fields definition dynamically
		/*if ($user->hasRight('scrumproject', 'scrumsprint', 'read')) {
			$this->fields['myfield']['visible'] = 1;
			$this->fields['myfield']['noteditable'] = 0;
		}*/

		// Unset fields that are disabled
		foreach ($this->fields as $key => $val)
		{
			if (isset($val['enabled']) && empty($val['enabled']))
			{
				unset($this->fields[$key]);
			}
		}

		// Translate some data of arrayofkeyval
		if (is_object($langs))
		{
			foreach ($this->fields as $key => $val)
			{
				if (!empty($val['arrayofkeyval']) && is_array($val['arrayofkeyval']))
				{
					foreach ($val['arrayofkeyval'] as $key2 => $val2)
					{
						$this->fields[$key]['arrayofkeyval'][$key2] = $langs->trans($val2);
					}
				}
			}
		}

		$this->status = self::STATUS_DRAFT;
	}

	/**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = false)
	{
		global $langs;
		require_once __DIR__ .'/scrumsprintuser.class.php';

		$res =  $this->createCommon($user, $notrigger);
		if($res > 0){

			$sql = ' SELECT ugu.fk_user  FROM ' . MAIN_DB_PREFIX .'usergroup_user as ugu'
				. ' INNER JOIN ' . MAIN_DB_PREFIX . 'user as u ON ugu.fk_user = u.rowid'
				. ' INNER JOIN ' . MAIN_DB_PREFIX . 'user_extrafields as ue ON ugu.fk_user = ue.fk_object'
				. ' WHERE ugu.fk_usergroup = ' .intval($this->fk_team)
				. ' AND u.statut = 1'
				. ' AND ue.scrumproject_role = "DEV"';


			$TUsers = $this->db->getRows($sql);

			if($TUsers !== false){
				foreach ($TUsers as $obj){

					$targetUser = new User($this->db);
					if($targetUser->fetch($obj->fk_user) > 0){

						if($targetUser->array_options['options_scrumproject_role'] == 'DEV'){

							$scrumSprintUser = new ScrumSprintUser($this->db);
							$scrumSprintUser->fk_user = $targetUser->id;
							$scrumSprintUser->fk_scrum_sprint = $this->id;
							$scrumSprintUser->status = ScrumSprintUser::STATUS_DRAFT;
							//Dispo
							$scrumSprintUser->qty_availability = floatval($targetUser->array_options['options_scrumproject_availability']);
							//Ratio
							$scrumSprintUser->availability_rate = floatval($targetUser->array_options['options_scrumproject_velocity_rate']);

							if($scrumSprintUser->create($user)<0){
								setEventMessage($langs->trans('ScrumSprintUserCreateError'), 'errors');
							}
						}
					}
					else{
						setEventMessage($langs->trans('ScrumSprintUserAddedNobody'), 'errors');
					}
				}
			}
			else{
				setEventMessage($langs->trans('ScrumSprintUserSqlError'), 'errors');
			}
			if (empty($TUsers)){
				setEventMessage($langs->trans('ScrumSprintUserNoDevAssociate'), 'warning');
			}
		}
		return $res;
	}

	/**
	 * Clone an object into another one
	 *
	 * @param  	User 	$user      	User that creates
	 * @param  	int 	$fromid     Id of object to clone
	 * @return 	mixed 				New object created, <0 if KO
	 */
	public function createFromClone(User $user, $fromid)
	{
		global $langs, $extrafields;
		$error = 0;

		dol_syslog(__METHOD__, LOG_DEBUG);

		$object = new self($this->db);

		$this->db->begin();

		// Load source object
		$result = $object->fetchCommon($fromid);
		if ($result > 0 && !empty($object->table_element_line)) $object->fetchLines();

		// get lines so they will be clone
		//foreach($this->lines as $line)
		//	$line->fetch_optionals();

		// Reset some properties
		unset($object->id);
		unset($object->fk_user_creat);
		unset($object->import_key);
		unset($object->fk_advkanban);


		// Clear fields
		if (property_exists($object, 'ref')) $object->ref = empty($this->fields['ref']['default']) ? "Copy_Of_".$object->ref : $this->fields['ref']['default'];
		if (property_exists($object, 'label')) $object->label = empty($this->fields['label']['default']) ? $langs->trans("CopyOf")." ".$object->label : $this->fields['label']['default'];
		if (property_exists($object, 'status')) { $object->status = self::STATUS_DRAFT; }
		if (property_exists($object, 'date_creation')) { $object->date_creation = dol_now(); }
		if (property_exists($object, 'date_modification')) { $object->date_modification = null; }
		// ...
		// Clear extrafields that are unique
		if (is_array($object->array_options) && count($object->array_options) > 0)
		{
			$extrafields->fetch_name_optionals_label($this->table_element);
			foreach ($object->array_options as $key => $option)
			{
				$shortkey = preg_replace('/options_/', '', $key);
				if (!empty($extrafields->attributes[$this->table_element]['unique'][$shortkey]))
				{
					//var_dump($key); var_dump($clonedObj->array_options[$key]); exit;
					unset($object->array_options[$key]);
				}
			}
		}

		// Create clone
		$object->context['createfromclone'] = 'createfromclone';
		$result = $object->createCommon($user);
		if ($result < 0) {
			$error++;
			$this->error = $object->error;
			$this->errors = $object->errors;
		}

		if (!$error)
		{
			// copy internal contacts
			if ($this->copy_linked_contact($object, 'internal') < 0)
			{
				$error++;
			}
		}

		if (!$error)
		{
			// copy external contacts if same company
			if (property_exists($this, 'socid') && $this->socid == $object->socid)
			{
				if ($this->copy_linked_contact($object, 'external') < 0)
					$error++;
			}
		}

		unset($object->context['createfromclone']);

		// End
		if (!$error) {
			$this->db->commit();
			return $object;
		} else {
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param int    $id   Id object
	 * @param string $ref  Ref
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id, $ref = null)
	{
		$result = $this->fetchCommon($id, $ref);
		if ($result > 0 && !empty($this->table_element_line)) $this->fetchLines();
		return $result;
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param int    $fk_advkanban   Id kanban
	 * @return ScrumSprint | false
	 */
	public static function getScrumSprintFromKanban($fk_advkanban)
	{
		global $db;
		$sprint = new self($db);
		$result = $sprint->fetchCommon('', '', ' AND fk_advkanban = '.intval($fk_advkanban));
		if ($result > 0){
			if(!empty($sprint->table_element_line)) {
				$sprint->fetchLines();
			}
			return $sprint;
		}

		return false;
	}

	/**
	 * Load object lines in memory from the database
	 *
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetchLines()
	{
		$this->lines = array();

		$result = $this->fetchLinesCommon();
		return $result;
	}


	/**
	 * @return  string
	 */
	function getQtyAvailableBadge()
	{
		global  $langs;
		$sprintQtyAvailable = $this->getQtyAvailable();

		$label = $langs->trans('XQtySprintCanPlan', $sprintQtyAvailable);

		if($sprintQtyAvailable < 0 ){
			$out =  dolGetBadge($label, '', 'danger');
		}
		else{
			$out =  $label;
		}

		return $out;
	}


	/**
	 * @return  string
	 */
	function getQtyAvailable()
	{
		return $this->qty_velocity - $this->qty_planned;
	}


	/**
	 * Load list of objects in memory from the database.
	 *
	 * @param  string      $sortorder    Sort Order
	 * @param  string      $sortfield    Sort field
	 * @param  int         $limit        limit
	 * @param  int         $offset       Offset
	 * @param  array       $filter       Filter array. Example array('field'=>'valueforlike', 'customurl'=>...)
	 * @param  string      $filtermode   Filter mode (AND or OR)
	 * @return array|int                 int <0 if KO, array of pages if OK
	 */
	public function fetchAll($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, array $filter = array(), $filtermode = 'AND')
	{
		global $conf;

		dol_syslog(__METHOD__, LOG_DEBUG);

		$records = array();

		$sql = 'SELECT ';
		$sql .= $this->getFieldList();
		$sql .= ' FROM '.MAIN_DB_PREFIX.$this->table_element.' as t';
		if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1) $sql .= ' WHERE t.entity IN ('.getEntity($this->table_element).')';
		else $sql .= ' WHERE 1 = 1';
		// Manage filter
		$sqlwhere = array();
		if (count($filter) > 0) {
			foreach ($filter as $key => $value) {
				if ($key == 't.rowid') {
					$sqlwhere[] = $key.'='.$value;
				} elseif (in_array($this->fields[$key]['type'], array('date', 'datetime', 'timestamp'))) {
					$sqlwhere[] = $key.' = \''.$this->db->idate($value).'\'';
				} elseif ($key == 'customsql') {
					$sqlwhere[] = $value;
				} elseif (strpos($value, '%') === false) {
					$sqlwhere[] = $key.' IN ('.$this->db->sanitize($this->db->escape($value)).')';
				} else {
					$sqlwhere[] = $key.' LIKE \'%'.$this->db->escape($value).'%\'';
				}
			}
		}
		if (count($sqlwhere) > 0) {
			$sql .= ' AND ('.implode(' '.$filtermode.' ', $sqlwhere).')';
		}

		if (!empty($sortfield)) {
			$sql .= $this->db->order($sortfield, $sortorder);
		}
		if (!empty($limit)) {
			$sql .= ' '.$this->db->plimit($limit, $offset);
		}

		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < ($limit ? min($limit, $num) : $num))
			{
				$obj = $this->db->fetch_object($resql);

				$record = new self($this->db);
				$record->setVarsFromFetchObj($obj);

				$records[$record->id] = $record;

				$i++;
			}
			$this->db->free($resql);

			return $records;
		} else {
			$this->errors[] = 'Error '.$this->db->lasterror();
			dol_syslog(__METHOD__.' '.join(',', $this->errors), LOG_ERR);

			return -1;
		}
	}

	/**
	 * Update object into database
	 *
	 * @param  User $user      User that modifies
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function update(User $user, $notrigger = false)
	{
		global $langs;

		if($this->autoCreateMissingListForKanban($user, $notrigger) < 0){
			return -1;
		}

		return $this->updateCommon($user, $notrigger);
	}

	/**
	 * Create missing list for kanban
	 *
	 * @param  User $user      User that modifies
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function autoCreateMissingListForKanban(User $user, $notrigger = false){
		global $langs;

		// Add DONE list if not exists
		if($this->fk_advkanban > 0 ){

			dol_include_once('/advancedkanban/class/advkanbanlist.class.php');

			$staticAdvKanbanList = new AdvKanbanList($this->db);
			$obj = $this->db->getRow("SELECT COUNT(rowid) nb, MAX(fk_rank) maxRank FROM ".$this->db->prefix().$staticAdvKanbanList->table_element." WHERE fk_advkanban = ".intval($this->fk_advkanban)." AND ref_code = 'done' ");
			if($obj && $obj->nb == 0){
				// create done list
				$doneList = new AdvKanbanList($this->db);
				$doneList->fk_advkanban = $this->fk_advkanban;
				$doneList->label = $langs->transnoentities('KanbanDoneList');
				$doneList->ref_code = 'done';
				$doneList->fk_rank = intval($obj->maxRank) + 1;
				$res = $doneList->create($user, $notrigger);
				if($res<0){
					$this->errors[] = $doneList->errorsToString();
					return  -1;
				}
			}

		}
	}

	/**
	 * Create missing list for kanban
	 *
	 * @param  User $user      User that modifies
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function createAdvKanbanCardsInAdvKanban(User $user, $notrigger = false){
		$errors = 0;
		if($this->fk_advkanban > 0 ){

			if(!class_exists('ScrumUserStorySprint')){ require_once __DIR__ . '/scrumuserstorysprint.class.php'; }
			if(!class_exists('ScrumUserStory')){ require_once __DIR__ . '/scrumuserstory.class.php'; }
			if(!class_exists('ScrumTask')){ require_once __DIR__ . '/scrumtask.class.php'; }



			$backLogList = new AdvKanbanList($this->db);
			$resFetch = $backLogList->fetchFromKanbanAndListRefCode($this->fk_advkanban, 'backlog');
			if($resFetch<0){
				// creation des listes manquantes
				if($this->autoCreateMissingListForKanban($user, $notrigger)<0){
					$this->error = $backLogList->errorsToString();
					return -1;
				}

				$resFetch = $backLogList->fetchFromKanbanAndListRefCode($this->fk_advkanban, 'backlog');
				if($resFetch<0) {
					$this->error = $backLogList->errorsToString();
					return -1;
				}
			}

			if(empty($resFetch)){
				$this->error = '';
				return -1;
			}

			// Add users stories to sprint
			$staticScrumUserStorySprint = new ScrumUserStorySprint($this->db);

			if(!class_exists('AdvKanbanCard')){ dol_include_once('advancedkanban/class/advkanbancard.class.php'); }

			/**
			 * @var ScrumUserStorySprint[] $TUsersStorySprint
			 */
			$TUsersStorySprint = $staticScrumUserStorySprint->fetchAll( 'ASC', 'business_value',0,  0, array('fk_scrum_sprint' => $this->id));
			if(!empty($TUsersStorySprint) && is_array($TUsersStorySprint)){
				foreach ($TUsersStorySprint as $usSprint){
					/**
					 * @var ScrumUserStory $us
					 */
					$us = scrumProjectGetObjectByElement('scrumproject_scrumuserstory', $usSprint->fk_scrum_user_story);
					if($us){
						$card = new AdvKanbanCard($this->db);
						$card->label = $us->label;
						$card->fk_element = $usSprint->id;
						$card->element_type = $usSprint->element;
						$card->fk_advkanbanlist = $backLogList->id;
						$card->fk_rank = $backLogList->getMaxRankOfKanBanListItems() +1 ;
						$res = $card->create($user, $notrigger);
						if($res>0){
							// Ajout des tâches
							$staticTask = new ScrumTask($this->db);
							$TScrumTask = $staticTask->fetchAll('', '', 0, 0, array('fk_scrum_user_story_sprint' => $usSprint->id));
							if(!empty($TScrumTask) && is_array($TScrumTask)){
								foreach ($TScrumTask as $scrumTask){
									$card = new AdvKanbanCard($this->db);
									$card->label = $scrumTask->label;
									$card->fk_element = $scrumTask->id;
									$card->element_type = $scrumTask->element;
									$card->fk_advkanbanlist = $backLogList->id;
									$card->fk_rank = $backLogList->getMaxRankOfKanBanListItems() +1;
									$res = $card->create($user, $notrigger);
									if($res<=0){
										$this->errors[] = $card->errorsToString();
										$errors++;
									}
								}
							}
						}else{
							$this->errors[] = $card->errorsToString();
							$errors++;
						}
					}else{
						$this->errors[] = 'US not found';
						$errors++;
					}
				}
			}elseif($TUsersStorySprint < 0){
				$this->errors[] = $staticScrumUserStorySprint->errorsToString();
				$errors++;
			}
		}

		if($errors){ return -1; }
		return 1;
	}

	/**
	 * Delete object in database
	 *
	 * @param User $user       User that deletes
	 * @param bool $notrigger  false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function delete(User $user, $notrigger = false)
	{
		$res = $this->deleteCommon($user, $notrigger);
		if($res > 0) {
			$res2 = $this->delete_linked_contact();
		}
		return $res;
		//return $this->deleteCommon($user, $notrigger, 1);
	}

	/**
	 *  Delete a line of object in database
	 *
	 *	@param  User	$user       User that delete
	 *  @param	int		$idline		Id of line to delete
	 *  @param 	bool 	$notrigger  false=launch triggers after, true=disable triggers
	 *  @return int         		>0 if OK, <0 if KO
	 */
	public function deleteLine(User $user, $idline, $notrigger = false)
	{
		if ($this->status < 0)
		{
			$this->error = 'ErrorDeleteLineNotAllowedByObjectStatus';
			return -2;
		}

		return $this->deleteLineCommon($user, $idline, $notrigger);
	}


	/**
	 *	Validate object
	 *
	 *	@param		User	$user     		User making status change
	 *  @param		int		$notrigger		1=Does not execute triggers, 0= execute triggers
	 *	@return  	int						<=0 if OK, 0=Nothing done, >0 if KO
	 */
	public function validate($user, $notrigger = 0)
	{
		global $conf, $langs;

		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

		$error = 0;

		// Protection
		if ($this->status == self::STATUS_VALIDATED)
		{
			dol_syslog(get_class($this)."::validate action abandonned: already validated", LOG_WARNING);
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->hasRight('scrumproject', 'scrumsprint', 'write')))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->hasRight('scrumproject', 'scrumsprint', 'scrumsprint_advance')->validate))))
		 {
		 $this->error='NotEnoughPermissions';
		 dol_syslog(get_class($this)."::valid ".$this->error, LOG_ERR);
		 return -1;
		 }*/

		$now = dol_now();

		$this->db->begin();

		// Define new ref
		if (!$error && (preg_match('/^[\(]?PROV/i', $this->ref) || empty($this->ref))) // empty should not happened, but when it occurs, the test save life
		{
			$num = $this->getNextNumRef();
		} else {
			$num = $this->ref;
		}
		$this->newref = $num;

		if (!empty($num)) {
			// Validate
			$sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element;
			$sql .= " SET ref = '".$this->db->escape($num)."',";
			$sql .= " status = ".self::STATUS_VALIDATED;
			if (!empty($this->fields['date_validation'])) $sql .= ", date_validation = '".$this->db->idate($now)."'";
			if (!empty($this->fields['fk_user_valid'])) $sql .= ", fk_user_valid = ".$user->id;
			$sql .= " WHERE rowid = ".$this->id;

			dol_syslog(get_class($this)."::validate()", LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (!$resql)
			{
				dol_print_error($this->db);
				$this->error = $this->db->lasterror();
				$error++;
			}

			if (!$error && !$notrigger)
			{
				// Call trigger
				$result = $this->call_trigger('SCRUMSPRINT_VALIDATE', $user);
				if ($result < 0) $error++;
				// End call triggers
			}
		}

		if (!$error)
		{
			$this->oldref = $this->ref;

			// Rename directory if dir was a temporary ref
			if (preg_match('/^[\(]?PROV/i', $this->ref))
			{
				// Now we rename also files into index
				$sql = 'UPDATE '.MAIN_DB_PREFIX."ecm_files set filename = CONCAT('".$this->db->escape($this->newref)."', SUBSTR(filename, ".(strlen($this->ref) + 1).")), filepath = 'scrumsprint/".$this->db->escape($this->newref)."'";
				$sql .= " WHERE filename LIKE '".$this->db->escape($this->ref)."%' AND filepath = 'scrumsprint/".$this->db->escape($this->ref)."' and entity = ".$conf->entity;
				$resql = $this->db->query($sql);
				if (!$resql) { $error++; $this->error = $this->db->lasterror(); }

				// We rename directory ($this->ref = old ref, $num = new ref) in order not to lose the attachments
				$oldref = dol_sanitizeFileName($this->ref);
				$newref = dol_sanitizeFileName($num);
				$dirsource = $conf->scrumproject->dir_output.'/scrumsprint/'.$oldref;
				$dirdest = $conf->scrumproject->dir_output.'/scrumsprint/'.$newref;
				if (!$error && file_exists($dirsource))
				{
					dol_syslog(get_class($this)."::validate() rename dir ".$dirsource." into ".$dirdest);

					if (@rename($dirsource, $dirdest))
					{
						dol_syslog("Rename ok");
						// Rename docs starting with $oldref with $newref
						$listoffiles = dol_dir_list($conf->scrumproject->dir_output.'/scrumsprint/'.$newref, 'files', 1, '^'.preg_quote($oldref, '/'));
						foreach ($listoffiles as $fileentry)
						{
							$dirsource = $fileentry['name'];
							$dirdest = preg_replace('/^'.preg_quote($oldref, '/').'/', $newref, $dirsource);
							$dirsource = $fileentry['path'].'/'.$dirsource;
							$dirdest = $fileentry['path'].'/'.$dirdest;
							@rename($dirsource, $dirdest);
						}
					}
				}
			}
		}

		// Set new ref and current status
		if (!$error)
		{
			$this->ref = $num;
			$this->status = self::STATUS_VALIDATED;
		}

		if (!$error)
		{
			$this->db->commit();
			return 1;
		} else {
			$this->db->rollback();
			return -1;
		}
	}


	/**
	 *	Set draft status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						<0 if KO, >0 if OK
	 */
	public function setDraft($user, $notrigger = 0)
	{
		// Protection
		if ($this->status <= self::STATUS_DRAFT)
		{
			return 0;
		}

		return $this->setStatusCommon($user, self::STATUS_DRAFT, $notrigger, 'SCRUMSPRINT_UNVALIDATE');
	}

	/**
	 *	Set back to validated status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						<0 if KO, 0=Nothing done, >0 if OK
	 */
	public function reopen($user, $notrigger = 0)
	{
		return $this->setStatusCommon($user, self::STATUS_VALIDATED, $notrigger, 'SCRUMSPRINT_REOPEN');
	}

	/**
	 *  Return a link to the object card (with optionaly the picto)
	 *
	 *  @param  int     $withpicto                  Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto)
	 *  @param  string  $option                     On what the link point to ('nolink', ...)
	 *  @param  int     $notooltip                  1=Disable tooltip
	 *  @param  string  $morecss                    Add more css on link
	 *  @param  int     $save_lastsearch_value      -1=Auto, 0=No save of lastsearch_values when clicking, 1=Save lastsearch_values whenclicking
	 *  @return	string                              String with URL
	 */
	public function getNomUrl($withpicto = 0, $option = '', $notooltip = 0, $morecss = '', $save_lastsearch_value = -1)
	{
		global $conf, $langs, $hookmanager;

		if (!empty($conf->dol_no_mouse_hover)) $notooltip = 1; // Force disable tooltips

		$result = '';

		$label = img_picto('', 'object_'.$this->picto).' <u>'.$langs->trans("ScrumSprint").'</u>';
		if (isset($this->status)) {
			$label .= ' '.$this->getLibStatut(5);
		}
		$label .= '<br>';
		$label .= '<b>'.$this->label.'</b> ';

		$label .= '<br>';
		$label .= '<b>'.$langs->trans('Ref').':</b> '.$this->ref;



		$label .= '<br>';
		$label .= '<span class="fa fa-calendar-alt" ></span> ';
		$fieldK = 'date_start';
		$label .= $this->showOutputFieldQuick($fieldK);
		$label .= ' - ';
		$fieldK = 'date_end';
		$label .= $this->showOutputFieldQuick($fieldK);




		$url = dol_buildpath('/scrumproject/scrumsprint_card.php', 1).'?id='.$this->id;

		if ($option != 'nolink')
		{
			// Add param to save lastsearch_values or not
			$add_save_lastsearch_values = ($save_lastsearch_value == 1 ? 1 : 0);
			if ($save_lastsearch_value == -1 && preg_match('/list\.php/', $_SERVER["PHP_SELF"])) $add_save_lastsearch_values = 1;
			if ($add_save_lastsearch_values) $url .= '&save_lastsearch_values=1';
		}

		$linkclose = '';
		if (empty($notooltip))
		{
			if (getDolGlobalString('MAIN_OPTIMIZEFORTEXTBROWSER'))
			{
				$label = $langs->trans("ShowScrumSprint");
				$linkclose .= ' alt="'.dol_escape_htmltag($label, 1).'"';
			}
			$linkclose .= ' title="'.dol_escape_htmltag($label, 1).'"';
			$linkclose .= ' class="classfortooltip'.($morecss ? ' '.$morecss : '').'"';
		} else $linkclose = ($morecss ? ' class="'.$morecss.'"' : '');

		$linkstart = '<a href="'.$url.'"';
		$linkstart .= $linkclose.'>';
		$linkend = '</a>';

		$result .= $linkstart;

		if (empty($this->showphoto_on_popup)) {
			if ($withpicto) $result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
		} else {
			if ($withpicto) {
				require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

				list($class, $module) = explode('@', $this->picto);
				$upload_dir = $conf->$module->multidir_output[$conf->entity]."/$class/".dol_sanitizeFileName($this->ref);
				$filearray = dol_dir_list($upload_dir, "files");
				$filename = $filearray[0]['name'];
				if (!empty($filename)) {
					$pospoint = strpos($filearray[0]['name'], '.');

					$pathtophoto = $class.'/'.$this->ref.'/thumbs/'.substr($filename, 0, $pospoint).'_mini'.substr($filename, $pospoint);
					if (empty(getDolGlobalString(strtoupper($module.'_'.$class).'_FORMATLISTPHOTOSASUSERS'))) {
						$result .= '<div class="floatleft inline-block valignmiddle divphotoref"><div class="photoref"><img class="photo'.$module.'" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($pathtophoto).'"></div></div>';
					} else {
						$result .= '<div class="floatleft inline-block valignmiddle divphotoref"><img class="photouserphoto userphoto" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($pathtophoto).'"></div>';
					}

					$result .= '</div>';
				} else {
					$result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
				}
			}
		}

		if ($withpicto != 2) $result .= $this->ref;

		$result .= $linkend;
		//if ($withpicto != 2) $result.=(($addlabel && $this->label) ? $sep . dol_trunc($this->label, ($addlabel > 1 ? $addlabel : 0)) : '');

		global $action, $hookmanager;
		$hookmanager->initHooks(array('scrumsprintdao'));
		$parameters = array('id'=>$this->id, 'getnomurl'=>$result);
		$reshook = $hookmanager->executeHooks('getNomUrl', $parameters, $this, $action); // Note that $action and $object may have been modified by some hooks
		if ($reshook > 0) $result = $hookmanager->resPrint;
		else $result .= $hookmanager->resPrint;

		return $result;
	}

	/**
	 *  Return the label of the status
	 *
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return	string 			       Label of status
	 */
	public function getLibStatut($mode = 0)
	{
		return $this->LibStatut($this->status, $mode);
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Return the status
	 *
	 *  @param	int		$status        Id status
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return string 			       Label of status
	 */
	public function LibStatut($status, $mode = 0)
	{
		// phpcs:enable
		if (empty($this->labelStatus) || empty($this->labelStatusShort))
		{
			global $langs;
			//$langs->load("scrumproject@scrumproject");
			$this->labelStatus[self::STATUS_DRAFT] = $langs->trans('StatusScrumSprintDraft');
			$this->labelStatus[self::STATUS_VALIDATED] = $langs->trans('StatusScrumSprintValid');
			$this->labelStatus[self::STATUS_PENDING] = $langs->trans('StatusScrumSprintPending');
			$this->labelStatus[self::STATUS_DONE] = $langs->trans('StatusScrumSprintDone');
			$this->labelStatusShort[self::STATUS_DRAFT] = $langs->trans('StatusScrumSprintDraft');
			$this->labelStatusShort[self::STATUS_VALIDATED] = $langs->trans('StatusScrumSprintValid');
			$this->labelStatusShort[self::STATUS_PENDING] = $langs->trans('StatusScrumSprintPending');
			$this->labelStatusShort[self::STATUS_DONE] = $langs->trans('StatusScrumSprintDone');
		}

		$statusType = 'status'.$status;
		if ($status == self::STATUS_VALIDATED) $statusType = 'status4';
		if ($status == self::STATUS_PENDING) $statusType = 'status1';
		if ($status == self::STATUS_DONE) $statusType = 'status6';

		return dolGetStatus($this->labelStatus[$status], $this->labelStatusShort[$status], '', $statusType, $mode);
	}

	/**
	 *	Load the info information in the object
	 *
	 *	@param  int		$id       Id of object
	 *	@return	void
	 */
	public function info($id)
	{
		$sql = 'SELECT rowid, date_creation as datec, tms as datem,';
		$sql .= ' fk_user_creat, fk_user_modif';
		$sql .= ' FROM '.MAIN_DB_PREFIX.$this->table_element.' as t';
		$sql .= ' WHERE t.rowid = '.$id;
		$result = $this->db->query($sql);
		if ($result)
		{
			if ($this->db->num_rows($result))
			{
				$obj = $this->db->fetch_object($result);
				$this->id = $obj->rowid;
				if (!empty($obj->fk_user_author))
				{
					$cuser = new User($this->db);
					$cuser->fetch($obj->fk_user_author);
					if(property_exists($this, 'user_creation')) $this->user_creation = $cuser;
					if(property_exists($this, 'user_creation_id')) $this->user_creation_id = $cuser;
				}

				if (!empty($obj->fk_user_valid))
				{
					$vuser = new User($this->db);
					$vuser->fetch($obj->fk_user_valid);
					if(property_exists($this, 'user_validation')) $this->user_validation = $vuser;
					if(property_exists($this, 'user_validation_id')) $this->user_validation_id = $vuser;
				}

				if (!empty($obj->fk_user_cloture))
				{
					$cluser = new User($this->db);
					$cluser->fetch($obj->fk_user_cloture);
					$this->user_cloture = $cluser;
				}

				$this->date_creation     = $this->db->jdate($obj->datec);
				$this->date_modification = $this->db->jdate($obj->datem);
			}

			$this->db->free($result);
		} else {
			dol_print_error($this->db);
		}
	}

	/**
	 * Initialise object with example values
	 * Id must be 0 if object instance is a specimen
	 *
	 * @return void
	 */
	public function initAsSpecimen()
	{
		$this->initAsSpecimenCommon();
	}

	/**
	 * 	Create an array of lines
	 *
	 * 	@return array|int		array of lines if OK, <0 if KO
	 */
	/*public function getLinesArray()
	{
		$this->lines = array();

		$objectline = new ScrumSprintLine($this->db);
		$result = $objectline->fetchAll('ASC', 'position', 0, 0, array('customsql'=>'fk_scrumsprint = '.$this->id));

		if (is_numeric($result))
		{
			$this->error = $this->error;
			$this->errors = $this->errors;
			return $result;
		} else {
			$this->lines = $result;
			return $this->lines;
		}
	}*/

	/**
	 *  Returns the reference to the following non used object depending on the active numbering module.
	 *
	 *  @return string      		Object free reference
	 */
	public function getNextNumRef()
	{
		global $langs, $conf;
		$langs->load("scrumproject@scrumproject");

		if (!getDolGlobalString('SCRUMPROJECT_SCRUMSPRINT_ADDON')) {
			$conf->global->SCRUMPROJECT_SCRUMSPRINT_ADDON = 'mod_scrumsprint_standard';
		}

		if (getDolGlobalString('SCRUMPROJECT_SCRUMSPRINT_ADDON'))
		{
			$mybool = false;

			$file = getDolGlobalString('SCRUMPROJECT_SCRUMSPRINT_ADDON') . ".php";
			$classname = getDolGlobalString('SCRUMPROJECT_SCRUMSPRINT_ADDON');

			// Include file with class
			$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);
			foreach ($dirmodels as $reldir)
			{
				$dir = dol_buildpath($reldir."core/modules/scrumproject/");

				// Load file with numbering class (if found)
				$mybool |= @include_once $dir.$file;
			}

			if ($mybool === false)
			{
				dol_print_error('', "Failed to include file ".$file);
				return '';
			}

			if (class_exists($classname)) {
				$obj = new $classname();
				$numref = $obj->getNextValue($this);

				if ($numref != '' && $numref != '-1')
				{
					return $numref;
				} else {
					$this->error = $obj->error;
					//dol_print_error($this->db,get_class($this)."::getNextNumRef ".$obj->error);
					return "";
				}
			} else {
				print $langs->trans("Error")." ".$langs->trans("ClassNotFound").' '.$classname;
				return "";
			}
		} else {
			print $langs->trans("ErrorNumberingModuleNotSetup", $this->element);
			return "";
		}
	}
//
//	/**
//	 *  Create a document onto disk according to template module.
//	 *
//	 *  @param	    string		$modele			Force template to use ('' to not force)
//	 *  @param		Translate	$outputlangs	objet lang a utiliser pour traduction
//	 *  @param      int			$hidedetails    Hide details of lines
//	 *  @param      int			$hidedesc       Hide description
//	 *  @param      int			$hideref        Hide ref
//	 *  @param      null|array  $moreparams     Array to provide more information
//	 *  @return     int         				0 if KO, 1 if OK
//	 */
//	public function generateDocument($modele, $outputlangs, $hidedetails = 0, $hidedesc = 0, $hideref = 0, $moreparams = null)
//	{
//		global $conf, $langs;
//
//		$result = 0;
//		$includedocgeneration = 0;
//
//		$langs->load("scrumproject@scrumproject");
//
//		if (!dol_strlen($modele)) {
//			$modele = 'standard_scrumsprint';
//
//			if (!empty($this->model_pdf)) {
//				$modele = $this->model_pdf;
//			} elseif (!empty($conf->global->SCRUMSPRINT_ADDON_PDF)) {
//				$modele = $conf->global->SCRUMSPRINT_ADDON_PDF;
//			}
//		}
//
//		$modelpath = "core/modules/scrumproject/doc/";
//
//		if ($includedocgeneration && !empty($modele)) {
//			$result = $this->commonGenerateDocument($modelpath, $modele, $outputlangs, $hidedetails, $hidedesc, $hideref, $moreparams);
//		}
//
//		return $result;
//	}

	/**
	 * Action executed by scheduler
	 * CAN BE A CRON TASK. In such a case, parameters come from the schedule job setup field 'Parameters'
	 * Use public function doScheduledJob($param1, $param2, ...) to get parameters
	 *
	 * @return	int			0 if OK, <>0 if KO (this function is used also by cron so only 0 is OK)
	 */
	public function doScheduledJob()
	{
		global $conf, $langs;

		//$conf->global->SYSLOG_FILE = 'DOL_DATA_ROOT/dolibarr_mydedicatedlofile.log';

		$error = 0;
		$this->output = '';
		$this->error = '';

		dol_syslog(__METHOD__, LOG_DEBUG);

		$now = dol_now();

		$this->db->begin();

		// ...

		$this->db->commit();

		return $error;
	}

	/**
	 * Link all users from the team to the sprint
	 * @return int Nb of users linked to the scrum sprint
	 */
	public function addTeamMembers() {
		$grp = new UserGroup($this->db);
		$grp->fetch($this->fk_team);
		if(!empty($grp->members)) {
			$nbAdd = 0;
			foreach ($grp->members as $usr) {
				if(empty($usr->array_options['options_scrumproject_role'])) continue;
				$res = $this->add_contact($usr->id, $usr->array_options['options_scrumproject_role'], 'internal');
				if($res > 0) $nbAdd++;
			}

			return $nbAdd;
		}

		return 0;
	}

	/**
	 *
	 * Calculates the sprint velocity based on the default velocity of each DEV user linked to the sprint
	 * @return int 1 if OK -1 if KO
	 */
	public function refreshVelocity(User $user, $update = false) {
		if($this->status != self::STATUS_DRAFT) return 0;

		$sql = /** @lang MySQL */ "SELECT SUM(qty_velocity) as qty_velocity "
			." FROM ".MAIN_DB_PREFIX."scrumproject_scrumsprintuser"
			." WHERE fk_scrum_sprint = ".intval($this->id);

		$resql = $this->db->query($sql);
		if($resql) {
			$obj = $this->db->fetch_object($resql);
			$this->qty_velocity = $obj->qty_velocity;

			if($update){
				return $this->update($user);
			} else {
				return 1;
			}
		} else {
			dol_print_error($this->db);
			$this->error = $this->db->lasterror();
			return -1;
		}
	}

	/**
	 * Calculates the sprint quantities : planned and done
	 * Planned is the sum of points of all cards linked to the sprint
	 * Done is the same but only for done cards
	 * @param User $user
	 * @param bool $update
	 * @return int 1 if OK -1 if KO
	 */
	public function refreshQuantities(User $user, $update = false) {

		$sql = /** @lang MySQL */ "SELECT SUM(qty_planned) as qty_planned, SUM(qty_done) as qty_done, SUM(qty_consumed) as qty_consumed "
			." FROM ".MAIN_DB_PREFIX."scrumproject_scrumuserstorysprint"
			." WHERE fk_scrum_sprint = ".intval($this->id);

		$resql = $this->db->query($sql);
		if($resql) {
			$obj = $this->db->fetch_object($resql);
			$this->qty_planned = $obj->qty_planned;
			$this->qty_done = $obj->qty_done;
			$this->qty_consumed = $obj->qty_consumed;

			if($update){
				return $this->update($user);
			} else {
				return 1;
			}
		} else {
			dol_print_error($this->db);
			$this->error = $this->db->lasterror();
			return -1;
		}
	}


	/**
	 * Return HTML string to show a field into a page
	 * Code very similar with showOutputField of extra fields
	 *
	 * @param  array   $val			     Array of properties of field to show
	 * @param  string  $key            Key of attribute
	 * @param  string  $value          Preselected value to show (for date type it must be in timestamp format, for amount or price it must be a php numeric value)
	 * @param  string  $moreparam      To add more parametes on html input tag
	 * @param  string  $keysuffix      Prefix string to add into name and id of field (can be used to avoid duplicate names)
	 * @param  string  $keyprefix      Suffix string to add into name and id of field (can be used to avoid duplicate names)
	 * @param  mixed   $morecss        Value for css to define size. May also be a numeric.
	 * @return string
	 */
	public function showOutputField($val, $key, $value, $moreparam = '', $keysuffix = '', $keyprefix = '', $morecss = '')
	{
		global $langs;

		if($key == 'qty_velocity'){
			$out = parent::showOutputField($val, $key, $value, $moreparam, $keysuffix, $keyprefix, $morecss);

			if(empty($out)){
				return '--';
			}

			$countObj = $this->db->getRow("SELECT SUM(qty_velocity) qty_velocity FROM ". MAIN_DB_PREFIX . "scrumproject_scrumsprintuser WHERE fk_scrum_sprint = ".intval($this->id));
			if($countObj){
				if(round(floatval($countObj->qty_velocity),2) != round(floatval($this->qty_velocity),2)){
					$tooltip = '<strong>' . $langs->trans('SumOfDeveloperAvailabilityIsDifferent') . '</strong></br>';
					$tooltip.= $langs->trans('SumOfDeveloperAvailability') . ' : ' .price($countObj->qty_velocity).'</br>';
					$tooltip.= $langs->trans('QtyVelocity') . ' : ' .price($value);
					$out = ' <span class="classfortooltip" title="'.dol_escape_htmltag($tooltip).'"  >'.price($value).' <span class="fa fa-warning"></span></span>';
				}
			}

			return $out;
		}
		elseif(in_array($key, array('qty_planned', 'qty_consumed', 'qty_done'))){
			$out = parent::showOutputField($val, $key, $value, $moreparam, $keysuffix, $keyprefix, $morecss);
			if(empty($out)){ $out = '--'; }
			return $out;
		}
		else{
			return parent::showOutputField($val, $key, $value, $moreparam, $keysuffix, $keyprefix, $morecss);
		}
	}


	/**
	 *
	 * @return int
	 */
	public function calcTimeSpent(){

		$sql = /** @lang MySQL */ "SELECT SUM(qty_consumed) sumTimeSpent FROM ".MAIN_DB_PREFIX."scrumproject_scrumuserstorysprint "
			." WHERE fk_scrum_user_story = ".intval($this->id);

		$obj = $this->db->getRow($sql);
		if($obj){
			$this->qty_consumed = doubleval($obj->sumTimeSpent);
			return $this->qty_consumed;
		}

		return 0;
	}

	/**
	 * @param User $user
	 * @param      $notrigger
	 * @return void
	 */
	public function updateTimeSpent(User $user, $notrigger = false){

		$this->calcTimeSpent();
		$sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element." SET qty_consumed = '".$this->qty_consumed."' WHERE rowid=".((int) $this->id);
		return $this->updateByQuery($user, $sql, 'SCRUMSPRINT_UPDATE_TIME_SPENT',  $notrigger);
	}


	/**
	 * Permet de lancer une requette d'update avec tout les triggers
	 * @param User $user
	 * @param string $sql the SQL UPDATE query
	 * @param string $tiggerName
	 * @param bool $notrigger
	 * @return int
	 */
	public function updateByQuery(User $user, $sql, $tiggerName,  $notrigger = false){
		global $user;
		$error = 0;
		$this->db->begin();
		if($this->db->query($sql)){

			// Triggers
			if (!$error && !$notrigger) {
				// Call triggers
				$result = $this->call_trigger($tiggerName, $user);
				if ($result < 0) {
					$error++;
				} //Do also here what you must do to rollback action if trigger fail
				// End call triggers
			}


			// Commit or rollback
			if ($error) {
				$this->db->rollback();
				return -2;
			} else {
				$this->db->commit();
				return $this->id;
			}
		}
		else {
			$this->error = $this->db->lasterror();
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 * @param $msg
	 * @return void
	 */
	public function setErrorMsg($msg){
		global $langs;

		if(is_array($msg)){
			foreach ($msg as $item){
				$this->setErrorMsg($item);
			}
			return;
		}

		if (!empty($langs->tab_translate[$msg])) {    // Translation is available
			$this->errors[] = $langs->trans($msg);
		}else{
			$this->errors[] = $msg;
		}
	}

	/**
	 * @return int
	 */
	public function getKanbanId()
	{
		if($this->fk_advkanban > 0){
			return $this->fk_advkanban;
		}
		else{
			return 0;
		}
	}

	/**
	 * Calcule et retourne un résumé de la progression par Utilisateur
	 * @return int
	 */
	public function displayUsersProgress($userIds = array()){
		global $langs;

		include_once DOL_DOCUMENT_ROOT . '/core/class/html.form.class.php';

		$out = '';


		$out.= '<div class="sprint-resume-text-section">';

		$out.= '<span class="inline-spaced-item"  >';
		$out.= $this->showOutputFieldQuick('label');
		$out.= '</span>';

		$out.= '<span class="inline-spaced-item"  >';
		$out.= '<span class="fa fa-calendar-alt" ></span> ';
		$fieldK = 'date_start';
		$out.= $this->showOutputFieldQuick($fieldK);
		$out.= ' - ';
		$fieldK = 'date_end';
		$out.= $this->showOutputFieldQuick($fieldK);
		$out.= '</span>';

		$fieldK = 'qty_velocity';
		$out.= '<span class="inline-spaced-item classfortooltip"  title="'.dol_escape_htmltag($langs->trans($this->fields[$fieldK]['label'])).'" >';
		$out.= '<span class="fa fa-running" ></span> ';
		$out.= $this->showOutputFieldQuick($fieldK);
		$out.= '</span>';

		$fieldK = 'qty_planned';
		$out.= '<span class="inline-spaced-item classfortooltip"  title="'.dol_escape_htmltag($langs->trans($this->fields[$fieldK]['label'])).'" >';
		$out.= '<span class="fa fa-calendar-check-o" ></span> ';
		$out.= $this->showOutputFieldQuick($fieldK);
		$out.= '</span>';


		$fieldK = 'qty_consumed';
		$out.= '<span class="inline-spaced-item classfortooltip"  title="'.dol_escape_htmltag($langs->trans($this->fields[$fieldK]['label'])).'" >';
		$out.= '<span class="fa fa-hourglass-o" ></span> ';
		$out.= $this->showOutputFieldQuick($fieldK);
		$out.= '</span>';

		$fieldK = 'qty_done';
		$out.= '<span class="inline-spaced-item classfortooltip"  title="'.dol_escape_htmltag($langs->trans($this->fields[$fieldK]['label'])).'" >';
		$out.= '<span class="fa fa-check" ></span> ';
		$out.= $this->showOutputFieldQuick($fieldK);
		$out.= '</span>';

		$out.= '</div>';

		$data = $this->getSprintUsersProgress($userIds);

		if($data === false || !is_array($data)){
			return $this->error;
		}

		if(empty($data)){
			$out.= $langs->trans('NoData');
		}else{
			$out.= '<table class="sprint-resume-table">';

			$out.= '<thead>';
			$out.= '<tr>';
			$out.= '	<th colspan="2"></th>';
			$out.= '	<th class="center sprint-resume-col"><span>'.$langs->trans('QtyAvailability').'</span></th>';
			$out.= '	<th class="center sprint-resume-col"><span class="classfortooltip" title="'.dol_escape_htmltag($langs->trans('QtyVelocityHelp')).'">'.$langs->trans('QtyVelocity').'</span></th>';

			if(getDolGlobalInt('SP_USE_LEAVE_DAYS')) {
				$out .= '	<th class="center sprint-resume-col"><span>' . $langs->trans('QtyLeaveDays') . '</span></th>';
			}
			$out.= '	<th class="center sprint-resume-col"><span class="classfortooltip" title="'.dol_escape_htmltag($langs->trans('TimeSpentHelp')).'">'.$langs->trans('TimeSpent').'</span></th>';
			$out.= '	<th class="center sprint-resume-col"><span class="classfortooltip" title="'.dol_escape_htmltag($langs->trans('TimePlannedDoneHelp')).'">'.$langs->trans('TimePlannedDone').'</span></th>';
//			$out.= '	<th class="center sprint-resume-col"><span class="classfortooltip" title="'.dol_escape_htmltag($langs->trans('TimeEngagedHelp')).'">'.$langs->trans('TimeEngaged').'</span></th>';
			$out.= '	<th class="center sprint-resume-col"><span class="classfortooltip" title="'.dol_escape_htmltag($langs->trans('RemainVelocityHelp')).'">'.$langs->trans('RemainVelocity').'</span></th>';
			$out.= '	<th class="center sprint-resume-col"><span class="classfortooltip" title="'.dol_escape_htmltag($langs->trans('ProductivityRealHelp')).'">'.$langs->trans('ProductivityReal').'</span></th>';
			$out.= '	<th class="center sprint-resume-col" colspan="2"><span class="classfortooltip" title="'.dol_escape_htmltag($langs->trans('ProductivityGoalHelp')).'">'.$langs->trans('ProductivityGoal').'</span></th>';
			$out.= '</tr>';
			$out.= '</thead>';


			$total = new stdClass();
			$total->userQtyAvailability = 0;
			$total->userQtyVelocity = 0;
			$total->userNotPlannedLeaveDays = 0;
			$total->remainVelocityTheoretical = 0;
			$total->sumTimeSpent = 0;
			$total->sumTimeDone = 0;

			$out.= '<tbody>';
			foreach ($data as $item) {
				$cUser = scrumProjectGetObjectByElement('user', $item->fk_user);
				/** @var User $cUser */
				if (!$cUser) {
					$out .= '<tr><th colspan="2" class="error">' . $langs->trans('Error') . '</th></tr>';
					continue;
				}

				// Calcule de la productivité
				$productivityRatio = 0;
				if ($item->sumTimeSpent > 0) {
					$productivityRatio = round($item->sumTimeDone / $item->sumTimeSpent, 2);
				}

				// Calcule des objectifs
				$productivityGoalRatio = 0;
				if ($item->userQtyVelocity > 0) {
					$productivityGoalRatio = round($item->sumTimeDone / $item->userQtyVelocity, 2);
				}

				// Restant à produire pour atteindre l'objectif utilisateur
				$remainToProd = $item->userQtyVelocity - $item->sumTimePlanned;
				if($remainToProd < 0){ $remainToProd = 0; }

				// Calcule de la vélocité réelle restante basée sur les jours ouvrés restants, les absences et le ratio de vélocité du sprint pour cet utilisateur
				$remainVelocityReal = 0;
				if($item->userRemainingWorkHours>=0){
					$remainVelocityReal = round($item->userRemainingWorkHours * $item->userAvailabilityRate, 2);
				}

				// La vélocité théorique se base uniquement sur la saisie
				$remainVelocityTheoretical =  $item->userQtyVelocity - $item->sumTimeSpent;
				if($remainVelocityTheoretical < 0){ $remainVelocityTheoretical = 0; }


				$productivityGoalRatioDisplay = '';
				$achievementBadge = '';
				if ($item->userQtyVelocity > 0){

					if ($productivityGoalRatio >= 1) {
						$productivityGoalRatioDisplay = '<span class="badge badge-success">' . ($productivityGoalRatio * 100) . '%</span>';
					} elseif ($productivityGoalRatio < 0.8) {
						$productivityGoalRatioDisplay = '<span class="badge badge-danger">' . ($productivityGoalRatio * 100) . '%</span>';
					} else {
						$productivityGoalRatioDisplay = '<span class="badge badge-warning">' . ($productivityGoalRatio * 100) . '%</span>';
					}

					$starClass = 'fa-star-o';
					if ($item->userAvailabilityRate > 1) {
						$starClass = 'fa-star';
					}

					if ($productivityGoalRatio >= 1.75) {
						$achievementBadge .= '<span class="productivity-badge-icon fa ' . $starClass . '"></span>';
						$achievementBadge .= '<span class="productivity-badge-icon fa ' . $starClass . ' fa-2x"></span>';
						$achievementBadge .= '<span class="productivity-badge-icon fa ' . $starClass . '"></span>';
					} elseif ($productivityGoalRatio >= 1.5) {
						$achievementBadge .= '<span class="productivity-badge-icon fa ' . $starClass . '"></span>';
						$achievementBadge .= '<span class="productivity-badge-icon fa ' . $starClass . '"></span>';
					} elseif ($productivityGoalRatio >= 1.1) {
						$achievementBadge .= '<span class="productivity-badge-icon fa ' . $starClass . '"></span>';
					}

				}


				// addition pour les totaux
				$total->userQtyAvailability+= $item->userQtyAvailability;
				$total->userQtyVelocity+= $item->userQtyVelocity;
				$total->userNotPlannedLeaveDays+= $item->userNotPlannedLeaveDays;
				$total->sumTimeSpent+= $item->sumTimeSpent;
				$total->sumTimeDone+= $item->sumTimeDone;
				$total->remainVelocityTheoretical+= $remainVelocityTheoretical;


				$out.= '<tr>';
				$out.= '	<th>';
				$out.= '		<span class="sprint-resume-user-img" data-user-id="';
				$out.= 				$cUser->id.'" >'.Form::showphoto('userphoto', $cUser, 0, 0, 0, 'sprint-resume-user-img__pic', '', '', 1);
				$out.= '		</span>';
				$out.= '	</th>';

				$out.= '	<th>';
				$out.= 			$cUser->getFullName($langs);
				if(empty($item->userWasPlanned)){
					$out.= 		'<br/>'.dolGetBadge($langs->trans('UserWasntPlannedOnSprintShort'), '', 'warning');
				}
				$out.= '	</th>';

				$out.= '	<td class="center sprint-resume-col">';
				$out.= 			convertFloatHourToHoursMins($item->userQtyAvailability, $langs) ;
				$out.= '	</td>';

				$out.= '	<td class="center sprint-resume-col">';
				$out.= 			convertFloatHourToHoursMins($item->userQtyVelocity, $langs) ;
				$out.= ' 		<small class="classfortooltip"  title="'.dol_escape_htmltag($langs->trans('AvailabilityRateHelp')).'">('.($item->userAvailabilityRate * 100) . '%'.')</small>';
				$out.= '	</td>';

				if(getDolGlobalInt('SP_USE_LEAVE_DAYS')){
					$out.= '	<td class="center sprint-resume-col">';
					$out.= 			$item->userNotPlannedLeaveDays . ' ' . $langs->trans('Days') ;
					$out.= '	</td>';
				}

				$out.= '	<td class="center sprint-resume-col">';
				$out.= convertFloatHourToHoursMins($item->sumTimeSpent, $langs) ;
				if($item->sumTimeSpent > $item->userQtyAvailability){
					$alertText = $langs->trans('MoreTimeSpendsThanAvailability', convertFloatHourToHoursMins($item->sumTimeSpent, $langs),  convertFloatHourToHoursMins($item->userQtyAvailability, $langs));
					$out.= ' <span class="fa fa-warning classfortooltip"  title="'.dol_escape_htmltag($alertText).'" ></span> ';
				}
				$out.= '	</td>';

				$out.= '	<td class="center sprint-resume-col">';
				$out.= convertFloatHourToHoursMins($item->sumTimeDone, $langs) ;
				$out.= '	</td>';

//				$out.= '	<td class="center sprint-resume-col">';
//				$out.= getTileFormatedTime($item->sumTimePlanned) ;
//				$out.= '	</td>';

				$out.= '	<td class="center sprint-resume-col">';

				if($remainToProd > $remainVelocityReal){
					// display alert
					$alertText = $langs->trans('WarningMissingRealVelocityAccordingToEndDate', $item->userRemainingWorkDays,  convertFloatHourToHoursMins($remainToProd, $langs));
					$out.= '<span class="fa fa-warning classfortooltip" title="'.dol_escape_htmltag($alertText).'" ></span> ';
				}
				$out.= convertFloatHourToHoursMins($remainVelocityTheoretical, $langs) ;
				$out.= '	</td>';

				$out.= '	<td class="center sprint-resume-col">';
				$out.= ($productivityRatio * 100) . '%';
				$out.= '	</td>';

				$out.= '	<td class="center sprint-resume-col">';
				$out.= $productivityGoalRatioDisplay;
				$out.= '	</td>';

				$out.= '	<td class="center ">';
				if(getDolGlobalInt('SP_USE_ACHIEVEMENTS')){
					$out.= $achievementBadge;
				}
				$out.= '	</td>';

				$out.= '</tr>';
			}
			$out.= '</tbody>';


			$out.= '<tfoot>';
			$out.= '<tr class="total">';
			$out.= '	<th colspan="2">'.$langs->trans('Total').'</th>';



			$out.= '	<th class="center sprint-resume-col">'.convertFloatHourToHoursMins($total->userQtyAvailability, $langs).'</th>';
			$out.= '	<th class="center sprint-resume-col">'.convertFloatHourToHoursMins($total->userQtyVelocity, $langs).'</th>';

			if(getDolGlobalInt('SP_USE_LEAVE_DAYS')) {
				$out .= '	<th class="center sprint-resume-col"><span>' . $total->userNotPlannedLeaveDays . ' ' . $langs->trans('Days')  . '</span></th>';
			}
			$out.= '	<th class="center sprint-resume-col">'.convertFloatHourToHoursMins($total->sumTimeSpent,$langs).'</th>';
			$out.= '	<th class="center sprint-resume-col">'.convertFloatHourToHoursMins($total->sumTimeDone,$langs).'</th>';
//			$out.= '	<th class="center sprint-resume-col">'.$langs->trans('TimeEngaged').'</span></th>';
			$out.= '	<th class="center sprint-resume-col">'.convertFloatHourToHoursMins($total->remainVelocityTheoretical,$langs).'</th>';
			$out.= '	<th class="center sprint-resume-col">';
			// Calcule des objectifs
			$TotalTeamProductivity = 0;
			if ($total->sumTimeSpent > 0) {
				$TotalTeamProductivity = round($total->sumTimeDone / $total->sumTimeSpent, 2)*100;
			}
			$out.= $TotalTeamProductivity.'%';
			$out.= '	</th>';
			$out.= '	<th class="center sprint-resume-col" colspan="2"></th>';
			$out.= '</tr>';
			$out.= '</tfoot>';

			$out.= '</table>';

		}



		return $out;
	}

	/**
	 * Calcule et retourne un résumé de la progression par Utilisateur
	 *
	 * @param array $userIds
	 * @return false|array
	 */
	public function getSprintUsersProgress(){

		if(!class_exists('ScrumSprintUser')){ require_once __DIR__ .'/scrumsprintuser.class.php';}

		// Récupération de la liste des utilisateurs affectés au sprint
		$usersAffectedList = $this->getPlannedUsersIdList();
		if($usersAffectedList === false){
			$this->error= $this->db->error();
			return false;
		}

		$TUsersAlreadyTreated = array();
		$dataAbstract = array();

		if($usersAffectedList){
			foreach ($usersAffectedList as $userId){
				$userStats = $this->calcUserProgress($userId);
				$userStats->userWasPlanned = 1;

				$TUsersAlreadyTreated[] = $userId;
				$dataAbstract[$userId] = $userStats;
			}
		}

		// Récupération de la liste des utilisateurs NON affectés au sprint mais pour lesquels du temps est saisis
		$usersNotAffectedList = $this->getNotPlannedUsersIdListButWithTimeSpend($TUsersAlreadyTreated);
		if($usersNotAffectedList === false){
			$this->error= $this->db->error();
			return false;
		}

		if($usersNotAffectedList){
			foreach ($usersNotAffectedList as $userId){
				$userStats = $this->calcUserProgress($userId);
				$userStats->userWasPlanned = 0;

				$TUsersAlreadyTreated[] = $userId;
				$dataAbstract[$userId] = $userStats;
			}
		}

		return $dataAbstract;
	}


	/**
	 * return list of planned user ids
	 * @return array|false
	 */
	public function getNotPlannedUsersIdListButWithTimeSpend($excludedUsersIds = array()){

		if(empty($excludedUsersIds)){
			$excludedUsersIds = $this->getPlannedUsersIdList();
		}

		if(empty($excludedUsersIds)){
			return false;
		}

		// récupération des utilisateurs avec des temps saisis mais qui normalement ne font pas parties du sprint
		if(version_compare(DOL_VERSION, '18.0.0', '<')) {
			$sql = /** @lang MySQL */
				"SELECT ptt.fk_user  "." FROM ".MAIN_DB_PREFIX."projet_task_time ptt "
				." JOIN ".MAIN_DB_PREFIX."scrumproject_scrumtask_projet_task_time st_ptt ON(st_ptt.fk_projet_task_time = ptt.rowid) "
				." JOIN ".MAIN_DB_PREFIX."scrumproject_scrumtask st ON(st.rowid = st_ptt.fk_scrumproject_scrumtask) "
				." JOIN ".MAIN_DB_PREFIX."scrumproject_scrumuserstorysprint USsprint  ON(USsprint.rowid = st.fk_scrum_user_story_sprint) "
				." WHERE "." ptt.fk_user NOT IN (".implode(',', $excludedUsersIds).')'
				." AND USsprint.fk_scrum_sprint = ".intval($this->id);
		}
		else {
			$sql = /** @lang MySQL */
			'SELECT ptt.fk_user  '.' FROM '.MAIN_DB_PREFIX.'element_time ptt '
			.' JOIN '.MAIN_DB_PREFIX.'scrumproject_scrumtask_projet_task_time st_ptt ON(st_ptt.fk_projet_task_time = ptt.rowid AND ptt.elementtype = "task") '
			.' JOIN '.MAIN_DB_PREFIX.'scrumproject_scrumtask st ON(st.rowid = st_ptt.fk_scrumproject_scrumtask) '
			.' JOIN '.MAIN_DB_PREFIX.'scrumproject_scrumuserstorysprint USsprint  ON(USsprint.rowid = st.fk_scrum_user_story_sprint) '
			.' WHERE '.' ptt.fk_user NOT IN ('.implode(',', $excludedUsersIds).')'
			.' AND USsprint.fk_scrum_sprint = '.intval($this->id);
		}

		$sqlObjList = $this->db->getRows($sql);

		if($sqlObjList === false){
			$this->error= $this->db->error();
			return false;
		}

		$TUsersIds = array();

		if($sqlObjList){
			foreach ($sqlObjList as $objUsers){
				$TUsersIds[] = $objUsers->fk_user;
			}
		}

		return $TUsersIds;
	}

	/**
	 * return list of planned user ids
	 * @return array|false
	 */
	public function getPlannedUsersIdList(){
		// Récupération de la liste des utilisateurs affectés au sprint
		$sql = /** @lang MySQL */
			"SELECT sUser.fk_user fk_user "
			." FROM ".MAIN_DB_PREFIX."scrumproject_scrumsprintuser sUser "
			." WHERE sUser.fk_scrum_sprint = ".intval($this->id)
		;

		$usersAffectedList = $this->db->getRows($sql);

		if($usersAffectedList === false){
			$this->error= $this->db->error();
			return false;
		}

		$TUsersIds = array();

		if($usersAffectedList){
			foreach ($usersAffectedList as $objUsers){
				$TUsersIds[] = $objUsers->fk_user;
			}
		}

		return $TUsersIds;
	}


	/**
	 *
	 * @return int
	 */
	public function calcUsPlannedInList($ref_code = 'done'){

		if(empty($this->fk_advkanban) ){
			return 0;
		}

		// TODO : changer les calculs et les baser sur le status des US et non les colonnes du kanban si les statuts des us refont leurs apparition

		$sql = /** @lang MySQL */ "SELECT SUM(usp.qty_planned) sumPlanned "
			." FROM ".MAIN_DB_PREFIX."scrumproject_scrumuserstorysprint usp "
			." JOIN ".MAIN_DB_PREFIX."advancedkanban_advkanbancard c ON (c.fk_element = usp.rowid AND c.element_type = 'scrumproject_scrumuserstorysprint' )"
			." WHERE  usp.fk_scrum_sprint = ".intval($this->id)
			." AND  c.fk_advkanbanlist IN (SELECT l.rowid FROM ".MAIN_DB_PREFIX."advancedkanban_advkanbanlist l WHERE l.ref_code = '".$this->db->escape($ref_code)."')  ";

		$obj = $this->db->getRow($sql);
		if($obj){
			return doubleval($obj->sumPlanned);
		}

		return false;
	}

	/**
	 * Calcule et retourne un résumé de la progression par Utilisateur
	 *
	 * @param int $userId
	 * @return false|stdClass
	 */
	public function calcUserProgress($userId){

		$userId = intval($userId);

		if($userId<=0){
			return false;
		}

		if(!class_exists('ScrumTask')){ require_once __DIR__ .'/scrumtask.class.php';}
		if(!class_exists('ScrumSprintUser')){ require_once __DIR__ .'/scrumsprintuser.class.php';}

//		La requette SQL génères des erreurs et trop d'aproximation
//		$sql = /** @lang MySQL */
//			"SELECT SUM(ptt.task_duration)  sumTimeSpent, SUM(st.qty_planned) sumTimePlanned,   "
//			." SUM(CASE WHEN st.status = ".ScrumTask::STATUS_DONE." THEN st.qty_planned ELSE 0 END) AS sumTimeDone "
//			." FROM ".MAIN_DB_PREFIX."projet_task_time ptt "
//			." JOIN ".MAIN_DB_PREFIX."scrumproject_scrumtask_projet_task_time st_ptt ON(st_ptt.fk_projet_task_time = ptt.rowid) "
//			." JOIN ".MAIN_DB_PREFIX."scrumproject_scrumtask st ON(st.rowid = st_ptt.fk_scrumproject_scrumtask) "
//			." JOIN ".MAIN_DB_PREFIX."scrumproject_scrumuserstorysprint USsprint  ON(USsprint.rowid = st.fk_scrum_user_story_sprint) "
//			." WHERE "
//			." ptt.fk_user = ".$userId
//			." AND USsprint.fk_scrum_sprint = ".intval($this->id)
//		;
//
//
//		$item = $this->db->getRow($sql);

		$item = new stdClass();
		$item->sumTimeSpent = 0;
		$item->sumTimePlanned = 0;
		$item->sumTimeDone = 0;


		// Extraction des tâches effectuées par le USER basé sur les temps saisis
		if(version_compare(DOL_VERSION, '18.0.0', '<')) {
			$sql = /** @lang MySQL */
				"SELECT DISTINCT USsprint.rowid USsprintId ".
				" FROM ".MAIN_DB_PREFIX."projet_task_time ptt ".
				" JOIN ".MAIN_DB_PREFIX."scrumproject_scrumtask_projet_task_time st_ptt ON(st_ptt.fk_projet_task_time = ptt.rowid) ".
				" JOIN ".MAIN_DB_PREFIX."scrumproject_scrumtask st ON(st.rowid = st_ptt.fk_scrumproject_scrumtask) ".
				" JOIN ".MAIN_DB_PREFIX."scrumproject_scrumuserstorysprint USsprint  ON(USsprint.rowid = st.fk_scrum_user_story_sprint) ".
				" WHERE "." ptt.fk_user = ".$userId." AND USsprint.fk_scrum_sprint = ".intval($this->id);
		}
		else {
				$sql = /** @lang MySQL */
				"SELECT DISTINCT USsprint.rowid USsprintId ".
				" FROM ".MAIN_DB_PREFIX."element_time ptt ".
				" JOIN ".MAIN_DB_PREFIX."scrumproject_scrumtask_projet_task_time st_ptt ON(st_ptt.fk_projet_task_time = ptt.rowid AND ptt.elementtype = 'task') ".
				" JOIN ".MAIN_DB_PREFIX."scrumproject_scrumtask st ON(st.rowid = st_ptt.fk_scrumproject_scrumtask) ".
				" JOIN ".MAIN_DB_PREFIX."scrumproject_scrumuserstorysprint USsprint  ON(USsprint.rowid = st.fk_scrum_user_story_sprint) ".
				" WHERE "." ptt.fk_user = ".$userId." AND USsprint.fk_scrum_sprint = ".intval($this->id);
		}

		$res = $this->db->query($sql);
		if ($res) {
			if ($this->db->num_rows($res) > 0) {
				while ($obj = $this->db->fetch_object($res)) {
					// TRAITEMENT DE CHAQUE USER STORY PLANNIFIEE
					$scrumTaskStatic = new ScrumTask($this->db);
					$TScrumTasks = $scrumTaskStatic->fetchAll('', '', 0, 0, array('fk_scrum_user_story_sprint' => $obj->USsprintId));
					if(is_array($TScrumTasks) && !empty($TScrumTasks)){
						foreach ($TScrumTasks as $scrumTask){
							$resultP = $scrumTask->calcUserProgress($userId);
							if($resultP === false){
								$this->error = 'Error calcUserProgress';
								return false;
							}

							$item->sumTimeSpent+= $resultP->sumTimeSpent;
							$item->sumTimePlanned+= $resultP->sumTimePlanned;
							$item->sumTimeDone+= $resultP->sumTimeDone;

						}
					}
				}
			}
		}



		if($item === false){
			$this->error= $this->db->error();
			return false;
		}

		$item->fk_user = $userId;
		$item->sumTimeSpent = $item->sumTimeSpent;

		$item->userQtyAvailability 	= 0;
		$item->userAvailabilityRate = 0;
		$item->userQtyVelocity 		= 0;

		$item->userRemainingWorkDays = 0;
		$item->userNotPlannedLeaveDays = 0;

		$sprintUser = new ScrumSprintUser($this->db);
		if($sprintUser->fetchFromSprintAndUser(intval($this->id), intval($item->fk_user)) > 0){
			$item->userQtyAvailability 	= $sprintUser->qty_availability;
			$item->userAvailabilityRate = $sprintUser->availability_rate;
			$item->userQtyVelocity 		= $sprintUser->qty_velocity;

			// calcule des heures et jours restant disponibles
			$item->userRemainingWorkDays	= $sprintUser->getRemainingWorkDays();
			if($item->userRemainingWorkDays>=0){
				$item->userRemainingWorkHours	= $sprintUser->convertWorkingDayToHours($item->userRemainingWorkDays);
			}else{
				$item->userRemainingWorkHours	= -1;
			}

			// Calcules des heures et jours perdus suite à un arrêt de travail imprévu
			$item->userNotPlannedLeaveDays	= $sprintUser->getLeaveDays(true);
			if($item->userNotPlannedLeaveDays>=0){
				$item->userNotPlannedLeaveHours	= $sprintUser->convertWorkingDayToHours($item->userNotPlannedLeaveDays);
			}else{
				$item->userNotPlannedLeaveHours	= -1;
			}
		}

		return $item;
	}

//	/**
//	 * @return void
//	 */
//	public function getUsersIdAffectedToScrumTask(){
//		TODO je garde la query au chaud pour justement travailler sur une extraction des utilisateurs affecté à une card ou autre éléménent
//		  c'était à la base la requete de calcUserProgress mais ce n'était pas bon car se basait justement que sur les contacts
//		$sql = /** @lang MySQL */
//			"SELECT ec.fk_socpeople fk_user, SUM(st.qty_consumed) sumTimeSpent, SUM(st.qty_planned) sumTimePlanned,   "
//			." SUM(CASE WHEN st.status = ".ScrumTask::STATUS_DONE." THEN st.qty_planned ELSE 0 END) AS sumTimeDone "
//			." FROM ".MAIN_DB_PREFIX."scrumproject_scrumtask st "
//			." JOIN ".MAIN_DB_PREFIX."scrumproject_scrumuserstorysprint usp ON(st.fk_scrum_user_story_sprint = usp.rowid) "
//			." LEFT JOIN ".MAIN_DB_PREFIX."element_contact ec ON(ec.element_id = st.rowid) "
//			." LEFT JOIN ".MAIN_DB_PREFIX."c_type_contact tc ON(ec.fk_c_type_contact = tc.rowid AND tc.element = 'scrumproject_scrumtask') "
//			." WHERE 1 = 1  "
//			." AND usp.fk_scrum_sprint = ".intval($this->id)
//			." AND tc.source = 'internal'";
//	}


}
