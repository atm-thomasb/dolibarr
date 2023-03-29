<?php
/* Copyright (C) 2021 Maxime Kohlhaas <maxime@m-development.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    core/triggers/interface_99_modScrumProject_ScrumProjectTriggers.class.php
 * \ingroup scrumproject
 * \brief   Example trigger.
 *
 * Put detailed description here.
 *
 * \remarks You can create other triggers by copying this one.
 * - File name should be either:
 *      - interface_99_modScrumProject_MyTrigger.class.php
 *      - interface_99_all_MyTrigger.class.php
 * - The file must stay in core/triggers
 * - The class name must be InterfaceMytrigger
 * - The constructor method must be named InterfaceMytrigger
 * - The name property name must be MyTrigger
 */

require_once DOL_DOCUMENT_ROOT.'/core/triggers/dolibarrtriggers.class.php';


/**
 *  Class of triggers for ScrumProject module
 */
class InterfaceScrumProjectTriggers extends DolibarrTriggers
{
	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;

		$this->name = preg_replace('/^Interface/i', '', get_class($this));
		$this->family = "demo";
		$this->description = "ScrumProject triggers.";
		// 'development', 'experimental', 'dolibarr' or version
		$this->version = 'development';
		$this->picto = 'scrumproject@scrumproject';
	}

	/**
	 * Trigger name
	 *
	 * @return string Name of trigger file
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Trigger description
	 *
	 * @return string Description of trigger file
	 */
	public function getDesc()
	{
		return $this->description;
	}


	/**
	 * Function called when a Dolibarrr business event is done.
	 * All functions "runTrigger" are triggered if file
	 * is inside directory core/triggers
	 *
	 * @param string 		$action 	Event action code
	 * @param CommonObject 	$object 	Object
	 * @param User 			$user 		Object user
	 * @param Translate 	$langs 		Object langs
	 * @param Conf 			$conf 		Object conf
	 * @return int              		<0 if KO, 0 if no triggered ran, >0 if OK
	 */
	public function runTrigger($action, $object, User $user, Translate $langs, Conf $conf)
	{
		if (empty($conf->scrumproject->enabled)) return 0; // If module is not enabled, we do nothing

		// Put here code you want to execute when a Dolibarr business events occurs.
		// Data and type of action are stored into $object and $action

		// You can isolate code for each action in a separate method: this method should be named like the trigger in camelCase.
		// For example : COMPANY_CREATE => public function companyCreate($action, $object, User $user, Translate $langs, Conf $conf)
		$methodName = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower($action)))));
		if (is_callable( array($this, $action))) {
			dol_syslog( "Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id );
			return call_user_func(array($this, $action), $action, $object, $user, $langs, $conf);
		}elseif (is_callable( array($this, $methodName))) {
			dol_syslog( "Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id );
			return call_user_func(array($this, $methodName), $action, $object, $user, $langs, $conf);
		}

		return 0;
	}

	/**
	 * @param           $action
	 * @param  ScrumSprint         $object
	 * @param User      $user
	 * @param Translate $langs
	 * @param Conf      $conf
	 * @return int
	 */
	public function taskTimespentDelete($action, $object, User $user, Translate $langs, Conf $conf) {

		$scrumTask = $this->loadScrumTaskFromProjectTaskSpend($object->timespent_id);
		if($scrumTask){
			// calcule du temps passé sans la saisie en cours
			$scrumTask->calcTimeSpent('AND ptt.rowid != ' . $object->timespent_id);
			return $scrumTask->updateTimeSpent($user, false, false);
		}

		return 0;
	}

	/**
	 * @param           $action
	 * @param  ScrumSprint         $object
	 * @param User      $user
	 * @param Translate $langs
	 * @param Conf      $conf
	 * @return int
	 */
	public function taskTimespentModify($action, $object, User $user, Translate $langs, Conf $conf) {

		$scrumTask = $this->loadScrumTaskFromProjectTaskSpend($object->timespent_id);
		if($scrumTask){
			// calcule du temps passé sans la saisie en cours
			$scrumTask->calcTimeSpent('AND ptt.rowid != '.$object->timespent_id);

			// ajoute la saisie en cours
			$scrumTask->qty_consumed+= round(intval($object->timespent_duration) / 3600 , 2);

			return $scrumTask->updateTimeSpent($user, false, false);
		}

		return 0;
	}

	/**
	 * @param           $action
	 * @param  ScrumSprint         $object
	 * @param User      $user
	 * @param Translate $langs
	 * @param Conf      $conf
	 * @return int
	 */
	public function scrumSprintCreate($action, $object, User $user, Translate $langs, Conf $conf) {
		$object->fetch($object->id);

		// Add users to the newly created sprint
		$res = $object->addTeamMembers();
		if($res > 0) {
			setEventMessage($langs->trans('ScrumSprintUserAdded', $res));
		} else if($res < 0) {
			setEventMessages($langs->trans('ScrumSprintUserAddedError'), $object->errors, 'errors');
			return -1;
		}

		return 0;
	}

	public function advKanbanDelete($action, $object, User $user, Translate $langs, Conf $conf) {
		include_once __DIR__ . '/../../class/scrumsprint.class.php';

		$sprint = ScrumSprint::getScrumSprintFromKanban($object->id);
		if($sprint){
			$sprint->fk_advkanban = null;
			if($sprint->update($user)<0){
				$this->errors[] = 'Fail update linked sprint';
				return -1;
			}
		}
	}

	public function advKanbanCardCreate($action, $object, User $user, Translate $langs, Conf $conf) {
		$this->_updateSprintQuantities($action, $object, $user, $langs, $conf);
	}

	public function advKanbanCardModify($action, $object, User $user, Translate $langs, Conf $conf) {
		$this->_updateSprintQuantities($action, $object, $user, $langs, $conf);
	}

	public function advKanbanCardDone($action, $object, User $user, Translate $langs, Conf $conf) {
		$this->_updateSprintQuantities($action, $object, $user, $langs, $conf);
	}

	public function advKanbanCardReopen($action, $object, User $user, Translate $langs, Conf $conf) {
		$this->_updateSprintQuantities($action, $object, $user, $langs, $conf);
	}

	public function _updateSprintQuantities($action, $object, User $user, Translate $langs, Conf $conf) {
		dol_include_once('/scrumproject/class/scrumsprint.class.php');
		$sprint = new ScrumSprint($this->db);
		$sprint->fetch($object->fk_scrumsprint);
		$res = $sprint->refreshQuantities($user);

		if($res < 0) {
			setEventMessages($langs->trans('ScrumSprintQuantitiesCalculatedError'), $sprint->errors, 'errors');
			return -1;
		}

		return 0;
	}

	/**
	 * TRIGGER : TASK_TIMESPENT_CREATE
	 * @param           $action
	 * @param           $object
	 * @param User      $user
	 * @param Translate $langs
	 * @param Conf      $conf
	 * @return void
	 */
	public function taskTimespentCreate($action, $object, User $user, Translate $langs, Conf $conf) {

		if(!empty($object->fk_scrumproject_scrumtask) && $object->fk_scrumproject_scrumtask > 0){
			$scrumTask = new ScrumTask($object->db);
			$scrumTask->id = $object->fk_scrumproject_scrumtask;
			$res = $scrumTask->linkTaskTimeToScrumTask($object->timespent_id);

			if($res < 0){
				$this->errors[] = $scrumTask->errorsToString();
				return -1;
			}
		}

	}

	/**
	 * TRIGGER : ADVANCEDKANBAN_GET_COMPATIBLE_ELEMENT_LIST
	 * @param           $action
	 * @param AdvKanbanCard $object
	 * @param User      $user
	 * @param Translate $langs
	 * @param Conf      $conf
	 * @return void
	 */
	public function advancedkanbanGetCompatibleElementList($action, $object, User $user, Translate $langs, Conf $conf) {

		if ($object->element_type == 'scrumproject_scrumtask'){
			$object->fields['fk_element']['visible'] = '5'; // Non mofifiable
		}

		// OVERRRIDE COMPATIBLE LIST we can't change
		$object->compatibleElementList['scrumproject_scrumtask'] = array(
			'selectable' => false,
			'label' => $langs->trans('ScrumTask'),
			'class' => 'ScrumTask',
			'classfile' => 'scrumproject/class/scrumtask.class.php',
		);

		if ($object->element_type == 'scrumproject_scrumuserstorysprint'){
			$object->fields['fk_element']['visible'] = '5'; // Non mofifiable
		}

		// OVERRRIDE COMPATIBLE LIST we can't change
		$object->compatibleElementList['scrumproject_scrumuserstorysprint'] = array(
			'selectable' => false,
			'label' => $langs->trans('ScrumUserStorySprint'),
			'class' => 'ScrumUserStorySprint',
			'classfile' => 'scrumproject/class/ScrumUserStorySprint.class.php',
		);
	}



	/**
	 * @param $timespentId
	 * @return Scrumtask
	 */
	public function loadScrumTaskFromProjectTaskSpend($timespentId){
		// scrumttask contient le total declarée et le total consommé
		$sql  = ' SELECT spt.fk_scrumproject_scrumtask   as scrumtask_id, Tablett.task_duration as qty ';
		$sql .= ' FROM ' .MAIN_DB_PREFIX .'scrumproject_scrumtask_projet_task_time as spt';
		$sql .= ' INNER JOIN '.MAIN_DB_PREFIX.'projet_task_time as Tablett ON Tablett.rowid = spt.fk_projet_task_time ';
		$sql .= ' WHERE spt.fk_projet_task_time ='. intval($timespentId);

		$obj = $this->db->getRow($sql);
		if ($obj){
			include_once __DIR__ .'/../../lib/scrumproject.lib.php';
			return scrumProjectGetObjectByElement('scrumproject_scrumtask', $obj->scrumtask_id,  0);
		}

		return false;
	}

}
