<?php
/**
 * Module viewEvent
 *
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

class viewEvent implements iViews
{
    function __construct(viewNavigation $Navigation)
    {
        $this->buildpage($Navigation);
    }

    public function buildpage(viewNavigation $Navigation)
    {
        global $db, $config, $phpbb_root_path, $phpEx, $user, $template;
        $raids =array();
        if ( !(isset($_GET[URI_EVENT]) && isset($_GET[URI_DKPSYS]) ))
        {
            trigger_error($user->lang['ERROR_EVENT_UNKNOWN']);
        }

        if (!class_exists('\bbdkp\controller\raids\Events'))
        {
            require("{$phpbb_root_path}includes/bbdkp/controller/raids/Events.$phpEx");
        }

        $event_id = request_var(URI_EVENT, 0);
        $url = append_sid("{$phpbb_root_path}dkp.$phpEx" , 'page=event&amp;' . URI_EVENT . '='.  $event_id . '&amp;' . URI_DKPSYS . '='. $Navigation->getDkpsysId() ) ;

        $event  = new \bbdkp\controller\raids\Events();
        $event->get($event_id);
        if(strlen($event->event_imagename) > 0)
        {
            $eventimg = $phpbb_root_path . "images/bbdkp/event_images/" . $event->event_imagename . ".png";
        }
        else
        {
            $eventimg = $phpbb_root_path . "images/bbdkp/event_images/dummy.png";
        }

        $template->assign_vars(array(
            'EVENTNAME' 	 => $event->event_name,
            'VALUE' 		 => $event->event_value,
            'IMAGEPATH' 	=>  $eventimg,
            'RECORDED_RAID_HISTORY' => sprintf($user->lang['RECORDED_RAID_HISTORY'], $event->event_name),
        ));

        $sort_order = array(
            0 => array('raid_start desc', 'raid_start'),
            1 => array('raid_note', 'raid_note desc'),
            2 => array('raid_value desc', 'raid_value')
        );

        $current_order = $Navigation->switch_order ( $sort_order );
        $sql_array = array (
            'SELECT' => ' e.event_dkpid, e.event_name,
					r.raid_id, r.raid_start, r.raid_note,
					r.raid_added_by, r.raid_updated_by,
					SUM(ra.raid_value) as raid_value,
					SUM(ra.time_bonus) as time_value,
					SUM(ra.zerosum_bonus) as zs_value,
					SUM(ra.raid_decay) as raiddecay,
					SUM(ra.raid_value + ra.time_bonus  + ra.zerosum_bonus - ra.raid_decay) as total',
            'FROM' => array (
                EVENTS_TABLE 		=> 'e',
                RAIDS_TABLE 		=> 'r' ,
                RAID_DETAIL_TABLE	=> 'ra' ,
                MEMBER_LIST_TABLE 	=> 'l',
            ),
            'WHERE' => ' ra.raid_id = r.raid_id
				AND r.event_id = e.event_id
				AND e.event_id = ' . ( int ) $event_id .'
				AND ra.member_id = l.member_id
				AND l.member_guild_id = ' . $Navigation->getGuildId(),
            'GROUP_BY' =>  'e.event_dkpid, e.event_name,
					r.raid_id,  r.raid_start, r.raid_note,
					r.raid_added_by, r.raid_updated_by',
            'ORDER_BY' => $current_order ['sql'],
        );

        $sql = $db->sql_build_query('SELECT', $sql_array);
        $result = $db->sql_query($sql);
        $raid_count=0;
        while ($row = $db->sql_fetchrow($result))
        {
            $raid_count++;
        }

        if($raid_count > 0)
        {

            $startr = request_var ( 'startr', 0 );
            // get requested window
            $raid_ids = array();
            $result = $db->sql_query_limit ( $sql, $config ['bbdkp_user_rlimit'], $startr );
            while ( $row = $db->sql_fetchrow($result) )
            {
                $raids[$row['raid_id']] = array(
                    'event_name'  	=> $row['event_name'],
                    'raid_id'    	=> $row['raid_id'],
                    'raid_start'  	=> $row['raid_start'],
                    'raid_note'  	=> $row['raid_note'],
                    'raid_value' 	=> $row['raid_value'],
                    'time_value' 	=> $row['time_value'],
                    'zs_value' 		=> $row['zs_value'],
                    'raiddecay' 	=> $row['raiddecay'],
                    'total' 		=> $row['total'],
                );

                $raid_ids[] = $row['raid_id'];
            }
            $db->sql_freeresult($result);

            // Find the attendees at each raid
            $sql = 'SELECT raid_id, count(member_id) AS countatt
			FROM ' . RAID_DETAIL_TABLE . '
			WHERE ' . $db->sql_in_set('raid_id', $raid_ids) . '
			GROUP BY raid_id';
            $result = $db->sql_query($sql);

            while ( $row = $db->sql_fetchrow($result) )
            {
                $raids[$row['raid_id']]['numattendees'] = $row['countatt'];
            }
            $db->sql_freeresult($result);

            //calculate the average event attendance and droprate
            // Find the item drops for each raid
            $sql = 'SELECT raid_id, count(item_id) AS countatt
			FROM ' . RAID_ITEMS_TABLE . '
			WHERE ' . $db->sql_in_set('raid_id', $raid_ids) . ' GROUP BY raid_id';
            $result = $db->sql_query($sql);
            while ( $row = $db->sql_fetchrow($result) )
            {
                $raids[$row['raid_id']]['numitems'] = $row['countatt'];
            }
            $db->sql_freeresult($result);

            $total_drop_count = 0;
            $total_attendees_count = 0;
            $total_earned = 0;

            // Loop through the raids for this event
            $total_raid_count = sizeof($raids);
            foreach ( $raids as $raid_id => $raid )
            {
                $drop_count = ( isset($raid['numitems']) ) ? $raid['numitems'] : 0;
                $attendees_count = ( isset($raid['numattendees']) ) ? $raid['numattendees'] : 0;

                $template->assign_block_vars('raids_row', array(
                        'U_VIEW_RAID' => append_sid("{$phpbb_root_path}dkp.$phpEx" , 'page=raid&amp;'. URI_RAID . '='.$raid['raid_id']),
                        'DATE'        => date($config['bbdkp_date_format'], $raid['raid_start']),
                        'ATTENDEES'   => $attendees_count,
                        'DROPS'       => $drop_count,
                        'NOTE'        => ( !empty($raid['raid_note']) ) ? $raid['raid_note'] : '&nbsp;',
                        'RAIDVALUE'   => $raid['raid_value'],
                        'TIMEVALUE'   => $raid['time_value'],
                        'ZSVALUE'     => $raid['zs_value'],
                        'DECAYVALUE'  => $raid['raiddecay'],
                        'TOTAL'       => $raid['total'],
                    )
                );

                $total_drop_count += $drop_count;
                $total_attendees_count += $attendees_count;
                $total_earned += $raid['raid_value'];
            }

            // Prevent div by 0
            $average_attendees = ( $total_raid_count > 0 ) ? round($total_attendees_count / $total_raid_count, 2) : 0;
            $average_drops     = ( $total_drop_count > 0 ) ? round($total_drop_count / $total_raid_count,2 )      : 0;

            $raidpagination = $Navigation->generate_pagination2($url . '&amp;o1=' . $current_order ['uri'] ['current'] , $raid_count, $config ['bbdkp_user_rlimit'], $startr, true, 'startr'  );

            $start = request_var('start' ,0);

            // item selection
            $sql_array = array(
                'SELECT'    => 'i.item_id, i.item_gameid, i.item_name, i.item_gameid, i.member_id, i.item_zs,
			l.member_name, c.colorcode, c.imagename, l.member_gender_id,
			a.image_female, a.image_male, i.item_date, i.raid_id, i.item_value,
			i.item_decay, i.item_value - i.item_decay as item_total',
                'FROM'      => array(
                    CLASS_TABLE 		=> 'c',
                    RACE_TABLE  		=> 'a',
                    MEMBER_LIST_TABLE 	=> 'l',
                    RAID_ITEMS_TABLE    => 'i',
                ),
                'WHERE'     =>  'c.class_id = l.member_class_id
			AND c.game_id = l.game_id
			AND l.member_race_id =  a.race_id
			AND a.game_id = l.game_id
			and l.member_id = i.member_id AND ' . $db->sql_in_set('raid_id', $raid_ids),
            );
            $sql = $db->sql_build_query('SELECT', $sql_array);

            $result = $db->sql_query_limit($sql, $config['bbdkp_user_ilimit'], $start);

            $number_items = 0;
            $item_value = 0.00;
            $item_decay = 0.00;
            $item_total = 0.00;

            while ( $row = $db->sql_fetchrow($result) )
            {
                if ($Navigation->bbtips == true && $row['item_gameid'] == 'wow')
                {
                    $item_name = '<strong>' . $Navigation->bbtips->parse('[itemdkp]' .  $row['item_name']   . '[/itemdkp]') . '</strong>';
                }
                else
                {
                    $item_name = '<strong>' . $row['item_name'] . '</strong>';
                }

                $race_image = (string) (($row['member_gender_id']==0) ? $row['image_male'] : $row['image_female']);
                $template->assign_block_vars('items_row', array(
                    'DATE'          => date($config['bbdkp_date_format'], $row['item_date']),
                    'U_VIEW_RAID'   => append_sid("{$phpbb_root_path}dkp.$phpEx" , 'page=raid&amp;' . URI_RAID . '=' . $row['raid_id']) ,
                    'COLORCODE'  	=> ($row['colorcode'] == '') ? '#254689' : $row['colorcode'],
                    'CLASS_IMAGE' 	=> (strlen($row['imagename']) > 1) ? $phpbb_root_path . "images/bbdkp/class_images/" . $row['imagename'] . ".png" : '',
                    'S_CLASS_IMAGE_EXISTS' => (strlen($row['imagename']) > 1) ? true : false,
                    'RACE_IMAGE' 	=> (strlen($race_image) > 1) ? $phpbb_root_path . "images/bbdkp/race_images/" . $race_image . ".png" : '',
                    'S_RACE_IMAGE_EXISTS' => (strlen($race_image) > 1) ? true : false,
                    'BUYER' 		=> (! empty ( $row ['member_name'] )) ? $row ['member_name'] : '&lt;<i>Not Found</i>&gt;',
                    'U_VIEW_MEMBER' => append_sid("{$phpbb_root_path}dkp.$phpEx" , 'page=member&amp;' . URI_NAMEID . '=' . $row['member_id'] . '&amp;' . URI_DKPSYS . '='. $Navigation->getDkpsysId()) ,
                    'ITEMNAME'      => $item_name,
                    'U_VIEW_ITEM'   => append_sid("{$phpbb_root_path}dkp.$phpEx" , 'page=item&amp;' . URI_ITEM . '=' . $row['item_id']) ,
                    'ITEM_ZS'      	=> ($row['item_zs'] == 1) ? ' checked="checked"' : '',
                    'ITEMVALUE' 	=> $row['item_value'],
                    'DECAYVALUE' 	=> $row['item_decay'],
                    'TOTAL' 		=> $row['item_total'],
                ));

                $number_items++;
                $item_value += $row['item_value'];
                $item_decay += $row['item_decay'];
                $item_total += $row['item_total'];
            }

            $itempagination = generate_pagination($url, $total_drop_count, $config['bbdkp_user_ilimit'], $start, true);

            $template->assign_vars(array(
                'RAIDPAGINATION' 	  => $raidpagination ,
                'O_DATE'  			  => $current_order['uri'][0],
                'O_NOTE'  			  => $current_order['uri'][1],
                'O_VALUE' 			  => $current_order['uri'][2],

                'DKPPOOL'			  => $event->dkpsys_name,
                'AVERAGE_ATTENDEES'   => $average_attendees,
                'AVERAGE_DROPS'       => $average_drops,
                'TOTAL_EARNED'        => sprintf("%.2f", $total_earned),
                'VIEWEVENT_FOOTCOUNT' => sprintf($user->lang['VIEWEVENT_FOOTCOUNT'], $total_raid_count),

                'S_SHOWZS' 			=> ($config['bbdkp_zerosum'] == '1') ? true : false,
                'S_SHOWTIME' 		=> ($config['bbdkp_timebased'] == '1') ? true : false,
                'S_SHOWDECAY' 		=> ($config['bbdkp_decay'] == '1') ? true : false,

                'L_RECORDED_DROP_HISTORY' => sprintf($user->lang['RECORDED_DROP_HISTORY'], $event->event_name),
                'ITEM_FOOTCOUNT'      => sprintf($user->lang['VIEWITEM_FOOTCOUNT'], $total_drop_count, $total_drop_count),
                'START' 			=> $start,
                'ITEM_PAGINATION' 	=> $itempagination,
                'S_DISPLAY_VIEWEVENT' => true,
                'S_EPGP' => $config['bbdkp_epgp'] == '1' ? true: false,
            ));
        }
        else
        {
            $template->assign_vars(array(
                'S_DISPLAY_VIEWEVENT' => true,
                'U_VIEW_EVENT'        => $url
            ));
        }

        // build breadcrumbs menu
        $navlinks_array = array(
            array(
                'DKPPAGE' => $user->lang['MENU_EVENTS'],
                'U_DKPPAGE' => append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=listevents&amp;guild_id=' . $Navigation->getGuildId()),
            ),

            array(
                'DKPPAGE' =>  $event->event_name,
                'U_DKPPAGE' => $url ,
            ),
        );

        foreach( $navlinks_array as $name )
        {
            $template->assign_block_vars('dkpnavlinks', array(
                'DKPPAGE' => $name['DKPPAGE'],
                'U_DKPPAGE' => $name['U_DKPPAGE'],
            ));
        }

        // Output page
        page_header($user->lang['MENU_VIEWEVENT'] . ' ' .  $event->event_name);


    }
}

