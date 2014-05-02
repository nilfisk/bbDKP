<?php
/**
 * returns memberlist xml based on ajax call used by acp_addraid.html
 * 
 * @package acp\ajax
 * @copyright (c) 2013 bbDkp <https://github.com/bbDKP>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * 
 */
define('IN_PHPBB', true);
define('ADMIN_START', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './../../../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

$guild_id = request_var('guild', 0);

if (!class_exists('\bbdkp\controller\members\Members'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/members/Members.$phpEx");
}
$members = new \bbdkp\controller\members\Members();
$members->listallmembers($guild_id);

header('Content-type: text/xml');
// preparing xml
$xml = '<?xml version="1.0" encoding="UTF-8"?>
<memberlist>';
foreach ( (array) $members->guildmemberlist as $member )
{
	$xml .= '<member>';
	$xml .= "<member_id>" . $member['member_id'] . "</member_id>";
	$xml .= "<member_name>" . $member['rank_name'] . ' '.  $member['member_name'] . "</member_name>";
	$xml .= '</member>';
}
$xml .= '</memberlist>';
//return xml to ajax
echo($xml); 
