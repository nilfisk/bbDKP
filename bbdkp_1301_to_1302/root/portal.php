<?php
/**
 * Indexpage for bbdkp
 *
 *   @package bbdkp
 * @copyright 2011 bbdkp <https://github.com/bbDKP>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author ippehe <ippe.he@gmail.com>
 * @author sajaki <sajaki@gmail.com>
 * @link http://www.bbdkp.com
 *
 */

/**
* @ignore
*/
define('IN_PHPBB', true);
define('IN_BBDKP', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);

$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/dkp_common');

if(!defined("EMED_BBDKP"))
{
	trigger_error($user->lang['BBDKPDISABLED'], E_USER_WARNING);
}
if (!$auth->acl_get('u_dkp'))
{
	trigger_error('NOT_AUTHORISED');
}
if (!isset($config['bbdkp_version']))
{
	// THE CONFIGS AND DATABASE TABLES AREN'T INSTALLED, EXIT
	trigger_error('GENERAL_ERROR', E_USER_WARNING);
}
if (!class_exists('\bbdkp\views\blockFactory'))
{
	require("{$phpbb_root_path}includes/bbdkp/block/blockFactory.$phpEx");
}

$template->assign_var('S_PORTAL', true);

page_header($user->lang['NEWS']);

$template->set_filenames(array(
		'body' => 'dkp/portal_body.html')
);

$blockFactory = new \bbdkp\views\blockFactory();
$blockFactory->get_blocks();
make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));

// auto-refreshing portal is disabled
// uncomment this line if you want it back
//meta_refresh(60, append_sid("{$phpbb_root_path}portal.$phpEx"));

page_footer();

?>
