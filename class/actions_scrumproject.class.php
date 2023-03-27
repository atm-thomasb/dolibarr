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
 * \file    scrumproject/class/actions_scrumproject.class.php
 * \ingroup scrumproject
 * \brief   Example hook overload.
 *
 * Put detailed description here.
 */

/**
 * Class ActionsScrumProject
 */
class ActionsScrumProject
{
	/**
	 * @var DoliDB Database handler.
	 */
	public $db;

	/**
	 * @var string Error code (or message)
	 */
	public $error = '';

	/**
	 * @var array Errors
	 */
	public $errors = array();


	/**
	 * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var string String displayed by executeHook() immediately after return
	 */
	public $resprints;


	/**
	 * Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * @param $parameters
	 * @param $object
	 * @param $action
	 * @return void
	 */
	public function formDolBanner($parameters, $object, $action){

		if (in_array($parameters['currentcontext'], array('scrumtaskcard')))		// do something only for the context 'somecontext1' or 'somecontext2'
		{
			?>
			<script>
				$(document).ready(function () {
					// on cache le statut de la carte
					$(".statusref").hide();
				});

			</script>
			<?php
		}
		return 0;
	}



	/**
	 * elementList Method Hook Call
	 *
	 * @param array $parameters parameters
	 * @param Object &$object Object to use hooks on
	 * @param string &$action Action code on calling page ('create', 'edit', 'view', 'add', 'update', 'delete'...)
	 * @param object $hookmanager class instance
	 * @return void
	 */
	public function emailElementlist($parameters, &$object, &$action, $hookmanager)
	{
		global $langs;
		$langs->load('scrumproject@scrumproject');

		$img = '<img src="'.dol_buildpath('scrumproject/img/object_scrumuserstorysprint.png',1).'" >';
		$this->results['scrumtask'] = $img.' '.$langs->trans('ScrumTaskMailModel');

		$img = '<span class="fa fa-lightbulb" style="color: #cb4f24;"></span>';
		$this->results['scrumuserstorysprint'] = $img.' '.$langs->trans('ScrumUserStoryMailModel');

		return 0;
	}

	/**
	 * addHtmlHeader Method Hook Call
	 *
	 * @param array $parameters parameters
	 * @param Object &$object Object to use hooks on
	 * @param string &$action Action code on calling page ('create', 'edit', 'view', 'add', 'update', 'delete'...)
	 * @param object $hookmanager class instance
	 * @return void
	 */
	public function addHtmlHeader($parameters, &$object, &$action, $hookmanager)
	{
		global $db;
		$TContext = explode(':', $parameters['context']);

		if (in_array('advkanbanview', $TContext))
		{
			include_once __DIR__ .'/scrumsprint.class.php';
			$scrumSprint = ScrumSprint::getScrumSprintFromKanban(GETPOST('id', 'int'));
			if($scrumSprint) {
				print '<!-- ScrumProject hooks -->' . "\n";
				print '<script src="' . dol_buildpath('scrumproject/js/scrumkanban.js', 1) . '"></script>' . "\n";
				print '<link rel="stylesheet" type="text/css" href="' . dol_buildpath('scrumproject/css/kanban.css', 1) . '" />' . "\n";
			}
		}

		return 0;
	}

	/**
	 * doActions Method Hook Call
	 *
	 * @param array $parameters parameters
	 * @param Object &$object Object to use hooks on
	 * @param string &$action Action code on calling page ('create', 'edit', 'view', 'add', 'update', 'delete'...)
	 * @param object $hookmanager class instance
	 * @return void
	 */
	public function doActions($parameters, &$object, &$action, $hookmanager)
	{
		global $langs;
		$TContext = explode(':', $parameters['context']);

		if (in_array('advkanbanview', $TContext))
		{
			$confToJs =& $parameters['confToJs'];
			$confToJs['interface_scrum_project_kanban_url'] = dol_buildpath('scrumproject/interface-scrum-project-kanban.php',1);
			$confToJs['srumprojectModuleFolderUrl'] 		= dol_buildpath('scrumproject/',1);
			$confToJs['maxScrumTaskStepQty'] 				= getDolGlobalString('SP_MAX_SCRUM_TASK_STEP_QTY', 0);
			$confToJs['maxScrumTaskMaxQty'] 				= getDolGlobalString('SP_MAX_SCRUM_TASK_MAX_QTY', 0);

//			$jsLangs = $parameters['jsLangs'];

		}

		return 0;
	}

