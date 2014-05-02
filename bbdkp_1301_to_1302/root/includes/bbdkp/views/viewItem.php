<?php
/**
* Viewitem module. shows one loot to user
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

class viewItem implements iViews
{
    function __construct(viewNavigation $Navigation)
    {
        $this->buildpage($Navigation);
    }


    public function buildpage(viewNavigation $Navigation)
    {
        global $config, $phpbb_root_path, $phpEx, $user, $template;

        $item_id = request_var(URI_ITEM, 0);
        if (!class_exists('\bbdkp\controller\loot\Loot'))
        {
            require("{$phpbb_root_path}includes/bbdkp/controller/loot/Loot.$phpEx");
        }
        if (!class_exists('\bbdkp\controller\members\Members'))
        {
            require("{$phpbb_root_path}includes/bbdkp/controller/members/Members.$phpEx");
        }
        if (!class_exists('\bbdkp\controller\raids\Raids'))
        {
            require("{$phpbb_root_path}includes/bbdkp/controller/raids/Raids.$phpEx");
        }
        $loot = new \bbdkp\controller\loot\Loot($item_id);
        $purchased_items = $loot->Loothistory($loot->item_name);

        $title = $user->lang['ITEM'] . ' : '. $loot->item_name;

        $sort_order = array (
            0 => array ('item_date desc, item_value desc', 'item_date asc, item_value desc'),
            1 => array ('member_name asc, item_value desc', 'member_name desc, item_value desc'),
            2 => array ('item_value desc', 'item_value asc'));
        $current_order = $Navigation->switch_order ($sort_order);

        foreach($purchased_items as $key => $item)
        {
            $buyer = new \bbdkp\controller\members\Members($item ['member_id']);
            $raid = new \bbdkp\controller\raids\Raids($item ['raid_id']);
            $template->assign_block_vars('items_row', array(
                    'DATE' => ( !empty($item['item_date']) ) ? date('d.m.y', $item['item_date']) : '&nbsp;',
                    'CLASS_COLOR' => $buyer->colorcode,
                    'CLASS_IMAGE' => $buyer->class_image,
                    'RACE_IMAGE' => $buyer->race_image,
                    'RACE' => $buyer->member_race,
                    'BUYER' => $buyer->member_name,
                    'U_VIEW_BUYER' => append_sid("{$phpbb_root_path}dkp.$phpEx" , "page=member&amp;" . URI_NAMEID . '='. $item['member_id']. '&amp;' . URI_DKPSYS . '=' . $item['dkpid']) ,
                    'U_VIEW_RAID' => append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=raid&amp;' . URI_RAID . '='.$item['raid_id']) ,
                    'EVENT_COLOR' => $raid->event_color,
                    'RAID' =>  $raid->event_name,
                    'VALUE' 	=> sprintf("%.2f", $item['item_value'])   ,
                    'DECAY' 	=> sprintf("%.2f", $item['item_decay']),
                    'TOTAL' 	=> sprintf("%.2f", $item['item_net']),
                )
            );
            unset($raid);
            unset($buyer);
        }

        $navlinks_array = array(
            array(
                'DKPPAGE' => $user->lang['MENU_ITEMVAL'],
                'U_DKPPAGE' => append_sid("{$phpbb_root_path}dkp.$phpEx", "page=listitems"),
            ),

            array(
                'DKPPAGE' => $user->lang['MENU_VIEWITEM'],
                'U_DKPPAGE' => append_sid("{$phpbb_root_path}dkp.$phpEx" , "page=item&amp;" . URI_ITEM . '='. $item_id),
            ));

        foreach( $navlinks_array as $name )
        {
            $template->assign_block_vars('dkpnavlinks', array(
                'DKPPAGE' => $name['DKPPAGE'],
                'U_DKPPAGE' => $name['U_DKPPAGE'],
            ));
        }

        $template->assign_vars(array(
            'S_SHOWDECAY' 			=> ($config['bbdkp_decay'] == '1') ? true : false,
            'L_PURCHASE_HISTORY_FOR' => sprintf($user->lang['PURCHASE_HISTORY_FOR'], '<strong>' . $loot->item_name . '</strong>'),
            'O_DATE' 				 => $current_order['uri'][0],
            'O_BUYER'				 => $current_order['uri'][1],
            'O_VALUE'			 	 => $current_order['uri'][2],
            'U_VIEW_ITEM' 			 => append_sid("{$phpbb_root_path}dkp.$phpEx" , 'page=item&amp;' . URI_ITEM . '='. $item_id) ,
            'VIEWITEM_FOOTCOUNT' 	 => sprintf($user->lang['VIEWITEM_FOOTCOUNT'], count($purchased_items)),
            'S_DISPLAY_VIEWITEM' 	 => true,
        ));
        unset($purchased_items);
        // Output page
        page_header($title);
    }
}

