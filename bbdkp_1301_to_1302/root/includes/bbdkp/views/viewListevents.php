<?php
/**
 * list events module
 * @link http://www.bbdkp.com
 * @author Sajaki@gmail.com
 * @copyright 2009 bbdkp
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.3.0
 */
namespace bbdkp\views;
/**
 * @ignore
 */
if ( !defined('IN_PHPBB') OR !defined('IN_BBDKP') )
{
	exit;
}

class viewListevents implements iViews
{
    function __construct(viewNavigation $Navigation)
    {
        $this->buildpage($Navigation);
    }


    public function buildpage(viewNavigation $Navigation)
    {
        global $phpbb_root_path, $phpEx, $user, $template;

        $u_listevents = append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=listevents&amp;guild_id=' . $Navigation->getGuildId());
        $navlinks_array = array(
            array(
                'DKPPAGE' => $user->lang['MENU_EVENTS'],
                'U_DKPPAGE' => $u_listevents,
            ));

        foreach( $navlinks_array as $name )
        {
            $template->assign_block_vars('dkpnavlinks', array(
                'DKPPAGE' => $name['DKPPAGE'],
                'U_DKPPAGE' => $name['U_DKPPAGE'],
            ));
        }
        if (!class_exists('\bbdkp\controller\raids\Events'))
        {
            require("{$phpbb_root_path}includes/bbdkp/controller/raids/Events.$phpEx");
        }

        $event  = new \bbdkp\controller\raids\Events();
        $event->countevents($Navigation->getDkpsysId());
        $event->viewlistevents($Navigation->getGuildId());

        $template->assign_vars(array(
            'U_LIST_EVENTS' => $u_listevents,
            'S_DISPLAY_LISTEVENTS' => true,
        ));

        $title = $user->lang['EVENTS'];

        // Output page
        page_header($title);

    }
}




?>
