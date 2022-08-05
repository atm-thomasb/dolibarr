<?php
/* Copyright (C) 2017  Laurent Destailleur <eldy@users.sourceforge.net>
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
 * \file        class/scrumuserstorysprint.class.php
 * \ingroup     scrumproject
 * \brief       This file is a CRUD class file for ScrumUserStorySprint (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

require_once __DIR__ .'/scrumuserstory.class.php';
require_once __DIR__ .'/scrumsprint.class.php';
require_once __DIR__ . '/commonObjectQuickTools.trait.php';
//require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
//require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

/**
 * Class for ScrumUserStorySprint
 */
class ScrumUserStorySprint extends CommonObject
{

	use CommonObjectQuickTools;

	/**
	 * @var string ID of module.
	 */
	public $module = 'scrumproject';

	/**
	 * @var string ID to identify managed object.
	 */
	public $element = 'scrumproject_scrumuserstorysprint';

	/**
	 * @var string Name of table without prefix where object is stored. This is also the key used for extrafields management.
	 */
	public $table_element = 'scrumproject_scrumuserstorysprint';

	/**
	 * @var int  Does this object support multicompany module ?
	 * 0=No test on entity, 1=Test with field entity, 'field@table'=Test with link by field@table
	 */
	public $ismultientitymanaged = 0;

	/**
	 * @var int  Does object support extrafields ? 0=No, 1=Yes
	 */
	public $isextrafieldmanaged = 1;

	/**
	 * @var string String with name of icon for scrumuserstorysprint. Must be the part after the 'object_' into object_scrumuserstorysprint.png
	 */
	public $picto = 'scrumuserstorysprint@scrumproject';


	const STATUS_DRAFT = 0;
	const STATUS_VALIDATED = 1;
	const STATUS_CANCELED = 9;


	/**
	 *  'type' field format ('integer', 'integer:ObjectClass:PathToClass[:AddCreateButtonOrNot[:Filter[:Sortfield]]]', 'sellist:TableName:LabelFieldName[:KeyFieldName[:KeyFieldParent[:Filter[:Sortfield]]]]', 'varchar(x)', 'double(24,8)', 'real', 'price', 'text', 'text:none', 'html', 'date', 'datetime', 'timestamp', 'duration', 'mail', 'phone', 'url', 'password')
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
	 *  'isameasure' must be set to 1 or 2 if field can be used for measure. Field type must be summable like integer or double(24,8). Use 1 in most cases, or 2 if you don't want to see the column total into list (for example for percentage)
	 *  'css' and 'cssview' and 'csslist' is the CSS style to use on field. 'css' is used in creation and update. 'cssview' is used in view mode. 'csslist' is used for columns in lists. For example: 'css'=>'minwidth300 maxwidth500 widthcentpercentminusx', 'cssview'=>'wordbreak', 'csslist'=>'tdoverflowmax200'
	 *  'help' is a 'TranslationString' to use to show a tooltip on field. You can also use 'TranslationString:keyfortooltiponlick' for a tooltip on click.
	 *  'showoncombobox' if value of the field must be visible into the label of the combobox that list record
	 *  'disabled' is 1 if we want to have the field locked by a 'disabled' attribute. In most cases, this is never set into the definition of $fields into class, but is set dynamically by some part of code.
	 *  'arrayofkeyval' to set a list of values if type is a list of predefined values. For example: array("0"=>"Draft","1"=>"Active","-1"=>"Cancel"). Note that type can be 'integer' or 'varchar'
	 *  'autofocusoncreate' to have field having the focus on a create form. Only 1 field should have this property set to 1.
	 *  'comment' is not used. You can store here any text of your choice. It is not used by application.
	 *	'validate' is 1 if need to validate with $this->validateField()
	 *  'copytoclipboard' is 1 or 2 to allow to add a picto to copy value into clipboard (1=picto after label, 2=picto after value)
	 *
	 *  Note: To have value dynamic, you can set value to 0 in definition and edit the value on the fly into the constructor.
	 */

