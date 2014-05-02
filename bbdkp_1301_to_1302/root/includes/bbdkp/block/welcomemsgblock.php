<?php
/**
 * welcome block
 *
 *   @package bbdkp
 * @link http://www.bbdkp.com
 * @author Sajaki@gmail.com
 * @copyright 2009 bbdkp
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.3.0
 */

if (!defined('IN_PHPBB'))
{
   exit;
}
$user->add_lang(array('posting'));

if (!function_exists('generate_text_for_display'))
{
	include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
}
$text="";
$sql = 'SELECT welcome_msg, bbcode_uid, bbcode_bitfield, bbcode_options FROM ' . WELCOME_MSG_TABLE;
$db->sql_query($sql);
$result = $db->sql_query($sql);
while ( $row = $db->sql_fetchrow($result) )
{
	$text = $row['welcome_msg'];
	$bbcode_uid = $row['bbcode_uid'];
	$bbcode_bitfield = $row['bbcode_bitfield'];
	$bbcode_options = $row['bbcode_options'];
}
$db->sql_freeresult($result);

$message = generate_text_for_display($text, $bbcode_uid, $bbcode_bitfield, $bbcode_options);
$message = smiley_text($message);

$template->assign_vars(array(
	'WELCOME_MESSAGE'		=> $message,
	'S_DISPLAY_WELCOME' 	=> true,
));
