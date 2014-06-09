<?php
/**
 * Returns rank xml based on ajax call 
 * 
 * @package acp\ajax
 * @copyright (c) 2009 bbDkp <https://github.com/bbDKP>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * 
 */
define('IN_PHPBB', true);
define('ADMIN_START', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './../../../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

$guild_id = request_var('guild', 0);

$sql = 'SELECT a.rank_id, a.rank_name, b.game_id
        FROM ' . MEMBER_RANKS_TABLE . ' a, ' . GUILD_TABLE. ' b WHERE a.rank_hide = 0 and
        a.guild_id =  '. $guild_id . ' AND a.guild_id = b.id ORDER BY rank_id desc';

$result = $db->sql_query($sql);
header('Content-type: text/xml');
// preparing xml
$xml = '<?xml version="1.0" encoding="UTF-8"?>
<ranklist>';
while ( $row = $db->sql_fetchrow($result)) 
{
	 $xml .= '<rank>';
     $xml .= "<rank_game_id>" . $row['game_id'] . "</rank_game_id>";
     $xml .= "<rank_id>" . $row['rank_id'] . "</rank_id>";
	 $xml .= "<rank_name>" . $row['rank_name'] . "</rank_name>";
	 $xml .= '</rank>'; 	 
}
$xml .= '</ranklist>';
$db->sql_freeresult($result);
//return xml to ajax
echo($xml);
