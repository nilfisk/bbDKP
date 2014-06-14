<?php
/**
 * block class
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
 * load blocks
 * @package bbdkp
 *
 */
class blockFactory extends \bbdkp\admin\Admin
{
    private $variable_blocks = array('menu', 'links', 'loot', 'newmembers', 'recent', 'recruitment', 'welcomemsg', 'whoisonline' );

	public function get_blocks()
	{
		global $phpbb_root_path, $phpEx, $user, $config, $template, $db;

        include($phpbb_root_path . 'includes/bbdkp/block/newsblock.' . $phpEx);

        if ($user->data['is_registered'])
        {
            include($phpbb_root_path .'includes/bbdkp/block/userblock.' . $phpEx);
        }
        else
        {
            include($phpbb_root_path . 'includes/bbdkp/block/loginblock.' . $phpEx);
        }

        foreach($this->variable_blocks as $block)
        {
            if ($config['bbdkp_portal_' . $block] == 1)
            {
                include($phpbb_root_path . 'includes/bbdkp/block/'.$block . 'block.' . $phpEx);
            }
        }

        $template->assign_var('S_BPSHOW', false);
        if (isset($config['bbdkp_gameworld_version']))
        {
            if ($config['bbdkp_portal_bossprogress'] == 1)
            {
                include($phpbb_root_path . 'includes/bbdkp/block/bossprogressblock.' . $phpEx);
                $template->assign_var('S_BPSHOW', true);
            }
        }

        if (isset($config['bbdkp_raidplanner']))
        {
            if ($config['rp_show_portal'] == 1)
            {
                $user->add_lang(array('mods/raidplanner'));
                if (!class_exists('\bbdkp\views\raidplanner\rpblocks', false))
                {
                    //display the blocks
                    include($phpbb_root_path . 'includes/bbdkp/block/rpblocks.' . $phpEx);
                }
                $blocks = new \bbdkp\views\raidplanner\rpblocks();
                $blocks->display();
            }
        }

    }



}

