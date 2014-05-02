<?php
/**
 * returns events xml based on ajax call used by acp_addraid.html
 *  
 * @package acp\ajax
 * @copyright (c) 2009 bbDkp <https://github.com/bbDKP>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link http://www.bbdkp.com
 */

define('IN_PHPBB', true);
define('ADMIN_START', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './../../../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

$pool_id = request_var('dkpsysid', 0);

$sql = 'SELECT event_id, event_name, event_value  
		FROM ' . EVENTS_TABLE . ' WHERE event_status=1 AND event_dkpid = '. $pool_id . ' ORDER BY event_name';

$result = $db->sql_query($sql);
header('Content-type: text/xml');
// preparing xml
$xml = '<?xml version="1.0" encoding="UTF-8"?>
<eventlist>';
while ( $row = $db->sql_fetchrow($result)) 
{
	 $xml .= '<event>'; 
	 $xml .= "<event_id>" . $row['event_id'] . "</event_id>";
	 $xml .= "<event_name>" . $row['event_name'] . ' - (' . $row['event_value']  . ")</event_name>";
	 $xml .= '</event>'; 	 
}
$xml .= '</eventlist>';
$db->sql_freeresult($result);
//return xml to ajax
echo($xml); 
