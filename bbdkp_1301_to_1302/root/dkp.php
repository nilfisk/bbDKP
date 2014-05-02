<?php
/**
 * frontpage for bbdkp
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

$user->session_begin();
$auth->acl($user->data);
$user->setup(array('mods/dkp_common', 'mods/dkp_admin'));

if(!defined("EMED_BBDKP"))
{
	trigger_error($user->lang['BBDKPDISABLED'], E_USER_WARNING);
}
if (!isset($config['bbdkp_version']))
{
	// THE CONFIGS AND DATABASE TABLES AREN'T INSTALLED, EXIT
	trigger_error('GENERAL_ERROR', E_USER_WARNING);
}
if (!$auth->acl_get('u_dkp'))
{
	trigger_error('NOT_AUTHORISED');
}

//load template frame
$template->set_filenames(array(
	'body' => 'dkp/dkpmain.html')
);
// load view class
if (!class_exists('\bbdkp\views\viewFactory'))
{
	require("{$phpbb_root_path}includes/bbdkp/views/viewFactory.$phpEx");
}

$viewFactory = new \bbdkp\views\viewFactory();
$page =  request_var('page', 'standings');

$viewFactory->get_view($page);

page_footer();