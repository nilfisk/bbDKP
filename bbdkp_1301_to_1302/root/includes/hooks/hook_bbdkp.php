<?php
/**
 *
 * @package bbdkp
 * @copyright 2011 bbdkp <https://github.com/bbDKP>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author sajaki <sajaki@gmail.com>
 * @link http://www.bbdkp.com
 * @version 1.3.0
 *
 */

/**
 * @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}
// bbDKP
if (file_exists($phpbb_root_path . 'configdkp.' . $phpEx))
{
	require($phpbb_root_path . 'configdkp.' . $phpEx);
	$user->add_lang(array('mods/dkp_common'));
	if(@defined("EMED_BBDKP"))
	{
		require($phpbb_root_path .  'includes/bbdkp/admin/constants_bbdkp.' . $phpEx);
	}
}

/**
 * hook in template chain to inject values
 *
 */
function hook_bbdkp(&$hook)
{

	global $db, $phpEx, $phpbb_root_path, $user, $template;
	if ($user->data['is_registered'])
	{

		// new posts since last visit
		$sql = "SELECT COUNT(distinct post_id) as total
		FROM " . POSTS_TABLE . "
		WHERE post_time >= " . $user->data['session_last_visit'];
		$result = $db->sql_query($sql);
		$new_posts_count = (int) $db->sql_fetchfield('total');

		// your post number
		$sql = "SELECT user_posts
			FROM " . USERS_TABLE . "
			WHERE user_id = " . $user->data['user_id'];
		$result = $db->sql_query($sql);
		$you_posts_count = (int) $db->sql_fetchfield('user_posts');
		$template->assign_vars(array(
			'L_NEW_POSTS'	=> $user->lang['SEARCH_NEW'] . '&nbsp;(' . $new_posts_count . ')',
			'L_SELF_POSTS'	=> $user->lang['SEARCH_SELF'] . '&nbsp;(' . $you_posts_count . ')',
			'U_NEW_POSTS'	=> append_sid($phpbb_root_path . 'search.' . $phpEx . '?search_id=newposts'),
			'U_SELF_POSTS'	=> append_sid($phpbb_root_path . 'search.' . $phpEx . '?search_id=egosearch'),
		));
	}

	$template->assign_vars(array(
			'L_PORTAL'		=> $user->lang['PORTAL'],
			'U_PORTAL'		=> append_sid("{$phpbb_root_path}portal.$phpEx"),
			'U_DKP'			=> append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=standings'),
			'L_DKPPAGE'		=> $user->lang['DKP'],
			'L_BBDKP'		=> $user->lang['FOOTERBBDKP'],
			'U_ABOUT'       => append_sid("{$phpbb_root_path}aboutbbdkp.$phpEx"),
	));
}

/**
 * Register all necessary hooks if bbdkp is present
 */

$phpbb_hook->register(array('template', 'display'), 'hook_bbdkp');

?>