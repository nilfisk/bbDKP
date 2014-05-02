<?php
/**
 * Viewraid module. shows one raid to user
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
if (!class_exists('\bbdkp\controller\raids\Raids'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/raids/Raids.$phpEx");
}
if (!class_exists('\bbdkp\controller\raids\Raiddetail'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/raids/Raiddetail.$phpEx");
}
if (!class_exists('\bbdkp\controller\loot\Loot'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/loot/Loot.$phpEx");
}
if (!class_exists('\bbdkp\controller\loot\LootController'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/loot/LootController.$phpEx");
}

class viewRaid implements iViews
{
    function __construct(viewNavigation $Navigation)
    {
        $this->buildpage($Navigation);
    }

    public function buildpage(viewNavigation $Navigation)
    {
        global $db, $config, $phpbb_root_path, $phpEx, $user, $template;
        if ( !isset($_GET[URI_RAID]) )
        {
            trigger_error ($user->lang['RNOTFOUND']);
        }
        $raid_id = request_var(URI_RAID,0);

        // breadcrumbs
        $navlinks_array = array(
            array(
                'DKPPAGE'		=> $user->lang['MENU_RAIDS'],
                'U_DKPPAGE'	=> append_sid("{$phpbb_root_path}dkp.$phpEx", '&amp;page=listraids&amp;guild_id=' . $Navigation->getGuildId()),
            ),
            array(
                'DKPPAGE'		=> $user->lang['MENU_VIEWRAID'],
                'U_DKPPAGE'	=> append_sid("{$phpbb_root_path}dkp.$phpEx", '&amp;page=listraids&amp;' . URI_RAID . '=' . $raid_id . '&amp;guild_id=' . $Navigation->getGuildId()),
            ),
        );

        foreach($navlinks_array as $name)
        {
            $template->assign_block_vars('dkpnavlinks', array(
                'DKPPAGE' => $name['DKPPAGE'],
                'U_DKPPAGE' => $name['U_DKPPAGE'],
            ));
        }

        //Raid information block
        $raid = new \bbdkp\controller\raids\Raids($raid_id);

        $title =  sprintf($user->lang['RAID_ON'], $raid->event_name, date('F j, Y', $raid->raid_start));

        $template->assign_vars(array(
            'S_DISPLAY_VIEWRAIDS' => true,
            'L_RAID_ON' 		  => sprintf($user->lang['RAID_ON'], $raid->event_name, date('F j, Y', $raid->raid_start)),
            'RAIDSTART' 		  => date('H:i:s', $raid->raid_start),
            'RAIDEND' 		  	  => date('H:i:s', $raid->raid_end) ,
            'DURATION' 		  	  => $raid->raid_duration,
            'RAID_ADDED_BY'		  => sprintf($user->lang['ADDED_BY'], $raid->raid_added_by ),
            'RAID_UPDATED_BY'	  => (trim($raid->raid_updated_by) != '') ? sprintf ( $user->lang ['UPDATED_BY'], $raid->raid_updated_by) : ' ',
            'RAID_NOTE'			  => $raid->raid_note,
            'IMAGEPATH' 			=> $phpbb_root_path . "images/bbdkp/event_images/" . $raid->event_imagename . ".png",
            'S_EVENT_IMAGE_EXISTS' 	=> (strlen($raid->event_imagename) > 1) ? true : false,
            'S_SHOWZS' 			=> ($config['bbdkp_zerosum'] == '1') ? true : false,
            'S_SHOWTIME' 		=> ($config['bbdkp_timebased'] == '1') ? true : false,
            'S_SHOWDECAY' 		=> ($config['bbdkp_decay'] == '1') ? true : false,
            'S_SHOWEPGP' 		=> ($config['bbdkp_epgp'] == '1') ? true : false,
            'F_RAID'			=> append_sid("{$phpbb_root_path}dkp.$phpEx" , 'page=raid&amp;'. URI_RAID . '=' . request_var(URI_RAID, 0))
        ));

// point listing
        $sort_order = array (
            0 => array ('member_name asc', 'member_name desc' ),
            1 => array ('raid_value asc', 'raid_value desc' ),
            2 => array ('time_bonus asc', 'time_bonus desc' ),
            3 => array ('zerosum_bonus asc', 'zerosum_bonus desc' ),
            4 => array ('raid_decay asc', 'raid_decay desc' ),
            5 => array ('total asc', 'total desc' ),
        );
        $current_order = $Navigation->switch_order ($sort_order);

        $raid_details = new \bbdkp\controller\raids\Raiddetail($raid_id);
        $raid->raid_details = (array) $raid_details->raid_details;

        $raid_value = 0.00;
        $time_bonus = 0.00;
        $zerosum_bonus = 0.00;
        $raid_decay = 0.00;
        $raid_total = 0.00;
        $countattendees = 0;

        foreach($raid->raid_details as $raid_detail)
        {
            // fill attendees table
            $template->assign_block_vars ('raids_row', array (
                    'U_VIEW_ATTENDEE' 		=> append_sid ("{$phpbb_root_path}dkp.$phpEx" , 'page=member&amp;' . URI_NAMEID .
                        "={$raid_detail['member_id']}&amp;" . URI_DKPSYS. "=" . $raid->event_dkpid),
                    'NAME' 		 			=> $raid_detail['member_name'],
                    'COLORCODE'  			=> ($raid_detail['colorcode'] == '') ? '#254689' : $raid_detail['colorcode'],
                    'CLASS_IMAGE' 			=> (strlen($raid_detail['imagename']) > 1) ? $phpbb_root_path . "images/bbdkp/class_images/" . $raid_detail['imagename'] . ".png" : '',
                    'S_CLASS_IMAGE_EXISTS' 	=> (strlen($raid_detail['imagename']) > 1) ? true : false,
                    'RACE_IMAGE' 			=> (strlen($raid_detail['raceimage']) > 1) ? $phpbb_root_path . "images/bbdkp/race_images/" . $raid_detail['raceimage'] . ".png" : '',
                    'S_RACE_IMAGE_EXISTS' 	=> (strlen($raid_detail['raceimage']) > 1) ? true : false,
                    'CLASS_NAME' 			=> $raid_detail['classname'],
                    'RAIDVALUE'  			=> $raid_detail['raid_value'],
                    'TIMEVALUE'  			=> $raid_detail['time_bonus'],
                    'ZSVALUE' 	 			=> $raid_detail['zerosum_bonus'],
                    'DECAYVALUE' 			=> $raid_detail['raid_decay'],
                    'TOTAL' 	 			=> $raid_detail['raid_value'] + $raid_detail['time_bonus']  + $raid_detail['zerosum_bonus'] - $raid_detail['raid_decay'],
                )
            );
            $raid_value += $raid_detail['raid_value'];
            $time_bonus += $raid_detail['time_bonus'];
            $zerosum_bonus += $raid_detail['zerosum_bonus'];
            $raid_decay += $raid_detail['raid_decay'];

            $countattendees += 1;
        }
        $raid_total = $raid_value + $time_bonus + $zerosum_bonus - $raid_decay;
        //reset the keys
        $raid->raid_details = array_values($raid->raid_details);
        // count blocks
        $blocksize = 7;
        $x = ceil(count($raid->raid_details) / $blocksize);
        //loop blocks
        for ( $i = 0; $i < $x; $i++ )
        {
            $block_vars = array();
            //loop columns
            for ( $j = 0; $j < $blocksize; $j++ )
            {
                $offset = $i + $x * $j;
                $attendee = ( isset($raid->raid_details[$offset]) ) ? $raid->raid_details[$offset] : '';
                if ( $attendee != '' )
                {
                    $block_vars += array(
                        'COLUMN'.$j.'_NAME' => '<strong><a style="color: '. $raid->raid_details[$offset]['colorcode'].';" href="' .
                            append_sid("{$phpbb_root_path}dkp.$phpEx", "page=member&amp;" . URI_NAMEID . '=' .
                                $raid->raid_details[$offset]['member_id'] . '&amp;' . URI_DKPSYS . '=' . $Navigation->getDkpsysId()) . '">' .
                            $raid->raid_details[$offset]['member_name'] . '</a></strong>'
                    );
                }
                else
                {
                    $block_vars += array(
                        'COLUMN'.$j.'_NAME' => ''
                    );
                }
                // Are we showing this column?
                $s_column = 's_column'.$j;
                ${$s_column} = true;
            }
            $template->assign_block_vars('attendees_row', $block_vars);
        }
        $column_width = floor(100 / $blocksize);

        $template->assign_vars(array(
            // attendees
            'O_NAME' 		=> $current_order ['uri'] [0],
            'O_RAIDVALUE' 	=> $current_order ['uri'] [1],
            'O_TIMEVALUE' 	=> $current_order ['uri'] [2],
            'O_ZSVALUE' 	=> $current_order ['uri'] [3],
            'O_DECAYVALUE' 	=> $current_order ['uri'] [4],
            'O_TOTALVALUE' 	=> $current_order ['uri'] [5],
            'RAIDVALUE'		=> sprintf("%.2f", $raid_value),
            'TIMEVALUE'		=> sprintf("%.2f", $time_bonus),
            'ZSVALUE'		=> sprintf("%.2f", $zerosum_bonus),
            'RAIDDECAY'		=> sprintf("%.2f", $raid_decay),
            'TOTAL'			=> sprintf("%.2f", $raid_total),
            'S_COLUMN0' => ( isset($s_column0) ) ? true : false,
            'S_COLUMN1' => ( isset($s_column1) ) ? true : false,
            'S_COLUMN2' => ( isset($s_column2) ) ? true : false,
            'S_COLUMN3' => ( isset($s_column3) ) ? true : false,
            'S_COLUMN4' => ( isset($s_column4) ) ? true : false,
            'S_COLUMN5' => ( isset($s_column5) ) ? true : false,
            'S_COLUMN6' => ( isset($s_column6) ) ? true : false,
            'S_COLUMN7' => ( isset($s_column7) ) ? true : false,
            'S_COLUMN8' => ( isset($s_column8) ) ? true : false,
            'S_COLUMN9' => ( isset($s_column9) ) ? true : false,
            'COLUMN_WIDTH' => ( isset($column_width) ) ? $column_width : 0,
            'COLSPAN'	   => $blocksize,
            'ATTENDEES_FOOTCOUNT' => sprintf($user->lang['VIEWRAID_ATTENDEES_FOOTCOUNT'], $countattendees),
        ));

        //drops block

        //prepare item list sql
        $isort_order = array (
            0 => array ('m.member_name', 'm.member_name desc' ),
            1 => array ('i.item_name', 'item_name desc' ),
            2 => array ('i.item_value ', 'item_value desc' ),
        );

        $icurrent_order = $Navigation->switch_order ($isort_order, 'ui');
        $loot = new \bbdkp\controller\loot\Loot();
        $raid->loot_details = $loot->GetAllLoot( $icurrent_order ['sql'], 0, $Navigation->getDkpsysId(), $raid_id, 0, 0);

        $number_items = 0;
        $item_value = 0.00;
        $item_decay = 0.00;
        $item_total = 0.00;
        while ( $item = $db->sql_fetchrow($raid->loot_details))
        {
            if ($Navigation->bbtips == true && $item['item_gameid'] == 'wow')
            {
                $item_name = '<strong>' . $Navigation->bbtips->parse('[itemdkp]' . $item['item_name']  . '[/itemdkp]')  . '</strong>';
            }
            else
            {
                $item_name = '<strong>' . $item ['item_name'] . '</strong>';
            }

            $buyer = new \bbdkp\controller\members\Members( $item['member_id']);

            $template->assign_block_vars ( 'items_row', array (
                'DATE' 			=> (! empty ( $item ['item_date'] )) ? $user->format_date($item['item_date']) : '&nbsp;',
                'COLORCODE'  	=> $buyer->colorcode,
                'CLASS_IMAGE' 	=> $buyer->class_image,
                'S_CLASS_IMAGE_EXISTS' => (strlen($buyer->class_image) > 1) ? true : false,
                'RACE_IMAGE' 	=> $buyer->race_image,
                'S_RACE_IMAGE_EXISTS' => (strlen($buyer->race_image) > 1) ? true : false,
                'BUYER' 		=> $buyer->member_name,
                'ITEMNAME'      => $item_name,
                'ITEM_ID'		=> $item['item_id'],
                'ITEM_ZS'      	=> ($item['item_zs'] == 1) ? ' checked="checked"' : '',
                'U_VIEW_BUYER' => append_sid ("{$phpbb_root_path}dkp.$phpEx" , "page=member&amp;" . URI_NAMEID . "={$item['member_id']}&amp;" . URI_DKPSYS. "=" . $raid->event_dkpid),
                'ITEMVALUE' 	=> $item['item_value'],
                'DECAYVALUE' 	=> $item['item_decay'],
                'TOTAL' 		=> $item['item_net'],
            ));
            unset($buyer);

            $number_items++;
            $item_value += $item['item_value'];
            $item_decay += $item['item_decay'];
            $item_total += $item['item_net'];
        }


        $template->assign_vars(array(
            'S_SHOWITEMPANE' 	=> ($number_items > 0 ) ? true : false,
            'ITEM_VALUE'	 	 => $item_value,
            'ITEMDECAYVALUE'	 => $item_decay,
            'ITEMTOTAL'			 => $item_total,
            'RAIDNET'			 => $raid_total - $item_total,
            'ITEM_FOOTCOUNT'	 => sprintf($user->lang['VIEWRAID_DROPS_FOOTCOUNT'], $number_items) ,

        ));

        // Class statistics
        $LootStats = new \bbdkp\controller\loot\LootController;
        $LootStats->ClassLootStats($raid, 0, true, $Navigation->getDkpsysId(), false);

        // Output page
        page_header($title);

    }
}

