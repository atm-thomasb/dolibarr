#!/usr/bin/env php
<?php
if(is_file('../master.inc.php')) include '../master.inc.php';
elseif(is_file('../../../master.inc.php')) include '../../../master.inc.php';
elseif(is_file('../../../../master.inc.php')) include '../../../../master.inc.php';
elseif(is_file('../../../../../master.inc.php')) include '../../../../../master.inc.php';
else include '../../master.inc.php';

require_once __DIR__ . '/common_script.lib.php';

/**
 * PERMET TRANSFERT LES KANBANS SCUM VERS ADVANCE KANBAN
 */


if(!scp_isBash()){
	scp_log('doit être lancé en bash', 'error');
	exit;
}

$errors = 0;
$warnings = 0;

// Recherche des lien dupliqué avec kanban et scrum
$duplicateLink = $db->getRows(
						'SELECT fk_scrum_sprint
						FROM '.$db->prefix().'scrumproject_scrumkanban AS scp
						GROUP BY fk_scrum_sprint
						HAVING COUNT(id) >1'
);

if(!empty($duplicateLink)){
	scp_log('DUPLICATE KANBAN LINK TU SPRINT :', 'error');
	foreach ($duplicateLink as $obj){

		$duplicate = $db->getRows(
			'SELECT  ref, label
						FROM '.$db->prefix().'scrumproject_scrumkanban AS scp
						WHERE  fk_scrum_sprint = '.$obj->fk_scrum_sprint
		);

		foreach ($duplicate as $dObj){
			scp_log('KANBAN '.$dObj->ref.' : '.$dObj->label, 'error');
		}
	}

	scp_log('YOU MUST CORRECT LINK TO HAVE ONLY ONE KANBAN FOR ONE SPRINT', 'error');
	$db->close();
	exit;
}


// les rolls back ne fonctionne pas sur un partie du script (Rename)
// du coup il est préférable de ne pas miser dessus sinon ils rollback une partie ce qui rend plus compliqué la suite
$db->begin();


/**
 * MIGRATION DES CONF
 */
$TMigrateConf = array(
	'SP_KANBAN_UNSPLASH_API_KEY' => 'ADVKANBAN_UNSPLASH_API_KEY',
	'SCRUMPROJECT_DEFAULT_KANBAN_CONTACT_CODE' => 'ADVKANBAN_DEFAULT_KANBAN_CONTACT_CODE',
);

foreach ($TMigrateConf as $confKey => $newConfKey) {
	scp_sqlQuerylog($db, /** @lang MySQL */ 'UPDATE ' . $db->prefix() . 'const SET name = \''.$db->escape($newConfKey).'\' WHERE name = \''.$db->escape($confKey).'\' ; ', $warnings); // no error count
}


/**
 * PREREQUIS FORCER MAJ 1.33 de scrumprohect
 */
// Pour cette query d'update :
// Ne pas gérer l'erreur car le module fait cette modif à l'activation
// or il ne peut pas la faire car il y à la dépendance à advanced kanban sauf que Advanced kanban ne s'active pas si ce script n'a pas était lancé... donc cette maj ne se fait pas etc...
scp_sqlQuerylog($db, /** @lang MySQL */ 'ALTER TABLE  '.$db->prefix().'scrumproject_scrumsprint ADD fk_advkanban integer ;');



/**
 * MIGRATE CATEGORIES
 */
scp_sqlQuerylog($db, /** @lang MySQL */ 'UPDATE '.$db->prefix().'categorie  SET type = 10489114 WHERE type = 14 ;',$errors);
scp_sqlQuerylog($db, /** @lang MySQL */ 'RENAME TABLE '.$db->prefix().'categorie_scrumcard TO '.$db->prefix().'categorie_advkanbancard',$errors);
scp_sqlQuerylog($db, /** @lang MySQL */ 'ALTER  TABLE '.$db->prefix().'categorie_advkanbancard  CHANGE fk_scrumcard fk_advkanbancard INT(11) NOT NULL',$errors);


/**
 * MIGRATE KANBAN By tables rename
 */

