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
 * \file        class/scrumtask.class.php
 * \ingroup     scrumproject
 * \brief       This file is a CRUD class file for ScrumTask (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
require_once __DIR__ . '/commonObjectQuickTools.trait.php';
require_once __DIR__ . '/scrumuserstorysprint.class.php';
//require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
//require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';

/**
 * Class for ScrumTask
 */
class ScrumTask extends CommonObject
{

	use CommonObjectQuickTools;

	/**
	 * Kanban contacts are sets on this object not in kanban card
	 */
	const OVERRIDE_KANBAN_CARD_CONTACTS = true; // Value doesn't used,  only definition is check

//	/**
//	 * @var string ID of module.
//	 */
//	public $module = 'scrumproject'; // already included in $this->element

	/**
	 * @var string ID to identify managed object.
	 */
	public $element = 'scrumproject_scrumtask';

	/**
	 * @var string Name of table without prefix where object is stored. This is also the key used for extrafields management.
	 */
	public $table_element = 'scrumproject_scrumtask';

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
	 * @var string String with name of icon for scrumtask. Must be the part after the 'object_' into object_scrumtask.png
	 */
	public $picto = 'scrumtask@scrumproject';


	const STATUS_DRAFT = 0;
	const STATUS_VALIDATED = 1; // is to do
	const STATUS_DONE= 2;
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

	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	public $fields=array(
		'rowid' => array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>'1', 'position'=>1, 'notnull'=>1, 'visible'=>0, 'noteditable'=>'1', 'index'=>1, 'css'=>'left', 'comment'=>"Id"),
		'ref' => array('type'=>'varchar(128)', 'label'=>'Ref', 'enabled'=>'1', 'position'=>20, 'notnull'=>1, 'visible'=>4, 'noteditable'=>'1', 'default'=>'(PROV)', 'index'=>1, 'searchall'=>1, 'showoncombobox'=>'1', 'validate'=>'1', 'comment'=>"Reference of object"),
		'fk_scrum_user_story_sprint' => array('type'=>'integer:ScrumUserStorySprint:scrumproject/class/scrumuserstorysprint.class.php:1', 'label'=>'ScrumUserStorySprint', 'enabled'=>'1', 'position'=>52, 'notnull'=>-1, 'visible'=>-1, 'index'=>1, 'foreignkey'=>'scrumproject_scrumuserstorysprint.rowid', 'validate'=>'1',),
		'label' => array('type'=>'varchar(255)', 'label'=>'Label', 'enabled'=>'1', 'position'=>30, 'notnull'=>0, 'visible'=>1, 'searchall'=>1, 'css'=>'minwidth300', 'cssview'=>'wordbreak', 'help'=>"Help text", 'showoncombobox'=>'2', 'validate'=>'1',),
		'prod_calc' => array('type'=>'varchar(10)', 'label'=>'ProductivityCalcMod', 'enabled'=>'1', 'position'=>35, 'notnull'=>1, 'visible'=>-1, 'index'=>1, 'arrayofkeyval'=>array('count'=>'ProductivityCalcModCount','onlyspent'=>'ProductivityCalcModOnlySpent','notcount'=>'ProductivityCalcModNotCount',), 'validate'=>'1', 'default' => 'count'),
		'qty_planned' => array('type'=>'real', 'label'=>'QtyPlanned', 'enabled'=>'1', 'position'=>40, 'notnull'=>1, 'visible'=>1, 'default'=>'0', 'isameasure'=>'1', 'css'=>'maxwidth75imp',),
//		'fk_user_dev' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserDEV', 'enabled'=>'1', 'position'=>55, 'notnull'=>-1, 'visible'=>-1, 'index'=>1, 'foreignkey'=>'user.rowid',),
		'qty_consumed' => array('type'=>'real', 'label'=>'QtyConsumed', 'enabled'=>'1', 'position'=>50, 'notnull'=>0, 'visible'=>1, 'noteditable'=>'1', 'default'=>'0', 'isameasure'=>'1', 'css'=>'maxwidth75imp',),
		'description' => array('type'=>'html', 'label'=>'Description', 'enabled'=>'1', 'position'=>60, 'notnull'=>0, 'visible'=>3, 'validate'=>'1',),
		'note_public' => array('type'=>'html', 'label'=>'NotePublic', 'enabled'=>'1', 'position'=>61, 'notnull'=>0, 'visible'=>0, 'validate'=>'1',),
		'note_private' => array('type'=>'html', 'label'=>'NotePrivate', 'enabled'=>'1', 'position'=>62, 'notnull'=>0, 'visible'=>0, 'validate'=>'1',),
		'date_creation' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>'1', 'position'=>500, 'notnull'=>1, 'visible'=>-2,),
		'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>'1', 'position'=>501, 'notnull'=>0, 'visible'=>-2,),
		'fk_user_creat' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserAuthor', 'enabled'=>'1', 'position'=>510, 'notnull'=>1, 'visible'=>-2, 'foreignkey'=>'user.rowid',),
		'fk_user_modif' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserModif', 'enabled'=>'1', 'position'=>511, 'notnull'=>-1, 'visible'=>-2,),
		'import_key' => array('type'=>'varchar(14)', 'label'=>'ImportId', 'enabled'=>'1', 'position'=>1000, 'notnull'=>-1, 'visible'=>-2,),
		'status' => array('type'=>'smallint', 'label'=>'Status', 'enabled'=>'1', 'position'=>1000, 'notnull'=>1, 'visible'=>5, 'index'=>1, 'arrayofkeyval'=>array('0'=>'Brouillon', '1'=>'A faire', '2'=>'Termin&eacute;', '9'=>'Annul&eacute;'), 'validate'=>'1', 'default' => 1),
	);
	public $rowid;
	public $ref;
	public $fk_scrum_user_story_sprint;
	public $qty_planned;
	public $qty_consumed;
	public $label;
	public $description;
	public $note_public;
	public $note_private;
	public $date_creation;
	public $tms;
	public $fk_user_creat;
	public $fk_user_modif;
	public $import_key;
	public $status;
	public $prod_calc = 'count';


	// If this object has a subtable with lines

	// /**
	//  * @var string    Name of subtable line
	//  */
	// public $table_element_line = 'scrumproject_scrumtaskline';

	// /**
	//  * @var string    Field with ID of parent key if this object has a parent
	//  */
	// public $fk_element = 'fk_scrumtask';

	// /**
	//  * @var string    Name of subtable class that manage subtable lines
	//  */
	// public $class_element_line = 'ScrumTaskline';

	// /**
	//  * @var array	List of child tables. To test if we can delete object.
	//  */
	// protected $childtables = array();

	// /**
	//  * @var array    List of child tables. To know object to delete on cascade.
	//  *               If name matches '@ClassNAme:FilePathClass;ParentFkFieldName' it will
	//  *               call method deleteByParentField(parentId, ParentFkFieldName) to fetch and delete child object
	//  */
	// protected $childtablesoncascade = array('scrumproject_scrumtaskdet');

	// /**
	//  * @var ScrumTaskLine[]     Array of subtable lines
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

		if (!getDolGlobalString('MAIN_SHOW_TECHNICAL_ID') && isset($this->fields['rowid'])) {
			$this->fields['rowid']['visible'] = 0;
		}
		if (empty($conf->multicompany->enabled) && isset($this->fields['entity'])) {
			$this->fields['entity']['enabled'] = 0;
		}

		// Example to show how to set values of fields definition dynamically
		/*if ($user->rights->scrumproject->scrumtask->read) {
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
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = false, $params = array())
	{
		$resultcreate = $this->createCommon($user, $notrigger);

		//$resultvalidate = $this->validate($user, $notrigger);

		// Kanban
		if(!class_exists('AdvKanban')){ dol_include_once('/advancedkanban/class/advkanban.class.php'); }
		if(!class_exists('AdvKanbanList')){ dol_include_once('/advancedkanban/class/advkanbanlis.class.php'); }
		if(!class_exists('AdvKanbanCard')){ dol_include_once('/advancedkanban/class/advkanbancard.class.php'); }
		if(!class_exists('ScrumUserStorySprint')){ require_once __DIR__ .'/scrumuserstorysprint.class.php'; }
		if(!class_exists('scrumSprint')){ require_once __DIR__ .'/crumsprint.class.php'; }

		$scrumUserStorySprint = new ScrumUserStorySprint($this->db);
		$res = $scrumUserStorySprint->fetch($this->fk_scrum_user_story_sprint);
		if($res > 0) {

			$scrumSprint = new ScrumSprint($this->db);
			if($scrumSprint->fetch($scrumUserStorySprint->fk_scrum_sprint) < 0){
				$this->error = 'Fail fetch sprint';
				return -1;
			}

			$staticAdvKanban = new AdvKanban($this->db);
			$kanban = $staticAdvKanban->fetch($scrumSprint->fk_advkanban);
			if($kanban<0) {
				$this->error = 'Fail fetch kanban';
				return -1;
			}

			if($kanban>0) {
				$staticAdvKanbanList = new AdvKanbanList($scrumUserStorySprint->db);
				if(isset($params['AdvKanbanCard']['fk_advkanbanlist'])){
					$customsql = ' rowid = '.intval($params['AdvKanbanCard']['fk_advkanbanlist']);
				} else {
					$customsql = 'fk_advkanban = '.intval($scrumSprint->fk_advkanban).' AND  ref_code = \'backlog\'';
				}
				$TAdvKanbanList = $staticAdvKanbanList->fetchAll('','', 1, 0, array('customsql' => $customsql));
				if(!empty($TAdvKanbanList) && is_array($TAdvKanbanList)){
					$backLogList = reset($TAdvKanbanList);

					$card = new AdvKanbanCard($scrumUserStorySprint->db);
					$card->label = $this->label;
					$card->fk_element = $this->id;
					$card->element_type = $this->element;
					$card->fk_advkanbanlist = $backLogList->id;
					if(isset($params['AdvKanbanCard'])  && !empty($params['AdvKanbanCard']['fk_advkanbanlist'])) {
						if(!$card->validateField($card->fields, 'fk_advkanbanlist', $params['AdvKanbanCard']['fk_advkanbanlist'])){
							$this->error = 'Fail creating AdvKanbanCard : '.$card->getFieldError('fk_advkanbanlist');
							return -1;
						}

						$card->fk_advkanbanlist = $params['AdvKanbanCard']['fk_advkanbanlist'];
					}

					//Gestion du rang
					$rank = $card->getCardRankByElement($backLogList->id, 'scrumproject_scrumuserstorysprint', $this->fk_scrum_user_story_sprint);
					if(isset($params['AdvKanbanCard'])  && !empty($params['AdvKanbanCard']['fk_rank'])) {
						$rank = $params['AdvKanbanCard']['fk_rank'];
					}
					if($rank > 0) {
						$newRank = $rank++;
						$card->fk_rank = $newRank;
						$card->shiftAllCardRankAfterRank();
					}
					else $card->fk_rank = $backLogList->getMaxRankOfKanBanListItems()+1;

					$res = $card->create($user, $notrigger);
					if($res<=0) {
						$this->errors[] = $card->errorsToString();
						$resultcreate = $res;
					}
					else $this->duplicateTag($this->fk_scrum_user_story_sprint, $card,  $backLogList->id);
				}
			}
		}




		return $resultcreate;
	}


	/**
	 * Permet de dupliquer les tags en reprenant ceux de l'us associée
	 *
	 * @param int $fk_user_story_sprint
	 * @param AdvKanbanCard $card
	 * @param int $fk_kanbanlist
	 * @return void
	 */
	public function duplicateTag(int $fk_user_story_sprint, AdvKanbanCard &$card, int $fk_kanbanlist) {
		//On récupère l'id de carte liée à l'us
		$sql = 'SELECT rowid FROM '.MAIN_DB_PREFIX.$card->table_element.' WHERE element_type="scrumproject_scrumuserstorysprint" AND fk_advkanbanlist='.$fk_kanbanlist.' AND fk_element='.$fk_user_story_sprint;
		$resql = $this->db->query($sql);
		if($resql) {
			$obj = $this->db->fetch_object($resql);

			require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';

			$cat = new Categorie($this->db);
			$categories = $cat->containing($obj->rowid, 'advkanbancard', 'id');
			if(! empty($categories)) $card->setCategories($categories);
		}
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
	 * @return self[]|int                 int <0 if KO, array of pages if OK
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
		global $langs;
		$out = '';

		if($key == 'qty_planned' ||  $key == 'qty_consumed')
		{
			if ( !function_exists('convertFloatHourToHoursMins')) {
				include_once __DIR__ . "/../lib/scrumproject.lib.php" ;
			}
			$out =  convertFloatHourToHoursMins($value,$langs) ;
		} else{
			$out = parent::showOutputField($val, $key, $value, $moreparam, $keysuffix, $keyprefix, $morecss);
		}
		return $out;
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
		return $this->updateCommon($user, $notrigger);
	}

	/**
	 * Update object into database
	 *
	 * @param  User $user      	User that modifies
	 * @param  bool $notrigger 	false=launch triggers after, true=disable triggers
	 * @return int             	<0 if KO, >0 if OK
	 */
	public function updateCommon(User $user, $notrigger = false)
	{
		$error = 0 ;

		$res = parent::updateCommon($user, $notrigger);

		if($res < 0){
			return $res;
		};


		// UPDATE PARENT SCRUM USER STORY FOR SPRINT
		require_once __DIR__ . '/scrumuserstorysprint.class.php';
		$scrumUserStorySprint = new ScrumUserStorySprint($this->db);
		if ($scrumUserStorySprint->fetch($this->fk_scrum_user_story_sprint) > 0) {
			if ($scrumUserStorySprint->updateTimeDone($user, $notrigger) < 0) {
				$this->setErrorMsg($scrumUserStorySprint->errorsToString());
				$error++;
			}
		} else {
			$this->setErrorMsg('failFetchingScrumUserStorySprint to update qty done field');
			$error++;
		}

		if ($error > 0) {
			return -1;
		}


		return $res;
	}

	/**
	 * object in database
	 *
	 * @param User $user       User that deletes
	 * @param bool $notrigger  false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function delete(User $user, $notrigger = false)
	{
		global $langs;

		/**
		 * TODO : Lors de la mise à jours de cette methode vérifier aussi onAdvKanbanCardDelete au cas ou
		 */
		if(!$this->canBeDeleted()){
			return -1;
		}

		if(!class_exists('AdvKanbanCard')){ dol_include_once('/advancedkanban/class/advkanbancard.class.php'); }
		$staticsAdvKanbanCard = new AdvKanbanCard($this->db);
		if($staticsAdvKanbanCard->deleteAllFromElement($user, $this->element, $this->id, $notrigger)<0){
			$this->error = $staticsAdvKanbanCard->error;
			$this->errors[] = array_merge($this->errors,  $staticsAdvKanbanCard->errors);
			return -1;
		}

		return  $this->deleteCommon($user, $notrigger);
	}


	/**
	 * On advKanbanCard delete this scrumTask if affected
	 *
	 * @param AdvKanbanCard $advKanbanCard the scrum card how run this kanban trigger
	 * @param User      $user      User that deletes
	 * @param bool      $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function onAdvKanbanCardDelete(AdvKanbanCard $advKanbanCard, User $user, $notrigger = false)
	{
		global $langs;
		if(!$this->canBeDeleted()){
			$this->error = $langs->trans('ErrorTimeOnScrumTask');
			return -1;
		}
		return $this->deleteCommon($user, $notrigger);
	}

	/**
	 * Check if object can be deleted
	 * @return bool
	 */
	public function canBeDeleted(){
		global $langs;
		if($this->countTimeSpentLines()>0){
			$this->error = $langs->trans('CantDeleteTaskWhenTimeAlreadySpent');
			$this->errors[] = $this->error;
			return false;
		}

		return true;
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
		if ($this->status == self::STATUS_VALIDATED) {
			dol_syslog(get_class($this)."::validate action abandonned: already validated", LOG_WARNING);
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->scrumproject->scrumtask->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->scrumproject->scrumtask->scrumtask_advance->validate))))
		 {
		 $this->error='NotEnoughPermissions';
		 dol_syslog(get_class($this)."::valid ".$this->error, LOG_ERR);
		 return -1;
		 }*/

		$now = dol_now();

		$this->db->begin();

		// Define new ref
		if (!$error && (preg_match('/^[\(]?PROV/i', $this->ref) || empty($this->ref))) { // empty should not happened, but when it occurs, the test save life
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
			if (!empty($this->fields['date_validation'])) {
				$sql .= ", date_validation = '".$this->db->idate($now)."'";
			}
			if (!empty($this->fields['fk_user_valid'])) {
				$sql .= ", fk_user_valid = ".((int) $user->id);
			}
			$sql .= " WHERE rowid = ".((int) $this->id);

			dol_syslog(get_class($this)."::validate()", LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (!$resql) {
				dol_print_error($this->db);
				$this->error = $this->db->lasterror();
				$error++;
			}

			if (!$error && !$notrigger) {
				// Call trigger
				$result = $this->call_trigger('SCRUMTASK_VALIDATE', $user);
				if ($result < 0) {
					$error++;
				}
				// End call triggers
			}
		}

		if (!$error) {
			$this->oldref = $this->ref;

			// Rename directory if dir was a temporary ref
			if (preg_match('/^[\(]?PROV/i', $this->ref)) {
				// Now we rename also files into index
				$sql = 'UPDATE '.MAIN_DB_PREFIX."ecm_files set filename = CONCAT('".$this->db->escape($this->newref)."', SUBSTR(filename, ".(strlen($this->ref) + 1).")), filepath = 'scrumtask/".$this->db->escape($this->newref)."'";
				$sql .= " WHERE filename LIKE '".$this->db->escape($this->ref)."%' AND filepath = 'scrumtask/".$this->db->escape($this->ref)."' and entity = ".$conf->entity;
				$resql = $this->db->query($sql);
				if (!$resql) {
					$error++; $this->error = $this->db->lasterror();
				}

				// We rename directory ($this->ref = old ref, $num = new ref) in order not to lose the attachments
				$oldref = dol_sanitizeFileName($this->ref);
				$newref = dol_sanitizeFileName($num);
				$dirsource = $conf->scrumproject->dir_output.'/scrumtask/'.$oldref;
				$dirdest = $conf->scrumproject->dir_output.'/scrumtask/'.$newref;
				if (!$error && file_exists($dirsource)) {
					dol_syslog(get_class($this)."::validate() rename dir ".$dirsource." into ".$dirdest);

					if (@rename($dirsource, $dirdest)) {
						dol_syslog("Rename ok");
						// Rename docs starting with $oldref with $newref
						$listoffiles = dol_dir_list($conf->scrumproject->dir_output.'/scrumtask/'.$newref, 'files', 1, '^'.preg_quote($oldref, '/'));
						foreach ($listoffiles as $fileentry) {
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
		if (!$error) {
			$this->ref = $num;
			$this->status = self::STATUS_VALIDATED;
		}

		if (!$error) {
			$this->db->commit();
			return 1;
		} else {
			$this->db->rollback();
			return -1;
		}
	}


	/**
	 * Update object into database
	 *
	 * @param User $user User that modifies
	 * @param AdvKanbanCard $advKanbanCard
	 * @param AdvKanbanList $kanbanList
	 * @param bool $noTrigger false=launch triggers after, true=disable triggers
	 * @param bool $noUpdate false=launch update after, true=disable update
	 * @return int             <if KO, >0 if OK
	 */
	public function dropInKanbanList(User $user, AdvKanbanCard $advKanbanCard, AdvKanbanList $kanbanList, $noTrigger = false, $noUpdate = false)
	{

        global $db, $conf;
        if($this->status != ScrumTask::STATUS_CANCELED){
			$advKanbanCard->status = ScrumTask::STATUS_VALIDATED;
			if($kanbanList->ref_code == 'backlog'){
				$advKanbanCard->status = ScrumTask::STATUS_DRAFT;
			}
			elseif($kanbanList->ref_code == 'done'){
				$advKanbanCard->status = ScrumTask::STATUS_DONE;
			}
		}


		// Déplacement des US avec la tâche si la tâche est la dernière
		$sqlCount = /** @lang MySQL */
			'  SELECT COUNT(c.rowid) nbFound '
			.' FROM '.$this->db->prefix().$advKanbanCard->table_element . ' c '
			.' JOIN '.$this->db->prefix().$this->table_element . ' st ON( st.rowid = c.fk_element) '
			.' WHERE c.fk_advkanbanlist IN ('
			.' 								SELECT l.rowid '
			.' 								FROM '.$this->db->prefix().$kanbanList->table_element . ' l '
			.' 								WHERE l.fk_advkanban = '.intval($kanbanList->fk_advkanban)
			.' 										 AND l.fk_rank < '.intval($kanbanList->fk_rank)
			.' 							 )'
			.' AND c.element_type = \''.$this->db->escape($advKanbanCard->element_type).'\' '
			.' AND st.fk_scrum_user_story_sprint = '.intval($this->fk_scrum_user_story_sprint)
			.' AND c.rowid != '.intval($advKanbanCard->id)
		;

		$obj = $this->db->getRow($sqlCount);
		if($obj && intval($obj->nbFound) == 0){

			// recherche de l'us dans le kanban
			$sqlCount = /** @lang MySQL */
				'  SELECT c.rowid cardId '
				.' FROM '.$this->db->prefix().$advKanbanCard->table_element . ' c '
				.' JOIN '.$this->db->prefix().'scrumproject_scrumuserstorysprint sus ON( sus.rowid = c.fk_element) '
				.' WHERE c.fk_advkanbanlist IN ('
				.' 								SELECT l.rowid '
				.' 								FROM '.$this->db->prefix().$kanbanList->table_element . ' l '
				.' 								WHERE l.fk_advkanban = '.intval($kanbanList->fk_advkanban)
				.' 										 AND l.fk_rank < '.intval($kanbanList->fk_rank)
				.' 							 )'
				.' AND c.element_type = \'scrumproject_scrumuserstorysprint\' '
				.' AND c.fk_element = '.intval($this->fk_scrum_user_story_sprint)
				.' AND sus.rowid = '.intval($this->fk_scrum_user_story_sprint)
			;

			$obj = $this->db->getRow($sqlCount);
			if($obj && $obj->cardId > 0){
				$usKanbanCard = new AdvKanbanCard($this->db);
				$usKanbanCard->fetch($obj->cardId);
				$usKanbanCard->fk_rank = $advKanbanCard->fk_rank;
				$usKanbanCard->dropInKanbanList($user, $kanbanList);
				if(!$usKanbanCard->shiftAllCardRankAfterRank()){
					$this->error = $usKanbanCard->error;
					$this->errors = $this->errors;
					return -1;
				}
				else {
					if ($kanbanList->ref_code == 'done') {
						$sql = "SELECT ssu.complete_task_on_us_done, ssu.fk_task, pt.rowid as ptRowid";
						$sql .= " FROM " . $db->prefix() . "scrumproject_scrumuserstory ssu ";
						$sql .= " JOIN " . $db->prefix() . "scrumproject_scrumuserstorysprint ssus ON ssu.rowid = ssus.fk_scrum_user_story";
						$sql .= " JOIN " . $db->prefix() . "scrumproject_scrumtask sst ON ssus.rowid = sst.fk_scrum_user_story_sprint";
						$sql .= " JOIN " . $db->prefix() . "advancedkanban_advkanbancard akac ON sst.rowid = akac.fk_element";
						$sql .= " JOIN " . $db->prefix() . "projet_task pt ON pt.rowid = ssu.fk_task";
						$sql .= " WHERE akac.rowid = " . (int)$advKanbanCard->id;
						$resql = $db->query($sql);
						if ($resql) {
							if ($db->num_rows($resql) > 0) {
								$obj = $db->fetch_object($resql);
								$fkTask = $obj->fk_task;

								$shouldCompleteTask = getDolGlobalString('SP_KANBAN_COMPLETE_PROJECT_TASK_WHEN_ALL_US_DONE');
								if ($obj->complete_task_on_us_done != 0) {
									if ($obj->complete_task_on_us_done == 1) {
										$shouldCompleteTaskByTask = true;
									} elseif ($obj->complete_task_on_us_done == 2) {
										$shouldCompleteTaskByTask = false;
									}
									if ($shouldCompleteTask != $shouldCompleteTaskByTask) $shouldCompleteTask = $shouldCompleteTaskByTask;
								}
								if ($shouldCompleteTask) {
									$task = new Task($db);
									if ($task->fetch($obj->ptRowid)){
										$task->progress = 100;
										$result = $task->update($user);
										if ($result <= 0){
											dol_syslog(__METHOD__ . ', Task update errors', LOG_ERR);
										}
									}
									else
									{
										dol_syslog(__METHOD__ . ', Task fetch errors', LOG_ERR);
									}
//
								}
							}
						}
						else
						{
							dol_syslog(__METHOD__ . ', sql UPDATE errors', $db->lasterror());
						}
					}
				}
			}
		}
		if($noUpdate){
			return 0;
		}

		return $this->setStatusCommon($user, $advKanbanCard->status, $noTrigger, 'SCRUMTASK_DROPINKABANLIST');
	}

	/**
	 *	Set to a status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$status			New status to set (often a constant like self::STATUS_XXX)
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *  @param  string  $triggercode    Trigger code to use
	 *	@return	int						<0 if KO, >0 if OK
	 */
	public function setStatusCommon($user, $status, $notrigger = 0, $triggercode = '')
	{
		$this->db->begin();
		$error=0;

		$res = parent::setStatusCommon($user, $status, $notrigger, $triggercode);
		if($res>0){
			// UPDATE PARENT SCRUM USER STORY FOR SPRINT
			require_once __DIR__ . '/scrumuserstorysprint.class.php';

			$scrumUserStorySprint = new ScrumUserStorySprint($this->db);
			if($scrumUserStorySprint->fetch($this->fk_scrum_user_story_sprint)>0){
				if($scrumUserStorySprint->updateTimeDone($user, $notrigger)<0){
					$this->setErrorMsg($scrumUserStorySprint->errorsToString());
					$error++;
				}
			}else{
				$this->setErrorMsg('failFetchingScrumUserStorySprint to update qty done field');
				$error++;
			}
		}

		if (!$error) {
			$this->status = $status;
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
		if ($this->status <= self::STATUS_DRAFT) {
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->scrumproject->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->scrumproject->scrumproject_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_DRAFT, $notrigger, 'SCRUMTASK_UNVALIDATE');
	}

	/**
	 *	Set cancel status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						<0 if KO, 0=Nothing done, >0 if OK
	 */
	public function cancel($user, $notrigger = 0)
	{
		// Protection
		if ($this->status != self::STATUS_VALIDATED) {
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->scrumproject->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->scrumproject->scrumproject_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_CANCELED, $notrigger, 'SCRUMTASK_CANCEL');
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
		// Protection
		if ($this->status != self::STATUS_CANCELED) {
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->scrumproject->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->scrumproject->scrumproject_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_VALIDATED, $notrigger, 'SCRUMTASK_REOPEN');
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

		if (!empty($conf->dol_no_mouse_hover)) {
			$notooltip = 1; // Force disable tooltips
		}

		$result = '';

		$label = img_picto('', 'object_'.$this->picto).' <u>'.$langs->trans("ScrumTask").'</u>';
		if (isset($this->status)) {
			$label .= ' '.$this->getLibStatut(5);
		}
		$label .= '<br>';
		$label .= '<b>'.$langs->trans('Ref').':</b> '.$this->ref;

		$url = dol_buildpath('/scrumproject/scrumtask_card.php', 1).'?id='.$this->id;

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
			if (getDolGlobalString('MAIN_OPTIMIZEFORTEXTBROWSER')) {
				$label = $langs->trans("ShowScrumTask");
				$linkclose .= ' alt="'.dol_escape_htmltag($label, 1).'"';
			}
			$linkclose .= ' title="'.dol_escape_htmltag($label, 1).'"';
			$linkclose .= ' class="classfortooltip'.($morecss ? ' '.$morecss : '').'"';
		} else {
			$linkclose = ($morecss ? ' class="'.$morecss.'"' : '');
		}

		if ($option == 'nolink' || empty($url)) {
			$linkstart = '<span';
		} else {
			$linkstart = '<a href="'.$url.'"';
		}
		$linkstart .= $linkclose.'>';
		if ($option == 'nolink' || empty($url)) {
			$linkend = '</span>';
		} else {
			$linkend = '</a>';
		}

		$result .= $linkstart;

		if (empty($this->showphoto_on_popup)) {
			if ($withpicto) {
				$result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'classfortooltip"'), 0, 0, $notooltip ? 0 : 1);
			}
		} else {
			if ($withpicto) {
				require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

				LIST($class, $module) = explode('@', $this->picto);
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

		if ($withpicto != 2) {
			$result .= $this->ref;
		}

		$result .= $linkend;
		//if ($withpicto != 2) $result.=(($addlabel && $this->label) ? $sep . dol_trunc($this->label, ($addlabel > 1 ? $addlabel : 0)) : '');

		global $action, $hookmanager;
		$hookmanager->initHooks(array('scrumtaskdao'));
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
			$this->labelStatus[self::STATUS_VALIDATED] = $langs->transnoentitiesnoconv('ScrumTaskStatusToDo');
			$this->labelStatus[self::STATUS_DONE] = $langs->transnoentitiesnoconv('ScrumTaskStatusDone');
			$this->labelStatus[self::STATUS_CANCELED] = $langs->transnoentitiesnoconv('Disabled');
			$this->labelStatusShort[self::STATUS_DRAFT] = $langs->transnoentitiesnoconv('Draft');
			$this->labelStatusShort[self::STATUS_VALIDATED] = $langs->transnoentitiesnoconv('ScrumTaskStatusToDo');
            $this->labelStatusShort[self::STATUS_DONE] = $langs->transnoentitiesnoconv('ScrumTaskStatusDone');
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
					if(property_exists($this, 'user_creation')) $this->user_creation = $cuser;
					if(property_exists($this, 'user_creation_id')) $this->user_creation_id = $cuser;
				}

				if (!empty($obj->fk_user_valid)) {
					$vuser = new User($this->db);
					$vuser->fetch($obj->fk_user_valid);
					if(property_exists($this, 'user_validation')) $this->user_validation = $vuser;
					if(property_exists($this, 'user_validation_id')) $this->user_validation_id = $vuser;
				}

				if (!empty($obj->fk_user_cloture)) {
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

		$objectline = new ScrumTaskLine($this->db);
		$result = $objectline->fetchAll('ASC', 'position', 0, 0, array('customsql'=>'fk_scrumtask = '.((int) $this->id)));

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

		if (!getDolGlobalString('SCRUMPROJECT_SCRUMTASK_ADDON')) {
			$conf->global->SCRUMPROJECT_SCRUMTASK_ADDON = 'mod_scrumtask_standard';
		}

		if (getDolGlobalString('SCRUMPROJECT_SCRUMTASK_ADDON')) {
			$mybool = false;

			$file = getDolGlobalString('SCRUMPROJECT_SCRUMTASK_ADDON') . ".php";
			$classname = getDolGlobalString('SCRUMPROJECT_SCRUMTASK_ADDON');

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
	 * @return int|false
	 */
	public function getProjectTaskId(){
		$sql = /** @lang MySQL */ "SELECT sus.fk_task FROM ".MAIN_DB_PREFIX."scrumproject_scrumuserstory sus "
			." JOIN ".MAIN_DB_PREFIX."scrumproject_scrumuserstorysprint susp ON (susp.fk_scrum_user_story = sus.rowid) "
			." WHERE susp.rowid = ".$this->fk_scrum_user_story_sprint;
		$obj = $this->db->getRow($sql);

		if($obj){
			return $obj->fk_task;
		}

		return false;
	}


	/**
	 * @param User $user User adding
	 * @param int $fk_user user for time spent
	 * @param int $timeSpent time spent in second
	 * @param int $progress 0-100
	 * @param timestamp $date
	 * @param string $note
	 * @return int|void
	 */
	public function addTimeSpend($user, $fk_user, $timeSpent, $progress = -1, $date = false, $note = '' ){

		global $langs;

		$langs->load('projects');

		if(empty($fk_user) || $fk_user < 0){
			$this->setErrorMsg('ErrorUserNotAssignedToTask');
			return -1;
		}

		if(empty($timeSpent)){
			$this->setErrorMsg('ErrorTimeSpentEmpty');
			return -1;
		}


		$fk_task = $this->getProjectTaskId();

		if($fk_task){
			$projectTask = new Task($this->db);
			if($projectTask->fetch($fk_task)>0) {

				$projectTask->timespent_note = $note;
				$projectTask->progress = $progress; // If progress is -1 (not defined), we do not change value
				$projectTask->timespent_duration = $timeSpent; // We store duration in seconds

				$projectTask->timespent_date = $date;
				$projectTask->timespent_withhour = 1;

				$projectTask->timespent_fk_user = $fk_user;

				$projectTask->fk_scrumproject_scrumtask = $this->id; // Permet au trigger de prendre le relay

				$result = $projectTask->addTimeSpent($user);
				if ($result > 0) {
					if($this->updateTimeSpent($user)>0){
						return 1;
					}
					else{
						return -2;
					}
				} else {
					$this->setErrorMsg($projectTask->errorsToString());
					return -1;
				}
			}else{
				$this->setErrorMsg('FailFetchingTask');
				return -1;
			}
		}
		else{
			$this->setErrorMsg('NoTaskLinkedToUS');
			return -1;
		}
	}

	/**
	 * Retourne la somme des temps saisis sur cette tache scrum
	 * @return int
	 */
	public function calcTimeSpent($moreSql = ''){

		if(version_compare(DOL_VERSION, '18.0.0', '<')) {
			$sql = /** @lang MySQL */
				"SELECT SUM(ptt.task_duration) sumTimeSpent
				FROM ".MAIN_DB_PREFIX."scrumproject_scrumtask_projet_task_time pttl "."
				JOIN ".MAIN_DB_PREFIX."projet_task_time ptt ON (ptt.rowid = pttl.fk_projet_task_time) "."
				WHERE pttl.fk_scrumproject_scrumtask = ".intval($this->id)." ".$moreSql;
		}
		else {
			$sql = /** @lang MySQL */
				'SELECT SUM(ptt.element_duration) sumTimeSpent
				FROM '.MAIN_DB_PREFIX.'scrumproject_scrumtask_projet_task_time pttl '.'
				JOIN '.MAIN_DB_PREFIX.'element_time ptt ON (ptt.rowid = pttl.fk_projet_task_time AND ptt.elementtype="task") '.'
				WHERE pttl.fk_scrumproject_scrumtask = '.intval($this->id).' '.$moreSql;
		}

		$obj = $this->db->getRow($sql);
		if($obj){
			$this->qty_consumed = round(intval($obj->sumTimeSpent) / 3600 , 2);
			return $this->qty_consumed;
		}

		return 0;
	}

	/**
	 * Retourn le nombre de lignes de saisie de temps
	 * @return int
	 */
	public function countTimeSpentLines()
	{

		$sql = /** @lang MySQL */
			'SELECT COUNT(pttl.rowid) nb FROM ' . MAIN_DB_PREFIX . 'scrumproject_scrumtask_projet_task_time pttl '
			. ' WHERE pttl.fk_scrumproject_scrumtask = ' . intval($this->id);
		$obj = $this->db->getRow($sql);
		if ($obj) {
			return $obj->nb;
		}
		return 0;
	}

	/**
	 * @param User $user
	 * @param bool $notrigger
	 * @return int
	 */
	public function updateTimeSpent(User $user, $notrigger = false, $calcTimeSpent = true){
		global $user;

		$error = 0;
		$this->db->begin();
		if($calcTimeSpent) { $this->calcTimeSpent();}

		$sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element." SET qty_consumed = '".$this->qty_consumed."' WHERE rowid=".((int) $this->id);

		if($this->db->query($sql)){

			// Triggers
			if (!$error && !$notrigger) {
				// Call triggers
				$result = $this->call_trigger('SCRUMTASK_UPDATE_TIME_SPENT', $user);
				if ($result < 0) {
					$error++;
				} //Do also here what you must do to rollback action if trigger fail
				// End call triggers
			}

			// UPDATE PARENT SCRUM USER STORY FOR SPRINT
			require_once __DIR__ . '/scrumuserstorysprint.class.php';

			$scrumUserStorySprint = new ScrumUserStorySprint($this->db);
			if($scrumUserStorySprint->fetch($this->fk_scrum_user_story_sprint)>0){
				if($scrumUserStorySprint->updateTimeSpent($user, $notrigger)<0){
					$this->setErrorMsg($scrumUserStorySprint->errorsToString());
					$error++;
				}
			}else{
				$this->setErrorMsg('failFetchingScrumUserStorySprint');
				$error++;
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
			$this->setErrorMsg($this->db->lasterror());
			$this->db->rollback();
			return -1;
		}
	}

	/**
	 * @param int $fk_project_task_time
	 * @return int 0 nothing to do , > 0 inserted and return rowid, -1 error
	 */
	public function linkTaskTimeToScrumTask($fk_project_task_time){

		if($fk_project_task_time <= 0 || $this->id <= 0){
			$this->setErrorMsg('InvalidIds');
			return -1;
		}

		$linkExist = $this->getProjectTaskTimeLinkId($fk_project_task_time);
		if(empty($linkExist)){

			$sql = /** @lang MySQL */ "INSERT INTO ".MAIN_DB_PREFIX."scrumproject_scrumtask_projet_task_time "
				." (rowid, fk_projet_task_time, fk_scrumproject_scrumtask) "
				." VALUES "
				." (NULL, '".intval($fk_project_task_time)."', '".intval($this->id)."') ";
			$res = $this->db->query($sql);
			if (!$res) {
				if($this->db->lasterrno() == 'DB_ERROR_RECORD_ALREADY_EXISTS'){
					$this->setErrorMsg('ErrorRefAlreadyExists');
				} else {
					$this->setErrorMsg($this->db->lasterror());
				}
				return -1;
			}
			else {
				return $this->db->last_insert_id(MAIN_DB_PREFIX.'scrumproject_scrumtask_projet_task_time');
			}
		}

		return 0;
	}

	/**
	 * @param int $fk_project_task_time project task time
	 * @return int|false
	 */
	public function getProjectTaskTimeLinkId($fk_project_task_time){
		$sql = /** @lang MySQL */ "SELECT rowid FROM ".MAIN_DB_PREFIX."scrumproject_scrumtask_projet_task_time pttl "
			." WHERE pttl.fk_projet_task_time = ".intval($fk_project_task_time)
			." AND pttl.fk_scrumproject_scrumtask = ".intval($this->id);

		$obj = $this->db->getRow($sql);
		if($obj){
			return $obj->rowid;
		}

		return false;
	}


	/**
	 * get this object formatted for ajax ans json
	 * @return stdClass
	 */
	public function getScrumKanBanItemObjectStd(){


		$object = new stdClass();
		$object->objectId = $this->id;
		$object->ref= $this->ref;
		$object->type = 'scrum-user-story-task';// le type dans le kanban tel que getAdvKanBanItemObjectFormatted le fait
		$object->label = $this->label;
		$object->element = $this->element;
		$object->socid = 0;
		$object->cardUrl = dol_buildpath('/scrumproject/scrumtask_card.php',1).'?id='.$this->id;
		$object->status = intval($this->status);
		$object->statusLabel = $this->LibStatut(intval($this->status), 1);
		$object->contactUsersAffected = $this->liste_contact(-1,'internal',1);

		$object->fk_scrum_user_story_sprint = $this->fk_scrum_user_story_sprint;
		$object->fk_scrum_user_story_sprint = $this->fk_scrum_user_story_sprint;
		$object->qty_planned = doubleval($this->qty_planned);
		$object->qty_consumed = doubleval($this->qty_consumed);

		$object->qty_remain_for_split = 0;
		if(getDolGlobalInt('SP_KANBAN_DISABLE_SPLIT_TASK_OVERSPEND', 0) == 0){
			$object->qty_remain_for_split = $object->qty_planned;
		}
		elseif($this->qty_planned - $this->qty_consumed > 0){
			$object->qty_remain_for_split = $this->qty_planned - $this->qty_consumed;
		}

		return $object;
	}

	/**
	 * used as callback function for advanced kanban module in advkanbancard.class.php
	 * @param $advKanbanCard AdvKanbanCard
	 * @param $cardDataObj stdClass
	 * @param $tpl stdClass
	 * @return void
	 */
	public function getItemObjectFormattedForAdvKanBan($advKanbanCard, &$cardDataObj, &$tpl){
		global $langs;

		$langs->load('scrumproject@scrumproject');

		$tpl->useTime = true;
		$tpl->useAutoStatusBadge = false;
		$tpl->linkedElementTitle = '';
		$tpl->timeSpend =   $this->showOutputFieldQuick('qty_consumed');
		$tpl->timePlanned = $this->showOutputFieldQuick('qty_planned');


		$tpl->QtyPlannedTitle = $langs->trans('QtyPlanned');
		if($this->prod_calc == 'count' ){
			$tpl->QtyPlannedMoreClass = 'time-planned-count';
			$tpl->QtyPlannedTitle.= ' - '.$langs->trans($this->fields['prod_calc']['arrayofkeyval'][$this->prod_calc]);
		}elseif($this->prod_calc == 'onlyspent' ){
			$tpl->timePlanned.= ' <i class="fa fa-toggle-on" aria-hidden="true"></i>';
			$tpl->QtyPlannedMoreClass = 'time-planned-onlyspent';
			$tpl->QtyPlannedTitle.= ' - '.$langs->trans($this->fields['prod_calc']['arrayofkeyval'][$this->prod_calc]);
			$tpl->QtyPlannedBefore = '<span class="fa fa-calendar-plus-o"></span>';
		}elseif($this->prod_calc == 'notcount' ){
			$tpl->timePlanned.= ' <i class="fa fa-toggle-off" aria-hidden="true"></i>';
			$tpl->QtyPlannedMoreClass = 'time-planned-notcount';
			$tpl->QtyPlannedTitle.= ' - '.$langs->trans($this->fields['prod_calc']['arrayofkeyval'][$this->prod_calc]);
			$tpl->QtyPlannedBefore = '<span class="fa fa-calendar-o"></span>';
		}

		if(doubleval($this->qty_consumed) > doubleval($this->qty_planned) && $this->qty_planned > 0){
			$cardDataObj->class[] = '--alert';
			$cardDataObj->class[] = '--time-consumed-error';
		}

		$cardDataObj->qty_planned = $this->qty_planned;

		$cardDataObj->label = $this->showOutputFieldQuick('label');

		$cardDataObj->cardUrl = dol_buildpath('/scrumproject/scrumtask_card.php',1).'?id='.$this->id;
		$cardDataObj->type = 'scrum-user-story-task';
		$cardDataObj->fk_scrum_user_story_sprint = $this->fk_scrum_user_story_sprint;

		$cardDataObj->socid = $this->getSocIdAssociated();
		$cardDataObj->cardTimeUrl = dol_buildpath('/scrumproject/scrumtask_time_list.php',1).'?id='.$this->id;

		$tpl->status = '<span class="highlight-scrum-task prevent-card-click" ></span>';


		return null;
	}


	/**
	 * @return false|int
	 */
	public function getSocIdAssociated(){
		return ScrumUserStorySprint::staticGetSocIdAssociated($this->db, $this->fk_scrum_user_story_sprint);
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
	 * Permet de spliter la carte en 2
	 * @param double $qty la quantité de la nouvelle carte
	 * @param string $newCardLabel le libelle de la nouvelle carte
	 * @param AdvKanbanCard $advKanbanCard
	 * @return bool
	 */
	public function splitCard($qty, $newCardLabel, AdvKanbanCard $advKanbanCard, User $user ){

		if(!class_exists('ScrumTask')){
			require_once __DIR__ . '/scrumtask.class.php';
		}

		$qty = doubleval($qty);

		$this->calcTimeSpent();

		// Vérification de la liaison entre AdvKanbanCard et ScrumTask
		if($advKanbanCard->element_type != $this->element || $advKanbanCard->fk_element != $this->id ){
			$this->error = 'Error : scrum card not linked';
			return false;
		}


		if(getDolGlobalInt('SP_KANBAN_DISABLE_SPLIT_TASK_OVERSPEND', 0) == 0){
			$qty_remain_for_split = $this->qty_planned;
		}
		elseif($this->qty_planned - $this->qty_consumed > 0){
			$qty_remain_for_split = $this->qty_planned - $this->qty_consumed;
		}

		// Vérification du temps restant
		if($qty > $qty_remain_for_split ){
			$this->error = 'Too much quantity';
			return false;
		}

		// Ajout de la nouvelle ScrumTask
		$newScrumTask = new ScrumTask($this->db);
		$newScrumTask->fk_scrum_user_story_sprint = $this->fk_scrum_user_story_sprint;
		$newScrumTask->description = $this->description;
		$newScrumTask->prod_calc = $this->prod_calc;

		$newScrumTask->qty_planned = $qty;
		$newScrumTask->label = $newCardLabel;
		if(empty($newCardLabel) || is_array($newCardLabel)){ $newScrumTask->label = $this->label;}

		$createParams = array(
			'AdvKanbanCard'=> array(
				'fk_advkanbanlist' => $advKanbanCard->fk_advkanbanlist,
				'fk_rank'=> $advKanbanCard->fk_rank,
			)
		);

		$resCreate = $newScrumTask->create($user, false, $createParams);
		if($resCreate<0){
			$this->error = $newScrumTask->error;
			return false;
		}

		// MISE A JOUR DE LA SCRUM TASK QUE L'ON SPLIT
		$this->qty_planned-= $qty;
		$resUpdate = $this->update($user);
		if($resUpdate<1){
			$this->error = 'Error updating ScrumTask : '.$this->error;
			return false;
		}

// Bloc deja effectué par  $newScrumTask->create
//		// AJOUT DE LA CARD LIÉE
//		$newAdvKanbanCard = new AdvKanbanCard($this->db);
//		if(empty($newCardLabel)){
//			$newAdvKanbanCard->label = $this->label;
//		}
//		$newAdvKanbanCard->label = $newScrumTask->label;
//		$newAdvKanbanCard->fk_element = $newScrumTask->id;
//		$newAdvKanbanCard->element_type = $newScrumTask->element;
//		$newAdvKanbanCard->fk_advkanbanlist = $advKanbanCard->fk_advkanbanlist;
//		$newAdvKanbanCard->fk_rank = $advKanbanCard->fk_rank;
//		$res = $newAdvKanbanCard->create($user);
//		if($res<=0){
//			$this->error = 'Error creating AdvKanbanCard : '.$newAdvKanbanCard->error;
//			$this->errors = array_merge($this->errors, $newAdvKanbanCard->errors);
//			return false;
//		}

		return true;
	}


	/**
	 * Calcule et retourne un résumé de la progression par Utilisateur
	 *
	 * @param int $userId
	 * @return false|stdClass
	 */
	public function calcUserProgress($userId){
		$item = new stdClass();
		$item->sumTimeSpent = 0;
		$item->sumTimeDone = 0;
		$item->sumTimePlanned = 0;

		$sumTimeSpent = $this->getTimeSpent(0);
		if($sumTimeSpent === false){
			return false;
		}
		$sumTimeSpent = floatval($sumTimeSpent);

		$userTimeSpent = $this->getTimeSpent($userId);
		if($userTimeSpent === false){
			return false;
		}
		$userTimeSpent = floatval($userTimeSpent);

		if($this->prod_calc == 'notcount'){
			return $item;
		}

		$item->sumTimeSpent = $userTimeSpent;

		// un moyen simple de detecter si une autre personne à saisi du temps
		if($sumTimeSpent > 0 && floatval($sumTimeSpent) > floatval($userTimeSpent)){
			// Cas particulier il y a plus d'une personne qui à saisi
			// TODO Idée : Ajouter une gestion des type de contact associé ex : dev (existe déjà) , help (le temps de prod ne lui est pas impacté au niveau productivité)

			// Il faut répartir au prorata
			$prorata = $userTimeSpent / $sumTimeSpent ;
//var_dump(array($userId, $prorata, $userTimeSpent, $sumTimeSpent));
			if($this->prod_calc == 'onlyspent'){
				$item->sumTimePlanned = min($this->qty_planned, $userTimeSpent);
				$item->sumTimeDone = $userTimeSpent;
			}else{
				$item->sumTimePlanned = $this->qty_planned*$prorata;
				if($this->status == self::STATUS_DONE){
					$item->sumTimeDone = $this->qty_planned*$prorata;
				}
			}
		}
		else{
			// L'utilisateur est le seul a avoir saisis du temps

			if($this->prod_calc == 'onlyspent'){
				$item->sumTimePlanned = min($this->qty_planned, $userTimeSpent);
				$item->sumTimeDone = $userTimeSpent;
			}else{
				$item->sumTimePlanned = $this->qty_planned;
				if($this->status == self::STATUS_DONE){
					$item->sumTimeDone = $this->qty_planned;
				}
			}
		}


		return $item;
	}


	/**
	 * Calcule et retourne un résumé de la progression par Utilisateur
	 *
	 * @param int $userId
	 * @return false|float
	 */
	public function getTimeSpent($userId = 0 ){

		if(version_compare(DOL_VERSION, '18.0.0', '<')) {
			$sql = /** @lang MySQL */
				"SELECT SUM(ptt.task_duration) sumTimeSpent  "
				." FROM ".MAIN_DB_PREFIX."projet_task_time ptt "
				." JOIN ".MAIN_DB_PREFIX."scrumproject_scrumtask_projet_task_time st_ptt ON(st_ptt.fk_projet_task_time = ptt.rowid) "
				." WHERE  "." st_ptt.fk_scrumproject_scrumtask = ".intval($this->id).($userId > 0 ? " AND ptt.fk_user = ".$userId : '');
		}
		else {
			$sql = /** @lang MySQL */
				'SELECT SUM(ptt.element_duration) sumTimeSpent  '
				.' FROM '.MAIN_DB_PREFIX.'element_time ptt '
				.' JOIN '.MAIN_DB_PREFIX.'scrumproject_scrumtask_projet_task_time st_ptt ON(st_ptt.fk_projet_task_time = ptt.rowid AND ptt.elementtype = "task") '
				.' WHERE  '.' st_ptt.fk_scrumproject_scrumtask = '.intval($this->id).($userId > 0 ? ' AND ptt.fk_user = '.$userId : '');
		}

		$obj = $this->db->getRow($sql);
		if($obj === false){
			$this->error = $this->db->error();
			return false;
		}

		return $obj->sumTimeSpent / 3600;
	}
}
