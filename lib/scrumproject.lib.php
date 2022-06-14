<?php
/* Copyright (C) 2020 Maxime Kohlhaas <maxime@m-development.com>
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
 * \file    scrumproject/lib/scrumproject.lib.php
 * \ingroup scrumproject
 * \brief   Library files with common functions for ScrumProject
 */

/**
 * Prepare admin pages header
 *
 * @return array
 */
function scrumprojectAdminPrepareHead()
{
	global $langs, $conf;

	$langs->load("scrumproject@scrumproject");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/scrumproject/admin/setup.php", 1);
	$head[$h][1] = $langs->trans("Settings");
	$head[$h][2] = 'settings';
	$h++;

	$head[$h][0] = dol_buildpath("/scrumproject/admin/scrumsprint_extrafields.php", 1);
	$head[$h][1] = $langs->trans("ScrumSprintExtraFields");
	$head[$h][2] = 'scrumsprint_extrafields';
	$h++;

	$head[$h][0] = dol_buildpath("/scrumproject/admin/scrumuserstory_extrafields.php", 1);
	$head[$h][1] = $langs->trans("ScrumUserStoryExtraFields");
	$head[$h][2] = 'scrumuserstory_extrafields';
	$h++;

	$head[$h][0] = dol_buildpath("/scrumproject/admin/srcumuserstorysprint_extrafields.php", 1);
	$head[$h][1] = $langs->trans("ScrumUserStorySprintExtraFields");
	$head[$h][2] = 'scrumuserstorysprint_extrafields';
	$h++;

	$head[$h][0] = dol_buildpath("/scrumproject/admin/scrumkanban_extrafields.php", 1);
	$head[$h][1] = $langs->trans("ScrumKanbanExtraFields");
	$head[$h][2] = 'scrumkanban_extrafields';
	$h++;

	$head[$h][0] = dol_buildpath("/scrumproject/admin/scrumcard_extrafields.php", 1);
	$head[$h][1] = $langs->trans("ScrumCardExtraFields");
	$head[$h][2] = 'scrumcard_extrafields';
	$h++;

	$head[$h][0] = dol_buildpath("/scrumproject/admin/about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@scrumproject:/scrumproject/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@scrumproject:/scrumproject/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, null, $head, $h, 'scrumproject');

	return $head;
}


/**
 * Create a new object instance based on the element type
 * Fetch the object if id is provided
 *
 * @param string $elementType Type of object ('invoice', 'order', 'expedition_bon', 'myobject@mymodule', ...)
 * @param int    $elementId   Id of element to provide if fetch is needed
 * @param int    $maxCacheByType max number of cached element by type
 * @return CommonObject object of $elementType, fetched by $elementId
 */
function scrumProjectGetObjectByElement($elementType, $elementId = 0, $maxCacheByType = 10)
{
	global $conf, $db;

	$regs = array();

	// Parse $objecttype (ex: project_task)
	$module = $myObject = $elementType;

	// If we ask an resource form external module (instead of default path)
	if (preg_match('/^([^@]+)@([^@]+)$/i', $elementType, $regs)) {
		$myObject = $regs[1];
		$module = $regs[2];
	}


	if (preg_match('/^([^_]+)_([^_]+)/i', $elementType, $regs))
	{
		$module = $regs[1];
		$myObject = $regs[2];
	}

	// Generic case for $classpath
	$classpath = $module.'/class';

	// Special cases, to work with non standard path
	if ($elementType == 'facture' || $elementType == 'invoice') {
		$classpath = 'compta/facture/class';
		$module='facture';
		$myObject='facture';
	}
	elseif ($elementType == 'commande' || $elementType == 'order') {
		$classpath = 'commande/class';
		$module='commande';
		$myObject='commande';
	}
	elseif ($elementType == 'contact')  {
		$module = 'societe';
	}
	elseif ($elementType == 'propal')  {
		$classpath = 'comm/propal/class';
	}
	elseif ($elementType == 'shipping') {
		$classpath = 'expedition/class';
		$myObject = 'expedition';
		$module = 'expedition';
	}
	elseif ($elementType == 'delivery') {
		$classpath = 'delivery/class';
		$myObject = 'delivery';
		$module = 'expedition';
	}
	elseif ($elementType == 'contract') {
		$classpath = 'contrat/class';
		$module='contrat';
		$myObject='contrat';
	}
	elseif ($elementType == 'member') {
		$classpath = 'adherents/class';
		$module='adherent';
		$myObject='adherent';
	}
	elseif ($elementType == 'cabinetmed_cons') {
		$classpath = 'cabinetmed/class';
		$module='cabinetmed';
		$myObject='cabinetmedcons';
	}
	elseif ($elementType == 'fichinter') {
		$classpath = 'fichinter/class';
		$module='ficheinter';
		$myObject='fichinter';
	}
	elseif ($elementType == 'task') {
		$classpath = 'projet/class';
		$module='projet';
		$myObject='task';
	}
	elseif ($elementType == 'stock') {
		$classpath = 'product/stock/class';
		$module='stock';
		$myObject='stock';
	}
	elseif ($elementType == 'inventory') {
		$classpath = 'product/inventory/class';
		$module='stock';
		$myObject='inventory';
	}
	elseif ($elementType == 'mo') {
		$classpath = 'mrp/class';
		$module='mrp';
		$myObject='mo';
	}
	elseif ($elementType == 'salary') {
		$classpath = 'salaries/class';
		$module='salaries';
	}
	elseif ($elementType == 'chargesociales') {
		$classpath = 'compta/sociales/class';
		$module='tax';
	}
	elseif ($elementType == 'tva') {
		$classpath = 'compta/tva/class';
		$module='tax';
	}
	elseif ($elementType == 'widthdraw') {
		$classpath = 'compta/prelevement/class';
		$module='prelevement';
		$myObject='bonprelevement';
	}
	elseif ($elementType == 'project') {
		$classpath = 'projet/class';
		$module='projet';
	}
	elseif ($elementType == 'project_task') {
		$classpath = 'projet/class';
		$module='projet';
	}
	elseif ($elementType == 'action') {
		$classpath = 'comm/action/class';
		$module='agenda';
		$myObject = 'ActionComm';
	}
	elseif ($elementType == 'mailing') {
		$classpath = 'comm/mailing/class';
	}
	elseif ($elementType == 'knowledgerecord') {
		$classpath = 'knowledgemanagement/class';
		$module='knowledgemanagement';
	}
	elseif ($elementType == 'recruitmentjobposition') {
		$classpath = 'recruitment/class';
		$module='recruitment';
	}
	elseif ($elementType == 'recruitmentcandidature') {
		$classpath = 'recruitment/class';
		$module='recruitment';
	}

	// Generic case for $classfile and $classname
	$classfile = strtolower($myObject); $classname = ucfirst($myObject);
	//print "objecttype=".$objecttype." module=".$module." subelement=".$subelement." classfile=".$classfile." classname=".$classname;

	if ($elementType == 'invoice_supplier') {
		$classfile = 'fournisseur.facture';
		$classname = 'FactureFournisseur';
		$classpath = 'fourn/class';
		$module = 'fournisseur';
	}
	elseif ($elementType == 'order_supplier') {
		$classfile = 'fournisseur.commande';
		$classname = 'CommandeFournisseur';
		$classpath = 'fourn/class';
		$module = 'fournisseur';
	}
	elseif ($elementType == 'supplier_proposal')  {
		$classfile = 'supplier_proposal';
		$classname = 'SupplierProposal';
		$classpath = 'supplier_proposal/class';
		$module = 'supplier_proposal';
	}
	elseif ($elementType == 'stock') {
		$classpath = 'product/stock/class';
		$classfile = 'entrepot';
		$classname = 'Entrepot';
	}
	elseif ($elementType == 'dolresource') {
		$classpath = 'resource/class';
		$classfile = 'dolresource';
		$classname = 'Dolresource';
		$module = 'resource';
	}
	elseif ($elementType == 'payment_various') {
		$classpath = 'compta/bank/class';
		$module='tax';
		$classfile = 'paymentvarious';
		$classname = 'PaymentVarious';
	}
	elseif ($elementType == 'bank_account') {
		$classpath = 'compta/bank/class';
		$module='banque';
		$classfile = 'account';
		$classname = 'Account';
	}
	elseif ($elementType == 'adherent_type')  {
		$classpath = 'adherents/class';
		$module = 'member';
		$classfile='adherent_type';
		$classname='AdherentType';
	}

//var_dump($conf);
	if (!empty($conf->$module->enabled))
	{
		$res = dol_include_once('/'.$classpath.'/'.$classfile.'.class.php');
		if ($res)
		{
			if (class_exists($classname))
			{
				return scrumProjectGetObjectFromCache($classname, $elementId, $maxCacheByType);
			}
		}
	}
	return false;
}


/**
 * @param string $objetClassName
 * @param int $fk_object
 * @param int $maxCacheByType
 * @return bool|CommonObject
 */
function scrumProjectGetObjectFromCache($objetClassName, $fk_object, $maxCacheByType = 10){
	global $db, $TScrumProjectGetObjectFromCache;

	if(!class_exists($objetClassName)){
		// TODO : Add error log here
		return false;
	}

	if(empty($TScrumProjectGetObjectFromCache[$objetClassName][$fk_object])){
		$object = new $objetClassName($db);
		if($object->fetch($fk_object, false) <= 0)
		{
			return false;
		}

		if(is_array($TScrumProjectGetObjectFromCache[$objetClassName]) && count($TScrumProjectGetObjectFromCache[$objetClassName]) >= $maxCacheByType){
			array_shift($TScrumProjectGetObjectFromCache[$objetClassName]);
		}

		$TScrumProjectGetObjectFromCache[$objetClassName][$fk_object] = $object;
	}
	else{
		$object = $TScrumProjectGetObjectFromCache[$objetClassName][$fk_object];
	}

	return $object;
}

/**
 * @param string $element             the commonobject element
 * @param int    $fk_element          the object id
 * @param string $field               field code to update
 * @param string $ajaxSuccessCallback a javascript function name used for call back on update fail
 * @param string $ajaxIdleCallback    a javascript function name used for call back on update do nothing
 * @param string $ajaxFailCallback    a javascript function name used for call back on update fail
 * @return string
 */
function scrumProjectGenLiveUpdateAttributes($element, $fk_element, $field, $ajaxSuccessCallback = '', $ajaxIdleCallback = '', $ajaxFailCallback = ''){
	$liveEditInterfaceUrl = dol_buildpath('scrumproject/interface.php',2);
	$liveEditInterfaceUrl.= '?element='.$element;
	$liveEditInterfaceUrl.= '&fk_element='.$fk_element;
	$liveEditInterfaceUrl.= '&field='.$field;

	$attributes = array(
		'data-ajax-target' => $liveEditInterfaceUrl,
		'data-live-edit' => 1
	);

	if(!empty($ajaxSuccessCallback)){
		$attributes['data-ajax-success-callback'] = $ajaxSuccessCallback;
	}

	if(!empty($ajaxIdleCallback)){
		$attributes['data-ajax-idle-callback'] = $ajaxIdleCallback;
	}

	if(!empty($ajaxFailCallback)){
		$attributes['data-ajax-fail-callback'] = $ajaxFailCallback;
	}

	$Aattr = array();
	if (is_array($attributes)) {
		foreach ($attributes as $attribute => $value) {
			if (is_array($value) || is_object($value)) {
				continue;
			}
			$Aattr[] = $attribute.'="'.dol_escape_htmltag($value).'"';
		}
	}

	return !empty($Aattr)?implode(' ', $Aattr):'';
}


/**
 * @param array $globalFields
 * @param array $fields
 * @param array $fieldsToKeep fields to keep with override values
 * @return void
 */
function scrumProjectAddObjectFieldDefinition(&$globalFields, $fields, $fieldsToKeep = array()){
	foreach ($fieldsToKeep as $fieldKey => $fieldParams){
		if(!empty($fields[$fieldKey])){
			$globalFields[$fieldKey] = $fields[$fieldKey];
			// au besoin il est possible de surchager la config originale
			foreach ($fieldParams as $param => $value){
				$globalFields[$fieldKey][$param]=$value;
			}
		}
	}
}

/**
 * Add tootltip to hours to get human days conversion
 * @param float $value
 * @return string
 */
function scrumProjectConvertQuantityToProjectGranularity($value){
	global $langs;
	$value = doubleval($value);
	$quotient = !empty($conf->global->DOC2PROJECT_NB_HOURS_PER_DAY)? intval($conf->global->DOC2PROJECT_NB_HOURS_PER_DAY): 7; // TODO ajouter soit une conf globale (une de plus ) ou utiliser celle de DOC2PROJECT_NB_HOURS_PER_DAY
	$outV = price($value / $quotient);

	$toolTip = $value.' '.$langs->trans('Hours').' / '.$quotient.' '.$langs->trans('HoursByDay').' = <strong>'.$outV.$langs->trans('shortLetterForDaysMan').'</strong>';
	return '<span class="classfortooltip" title="'.dol_escape_htmltag($toolTip).'" >'.$value.'</span>';
}