scp_sqlQuerylog($db, /** @lang MySQL */ 'RENAME TABLE '.$db->prefix().'scrumproject_scrumkanban TO '.$db->prefix().'advancedkanban_advkanban',$errors);
scp_sqlQuerylog($db, /** @lang MySQL */ 'RENAME TABLE '.$db->prefix().'scrumproject_scrumkanban_extrafields TO '.$db->prefix().'advancedkanban_advkanban_extrafields',$errors);

scp_sqlQuerylog($db, /** @lang MySQL */ 'RENAME TABLE '.$db->prefix().'scrumproject_scrumkanbanlist TO '.$db->prefix().'advancedkanban_advkanbanlist',$errors);
scp_sqlQuerylog($db, /** @lang MySQL */ 'RENAME TABLE '.$db->prefix().'scrumproject_scrumkanbanlist_extrafields TO '.$db->prefix().'advancedkanban_advkanbanlist_extrafields',$errors);
scp_sqlQuerylog($db, /** @lang MySQL */ 'ALTER  TABLE '.$db->prefix().'advancedkanban_advkanbanlist CHANGE fk_scrum_kanban fk_advkanban INT(11) NOT NULL',$errors);


scp_sqlQuerylog($db, /** @lang MySQL */ 'RENAME TABLE '.$db->prefix().'scrumproject_scrumcard TO '.$db->prefix().'advancedkanban_advkanbancard',$errors);
scp_sqlQuerylog($db, /** @lang MySQL */ 'RENAME TABLE '.$db->prefix().'scrumproject_scrumcard_extrafields TO '.$db->prefix().'advancedkanban_advkanbancard_extrafields',$errors);
scp_sqlQuerylog($db, /** @lang MySQL */ 'ALTER  TABLE '.$db->prefix().'advancedkanban_advkanbancard  CHANGE fk_scrum_kanbanlist fk_advkanbanlist INT(11) NOT NULL',$errors);



