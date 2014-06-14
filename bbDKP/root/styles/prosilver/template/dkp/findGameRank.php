<?php
/**
 * Returns rank xml based on ajax call 
 * test url http://localhost:8082/qi/boards/test13/styles/prosilver/template/dkp/findGameRank.php?guild=2
 * @package bbdkp
 * @copyright (c) 2011 https://github.com/bbDKP
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.3.0.5
 * 
 */
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './../../../../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

$guild_id = request_var('guild', 0);

$sql = 'SELECT a.rank_id, a.rank_name, b.game_id, g.game_name
        FROM ' . MEMBER_RANKS_TABLE . ' a, ' . GUILD_TABLE. ' b, ' . GAMES_TABLE . ' g
        WHERE a.rank_hide = 0
        AND a.guild_id =  '. $guild_id . '
        AND a.guild_id = b.id
        AND b.game_id = g.game_id
        ORDER BY rank_id desc';

$result = $db->sql_query($sql);
header('Content-type: text/xml');
// preparing xml
$xml = '<?xml version="1.0" encoding="UTF-8"?>
<ranklist>';
while ( $row = $db->sql_fetchrow($result))
{
    $xml .= '<rank>';
    $xml .= "<game_id>" . $row['game_id'] . "</game_id>";
    $xml .= "<game_name>" . $row['game_name'] . "</game_name>";
    $xml .= "<rank_id>" . $row['rank_id'] . "</rank_id>";
    $xml .= "<rank_name>" . $row['rank_name'] . "</rank_name>";
    $xml .= '</rank>';
}

$xml .= '</ranklist>';
$db->sql_freeresult($result);
//return xml to ajax
echo($xml);