	/**
	 * kanbanParamNavBar Method Hook Call
	 *
	 * @param array $parameters parameters
	 * @param Object $advKanban Object to use hooks on
	 * @param string $action Action code on calling page ('create', 'edit', 'view', 'add', 'update', 'delete'...)
	 * @param object $hookmanager class instance
	 * @return void
	 */
	public function kanbanParamNavBar($parameters, $advKanban, &$action, $hookmanager)
	{
		global $db, $langs;
		$TContext = explode(':', $parameters['context']);
		if (in_array('advkanbanview', $TContext))
		{
			require_once __DIR__ . '/../class/scrumsprint.class.php';
			$scrumSprint = ScrumSprint::getScrumSprintFromKanban($advKanban->id);

			$this->resprints.= '<span id="kanban-header-scrum-sprint-resume">';
			if ($scrumSprint){

				$fieldK = 'label';
				$this->resprints.= '<span class="kanban-header__item"  >';
				$this->resprints.= '<span class="kanban-header__item__value" data-element="'.$scrumSprint->element.'" data-field="'.$fieldK.'" >';
				$this->resprints.= $scrumSprint->getNomUrl(1); //. ' '.$scrumSprint->showOutputFieldQuick($fieldK);
				$this->resprints.= '</span>';
				$this->resprints.= '</span>';


				$fieldK = 'fk_team';
				$this->resprints.= '<span class="kanban-header__item"  >';
				$this->resprints.= '<span class="kanban-header__item__value" data-element="'.$scrumSprint->element.'" data-field="'.$fieldK.'" >';
				$this->resprints.= $scrumSprint->showOutputFieldQuick($fieldK);
				$this->resprints.= '</span>';
				$this->resprints.= '</span>';

				/*
				$this->resprints.= '<span class="kanban-header__item" >';
				$this->resprints.= '<span class="fa fa-calendar-alt" ></span>';
				$fieldK = 'date_start';
				$this->resprints.= '<span class="kanban-header__item__value" data-element="'.$scrumSprint->element.'" data-field="'.$fieldK.'" >';
				$this->resprints.= $scrumSprint->showOutputFieldQuick($fieldK);
				$this->resprints.= '</span>';

				$this->resprints.= '<span class="kanban-header__item__value">-</span>';

				$fieldK = 'date_end';
				$this->resprints.= '<span class="kanban-header__item__value" data-element="'.$scrumSprint->element.'" data-field="'.$fieldK.'" >';
				$this->resprints.= $scrumSprint->showOutputFieldQuick($fieldK);
				$this->resprints.= '</span>';

				$this->resprints.= '</span>';
				*/


				$fieldK = 'qty_velocity';
				$this->resprints.= '<span class="kanban-header__item"  data-ktooltip="'.dol_escape_htmltag($langs->trans($scrumSprint->fields[$fieldK]['label'])).'" >';
				$this->resprints.= '<span class="fa fa-running" ></span>';
				$this->resprints.= '<span class="kanban-header__item__value" data-element="'.$scrumSprint->element.'" data-field="'.$fieldK.'" >';
				$this->resprints.= $scrumSprint->showOutputFieldQuick($fieldK);
				$this->resprints.= '</span>';
				$this->resprints.= '</span>';

				$fieldK = 'qty_planned';
				$this->resprints.= '<span class="kanban-header__item"  data-ktooltip="'.dol_escape_htmltag($langs->trans($scrumSprint->fields[$fieldK]['label'])).'" >';
				$this->resprints.= '<span class="fa fa-calendar-check-o" ></span>';
				$this->resprints.= '<span class="kanban-header__item__value" data-element="'.$scrumSprint->element.'" data-field="'.$fieldK.'" >';
				$this->resprints.= $scrumSprint->showOutputFieldQuick($fieldK);
				$this->resprints.= '</span>';
				$this->resprints.= '</span>';


				$fieldK = 'qty_consumed';
				$this->resprints.= '<span class="kanban-header__item"  data-ktooltip="'.dol_escape_htmltag($langs->trans($scrumSprint->fields[$fieldK]['label'])).'" >';
				$this->resprints.= '<span class="fa fa-hourglass-o" ></span>';
				$this->resprints.= '<span class="kanban-header__item__value" data-element="'.$scrumSprint->element.'" data-field="'.$fieldK.'" >';
				$this->resprints.= $scrumSprint->showOutputFieldQuick($fieldK);
				$this->resprints.= '</span>';
				$this->resprints.= '</span>';

				$fieldK = 'qty_done';
				$this->resprints.= '<span class="kanban-header__item"  data-ktooltip="'.dol_escape_htmltag($langs->trans($scrumSprint->fields[$fieldK]['label'])).'" >';
				$this->resprints.= '<span class="fa fa-check" ></span>';
				$this->resprints.= '<span class="kanban-header__item__value" data-element="'.$scrumSprint->element.'" data-field="'.$fieldK.'" >';
				$this->resprints.= $scrumSprint->showOutputFieldQuick($fieldK);
				$this->resprints.= '</span>';
				$this->resprints.= '</span>';


				// get US done in this kanban
				$this->resprints.= '<span class="kanban-header__item"  data-ktooltip="'.dol_escape_htmltag($langs->trans('UserStoryPlannedDone')).'" >';
				$this->resprints.= '<span class="fa fa-check-double" ></span>';
				$this->resprints.= '<span class="kanban-header__item__value" data-element="'.$scrumSprint->element.'" data-field="qty_us_planned_done" >';
				$this->resprints.= $scrumSprint->calcUsPlannedInList('done');
				$this->resprints.= '</span>';
				$this->resprints.= '</span>';


				// get US done in this kanban
				$this->resprints.= '<button class="nav-button" id="kanban-resume-btn"  >';
				$this->resprints.= '<span class="fa fa-dashboard" ></span>';
				$this->resprints.= '</button>';
			}

			$this->resprints.= '</span>';
		}

		return 0;
	}

