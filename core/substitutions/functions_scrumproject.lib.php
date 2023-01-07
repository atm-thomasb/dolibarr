<?php

/** 		Function called to complete substitution array (before generating on ODT, or a personalized email)
 * 		functions xxx_completesubstitutionarray are called by make_substitutions() if file
 * 		is inside directory htdocs/core/substitutions
 *
 * 		@param	array		$substitutionarray	Array with substitution key=>val
 * 		@param	Translate	$langs			Output langs
 * 		@param	Object		$object			Object to use to get values
 * 		@return	void					The entry parameter $substitutionarray is modified
 */
function scrumproject_completesubstitutionarray(&$substitutionarray, $langs, $object) {

	if($object->element == 'scrumproject_scrumuserstorysprint' ) {
		/** @var ScrumUserStorySprint $object  */
		completeSubstitutionFromScrumUserStorySprint($substitutionarray, $langs, $object);
	}

	if($object->element == 'scrumproject_scrumtask' ) {
		if(!function_exists('scrumProjectGetObjectByElement')){
			require_once __DIR__ . '/../../lib/scrumproject.lib.php';
		}

		/** @var ScrumTask $object  */
		$userStorySprint = scrumProjectGetObjectByElement('scrumproject_scrumuserstorysprint', $object->fk_scrum_user_story_sprint);
		completeSubstitutionFromScrumUserStorySprint($substitutionarray, $langs, $userStorySprint);
	}
}

/**
 * @param array $substitutionarray	Array with substitution key=>val
 * @param Translate $langs Output langs
 * @param ScrumUserStorySprint $object
 * @return void
 */
function completeSubstitutionFromScrumUserStorySprint(&$substitutionarray, $langs, ScrumUserStorySprint $object){

	// define default value as empty to avoid substitution replacement fail on empty
	$substitutionarray['__PROJECT_TASK_REF__'] = '';
	$substitutionarray['__PROJECT_TASK_LABEL__'] = '';
	$substitutionarray['__PROJECT_REF__'] = '';
	$substitutionarray['__PROJECT_LABEL__'] = '';
	$substitutionarray['__PROJECT_THIRDPARTY_NAME__'] = ''; // TODO reprendre le format standard de dolibarr pour les substitutions de thirdparty


	$substitutionarray['__USPLANNED_LABEL__'] = $object->showOutputFieldQuick('label');
	$substitutionarray['__USPLANNED_QTY_PLANNED__'] = $object->showOutputFieldQuick('qty_planned');
	$substitutionarray['__USPLANNED_QTY_CONSUMED__'] = $object->showOutputFieldQuick('qty_consumed');
	$substitutionarray['__USPLANNED_QTY_DONE__'] = $object->showOutputFieldQuick('qty_done');
	$substitutionarray['__USPLANNED_DESCRIPTION__'] = $object->showOutputFieldQuick('description');


	$substitutionarray['__US_LABEL__'] = '';
	$substitutionarray['__US_DESCRIPTION__'] = '';

	if($object->fk_scrum_user_story > 0){

		if(!function_exists('scrumProjectGetObjectByElement')){
			require_once __DIR__ . '/../../lib/scrumproject.lib.php';
		}

		$userStory = scrumProjectGetObjectByElement('scrumproject_scrumuserstory', $object->fk_scrum_user_story);
		if($userStory){
			/**
			 * @var ScrumUserStory $userStory
			 */
			$substitutionarray['__US_LABEL__'] = $userStory->showOutputFieldQuick('label');
			$substitutionarray['__US_DESCRIPTION__'] = $userStory->showOutputFieldQuick('description');

			// lorsqu'une US plannifié na pas de label c'est qu'elle utilise celui défini sur l'US
			if(empty($substitutionarray['__USPLANNED_LABEL__']) && !empty($substitutionarray['__US_LABEL__'])){
				$substitutionarray['__USPLANNED_LABEL__'] = $substitutionarray['__US_LABEL__'];
			}


			if($userStory->fk_task > 0 && $task = scrumProjectGetObjectByElement('projet_task', $userStory->fk_task)){
				/** @var Task $task  */
				$substitutionarray['__PROJECT_TASK_REF__'] = $task->ref;
				$substitutionarray['__PROJECT_TASK_LABEL__'] = $task->label;
				if($project = scrumProjectGetObjectByElement('project', $task->fk_project)){
					/** @var Project $project  */
					$substitutionarray['__PROJECT_REF__'] = $project->ref;
					$substitutionarray['__PROJECT_LABEL__'] = $project->title;
					$substitutionarray['__PROJECT_THIRDPARTY_NAME__'] = $project->thirdparty_name;

					if($project->fetch_thirdparty()>0){
						$substitutionarray['__PROJECT_THIRDPARTY_NAME__'] = $project->thirdparty->getFullName($langs,1);
					}
				}
			}
		}
	}
}
