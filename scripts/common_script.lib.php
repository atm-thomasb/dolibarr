<?php

/**
 * COMMON SCRUM PROJECT SCRIPT TOOL
 * scp_ prefix for scrum project script tools
 * copied from module abricot
 */

/**
 * check if current output is bash
 * @return bool
 */
function scp_isBash()
{
	// Use only on command line
	$isBash = true;
	$sapi_type = php_sapi_name();
	if (substr($sapi_type, 0, 3) == 'cgi' || $sapi_type == 'apache2handler') {
		$isBash = false;
	}

	return $isBash;
}


/**
 *
 * @param string $msg
 * @param string $type '' | 'success' | 'error' | 'error-code' | 'warning'
 * @return void
 */
function scp_log($msg, $type = ''){
	global $isBash;

	if(!isset($isBash)){
		$isBash = scp_isBash();
	}

	if($isBash){
		$bashColor = '0;37';
		if($type == 'error' ){
			$bashColor = '0;31';
		}elseif($type == 'error-code'){
			$bashColor = '1;91;40';
		}elseif($type == 'success'){
			$bashColor = '0;32';
		}elseif($type == 'warning'){
			$bashColor = '0;33';
		}elseif($type == 'warning-code'){
			$bashColor = '1;93;40';
		}
		echo "\e[".$bashColor."m".$msg."\e[0m\n";
	}else{
		$style = '';
		if($type == 'error' || $type == 'error-code'){
			$style = ' style="background: #fbb"';
		}

		echo '<p' . $style . '>'.$msg.'</p>';
	}
}


/**
 * launch sql query and display log
 * @param string $sql @lang MySQL
 * @param bool $res
 * @param DoliDB $db
 * @return void
 */
function scp_sqlQuerylog($db, $sql, &$errors = 0, $errorStyle="error"){
	if($db->query($sql)){
		scp_log($sql, 'success');
		return true;
	}else{
		$errors++;
		scp_log($sql, $errorStyle);
		scp_log($db->error(), $errorStyle.'-code');
		return false;
	}
}