	/**
	 * kanbanParamPanelBefore Method Hook Call
	 *
	 * @param array $parameters parameters
	 * @param Object $advKanban Object to use hooks on
	 * @param string $action Action code on calling page ('create', 'edit', 'view', 'add', 'update', 'delete'...)
	 * @param object $hookmanager class instance
	 * @return void
	 */
	public function kanbanParamPanelBefore($parameters, $advKanban, &$action, $hookmanager){
		global $langs;

		$TContext = explode(':', $parameters['context']);
		if (in_array('advkanbanview', $TContext)) {
			require_once __DIR__ . '/../class/scrumsprint.class.php';
			$scrumSprint = ScrumSprint::getScrumSprintFromKanban($advKanban->id);

			$this->resprints = '';
			if ($scrumSprint) {

				$langs->load('scrumproject@scrumproject');

				$this->resprints.= '<details class="option-box">';
				$this->resprints.= '	<summary class="option-box-title" >'.$langs->trans('SprintInfos').'</summary>';
				$this->resprints.= '		<div class="option-box-content">';

				$this->resprints.= '			<div class="panel-infos">';

				$fieldK = 'label';
				$this->resprints.= $scrumSprint->getNomUrl(1).' : '.$scrumSprint->showOutputFieldQuick($fieldK).'<br/>';
				$fieldK = 'fk_team';
				$this->resprints.= $langs->trans($scrumSprint->fields[$fieldK]['label']).' : '.$scrumSprint->showOutputFieldQuick($fieldK).'<br/>';

				$this->resprints.= '<span class="fa fa-calendar-alt" ></span>';
				$this->resprints.= $scrumSprint->showOutputFieldQuick('date_start').' - '.$scrumSprint->showOutputFieldQuick('date_start').'<br/>';

				$this->resprints.= '			</div>';


				$this->resprints.= '		</div>';
				$this->resprints.= '</details>';
			}
		}
	}

