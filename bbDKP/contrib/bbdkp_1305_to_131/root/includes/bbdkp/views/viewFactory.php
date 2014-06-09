<?php
/**
 * View class
 * @link http://www.bbdkp.com
 * @author Sajaki@gmail.com
 * @copyright 2009 bbdkp
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.3.0
 *
 */
namespace bbdkp\views;
use \bbdkp\admin;
/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

$phpEx = substr(strrchr(__FILE__, '.'), 1);
global $phpbb_root_path;

if (!class_exists('\bbdkp\admin\Admin'))
{
	require("{$phpbb_root_path}includes/bbdkp/admin/admin.$phpEx");
}

/**
 * dispatch request to right viewpage
 *   @package bbdkp
 *
 */
class viewFactory extends \bbdkp\admin\Admin
{

    private $valid_views = array('news', 'roster', 'standings', 'loothistory', 'lootdb',
    'listevents', 'stats', 'listraids', 'event',
    'item', 'raid', 'member', 'bossprogress', 'planner');

	/**
	 * load a page asked for by user
	 *
	 * @param string $page
	 */
	public function get_view($page)
	{
		global $user, $phpbb_root_path, $phpEx ;

        if (in_array($page, $this->valid_views))
        {
            include($phpbb_root_path . 'includes/bbdkp/views/iViews.' . $phpEx);

            include($phpbb_root_path . 'includes/bbdkp/views/viewNavigation.'. $phpEx);
            $Navigation = new viewNavigation($page);

            include($phpbb_root_path . 'includes/bbdkp/views/view'. ucfirst($page) . '.' . $phpEx);
            $viewtype = "\\bbdkp\\views\\view". ucfirst($page);

            return new $viewtype($Navigation);

        }
        else
        {
            trigger_error(sprintf($user->lang['NOVIEW'], $page ));
        }
    }
}

