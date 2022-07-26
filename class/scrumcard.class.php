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
 * \file        class/scrumcard.class.php
 * \ingroup     scrumproject
 * \brief       This file is a CRUD class file for ScrumCard (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
//require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
//require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';
require_once __DIR__ . '/commonKanban.trait.php';

/**
 * Class for ScrumCard
 */
class ScrumCard extends CommonObject
{
	use CommonKanban;

	/**
	 * @var string ID of module.
	 */
	public $module = 'scrumproject';

	/**
	 * @var string ID to identify managed object.
	 */
	public $element = 'scrumproject_scrumcard';

	/**
	 * @var string Name of table without prefix where object is stored. This is also the key used for extrafields management.
	 */
	public $table_element = 'scrumproject_scrumcard';

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
	 * @var string String with name of icon for scrumcard. Must be the part after the 'object_' into object_scrumcard.png
	 */
	public $picto = 'scrumcard@scrumproject';


	const STATUS_DRAFT = 0;
	const STATUS_READY = 1;
	const STATUS_DONE = 2;


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

	// BEGIN MODULEBUILDER PROPERTIES
	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	public $fields=array(
		'rowid' => array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>'1', 'position'=>1, 'notnull'=>1, 'visible'=>0, 'noteditable'=>'1', 'index'=>1, 'css'=>'left', 'comment'=>"Id"),
		'entity' => array('type'=>'integer', 'label'=>'Entity', 'enabled'=>'1', 'position'=>10, 'notnull'=>1, 'visible'=>0, 'default'=>'1', 'index'=>1,),
		'label' => array('type'=>'varchar(255)', 'label'=>'Label', 'enabled'=>'1', 'position'=>30, 'notnull'=>0, 'visible'=>1, 'searchall'=>1, 'css'=>'minwidth300', 'showoncombobox'=>'1',),
		'fk_rank' => array('type'=>'integer', 'label'=>'Rank', 'enabled'=>'1', 'position'=>1, 'notnull'=>1, 'visible'=>0, 'noteditable'=>'1', 'index'=>1, 'css'=>'left', 'comment'=>"Id"),
		'fk_scrum_kanbanlist' => array('type'=>'integer:ScrumKanbanList:scrumproject/class/scrumkanbanlist.class.php', 'label'=>'ScrumKanbanList', 'enabled'=>'1', 'position'=>55, 'notnull'=>1, 'visible'=>0, 'index'=>1, 'foreignkey'=>'scrumproject_scrumkanbanlist.rowid',),
		'fk_element' => array('type' => 'integer','label' => 'ScrumCardLinkedTo','help' => 'ScrumCardLinkedToHelp','enabled' => 1,'visible' => 1,'notnull' => 0,'default' => 0,'index' => 1,'position' => 0),
		'element_type' => array('type' => 'varchar(40)','label' => 'element_type','enabled' => 1,'visible' => 0,'position' => 10,'required' => 0),
		'description' => array('type'=>'html', 'label'=>'Description', 'enabled'=>'1', 'position'=>60, 'notnull'=>0, 'visible'=>3,),
		'note_public' => array('type'=>'html', 'label'=>'NotePublic', 'enabled'=>'1', 'position'=>61, 'notnull'=>0, 'visible'=>0,),
		'note_private' => array('type'=>'html', 'label'=>'NotePrivate', 'enabled'=>'1', 'position'=>62, 'notnull'=>0, 'visible'=>0,),
		'date_creation' => array('type'=>'datetime', 'label'=>'DateCreation', 'enabled'=>'1', 'position'=>500, 'notnull'=>1, 'visible'=>-2,),
		'tms' => array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>'1', 'position'=>501, 'notnull'=>0, 'visible'=>-2,),
		'fk_user_creat' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserAuthor', 'enabled'=>'1', 'position'=>510, 'notnull'=>1, 'visible'=>-2, 'foreignkey'=>'user.rowid',),
		'fk_user_modif' => array('type'=>'integer:User:user/class/user.class.php', 'label'=>'UserModif', 'enabled'=>'1', 'position'=>511, 'notnull'=>-1, 'visible'=>-2, 'foreignkey'=>'user.rowid',),
		'import_key' => array('type'=>'varchar(14)', 'label'=>'ImportId', 'enabled'=>'1', 'position'=>1000, 'notnull'=>-1, 'visible'=>-2,),
		'status' => array('type'=>'smallint', 'label'=>'Status', 'enabled'=>'1', 'position'=>1000, 'notnull'=>1, 'visible'=>2,  'default' => 0, 'index'=>1, 'arrayofkeyval'=>array('0'=>'Brouillon', '1'=>'Pr&ecirc;te', '2'=>'Termin&eacute;e'),),
	);
	public $rowid;
	public $entity;
	public $label;
	public $fk_rank;
	public $fk_scrum_kanbanlist;

	/** @var int $fk_element targeted element rowid */
	public $fk_element;

	/** @var string $element_type targeted element  */
	public $element_type;


	public $description;
	public $note_public;
	public $note_private;
	public $date_creation;
	public $tms;
	public $fk_user_creat;
	public $fk_user_modif;
	public $import_key = '';
	public $status = 0;
	// END MODULEBUILDER PROPERTIES

	/** @var object $elementObject targeted element object  */
	public $elementObject;

	// If this object has a subtable with lines

	// /**
	//  * @var string    Name of subtable line
	//  */
	// public $table_element_line = 'scrumproject_scrumcardline';

	// /**
	//  * @var string    Field with ID of parent key if this object has a parent
	//  */
	// public $fk_element = 'fk_scrumcard';

	// /**
	//  * @var string    Name of subtable class that manage subtable lines
	//  */
	// public $class_element_line = 'ScrumCardline';

	// /**
	//  * @var array	List of child tables. To test if we can delete object.
	//  */
	// protected $childtables = array();

	// /**
	//  * @var array    List of child tables. To know object to delete on cascade.
	//  *               If name matches '@ClassNAme:FilePathClass;ParentFkFieldName' it will
	//  *               call method deleteByParentField(parentId, ParentFkFieldName) to fetch and delete child object
	//  */
	// protected $childtablesoncascade = array('scrumproject_scrumcarddet');

	// /**
	//  * @var ScrumCardLine[]     Array of subtable lines
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

		if (empty($conf->global->MAIN_SHOW_TECHNICAL_ID) && isset($this->fields['rowid'])) $this->fields['rowid']['visible'] = 0;
		//if (empty($conf->multicompany->enabled) && isset($this->fields['entity'])) $this->fields['entity']['enabled'] = 0;

		// Example to show how to set values of fields definition dynamically
		/*if ($user->rights->scrumproject->scrumcard->read) {
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
		return $this->createCommon($user, $notrigger);
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
		return $this->updateCommon($user, $notrigger);
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
		unset($this->fk_element); // avoid conflict with standard Dolibarr comportment

		return $this->deleteCommon($user, $notrigger);
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
		if ($this->status == self::STATUS_READY)
		{
			dol_syslog(get_class($this)."::validate action abandonned: already validated", LOG_WARNING);
			return 0;
		}

		$now = dol_now();

		$this->db->begin();



		if (!empty($num)) {
			// Validate
			$sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element;
			$sql .= " SET status = ".self::STATUS_READY;
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
				$result = $this->call_trigger('SCRUMCARD_VALIDATE', $user);
				if ($result < 0) $error++;
				// End call triggers
			}
		}

		if (!$error)
		{
			$this->oldref = '(PROV'.$this->id.')';
			$num = $this->id;
			// Rename directory if dir was a temporary ref
			if (preg_match('/^[\(]?PROV/i', $this->id))
			{
				// Now we rename also files into index
				$sql = 'UPDATE '.MAIN_DB_PREFIX."ecm_files set filename = CONCAT('".$this->db->escape($this->newref)."', SUBSTR(filename, ".(strlen($this->ref) + 1).")), filepath = 'scrumcard/".$this->db->escape($this->newref)."'";
				$sql .= " WHERE filename LIKE '".$this->db->escape($this->ref)."%' AND filepath = 'scrumcard/".$this->db->escape($this->id)."' and entity = ".$conf->entity;
				$resql = $this->db->query($sql);
				if (!$resql) { $error++; $this->error = $this->db->lasterror(); }

				// We rename directory ($this->ref = old ref, $num = new ref) in order not to lose the attachments
				$oldref = dol_sanitizeFileName($this->id);
				$newref = dol_sanitizeFileName($num);
				$dirsource = $conf->scrumproject->dir_output.'/scrumcard/'.$oldref;
				$dirdest = $conf->scrumproject->dir_output.'/scrumcard/'.$newref;
				if (!$error && file_exists($dirsource))
				{
					dol_syslog(get_class($this)."::validate() rename dir ".$dirsource." into ".$dirdest);

					if (@rename($dirsource, $dirdest))
					{
						dol_syslog("Rename ok");
						// Rename docs starting with $oldref with $newref
						$listoffiles = dol_dir_list($conf->scrumproject->dir_output.'/scrumcard/'.$newref, 'files', 1, '^'.preg_quote($oldref, '/'));
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
			$this->status = self::STATUS_READY;
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

		return $this->setStatusCommon($user, self::STATUS_DRAFT, $notrigger, 'SCRUMCARD_UNVALIDATE');
	}

	/**
	 *	Set done status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						<0 if KO, 0=Nothing done, >0 if OK
	 */
	public function done($user, $notrigger = 0)
	{
		// Protection
		if ($this->status != self::STATUS_READY)
		{
			return 0;
		}

		/*if (! ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->scrumproject->write))
		 || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->scrumproject->scrumproject_advance->validate))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_DONE, $notrigger, 'SCRUMCARD_CLOSE');
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
		if ($this->status != self::STATUS_DONE)
		{
			return 0;
		}

		return $this->setStatusCommon($user, self::STATUS_READY, $notrigger, 'SCRUMCARD_REOPEN');
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

		$label = img_picto('', 'object_'.$this->picto).' <u>'.$langs->trans("ScrumCard").'</u>';
		if (isset($this->status)) {
			$label .= ' '.$this->getLibStatut(5);
		}
		$label .= '<br>';
		$label .= '<b>'.$langs->trans('Ref').':</b> '.$this->ref;

		$url = dol_buildpath('/scrumproject/scrumcard_card.php', 1).'?id='.$this->id;

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
			if (!empty($conf->global->MAIN_OPTIMIZEFORTEXTBROWSER))
			{
				$label = $langs->trans("ShowScrumCard");
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
					if (empty($conf->global->{strtoupper($module.'_'.$class).'_FORMATLISTPHOTOSASUSERS'})) {
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
		$hookmanager->initHooks(array('scrumcarddao'));
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
			$this->labelStatus[self::STATUS_DRAFT] = $langs->trans('StatusScrumCardDraft');
			$this->labelStatus[self::STATUS_READY] = $langs->trans('StatusScrumCardReady');
			$this->labelStatus[self::STATUS_DONE] = $langs->trans('StatusScrumCardDone');
			$this->labelStatusShort[self::STATUS_DRAFT] = $langs->trans('StatusScrumCardDraft');
			$this->labelStatusShort[self::STATUS_READY] = $langs->trans('StatusScrumCardReady');
			$this->labelStatusShort[self::STATUS_DONE] = $langs->trans('StatusScrumCardDone');
		}

		$statusType = 'status'.$status;
		if ($status == self::STATUS_READY) $statusType = 'status4';
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
				if ($obj->fk_user_author)
				{
					$cuser = new User($this->db);
					$cuser->fetch($obj->fk_user_author);
					$this->user_creation = $cuser;
				}

				if ($obj->fk_user_valid)
				{
					$vuser = new User($this->db);
					$vuser->fetch($obj->fk_user_valid);
					$this->user_validation = $vuser;
				}

				if ($obj->fk_user_cloture)
				{
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
	public function getLinesArray()
	{
		$this->lines = array();

		$objectline = new ScrumCardLine($this->db);
		$result = $objectline->fetchAll('ASC', 'position', 0, 0, array('customsql'=>'fk_scrumcard = '.$this->id));

		if (is_numeric($result))
		{
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

		if (empty($conf->global->SCRUMPROJECT_SCRUMCARD_ADDON)) {
			$conf->global->SCRUMPROJECT_SCRUMCARD_ADDON = 'mod_scrumcard_standard';
		}

		if (!empty($conf->global->SCRUMPROJECT_SCRUMCARD_ADDON))
		{
			$mybool = false;

			$file = $conf->global->SCRUMPROJECT_SCRUMCARD_ADDON.".php";
			$classname = $conf->global->SCRUMPROJECT_SCRUMCARD_ADDON;

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

	/**
	 * get this object formatted for jKanan
	 * @return stdClass
	 */
	public function getScrumKanBanItemObjectFormatted(){
		global $user;

		// TODO : voir si $object peut être factorisé avec getScrumKanBanItemObjectStd mais attention il doit être compatible avec l'objet js des items de kanban
		$object = new stdClass();
		$object->id = 'scrumcard-' . $this->id; // kanban dom id

		$object->label = $this->label;
		$object->type = 'scrum-card';
		$object->class = array();     // array of additional classes
		$object->element = $this->element;
		$object->cardUrl = dol_buildpath('/scrumproject/scrumcard_card.php',1).'?id='.$this->id;
		$object->objectId = $this->id;
		$object->title = '';
		$useTime = false;
		$timeSpend = $timePlanned ='--';
		$status = $this->LibStatut(intval($this->status), 2);
		$TContactUsersAffected = $this->liste_contact(-1,'internal');

		/**
		 * Traitement de l'element attaché
		 */
		$res = $this->fetchElementObject();

		if($res){
			$elementObject = $this->elementObject;
			$TContactUsersAffected = $elementObject->liste_contact(-1,'internal');
			$object->element = $elementObject->element;
			$object->targetelementid = $elementObject->id;

			if(is_callable(array($elementObject, 'getScrumKanBanItemObjectFormatted'))){
				$objectFromElement = $elementObject->getScrumKanBanItemObjectFormatted($this, $object);
				if($objectFromElement){
					return $objectFromElement;
				}
			}



			if($elementObject->element == 'scrumproject_scrumuserstorysprint'){
				/** @var ScrumTask $elementObject */
				$useTime = true;
				$timeSpend =   $elementObject->showOutputFieldQuick('qty_consumed');
				$timePlanned = $elementObject->showOutputFieldQuick('qty_planned');

				if(doubleval($elementObject->qty_consumed) > doubleval($elementObject->qty_planned) && $elementObject->qty_planned > 0){
					$object->class[] = '--alert';
					$object->class[] = '--time-consumed-error';
				}

				$us = scrumProjectGetObjectByElement('scrumproject_scrumuserstory', $elementObject->fk_scrum_user_story);

				if($us ){
					$object->label = $us->label;

//					/** @var Task $elementObject */
//					$task = scrumProjectGetObjectByElement('task', $us->fk_task);
//					if($task){
//						$object->label = $task->label; // les us plannifiées n'ont pas de libellé
//					}
//					else{
//						$object->label = '<span class="error">Task Error</span>';
//					}
				}
				else{
					$object->label = '<span class="error">US Error</span>';
				}

				$object->cardUrl = dol_buildpath('/scrumproject/scrumuserstorysprint_card.php',1).'?id='.$elementObject->id;
				$object->type = 'scrum-user-story';

				$status = '';
				if(is_callable(array($elementObject, 'LibStatut'))){
					$status.= $elementObject->LibStatut(intval($elementObject->status), 2);
				}
				$status.= '<span class="highlight-scrum-task prevent-card-click" ></span>';
			}
			elseif($elementObject->element == 'scrumproject_scrumtask'){
				/** @var ScrumTask $elementObject */
				$useTime = true;
				$timeSpend =   $elementObject->showOutputFieldQuick('qty_consumed');
				$timePlanned = $elementObject->showOutputFieldQuick('qty_planned');

				if(doubleval($elementObject->qty_consumed) > doubleval($elementObject->qty_planned) && $elementObject->qty_planned > 0){
					$object->class[] = '--alert';
					$object->class[] = '--time-consumed-error';
				}


				$object->label = $elementObject->showOutputFieldQuick('label');

				$object->cardUrl = dol_buildpath('/scrumproject/scrumtask_card.php',1).'?id='.$elementObject->id;
				$object->type = 'scrum-user-story-task';
				$object->fk_scrum_user_story_sprint = $elementObject->fk_scrum_user_story_sprint;

				if(is_callable(array($elementObject, 'LibStatut'))){
					$status = $elementObject->LibStatut(intval($elementObject->status), 2);
				}
				$status.= '<span class="highlight-scrum-task prevent-card-click" ></span>';
			}
			elseif($elementObject->element == 'project_task'){
				/** @var Task $elementObject */
				$useTime = true;
				$object->type = 'project-task';

				// todo prendre seulement les temps des utilisateurs affectés au kanban et à la tache
				//  + limiter au temps du strint pout la remonté des temps
			}
			else{
				$object->type = 'scrum-card-linked';
			}
		}


		$object->title.= '<div class="kanban-item__header">';

		$object->title.= '</div>';


		$object->title.= '<div class="kanban-item__body">';
		$object->title.= '<span class="kanban-item__label">'.$object->label.'</span>';
		$object->title.= '</div>';


		$object->title.= '<div class="kanban-item__footer">';
		if($useTime){
			$object->title.= '<span class="kanban-item__time-spend">';
//			$object->title.= '<i class="fa fa-hourglass-o"></i> ';
			$object->title.= '<span class="kanban-item__time-consumed">'.$timeSpend.'</span> / <span class="kanban-item__time-planned">'.$timePlanned.'</span>';
			$object->title.= '</span>';
		}
		$object->title.= '<span class="kanban-item__status">'.$status.'</span>';
		$object->title.= '</div>';


		// Afficher les contacts de la carte et/ou object attaché (user story, taches etcc)
		if(!empty($TContactUsersAffected)){
			include_once DOL_DOCUMENT_ROOT . '/core/class/html.form.class.php';
			$object->title.= '<span class="kanban-item__users">';
			foreach ($TContactUsersAffected as $contactUserAffected){
				$userAffected = new User($this->db);
				if($userAffected->fetch($contactUserAffected['id']) > 0){
					$object->title.= self::getUserImg($userAffected, 'kanban-item__user');
				}
			}
			$object->title.= '</span>';
		}

		$object->item = array();

		return $object;
	}


	/**
	 * get this object formatted for ajax ans json
	 * @return stdClass
	 */
	public function getScrumKanBanItemObjectStd(){


		$object = new stdClass();
		$object->objectId = $this->id;
		$object->type = 'scrum-card';// le type dans le kanban tel que getScrumKanBanItemObjectFormatted le fait
		$object->id = 'scrumcard-' . $this->id; // kanban dom id
		$object->label = $this->label;
		$object->element = $this->element;
		$object->cardUrl = dol_buildpath('/scrumproject/scrumcard_card.php',1).'?id='.$this->id;
		$object->title = '';
		$object->status = intval($this->status);
		$object->statusLabel = $this->LibStatut(intval($this->status), 1);
		$object->contactUsersAffected = $this->liste_contact(-1,'internal',1);

		/**
		 * Traitement de l'élément attaché
		 */

		$object->targetelementid = $this->fk_element;
		$object->targetelement = $this->element_type;

		$res = $this->fetchElementObject();
		if($res){
			$object->elementObject = false;
			if(is_callable(array($this->elementObject, 'getScrumKanBanItemObjectStd'))){
				$object->elementObject = $this->elementObject->getScrumKanBanItemObjectStd($this, $object);
			}

			// Si gestion de l'object sans getScrumKanBanItemObjectStd : **typiquement les objects Dolibarr**
			if(!$object->elementObject){
				$object->elementObject = new stdClass();
				$object->elementObject->contactUsersAffected = $this->elementObject->liste_contact(-1,'internal', 1);

				if($this->elementObject->element == 'project_task'){
					$object->type = 'project-task';
				}else{
					$object->type = 'scrum-card-linked';
				}
			}
		}

		return $object;
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
		global $langs, $db;

		// for cache
		if(empty($this->form)){
			$this->form = new Form($db);
		}


		if ($key == 'fk_element')
		{
			$compatibleElementList = $this->getCompatibleElementListLabels();
			$elementTypeMoreParam = ' data-reset-target="#'.$keyprefix.$key.$keysuffix.'" data-reset-value="0" ';
			$out= $this->form->selectarray($keyprefix.'element_type'.$keysuffix, $compatibleElementList, $this->element_type, 1, 0, 0, $elementTypeMoreParam, 0, 0, 0, '', 'scrum-project-form-toggle-trigger scrum-project-form-reset-trigger', 1);

			$out.= '<input type="hidden" id="'.$keyprefix.$key.$keysuffix.'" name="'.$keyprefix.$key.$keysuffix.'" value="'.$this->fk_element.'" />';

			// TODO : a remplacer par une recherche ajax plus propre
			$compatibleElementList = $this->getCompatibleElementList();
			foreach ($compatibleElementList as $item => $itemvalue){

				// utilisation d'un override pour plus de flexibilite : peut etre issue d'un hook de getCompatibleElementList()
				if(!empty($compatibleElementList[$item]['overrideFkElementType']))
				{
					$this->fields[$key]['type'] = $compatibleElementList[$item]['overrideFkElementType']; //'integer:webpassword:webpassword/class/webpassword.class.php:1:statut=1'
				}
				else{
					$this->fields[$key]['type'] = $val['type'] = 'integer:'.$compatibleElementList[$item]['class'].':'.$compatibleElementList[$item]['classfile'].':1';
				}

				// Affichage par defaut du conteneur de formaulaire fonction de $this->element_type
				$containerStatus = 0;
				if($item==$this->element_type){
					$containerStatus = 1;
				}



				$out.= '<div id="container_'.$item.'_'.$keyprefix.$key.$keysuffix.'" class="scrum-project-form-toggle-target" data-display="'.$containerStatus.'" data-toggle-trigger="'.$keyprefix.'element_type'.$keysuffix.'" data-toggle-trigger-value="'.$item.'" >';

				$moreparam = ' data-cloneval-target="#'.$keyprefix.$key.$keysuffix.'" ';
				$out.= parent::showInputField($val, $key, $value, $moreparam, $keysuffix, $item.'_'.$keyprefix, 'scrum-project-form-cloneval-trigger', $nonewbutton);

				$out.= '</div>';
			}


		}
		else{
			$out = parent::showInputField($val, $key, $value, $moreparam, $keysuffix, $keyprefix, $morecss);
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

		if ($key == 'fk_element')
		{
			$compatibleElementList = $this->getCompatibleElementListLabels();

			if(!empty($compatibleElementList[$this->element_type])){
				$out = $compatibleElementList[$this->element_type];

				$this->fetchElementObject();
				if(!empty($this->elementObject->id))
				{
					if(is_callable(array($this->elementObject, 'getNomUrl')))
					{
						$out =  $this->elementObject->getNomUrl(1);
					}
				}
			}
			else{
				$out = '';
			}
		}
		else{
			$out = parent::showOutputField($val, $key, $value, $moreparam, $keysuffix, $keyprefix, $morecss);
		}

		return $out;
	}


	/**
	 * Return array of compatible elements
	 * Code very similar with showOutputField of extra fields
	 *
	 * @return array
	 */
	public function getCompatibleElementListLabels()
	{
		global $langs;

		$compatibleElements = $this->getCompatibleElementList();

		$list = array();

		foreach ($compatibleElements as $key => $value){
			$list[$key] = $value['label'];
		}


		// TODO : add hook here

		return $list;
	}

	/**
	 * Return array of compatible elements
	 * Code very similar with showOutputField of extra fields
	 *
	 * @return array
	 */
	public function getCompatibleElementList()
	{
		global $langs, $user, $conf;
		$error = 0;

		$this->compatibleElementList = array();

		if(!empty($conf->societe->enabled)){
			$this->compatibleElementList['societe'] = array(
				'label' 	=> $langs->trans('Societe'),
				'class' 	=> 'Societe',
				'classfile' => 'societe/class/societe.class.php',
			);
		}

		if(!empty($conf->resource->enabled)){

			$this->compatibleElementList['dolresource'] = array(
				'label' => $langs->trans('Resource'),
				'class' => 'Dolresource',
				'classfile' => 'resource/class/dolresource.class.php',
				'overrideFkElementType' => 'integer:Dolresource:resource/class/dolresource.class.php:1:fk_statut>=0',
			);
		}


		if(!empty($conf->projet->enabled)) {
			$this->compatibleElementList['task'] = array(
				'label' => $langs->trans('Task'),
				'class' => 'Task',
				'classfile' => 'projet/class/task.class.php',
				'overrideFkElementType' => 'integer:Task:projet/class/task.class.php:1',
			);


		}

		// Call triggers for the "security events" log
		include_once DOL_DOCUMENT_ROOT.'/core/class/interfaces.class.php';
		$interface = new Interfaces($this->db);
		$result = $interface->run_triggers('SCRUMPROJECT_GET_COMPATIBLE_ELEMENT_LIST', $this, $user, $langs, $conf);
		if ($result < 0) {
			$error++;
		}
		// End call triggers

		return $this->compatibleElementList;
	}

	/**
	 * test if curent object is compatible with webpassword
	 *
	 * @param CommonObject $object
	 * @return string|false
	 */
	public function isCompatibleElement($object)
	{
		if(!is_object($object)){
			return false;
		}

		if(empty($this->compatibleElementList)){
			$this->getCompatibleElementList();
		}

		foreach ($this->compatibleElementList as $key => $values){
			if($object->element === $key){
				return $key;
			}
		}

		return false;
	}


	/**
	 * Return the number of password stored for the element
	 *
	 * @param string $element
	 * @param  int  $fk_element
	 * @return string|false
	 */
	public function countElementItems($element, $fk_element)
	{
		if(empty($element) || empty($fk_element)){
			return false;
		}

		$sql = 'SELECT COUNT(*) nb FROM '.MAIN_DB_PREFIX.$this->table_element.' t ';
		$sql.= ' WHERE t.fk_element = '.intval($fk_element);
		$sql.= ' AND t.element_type = \''.$this->db->escape($element).'\'';


		$res = $this->db->query($sql);
		if ($res)
		{
			$obj = $this->db->fetch_object($res);
			return $obj->nb;
		}

		return false;
	}


	/**
	 *	Get element object and children from database
	 *	@param      bool	$force       force fetching new
	 *	@return     int         				>0 if OK, <0 if KO, 0 if not found
	 */
	public function fetchElementObject($force = false)
	{
		if(empty($force) && is_object($this->elementObject) && $this->elementObject->id > 0){
			// use cache
			return 1;
		}

		if(!function_exists('scrumProjectGetObjectByElement')){
			require_once __DIR__ . '/../lib/scrumproject.lib.php';
		}

		$this->elementObject = scrumProjectGetObjectByElement($this->element_type, $this->fk_element);
		if($this->elementObject !== false){
			return 1;
		}

		$this->elementObject = false;
		return 0;
	}

	/**
	 * @param string $code
	 * @param CommonObject $object
	 * @return void
	 */
	public static function getInternalContactIdFromCode($code, $object, &$error = ''){
		global $db;
		$sql = "SELECT rowid id FROM ".MAIN_DB_PREFIX."c_type_contact WHERE active=1 AND element='".$db->escape($object->element)."' AND source='internal' AND code = '".$db->escape($code)."' ";
		$obj = $db->getRow($sql);
		if(!empty($obj)){
			return $obj->id;
		}elseif($obj!==false){
			$error = $sql;
			return 0;
		}
		else{
			$error = $db->error();
			return false;
		}
	}

}