	// BEGIN MODULEBUILDER PROPERTIES
	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	public $fields=array(
		'rowid' => array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>'1', 'position'=>1, 'notnull'=>1, 'visible'=>2, 'noteditable'=>'1', 'index'=>1, 'css'=>'left', 'comment'=>"Id", 'showoncombobox' => 1),
		'fk_scrum_user_story' => array('type'=>'integer:ScrumUserStory:scrumproject/class/scrumuserstory.class.php:1', 'label'=>'ScrumUserStory', 'enabled'=>'1', 'position'=>52, 'notnull'=>1, 'visible'=>-1, 'index'=>1, 'foreignkey'=>'scrumproject_scrumuserstory.rowid', 'validate'=>'1',),
		'fk_scrum_sprint' => array('type'=>'integer:ScrumSprint:scrumproject/class/scrumsprint.class.php:1', 'label'=>'ScrumSprint', 'enabled'=>'1', 'position'=>52, 'notnull'=>1, 'visible'=>-1, 'index'=>1, 'foreignkey'=>'scrumproject_scrumsprint.rowid', 'validate'=>'1',),
		'business_value' => array('type'=>'integer', 'label'=>'BusinessValue', 'enabled'=>'1', 'position'=>52, 'notnull'=>1, 'visible'=>-1, 'default'=>'50', 'index'=>1, 'validate'=>'1',),
		'qty_planned' => array('type'=>'real', 'label'=>'QtyPlanned', 'enabled'=>'1', 'position'=>100, 'notnull'=>1, 'visible'=>1, 'default'=>'0', 'isameasure'=>'1', 'css'=>'maxwidth75imp',),
		'qty_consumed' => array('type'=>'real', 'label'=>'QtyConsumed', 'enabled'=>'1', 'position'=>105, 'notnull'=>0, 'visible'=>4, 'noteditable'=>'1', 'default'=>'0', 'isameasure'=>'1', 'css'=>'maxwidth75imp',),
		'qty_done' => array('type'=>'real', 'label'=>'QtyDone', 'enabled'=>'1', 'position'=>110, 'notnull'=>0, 'visible'=>4, 'noteditable'=>'1', 'default'=>'0', 'isameasure'=>'1', 'css'=>'maxwidth75imp',),
		'label' => array('type'=>'varchar(255)', 'label'=>'Label', 'enabled'=>'1', 'position'=>30, 'notnull'=>0, 'visible'=>1, 'searchall'=>1, 'css'=>'minwidth300', 'cssview'=>'wordbreak', 'help'=>"Help text", 'showoncombobox'=>'2', 'validate'=>'1',),
		'description' => array('type'=>'html', 'label'=>'Description', 'enabled'=>'1', 'position'=>120, 'notnull'=>0, 'visible'=>3, 'validate'=>'1',),
		'note_public' => array('type'=>'html', 'label'=>'NotePublic', 'enabled'=>'1', 'position'=>130, 'notnull'=>0, 'visible'=>0, 'validate'=>'1',),
		'note_private' => array('type'=>'html', 'label'=>'NotePrivate', 'enabled'=>'1', 'position'=>140, 'notnull'=>0, 'visible'=>0, 'validate'=>'1',),
		'date_creation' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>'1', 'position'=>500, 'notnull'=>1, 'visible'=>-2,),
		'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>'1', 'position'=>501, 'notnull'=>0, 'visible'=>-2,),
		'fk_user_creat' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserAuthor', 'enabled'=>'1', 'position'=>510, 'notnull'=>1, 'visible'=>-2, 'foreignkey'=>'user.rowid',),
		'fk_user_modif' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserModif', 'enabled'=>'1', 'position'=>511, 'notnull'=>-1, 'visible'=>-2,),
		'last_main_doc' => array('type'=>'varchar(255)', 'label'=>'LastMainDoc', 'enabled'=>'1', 'position'=>600, 'notnull'=>0, 'visible'=>0,),
		'import_key' => array('type'=>'varchar(14)', 'label'=>'ImportId', 'enabled'=>'1', 'position'=>1000, 'notnull'=>-1, 'visible'=>-2,),
		'model_pdf' => array('type'=>'varchar(255)', 'label'=>'Model pdf', 'enabled'=>'1', 'position'=>1010, 'notnull'=>-1, 'visible'=>0,),
	);
	public $rowid;
	public $fk_scrum_user_story;
	public $fk_scrum_sprint;
	public $business_value;
	public $qty_planned;
	public $qty_consumed;
	public $qty_done;
	public $label;
	public $description;
	public $note_public;
	public $note_private;
	public $date_creation;
	public $tms;
	public $fk_user_creat;
	public $fk_user_modif;
	public $last_main_doc;
	public $import_key;
	public $model_pdf;
	// END MODULEBUILDER PROPERTIES

	/**
	 * valeur dynamique non stocké en base ,  recupérée par $this->calcTimeTaskPlanned()
	 * @var $qty_task_planned
	 */
	public $qty_task_planned;

	// If this object has a subtable with lines

	// /**
	//  * @var string    Name of subtable line
	//  */
	// public $table_element_line = 'scrumproject_scrumuserstorysprintline';

	// /**
	//  * @var string    Field with ID of parent key if this object has a parent
	//  */
	// public $fk_element = 'fk_scrumuserstorysprint';

	// /**
	//  * @var string    Name of subtable class that manage subtable lines
	//  */
	// public $class_element_line = 'ScrumUserStorySprintline';

	// /**
	//  * @var array	List of child tables. To test if we can delete object.
	//  */
	// protected $childtables = array();

	// /**
	//  * @var array    List of child tables. To know object to delete on cascade.
	//  *               If name matches '@ClassNAme:FilePathClass;ParentFkFieldName' it will
	//  *               call method deleteByParentField(parentId, ParentFkFieldName) to fetch and delete child object
	//  */
	// protected $childtablesoncascade = array('scrumproject_scrumuserstorysprintdet');

	// /**
	//  * @var ScrumUserStorySprintLine[]     Array of subtable lines
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

