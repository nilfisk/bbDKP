<?php
/**
 * bbdkp about popup
 *
 * @copyright (c) 2009 bbDKP <https://github.com/bbDKP>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author ippeh
 *
 */

/**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('mods/dkp_common');
global $config, $db;

if(!defined("EMED_BBDKP"))
{
    trigger_error($user->lang['BBDKPDISABLED'], E_USER_WARNING);
}
// Include the base class
if (!class_exists('\bbdkp\admin\Admin'))
{
	require("{$phpbb_root_path}includes/bbdkp/admin/admin.$phpEx");
}

$admin = new \bbdkp\admin\Admin;
$plugins = $admin->get_plugin_info();

foreach($plugins as $pname => $pdetails)
{
	$a = \phpbb_version_compare(trim( $pdetails['latest'] ), $pdetails['version'] , '<=');
	$template->assign_block_vars('plugins_row', array(
			'PLUGINNAME' 	=> ucwords($pdetails['name']) ,
			'VERSION' 		=> $pdetails['version'] ,
	));
}

$template->assign_vars(array(
        'PBPBBVERSION' 		=> $config['version'],
        'BBDKPVERSION' 		=> $config['bbdkp_version'],
));

$title = $user->lang['ABOUT'];
unset($admin);
// Output page
page_header($title);

$template->set_filenames(array(
	'body' => 'dkp/about.html')
);

page_footer();

?>
