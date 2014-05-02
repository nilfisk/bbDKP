<?php
/**
 * sets pool status from ajax call in acp_listdkpsys.html
 * @package acp\ajax
 * @copyright (c) 2013 bbDkp <https://github.com/bbDKP>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */
$a = 1; 
define('IN_PHPBB', true);
define('ADMIN_START', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './../../../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

$dkpsys_id = request_var('dkpsysid', 0);
$status = request_var('status', 0);
if($dkpsys_id > 0)
{
	$sql = 'UPDATE ' . DKPSYS_TABLE . " SET dkpsys_status = '" . ($status == 1 ? 'Y' : 'N') . "' WHERE dkpsys_id = " . (int) $dkpsys_id; 
	$db->sql_query($sql);
	echo('1'); 
}
echo('0');