//		if (empty($conf->global->MAIN_SHOW_TECHNICAL_ID) && isset($this->fields['rowid'])) {
//			$this->fields['rowid']['visible'] = 0;
//		}
		if (empty($conf->multicompany->enabled) && isset($this->fields['entity'])) {
			$this->fields['entity']['enabled'] = 0;
		}

		// Example to show how to set values of fields definition dynamically
		/*if ($user->rights->scrumproject->scrumuserstorysprint->read) {
			$this->fields['myfield']['visible'] = 1;
			$this->fields['myfield']['noteditable'] = 0;
		}*/

		// Unset fields that are disabled
		foreach ($this->fields as $key => $val) {
			if (isset($val['enabled']) && empty($val['enabled'])) {
				unset($this->fields[$key]);
			}
		}

		// Translate some data of arrayofkeyval
		if (is_object($langs)) {
			foreach ($this->fields as $key => $val) {
				if (!empty($val['arrayofkeyval']) && is_array($val['arrayofkeyval'])) {
					foreach ($val['arrayofkeyval'] as $key2 => $val2) {
						$this->fields[$key]['arrayofkeyval'][$key2] = $langs->trans($val2);
					}
				}
			}
		}
	}

	/**
	 * Check if a foreignKey exist
	 * @return bool
	 */
	public function canBeDeleted(){
		$obj = $this->db->getRow('SELECT COUNT(rowid) nb FROM '.MAIN_DB_PREFIX.'scrumproject_scrumtask WHERE fk_scrum_user_story_sprint = '.$this->id);
		if($obj !== false){
			return !(intval($obj->nb)>0);
		}

		return false;
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
		$resultcreate = $this->createCommon($user, $notrigger);

		if($resultcreate>0 && !$notrigger){
			$scrumUserStory = new ScrumUserStory($this->db);
			if($scrumUserStory->fetch($this->fk_scrum_user_story)>0){
				if($scrumUserStory->setPlanned($user) < 0){
					$this->error = $scrumUserStory->error;
					$this->errors = $scrumUserStory->errors;
					return -1;
				}
			}

			if($this->refreshSprintQuantities($user)<0){
				return -1;
			}

			// Kanban
			if(!class_exists('ScrumKanban')){ require_once __DIR__ .'/scrumkanban.class.php'; }
			if(!class_exists('ScrumKanbanList')){ require_once __DIR__ .'/scrumkanbanlist.class.php'; }
			if(!class_exists('ScrumCard')){ require_once __DIR__ .'/scrumcard.class.php'; }

			$staticScrumKanban = new ScrumKanban($this->db);
			$TScrumKanban = $staticScrumKanban->fetchAll('','', 1, 0, array('fk_scrum_sprint' => $this->fk_scrum_sprint));
			if(is_array($TScrumKanban) && !empty($TScrumKanban)){
				foreach ($TScrumKanban as $scrumkanban){
					$staticScrumKanbanList = new ScrumKanbanList($this->db);
					$TScrumKanbanList = $staticScrumKanbanList->fetchAll('','', 1, 0, array('customsql' => 'fk_scrum_kanban = '. intval($scrumkanban->id) .' AND  ref_code = \'backlog\''));

					if(!empty($TScrumKanbanList) && is_array($TScrumKanbanList)){
						$backLogList = reset($TScrumKanbanList);

						$card = new ScrumCard($this->db);
						$us = scrumProjectGetObjectByElement('scrumproject_scrumuserstory', $this->fk_scrum_user_story);
						if($us){
							$card->label = $us->label;
						}
						$card->fk_element = $this->id;
						$card->element_type = $this->element;
						$card->fk_scrum_kanbanlist = $backLogList->id;
						$card->fk_rank = $backLogList->getMaxRankOfKanBanListItems();
						$res = $card->create($user, $notrigger);
						if($res<=0){
							$this->errors[] = $card->errorsToString();
							$resultcreate = $res;
						}
					}
				}
			}
			else{
				// TODO gérer le cas des erreurs : passer ce code dans les triggers ?
			}

		}

		return $resultcreate;
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
		if ($result > 0 && !empty($object->table_element_line)) {
			$object->fetchLines();
		}

		// get lines so they will be clone
		//foreach($this->lines as $line)
		//	$line->fetch_optionals();

		// Reset some properties
		unset($object->id);
		unset($object->fk_user_creat);
		unset($object->import_key);

		// Clear fields
		if (property_exists($object, 'ref')) {
			$object->ref = empty($this->fields['ref']['default']) ? "Copy_Of_".$object->ref : $this->fields['ref']['default'];
		}
		if (property_exists($object, 'label')) {
			$object->label = empty($this->fields['label']['default']) ? $langs->trans("CopyOf")." ".$object->label : $this->fields['label']['default'];
		}
		if (property_exists($object, 'status')) {
			$object->status = self::STATUS_DRAFT;
		}
		if (property_exists($object, 'date_creation')) {
			$object->date_creation = dol_now();
		}
		if (property_exists($object, 'date_modification')) {
			$object->date_modification = null;
		}
		// ...
		// Clear extrafields that are unique
		if (is_array($object->array_options) && count($object->array_options) > 0) {
			$extrafields->fetch_name_optionals_label($this->table_element);
			foreach ($object->array_options as $key => $option) {
				$shortkey = preg_replace('/options_/', '', $key);
				if (!empty($extrafields->attributes[$this->table_element]['unique'][$shortkey])) {
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

		if (!$error) {
			// copy internal contacts
			if ($this->copy_linked_contact($object, 'internal') < 0) {
				$error++;
			}
		}

		if (!$error) {
			// copy external contacts if same company
			if (!empty($object->socid) && property_exists($this, 'fk_soc') && $this->fk_soc == $object->socid) {
				if ($this->copy_linked_contact($object, 'external') < 0) {
					$error++;
				}
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
		if ($result > 0 && !empty($this->table_element_line)) {
			$this->fetchLines();
		}
		return $result;
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

		$sql = "SELECT ";
		$sql .= $this->getFieldList('t');
		$sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element." as t";
		if (isset($this->ismultientitymanaged) && $this->ismultientitymanaged == 1) {
			$sql .= " WHERE t.entity IN (".getEntity($this->table_element).")";
		} else {
			$sql .= " WHERE 1 = 1";
		}
		// Manage filter
		$sqlwhere = array();
		if (count($filter) > 0) {
			foreach ($filter as $key => $value) {
				if ($key == 't.rowid') {
					$sqlwhere[] = $key." = ".((int) $value);
				} elseif (in_array($this->fields[$key]['type'], array('date', 'datetime', 'timestamp'))) {
					$sqlwhere[] = $key." = '".$this->db->idate($value)."'";
				} elseif ($key == 'customsql') {
					$sqlwhere[] = $value;
				} elseif (strpos($value, '%') === false) {
					$sqlwhere[] = $key." IN (".$this->db->sanitize($this->db->escape($value)).")";
				} else {
					$sqlwhere[] = $key." LIKE '%".$this->db->escape($value)."%'";
				}
			}
		}
		if (count($sqlwhere) > 0) {
			$sql .= " AND (".implode(" ".$filtermode." ", $sqlwhere).")";
		}

		if (!empty($sortfield)) {
			$sql .= $this->db->order($sortfield, $sortorder);
		}
		if (!empty($limit)) {
			$sql .= $this->db->plimit($limit, $offset);
		}

		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			$i = 0;
			while ($i < ($limit ? min($limit, $num) : $num)) {
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

		$result = $this->updateCommon($user, $notrigger);
		if($result>0 && !$notrigger){
			if($this->refreshSprintQuantities($user)<0){
				return -1;
			}
		}

		return $result;
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
		$delResult =  $this->deleteCommon($user, $notrigger);

		if($this->refreshSprintQuantities($user)<0){
			return -1;
		}

		return $delResult;
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
		if ($this->status < 0) {
			$this->error = 'ErrorDeleteLineNotAllowedByObjectStatus';
			return -2;
		}

		return $this->deleteLineCommon($user, $idline, $notrigger);
	}

//
//	/**
//	 *	Validate object
//	 *
//	 *	@param		User	$user     		User making status change
//	 *  @param		int		$notrigger		1=Does not execute triggers, 0= execute triggers
//	 *	@return  	int						<=0 if OK, 0=Nothing done, >0 if KO
//	 */
//	public function validate($user, $notrigger = 0)
//	{
//		global $conf, $langs;
//
//		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
//
//		$error = 0;
//
//		// Protection
//		if ($this->status == self::STATUS_VALIDATED) {
//			dol_syslog(get_class($this)."::validate action abandonned: already validated", LOG_WARNING);
//			return 0;
//		}
//
//		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->scrumproject->scrumuserstorysprint->write))
//		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->scrumproject->scrumuserstorysprint->scrumuserstorysprint_advance->validate))))
//		 {
//		 $this->error='NotEnoughPermissions';
//		 dol_syslog(get_class($this)."::valid ".$this->error, LOG_ERR);
//		 return -1;
//		 }*/
//
//		$now = dol_now();
//
//		$this->db->begin();
//
//		// Define new ref
//		if (!$error && (preg_match('/^[\(]?PROV/i', $this->ref) || empty($this->ref))) { // empty should not happened, but when it occurs, the test save life
//			$num = $this->getNextNumRef();
//		} else {
//			$num = $this->ref;
//		}
//		$this->newref = $num;
//
//		if (!empty($num)) {
//			// Validate
//			$sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element;
//			$sql .= " SET status = ".self::STATUS_VALIDATED;
//			if (!empty($this->fields['date_validation'])) {
//				$sql .= ", date_validation = '".$this->db->idate($now)."'";
//			}
//			if (!empty($this->fields['fk_user_valid'])) {
//				$sql .= ", fk_user_valid = ".((int) $user->id);
//			}
//			$sql .= " WHERE rowid = ".((int) $this->id);
//
//			dol_syslog(get_class($this)."::validate()", LOG_DEBUG);
//			$resql = $this->db->query($sql);
//			if (!$resql) {
//				dol_print_error($this->db);
//				$this->error = $this->db->lasterror();
//				$error++;
//			}
//
//			if (!$error && !$notrigger) {
//				// Call trigger
//				$result = $this->call_trigger('SCRUMUSERSTORYSPRINT_VALIDATE', $user);
//				if ($result < 0) {
//					$error++;
//				}
//				// End call triggers
//			}
//		}
//
//		if (!$error) {
//			$this->oldref = $this->id;
//		}
//
//		// Set new ref and current status
//		if (!$error) {
//			$this->ref = $num;
//			$this->status = self::STATUS_VALIDATED;
//		}
//
//		if (!$error) {
//			$this->db->commit();
//			return 1;
//		} else {
//			$this->db->rollback();
//			return -1;
//		}
//	}


//	/**
//	 *	Set draft status
//	 *
//	 *	@param	User	$user			Object user that modify
//	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
//	 *	@return	int						<0 if KO, >0 if OK
//	 */
//	public function setDraft($user, $notrigger = 0)
//	{
//		// Protection
//		if ($this->status <= self::STATUS_DRAFT) {
//			return 0;
//		}
//
//		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->scrumproject->write))
//		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->scrumproject->scrumproject_advance->validate))))
//		 {
//		 $this->error='Permission denied';
//		 return -1;
//		 }*/
//
//		return $this->setStatusCommon($user, self::STATUS_DRAFT, $notrigger, 'SCRUMUSERSTORYSPRINT_UNVALIDATE');
//	}
//
//	/**
//	 *	Set cancel status
//	 *
//	 *	@param	User	$user			Object user that modify
//	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
//	 *	@return	int						<0 if KO, 0=Nothing done, >0 if OK
//	 */
//	public function cancel($user, $notrigger = 0)
//	{
//		// Protection
//		if ($this->status != self::STATUS_VALIDATED) {
//			return 0;
//		}
//
//		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->scrumproject->write))
//		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->scrumproject->scrumproject_advance->validate))))
//		 {
//		 $this->error='Permission denied';
//		 return -1;
//		 }*/
//
//		return $this->setStatusCommon($user, self::STATUS_CANCELED, $notrigger, 'SCRUMUSERSTORYSPRINT_CANCEL');
//	}
//
//	/**
//	 *	Set back to validated status
//	 *
//	 *	@param	User	$user			Object user that modify
//	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
//	 *	@return	int						<0 if KO, 0=Nothing done, >0 if OK
//	 */
//	public function reopen($user, $notrigger = 0)
//	{
//		// Protection
//		if ($this->status != self::STATUS_CANCELED) {
//			return 0;
//		}
//
//		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->scrumproject->write))
//		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->scrumproject->scrumproject_advance->validate))))
//		 {
//		 $this->error='Permission denied';
//		 return -1;
//		 }*/
//
//		return $this->setStatusCommon($user, self::STATUS_VALIDATED, $notrigger, 'SCRUMUSERSTORYSPRINT_REOPEN');
//	}

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

		if (!empty($conf->dol_no_mouse_hover)) {
			$notooltip = 1; // Force disable tooltips
		}

		$result = '';

		$tooltip = img_picto('', 'object_'.$this->picto).' <u>'.$langs->trans("ScrumUserStorySprint").'</u>';
		if (isset($this->status)) {
			$tooltip .= ' '.$this->getLibStatut(5);
		}
		$tooltip .= '<br>';
		$tooltip .= '<b>'.$langs->trans('Ref').':</b> '.$this->ref;

		$url = dol_buildpath('/scrumproject/scrumuserstorysprint_card.php', 1).'?id='.$this->id;

		if ($option != 'nolink') {
			// Add param to save lastsearch_values or not
			$add_save_lastsearch_values = ($save_lastsearch_value == 1 ? 1 : 0);
			if ($save_lastsearch_value == -1 && preg_match('/list\.php/', $_SERVER["PHP_SELF"])) {
				$add_save_lastsearch_values = 1;
			}
			if ($url && $add_save_lastsearch_values) {
				$url .= '&save_lastsearch_values=1';
			}
		}

		$linkclose = '';
		if (empty($notooltip)) {
			if (!empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER)) {
				$tooltip = $langs->trans("ShowScrumUserStorySprint");
				$linkclose .= ' alt="'.dol_escape_htmltag($tooltip, 1).'"';
			}
			$linkclose .= ' title="'.dol_escape_htmltag($tooltip, 1).'"';
			$linkclose .= ' class="classfortooltip'.($morecss ? ' '.$morecss : '').'"';
		} else {
			$linkclose = ($morecss ? ' class="'.$morecss.'"' : '');
		}


		$linkHtml = '';
		if (empty($this->showphoto_on_popup)) {
			if ($withpicto) {
				$linkHtml .= img_object(($notooltip ? '' : $tooltip), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
			}
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
					if (empty($conf->global->{strtoupper($module.'_'.$class).'_FORMATLISTPHOTOSASUSERS'})) {
						$linkHtml .= '<div class="floatleft inline-block valignmiddle divphotoref"><div class="photoref"><img class="photo'.$module.'" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($pathtophoto).'"></div></div>';
					} else {
						$linkHtml .= '<div class="floatleft inline-block valignmiddle divphotoref"><img class="photouserphoto userphoto" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($pathtophoto).'"></div>';
					}

					$linkHtml .= '</div>';
				} else {
					$linkHtml .= img_object(($notooltip ? '' : $tooltip), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
				}
			}
		}

		if ($withpicto != 2) {
			$linkHtml .= $this->ref;
		}


		if ($option == 'nolink' || empty($url)) {
			$tagName =  'span';
		} else {
			$tagName =  'a';
		}

		$moreAttr = '';
		if(!$option == 'nolink' && !empty($url)){
			$moreAttr = ' href="'.$url.'" ';
		}

		$result .= '<'.$tagName.' '.$moreAttr . $linkclose.'>' . $linkHtml.'</'.$tagName.'>';


		global $action, $hookmanager;
		$hookmanager->initHooks(array('scrumuserstorysprintdao'));
		$parameters = array('id'=>$this->id, 'getnomurl'=>$result);
		$reshook = $hookmanager->executeHooks('getNomUrl', $parameters, $this, $action); // Note that $action and $object may have been modified by some hooks
		if ($reshook > 0) {
			$result = $hookmanager->resPrint;
		} else {
			$result .= $hookmanager->resPrint;
		}

		return $result;
	}

	/**
	 *  Return the label of the status
	 *
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return	string 			       Label of status
	 */
	public function getLabelStatus($mode = 0)
	{
		return $this->LibStatut($this->status, $mode);
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
		if (empty($this->labelStatus) || empty($this->labelStatusShort)) {
			global $langs;
			//$langs->load("scrumproject@scrumproject");
			$this->labelStatus[self::STATUS_DRAFT] = $langs->transnoentitiesnoconv('Draft');
			$this->labelStatus[self::STATUS_VALIDATED] = $langs->transnoentitiesnoconv('Enabled');
			$this->labelStatus[self::STATUS_CANCELED] = $langs->transnoentitiesnoconv('Disabled');
			$this->labelStatusShort[self::STATUS_DRAFT] = $langs->transnoentitiesnoconv('Draft');
			$this->labelStatusShort[self::STATUS_VALIDATED] = $langs->transnoentitiesnoconv('Enabled');
			$this->labelStatusShort[self::STATUS_CANCELED] = $langs->transnoentitiesnoconv('Disabled');
		}

		$statusType = 'status'.$status;
		//if ($status == self::STATUS_VALIDATED) $statusType = 'status1';
		if ($status == self::STATUS_CANCELED) {
			$statusType = 'status6';
		}

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
		$sql = "SELECT rowid, date_creation as datec, tms as datem,";
		$sql .= " fk_user_creat, fk_user_modif";
		$sql .= " FROM ".MAIN_DB_PREFIX.$this->table_element." as t";
		$sql .= " WHERE t.rowid = ".((int) $id);

		$result = $this->db->query($sql);
		if ($result) {
			if ($this->db->num_rows($result)) {
				$obj = $this->db->fetch_object($result);
				$this->id = $obj->rowid;
				if (!empty($obj->fk_user_author)) {
					$cuser = new User($this->db);
					$cuser->fetch($obj->fk_user_author);
					$this->user_creation = $cuser;
				}

				if (!empty($obj->fk_user_valid)) {
					$vuser = new User($this->db);
					$vuser->fetch($obj->fk_user_valid);
					$this->user_validation = $vuser;
				}

				if (!empty($obj->fk_user_cloture)) {
					$cluser = new User($this->db);
					$cluser->fetch($obj->fk_user_cloture);
					$this->user_cloture = $cluser;
				}

				$this->date_creation     = $this->db->jdate($obj->datec);
				$this->date_modification = $this->db->jdate($obj->datem);
				$this->date_validation   = $this->db->jdate($obj->datev);
			}

			$this->db->free($result);
		} else {
			dol_print_error($this->db);
		}
	}



	/**
	 * get this object formatted for ajax ans json
	 * @return stdClass
	 */
	public function getScrumKanBanItemObjectStd(){

		$object = new stdClass();
		$object->objectId = $this->id;
		$object->ref= $this->ref;
		$object->type = 'scrum-user-story';// le type dans le kanban tel que getScrumKanBanItemObjectFormatted le fait
		$object->label = $this->label;
		$object->element = $this->element;
		$object->cardUrl = dol_buildpath('/scrumproject/scrumuserstorysprint_card.php',1).'?id='.$this->id;

		$object->status = intval($this->status);
		$object->statusLabel = $this->LibStatut(intval($this->status), 1);
		$object->contactUsersAffected = $this->liste_contact(-1,'internal',1);

		$object->fk_scrum_user_story_sprint = $this->fk_scrum_user_story_sprint;
		$object->fk_scrum_user_story_sprint= $this->fk_scrum_user_story_sprint;
		$object->qty_planned = doubleval($this->qty_planned);
		$object->qty_consumed = doubleval($this->qty_consumed);

		$this->calcTimeTaskPlanned();
		$object->qty_task_planned = doubleval($this->qty_task_planned);

		$object->qty_remain_for_split = 0;
		$qtyConsumeBase = max($this->qty_task_planned, $this->qty_consumed);
		if($this->qty_planned - $qtyConsumeBase > 0){
			$object->qty_remain_for_split = $this->qty_planned - $qtyConsumeBase;
		}


		return $object;
	}



	/**
	 * Permet de spliter l'us carte en scrum task
	 * @param double $qty la quantité de la nouvelle carte
	 * @param string $newCardLabel le libelle de la nouvelle carte
	 * @param ScrumCard $scrumCard
	 * @return bool
	 */
	public function splitCard($qty, $newCardLabel, ScrumCard $scrumCard, User $user ){

		$qty = doubleval($qty);

		if(!class_exists('ScrumTask')){
			require_once __DIR__ . '/scrumtask.class.php';
		}
		if(!class_exists('ScrumCard')){
			require_once __DIR__ . '/scrumcard.class.php';
		}

		$this->calcTimeTaskPlanned();

		// Vérification de la liaison entre ScrumCard et ScrumTask
		if($scrumCard->element_type != $this->element || $scrumCard->fk_element != $this->id ){
			$this->error = 'Error : scrum card not linked';
			$this->errors[] = $this->error;
			return false;
		}


		// Vérification du temps restant
		if($qty > $this->qty_planned - $this->qty_task_planned ){
			$this->error = 'Too much quantity';
			$this->errors[] = $this->error;
			return false;
		}

		// Ajout de la nouvelle ScrumTask
		$newScrumTask = new ScrumTask($this->db);
		$newScrumTask->fk_scrum_user_story_sprint = $this->id;
		$newScrumTask->description = $this->description;

		$newScrumTask->qty_planned = $qty;
		$newScrumTask->label = $newCardLabel;
		if(empty($newCardLabel) || is_array($newCardLabel)){ $newScrumTask->label = $this->label;}

		$resCreate = $newScrumTask->create($user);
		if($resCreate<0){
			$this->error = $newScrumTask->error;
			$this->errors = array_merge($this->errors, $newScrumTask->errors);
			return false;
		}

		// MISE A JOUR DE LA SCRUM TASK QUE L'ON SPLIT
		$this->qty_task_planned-= $qty;

// Bloc deja effectué par  $newScrumTask->create
//		// AJOUT DE LA CARD LIÉE
//		$newScrumCard = new ScrumCard($this->db);
//		if(empty($newCardLabel)){
//			$newScrumCard->label = $this->label;
//		}
//		$newScrumCard->label = $newScrumTask->label;
//		$newScrumCard->fk_element = $newScrumTask->id;
//		$newScrumCard->element_type = $newScrumTask->element;
//		$newScrumCard->fk_scrum_kanbanlist = $scrumCard->fk_scrum_kanbanlist;
//		$newScrumCard->fk_rank = $scrumCard->fk_rank;
//		$res = $newScrumCard->create($user);
//		if($res<=0){
//			$this->error = 'Error creating ScrumCard : '.$newScrumCard->error;
//			$this->errors = array_merge($this->errors, $newScrumCard->errors);
//			return false;
//		}

		return true;
	}

	/**
	 * Initialise object with example values
	 * Id must be 0 if object instance is a specimen
	 *
	 * @return void
	 */
	public function initAsSpecimen()
	{
		// Set here init that are not commonf fields
		// $this->property1 = ...
		// $this->property2 = ...

		$this->initAsSpecimenCommon();
	}

	/**
	 * 	Create an array of lines
	 *
	 * 	@return array|int		array of lines if OK, <0 if KO
	 */
	public function getLinesArray()
	{
		$this->lines = array();

		$objectline = new ScrumUserStorySprintLine($this->db);
		$result = $objectline->fetchAll('ASC', 'position', 0, 0, array('customsql'=>'fk_scrumuserstorysprint = '.((int) $this->id)));

		if (is_numeric($result)) {
			$this->error = $this->error;
			$this->errors = $this->errors;
			return $result;
		} else {
			$this->lines = $result;
			return $this->lines;
		}
	}

	/**
	 *  Returns the reference to the following non used object depending on the active numbering module.
	 *
	 *  @return string      		Object free reference
	 */
	public function getNextNumRef()
	{
		global $langs, $conf;
		$langs->load("scrumproject@scrumproject");

		if (empty($conf->global->SCRUMPROJECT_SCRUMUSERSTORYSPRINT_ADDON)) {
			$conf->global->SCRUMPROJECT_SCRUMUSERSTORYSPRINT_ADDON = 'mod_scrumuserstorysprint_standard';
		}

		if (!empty($conf->global->SCRUMPROJECT_SCRUMUSERSTORYSPRINT_ADDON)) {
			$mybool = false;

			$file = $conf->global->SCRUMPROJECT_SCRUMUSERSTORYSPRINT_ADDON.".php";
			$classname = $conf->global->SCRUMPROJECT_SCRUMUSERSTORYSPRINT_ADDON;

			// Include file with class
			$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);
			foreach ($dirmodels as $reldir) {
				$dir = dol_buildpath($reldir."core/modules/scrumproject/");

				// Load file with numbering class (if found)
				$mybool |= @include_once $dir.$file;
			}

			if ($mybool === false) {
				dol_print_error('', "Failed to include file ".$file);
				return '';
			}

			if (class_exists($classname)) {
				$obj = new $classname();
				$numref = $obj->getNextValue($this);

				if ($numref != '' && $numref != '-1') {
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



	/**
	 * Return HTML string to put an input field into a page
	 * Code very similar with showInputField of extra fields
	 *
	 * @param  array   		$val	       Array of properties for field to show
	 * @param  string  		$key           Key of attribute
	 * @param  string  		$value         Preselected value to show (for date type it must be in timestamp format, for amount or price it must be a php numeric value)
	 * @param  string  		$moreparam     To add more parameters on html input tag
	 * @param  string  		$keysuffix     Prefix string to add into name and id of field (can be used to avoid duplicate names)
	 * @param  string  		$keyprefix     Suffix string to add into name and id of field (can be used to avoid duplicate names)
	 * @param  string|int	$morecss       Value for css to define style/length of field. May also be a numeric.
	 * @return string
	 */
	public function showInputField($val, $key, $value, $moreparam = '', $keysuffix = '', $keyprefix = '', $morecss = 0, $nonewbutton = 0)
	{
		global $conf, $langs, $form, $action;

		if($key == 'rowid'){
			$out = $this->id;
		}
		else
		{
			$out = parent::showInputField($val, $key, $value, $moreparam, $keysuffix, $keyprefix, $morecss, $nonewbutton);
		}

		return $out;
	}



	/**
	 * Return HTML string to show a field into a page
	 * Code very similar with showOutputField of extra fields
	 *
	 * @param  array   $val		       Array of properties of field to show
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
		global $conf, $langs, $form;
		$out = '';
		if ($key == 'status'){
			$out =  $this->getLibStatut(5); // to fix dolibarr using 3 instead of 2
		}
		elseif($key == 'rowid')
		{
			$out = $this->getNomUrl(1);
		}
		else{
			$out = parent::showOutputField($val, $key, $value, $moreparam, $keysuffix, $keyprefix, $morecss);
		}

		return $out;
	}

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
//			$modele = 'standard_scrumuserstorysprint';
//
//			if (!empty($this->model_pdf)) {
//				$modele = $this->model_pdf;
//			} elseif (!empty($conf->global->SCRUMUSERSTORYSPRINT_ADDON_PDF)) {
//				$modele = $conf->global->SCRUMUSERSTORYSPRINT_ADDON_PDF;
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
	 *
	 * @return int
	 */
	public function calcTimeSpent(){

		$sql = /** @lang MySQL */ "SELECT SUM(qty_consumed) sumTimeSpent FROM ".MAIN_DB_PREFIX."scrumproject_scrumtask "
			." WHERE fk_scrum_user_story_sprint = ".intval($this->id);

		$obj = $this->db->getRow($sql);
		if($obj){
			$this->qty_consumed = doubleval($obj->sumTimeSpent);
			return $this->qty_consumed;
		}

		return 0;
	}

	/**
	 *
	 * @return int
	 */
	public function calcTimeTaskPlanned(){

		$sql = /** @lang MySQL */ "SELECT SUM(qty_planned) sumTaskPlanned FROM ".MAIN_DB_PREFIX."scrumproject_scrumtask "
			." WHERE fk_scrum_user_story_sprint = ".intval($this->id);

		$obj = $this->db->getRow($sql);
		if($obj){
			$this->qty_task_planned = doubleval($obj->sumTaskPlanned);
			return $this->qty_task_planned;
		}

		return 0;
	}

	/**
	 * @param User $user
	 * @param      $notrigger
	 * @return int
	 */
	public function updateTimeSpent(User $user, $notrigger = false){
		global $user;

		$error = 0;
		$this->db->begin();

		$this->calcTimeSpent();

		$sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element." SET qty_consumed = '".$this->qty_consumed."' WHERE rowid=".((int) $this->id);

		if($this->db->query($sql)){

			// Triggers
			if (!$error && !$notrigger) {
				// Call triggers
				$result = $this->call_trigger('SCRUMUSERSTORYSPRINT_UPDATE_TIME_SPENT', $user);
				if ($result < 0) {
					$error++;
				} //Do also here what you must do to rollback action if trigger fail
				// End call triggers
			}

			// Commit or rollback
			if ($error) {
				$this->db->rollback();
				return -1;
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
	 * @return void
	 */
	public function refreshSprintQuantities($user){
		if($this->fk_scrum_sprint > 0){
			$sprint = new ScrumSprint($this->db);
			if($sprint->fetch($this->fk_scrum_sprint)>0){
				if($sprint->refreshQuantities($user, true)<0){
					$this->error = $sprint->error;
					$this->errors = array_merge($this->errors, $sprint->errors);
					return -1;
				}
				return 1;
			}
			else{
				$this->error = 'ErrorSprintNotFound';
				$this->errors[] = $this->error;
				return -1;
			}
		}

		return 0;
	}
}