	/**
	 * kanbanParamNavBar Method Hook Call
	 *
	 * @param array $parameters parameters
	 * @param AdvKanbanList $advKanbanList Object to use hooks on
	 * @param string $action Action code on calling page ('create', 'edit', 'view', 'add', 'update', 'delete'...)
	 * @param object $hookmanager class instance
	 * @return void
	 */
	public function getKanBanListObjectFormatted($parameters, $advKanbanList, &$action, $hookmanager)
	{
		global $db, $langs;
		$TContext = explode(':', $parameters['context']);
		if (in_array('advkanbandao', $TContext)) {
			$formattedObject = $parameters['formattedObject'];

			include_once __DIR__ . "/../lib/scrumproject.lib.php" ;
			require_once __DIR__ . '/../class/scrumsprint.class.php';
			$sprint = ScrumSprint::getScrumSprintFromKanban($advKanbanList->fk_advkanban);

			if(!$sprint){
				return 0;
			}

			$formattedObject->title.= '<div class="kanban-title-board__scrumproject_resume" >';


			/**
			 * Scrum USER STORIES PLANNED data
			 */
			$sql = /** @lang MySQL */
				"SELECT SUM(us.qty_planned) sumPlanned, SUM(us.qty_consumed) sumConsumed "
				." FROM ".MAIN_DB_PREFIX."scrumproject_scrumuserstorysprint us "
				." JOIN ".MAIN_DB_PREFIX."advancedkanban_advkanbancard c ON (c.fk_element = us.rowid AND c.element_type = 'scrumproject_scrumuserstorysprint' )"
				." WHERE  c.fk_advkanbanlist = ".intval($advKanbanList->id);

			$objUS = $this->db->getRow($sql);
			if($objUS){

				$objUS->sumPlanned = convertFloatHourToHoursMins($objUS->sumPlanned,$langs) ;
				if(is_null($objUS->sumPlanned)){
					$objUS->sumPlanned = '00h00';
				}
				$objUS->sumConsumed = convertFloatHourToHoursMins($objUS->sumConsumed,$langs) ;
				if(is_null($objUS->sumConsumed)){
					$objUS->sumConsumed = '00h00';
				}

//				$formattedObject->title.= img_picto($langs->trans('ScrumUserStory'), 'scrumuserstory@scrumproject', '', false, 0, 0, '', 'kanban-title-board__scrum-resume-obj-icon');
				$formattedObject->title.= ' <span class="kanban-title-board__scrumtime qty-consumed-scrum-us"  title="'.dol_escape_htmltag($langs->trans('QtyConsumed').' ('.$langs->trans('ScrumUserStory').')').'"><span class="fa fa-hourglass-o"></span> '.$objUS->sumConsumed.'</span>';
				$formattedObject->title.= ' <span class="kanban-title-board__scrumtime qty-planned-scrum-us"  title="'.dol_escape_htmltag($langs->trans('QtyPlanned').' ('.$langs->trans('ScrumUserStory').')').'"><span class="fa fa-calendar-plus-o"></span> '.$objUS->sumPlanned.'</span>';
			}


			/**
			 * Scrum TASK data
			 */
			$sql = /** @lang MySQL */
				"SELECT SUM(t.qty_planned) sumPlanned, SUM(t.qty_consumed) sumConsumed "
				." FROM ".MAIN_DB_PREFIX."scrumproject_scrumtask t "
				." JOIN ".MAIN_DB_PREFIX."advancedkanban_advkanbancard c ON (c.fk_element = t.rowid AND c.element_type = 'scrumproject_scrumtask' )"
				." WHERE  c.fk_advkanbanlist = ".intval($advKanbanList->id);

			$objTasks = $this->db->getRow($sql);
			if($objTasks){
				$objTasks->sumPlanned = convertFloatHourToHoursMins($objTasks->sumPlanned,$langs) ;
				if(is_null($objTasks->sumPlanned)){
					$objTasks->sumPlanned = '00h00';
				}
				$objTasks->sumConsumed = convertFloatHourToHoursMins($objTasks->sumConsumed,$langs) ;
				if(is_null($objTasks->sumConsumed)){
					$objTasks->sumConsumed = '00h00';
				}

//				$formattedObject->title.= img_picto($langs->trans('ScrumUserStory'), 'scrumtask@scrumproject', '', false, 0, 0, '', 'kanban-title-board__scrum-resume-obj-icon');
				$formattedObject->title.= ' <span class="kanban-title-board__scrumtime qty-consumed-scrum-task"  title="'.dol_escape_htmltag($langs->trans('QtyConsumed').' ('.$langs->trans('ScrumTasks').')').'"><span class="fa fa-hourglass-o"></span> '.$objTasks->sumConsumed.'</span>';
				$formattedObject->title.= ' <span class="kanban-title-board__scrumtime qty-planned-scrum-task"  title="'.dol_escape_htmltag($langs->trans('QtyPlanned').' ('.$langs->trans('ScrumTasks').')').'"><span class="fa fa-calendar-plus-o"></span> '.$objTasks->sumPlanned.'</span>';
			}


			$formattedObject->title.= '</div>';
		}
	}


	/**
	 * kanbanFilterPanelAfter Method Hook Call
	 *
	 * @param array $parameters parameters
	 * @param Object $advKanban Object to use hooks on
	 * @param string $action Action code on calling page ('create', 'edit', 'view', 'add', 'update', 'delete'...)
	 * @param object $hookmanager class instance
	 * @return void
	 */
	public function kanbanFilterPanelAfter($parameters, $advKanban, &$action, $hookmanager)
	{
		global $db, $langs;
		$TContext = explode(':', $parameters['context']);
		if (in_array('advkanbanview', $TContext)) {
			require_once __DIR__ . '/../class/scrumsprint.class.php';
			$scrumSprint = ScrumSprint::getScrumSprintFromKanban($advKanban->id);

			if ($scrumSprint) {
				$this->resprints .= '<div class="result-resume-item">'.$langs->trans('CardScrumUserStoryFound').' : <span id="nb-scrum-user-story-found"></span></div>';
				$this->resprints .= '<div class="result-resume-item">'.$langs->trans('CardScrumTaskFound').' : <span id="nb-scrum-task-found"></span></div>';
			}
		}
	}
}