// Update kanban in sprint : copie de la colonne fk_scrum_sprint du kanaban dans fk_advkanban de advkanban
scp_sqlQuerylog($db, /** @lang MySQL */'UPDATE '.$db->prefix().'scrumproject_scrumsprint AS scp
						SET scp.fk_advkanban = (
							SELECT advk.rowid AS fk_advkanban
							FROM '.$db->prefix().'advancedkanban_advkanban AS advk
							WHERE advk.fk_scrum_sprint = scp.rowid
						)
				',$errors);


scp_sqlQuerylog($db, /** @lang MySQL */ 'ALTER TABLE '.$db->prefix().'advancedkanban_advkanban DROP CONSTRAINT llx_scrumproject_scrumkanban_fk_scrum_sprint; ', $warnings); // no error count
scp_sqlQuerylog($db, /** @lang MySQL */ 'ALTER TABLE '.$db->prefix().'advancedkanban_advkanban DROP INDEX idx_scrumproject_scrumkanban_fk_scrum_sprint; ', $warnings); // no error count


// suppression de la colonne devenue inutile
scp_sqlQuerylog($db, /** @lang MySQL */'ALTER TABLE '.$db->prefix().'advancedkanban_advkanban DROP COLUMN fk_scrum_sprint ',$errors);

/**
 * TODO suppression de tous les index et contraintes lié à scrum kanban
 * elles serons recréés par l'activation de Advanced kanban
 * Attention retirer les contraintes en premiers
 */

$tableIdx = 'advancedkanban_advkanbancard';
$TConstraintIdx = [
	'llx_scrumproject_scrumcard_fk_user_creat',
	'llx_scrumproject_scrumcard_fk_user_modif',
	'llx_scrumproject_scrumcard_fk_scrum_kanbanlist'
];
$TIndexIdx = [
	'idx_scrumproject_scrumkanban_fk_scrum_sprint',
	'idx_categorie_scrumcard_fk_categorie',
	'idx_categorie_scrumcard_fk_product',
	'idx_scrumproject_scrumcard_rowid',
	'idx_scrumproject_scrumcard_fk_rank',
	'idx_scrumproject_scrumcard_fk_scrum_kanbanlist',
	'idx_scrumproject_scrumcard_status',
	'idx_scrumproject_scrumcard_entity'
];
foreach ($TConstraintIdx as $constraintIdx ){ scp_sqlQuerylog($db, /** @lang MySQL */ 'ALTER TABLE '.$db->prefix().$tableIdx.' DROP CONSTRAINT '.$constraintIdx.'; ', $warnings, 'warning'); }
foreach ($TIndexIdx as $indexIdx ){ scp_sqlQuerylog($db, /** @lang MySQL */ 'ALTER TABLE '.$db->prefix().$tableIdx.' DROP INDEX '.$indexIdx.'; ', $warnings, 'warning'); }

$tableIdx = 'advancedkanban_advkanbancard_extrafields';
$TIndexIdx = ['idx_scrumcard_fk_object'];
foreach ($TIndexIdx as $indexIdx ){ scp_sqlQuerylog($db, /** @lang MySQL */ 'ALTER TABLE '.$db->prefix().$tableIdx.' DROP INDEX '.$indexIdx.'; ', $warnings, 'warning'); }


$tableIdx = 'advancedkanban_advkanban_extrafields';
$TIndexIdx = ['idx_scrumkanban_fk_object'];
foreach ($TIndexIdx as $indexIdx ){ scp_sqlQuerylog($db, /** @lang MySQL */ 'ALTER TABLE '.$db->prefix().$tableIdx.' DROP INDEX '.$indexIdx.'; ', $warnings, 'warning'); }


$tableIdx = 'advancedkanban_advkanban';
$TConstraintIdx = [
	'llx_scrumproject_scrumkanban_fk_scrum_sprint',
];
$TIndexIdx = [
	'idx_scrumproject_scrumkanban_rowid',
	'idx_scrumproject_scrumkanban_entity',
	'idx_scrumproject_scrumkanban_ref',
	'idx_scrumproject_scrumkanban_fk_soc',
	'idx_scrumproject_scrumkanban_fk_project',
	'llx_scrumproject_scrumkanban_fk_user_creat',
	'idx_scrumproject_scrumkanban_status',
];
foreach ($TConstraintIdx as $constraintIdx ){ scp_sqlQuerylog($db, /** @lang MySQL */ 'ALTER TABLE '.$db->prefix().$tableIdx.' DROP CONSTRAINT '.$constraintIdx.'; ', $warnings, 'warning'); }
foreach ($TIndexIdx as $indexIdx ){ scp_sqlQuerylog($db, /** @lang MySQL */ 'ALTER TABLE '.$db->prefix().$tableIdx.' DROP INDEX '.$indexIdx.'; ', $warnings, 'warning'); }

$tableIdx = 'advancedkanban_advkanbanlist_extrafields';
$TIndexIdx = ['idx_scrumkanbanlist_fk_object'];
foreach ($TIndexIdx as $indexIdx ){ scp_sqlQuerylog($db, /** @lang MySQL */ 'ALTER TABLE '.$db->prefix().$tableIdx.' DROP INDEX '.$indexIdx.'; ', $warnings, 'warning'); }



$tableIdx = 'advancedkanban_advkanbanlist';
$TConstraintIdx = [
	'llx_scrumproject_scrumkanbanlist_fk_scrum_kanban',
	'llx_scrumproject_scrumkanbanlist_fk_user_creat',
];
$TIndexIdx = [
	'idx_scrumproject_scrumkanbanlist_rowid',
	'idx_scrumproject_scrumkanbanlist_fk_scrum_kanban',
	'idx_scrumproject_scrumkanbanlist_fk_rank',
	'uk_scrumproject_scrumkanbanlist_code',
];
foreach ($TConstraintIdx as $constraintIdx ){ scp_sqlQuerylog($db, /** @lang MySQL */ 'ALTER TABLE '.$db->prefix().$tableIdx.' DROP CONSTRAINT '.$constraintIdx.'; ', $warnings, 'warning'); }
foreach ($TIndexIdx as $indexIdx ){ scp_sqlQuerylog($db, /** @lang MySQL */ 'ALTER TABLE '.$db->prefix().$tableIdx.' DROP INDEX '.$indexIdx.'; ', $warnings, 'warning'); }



scp_log($warnings.' WARNING', 'warning');
scp_log($errors. ' ERRORS', 'error');
if($errors==0){
	scp_log('DONE', 'success');
}

// les rolls back ne fonctionne pas sur un partie du script (Rename)
// du coup il est préférable de ne pas miser dessus sinon ils rollback une partie ce qui rend plus compliqué la suite
$db->commit();

$db->close();
