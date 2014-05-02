<?php
/**
 * lootdb : loot catalogue
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
$phpEx = substr(strrchr(__FILE__, '.'), 1);
global $phpbb_root_path;
if (!class_exists('\bbdkp\controller\loot\Loot'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/loot/Loot.$phpEx");
}
if (!class_exists('\bbdkp\controller\raids\Raids'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/raids/Raids.$phpEx");
}

class viewLootdb implements iViews
{

    function __construct(viewNavigation $Navigation)
    {
        $this->buildpage($Navigation);
    }

    public function buildpage(viewNavigation $Navigation)
    {
        global $db, $phpbb_root_path, $phpEx, $config, $user, $template;
        $loot = new \bbdkp\controller\loot\Loot();
        $total_items = $loot->countloot('values', $Navigation->getGuildId(), $Navigation->getDkpsysId());

        $start = request_var ( 'start', 0 );

        if ($Navigation->getDkpsysId() > 0)
        {
            $u_list_items = append_sid ( "{$phpbb_root_path}dkp.$phpEx", 'page=lootdb&amp;guild_id=' . $Navigation->getGuildId() . '&amp;' . URI_DKPSYS . '=' . $Navigation->getDkpsysId() );
        }
        else
        {
            $u_list_items = append_sid ( "{$phpbb_root_path}dkp.$phpEx", 'page=lootdb&amp;guild_id=' . $Navigation->getGuildId());
        }

        $listitems_footcount = sprintf ( $user->lang ['LISTITEMS_FOOTCOUNT'], $total_items, $config ['bbdkp_user_ilimit'] );

        $sort_order = array (
            0 => array ('MAX(i.item_date) desc, sum(item_value) desc', 'MAX(i.item_date) asc, sum(item_value) desc'),
            1 => array ('item_name asc, sum(item_value) desc', 'item_name desc, sum(item_value) desc'),
            2 => array ('event_name asc, sum(item_value) desc', 'event_name desc, sum(item_value) desc'),
            3 => array ('SUM(item_value) desc', 'sum(item_value) asc'),
            4 => array ('COUNT(i.item_id) desc', 'COUNT(i.item_id) asc')
        );

        $current_order = $Navigation->switch_order ($sort_order);
        $items_result = $loot->Lootstat($current_order['sql'], $Navigation->getGuildId(), $Navigation->getDkpsysId(), 0, $start,0);
        $pagination = generate_pagination ( $u_list_items . '&amp;o=' . $current_order ['uri']['current'],$total_items, $config['bbdkp_user_ilimit'], $start, true );

        $number_items = 0;
        $item_value = 0.00;
        $item_decay = 0.00;
        $item_total = 0.00;

        while ( $item = $db->sql_fetchrow ( $items_result ) )
        {

            if ($Navigation->bbtips == true && $item['item_gameid'] == 'wow')
            {
                $valuename = '<strong>' . $Navigation->bbtips->parse('[itemdkp]' . $item['item_name']  . '[/itemdkp]')  . '</strong>';
            }
            else
            {
                $valuename = '<strong>' . $item ['item_name'] . '</strong>';
            }
            $raid = new \bbdkp\controller\raids\Raids($item ['raid_id']);
            $template->assign_block_vars ( 'items_row', array (
                'DATE' 			=> (! empty ( $item ['item_date'] )) ? date($config['bbdkp_date_format'], $item ['item_date'] ) : '&nbsp;',
                'ITEMNAME' 		=> $valuename,
                'U_VIEW_ITEM' 	=> append_sid ( "{$phpbb_root_path}dkp.$phpEx", "page=item&amp;" .  URI_ITEM . '=' . $item ['item_id'] ),
                'RAID' 			=> $item ['event_name'],
                'EVENTCOLOR' 	=> (!empty($item['event_color']) ) ? $item['event_color'] : '#254689',
                'U_VIEW_RAID' 	=> append_sid ( "{$phpbb_root_path}dkp.$phpEx", "page=raid&amp;" . URI_RAID . '=' . $item ['raid_id'] ),
                'EVENT_COLOR' 	=> $raid->event_color,
                'ITEMVALUE' 	=> sprintf("%.2f", $item['item_value'])   ,
                'DECAYVALUE' 	=> sprintf("%.2f", $item['item_decay']),
                'TOTAL' 		=> sprintf("%.2f", $item['item_net']),
                'DROPCOUNT' 	=> $item['dropcount'],
            ));
            $number_items++;
            $item_value += $item['item_value'];
            $item_decay += $item['item_decay'];
            $item_total += $item['item_net'];

        }
        $db->sql_freeresult ( $items_result );

        // breadcrumbs menu
        $navlinks_array = array (
            array (
                'DKPPAGE' => $user->lang ['MENU_ITEMVAL'],
                'U_DKPPAGE' => $u_list_items ) );

        foreach ( $navlinks_array as $name )
        {
            $template->assign_block_vars ( 'dkpnavlinks', array (
                'DKPPAGE' => $name ['DKPPAGE'],
                'U_DKPPAGE' => $name ['U_DKPPAGE'] ) );
        }

        $template->assign_vars ( array (
            'F_LISTITEM' => $u_list_items,
            'O_DATE' 		=> $u_list_items .'&amp;o=' . $current_order ['uri'] [0] . '&amp;start=' . $start,
            'O_ITEMNAME' 	=> $u_list_items .'&amp;o=' . $current_order ['uri'] [1] . '&amp;start=' . $start,
            'O_RAID' 		=> $u_list_items .'&amp;o=' . $current_order ['uri'] [2] . '&amp;start=' . $start,
            'O_VALUE' 		=> $u_list_items .'&amp;o=' . $current_order ['uri'] [3] . '&amp;start=' . $start,
            'O_DROPCOUNT' 	=> $u_list_items .'&amp;o=' . $current_order ['uri'] [4] . '&amp;start=' . $start,
            'S_SHOWZS' 		=> ($config['bbdkp_zerosum'] == '1') ? true : false,
            'S_SHOWTIME' 	=> ($config['bbdkp_timebased'] == '1') ? true : false,
            'S_SHOWDECAY' 	=> ($config['bbdkp_decay'] == '1') ? true : false,
            'S_SHOWEPGP' 	=> ($config['bbdkp_epgp'] == '1') ? true : false,
            'TOTAL_ITEMVALUE' 	=> sprintf("%.2f",$item_value)   ,
            'TOTAL_ITEMDECAY' 	=> sprintf("%.2f", $item_decay),
            'TOTAL_ITEMTOTAL' 	=> sprintf("%.2f", $item_total),

            'LISTITEMS_FOOTCOUNT' => $listitems_footcount,
            'ITEM_PAGINATION' => $pagination ,
            'S_DISPLAY_LOOTDB' => true,
        ));

        $title = $user->lang ['MENU_ITEMVAL'];

        // Output page
        page_header ( $title );

    }

}