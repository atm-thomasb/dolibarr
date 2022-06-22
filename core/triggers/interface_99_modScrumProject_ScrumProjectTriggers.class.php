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
		$callback = array($this, $methodName);
		if (is_callable($callback)) {
			dol_syslog(
				"Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id
			);

			return call_user_func($callback, $action, $object, $user, $langs, $conf);
		};




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

	public function scrumCardCreate($action, $object, User $user, Translate $langs, Conf $conf) {
		$this->_updateSprintQuantities($action, $object, $user, $langs, $conf);
	}

	public function scrumCardModify($action, $object, User $user, Translate $langs, Conf $conf) {
		$this->_updateSprintQuantities($action, $object, $user, $langs, $conf);
	}

	public function scrumCardDone($action, $object, User $user, Translate $langs, Conf $conf) {
		$this->_updateSprintQuantities($action, $object, $user, $langs, $conf);
	}

	public function scrumCardReopen($action, $object, User $user, Translate $langs, Conf $conf) {
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

}
