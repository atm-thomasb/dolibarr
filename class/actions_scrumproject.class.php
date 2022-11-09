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
	 * @param $parameters
	 * @param $object
	 * @param $hookmanager
	 * @return int
	 */
	public function constructCategory($parameters, $object, $hookmanager) {
        $this->results = array(array(
            'id' => 14,
            'code' => 'scrumcard',
            'cat_fk' => 'scrumcard',
            'cat_table' => 'scrumcard',
            'obj_class' => 'ScrumCard',
            'obj_table' => 'scrumproject_scrumcard',
        ));
        return 1;
    }
}
