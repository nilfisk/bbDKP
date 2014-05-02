<?php
/**
* Viewmember module. shows one raid to user
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

// Include the member class
if (!class_exists('\bbdkp\controller\members\Members'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/members/Members.$phpEx");
}
if (!class_exists('\bbdkp\controller\raids\Raids'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/raids/Raids.$phpEx");
}
if (!class_exists('\bbdkp\controller\points\Points'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/points/Points.$phpEx");
}
if (!class_exists('\bbdkp\controller\loot\Loot'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/loot/Loot.$phpEx");
}
if (!class_exists('\bbdkp\controller\adjustments\Adjust'))
{
    require("{$phpbb_root_path}includes/bbdkp/controller/adjustments/Adjust.$phpEx");
}

class viewMember implements iViews
{
    function __construct(viewNavigation $Navigation)
    {
        $this->buildpage($Navigation);
    }

    public function buildpage(viewNavigation $Navigation)
    {
        global $db, $config, $phpbb_root_path, $phpEx, $user, $template;
        if ( !isset($_GET[URI_NAMEID]) )
        {
            trigger_error ($user->lang['MNOTFOUND']);
        }
        $member_id = request_var(URI_NAMEID, 0);
        $member = new \bbdkp\controller\members\Members($member_id);
        $points = new \bbdkp\controller\points\Points($member_id, $Navigation->getDkpsysId());
        $Raids = new \bbdkp\controller\raids\Raids();
        $Adjustments = new \bbdkp\controller\adjustments\Adjust($Navigation->getDkpsysId());

        /* Get attendance */
        $range1 = $config['bbdkp_list_p1'];
        $range2 = $config['bbdkp_list_p2'];
        $range3 = $config['bbdkp_list_p3'];

        $mc1 = $Raids->raidcount($Navigation->getDkpsysId(), $range1, $member_id, 0, false, $member->member_guild_id);
        $mc2 = $Raids->raidcount($Navigation->getDkpsysId(), $range2, $member_id, 0, false, $member->member_guild_id);
        $mc3 = $Raids->raidcount($Navigation->getDkpsysId(), $range3, $member_id, 0, false, $member->member_guild_id);
        $mclife = $Raids->raidcount($Navigation->getDkpsysId(), 0, $member_id, 0, true, $member->member_guild_id);

        $pc1	= $Raids->raidcount($Navigation->getDkpsysId(), $range1, $member_id, 1, false, $member->member_guild_id);
        $pc2	= $Raids->raidcount($Navigation->getDkpsysId(), $range2, $member_id, 1, false, $member->member_guild_id);
        $pc3	= $Raids->raidcount($Navigation->getDkpsysId(), $range3, $member_id, 1, false, $member->member_guild_id);
        $pclife = $Raids->raidcount($Navigation->getDkpsysId(), 0, $member_id, 1, true, $member->member_guild_id);

        $pct1 =	 ( $pc1 > 0 ) ? round(($mc1 / $pc1) * 100, 1) : 0;
        $pct2 =	 ( $pc2 > 0 ) ? round(($mc2 / $pc2) * 100, 1) : 0;
        $pct3 =	 ( $pc3 > 0 ) ? round(($mc3 / $pc3) * 100, 1) : 0;
        $pctlife = ( $pclife > 0 ) ? round(($mclife / $pclife) * 100, 1) : 0;

        /**
         *
         * RAID history
         *
         *
         **/
        $rstart = request_var('rstart',0);
        if($config['bbdkp_epgp'] == '1')
        {
            $current_earned = $points->earned_net;
        }
        else
        {
            $current_earned = $points->earned_net;
        }

        $raids_result = $Raids->getRaids('r.raid_start DESC', $Navigation->getDkpsysId(), 0, $rstart, $member_id);
        while ( $raid = $db->sql_fetchrow($raids_result))
        {
            $template->assign_block_vars('raids_row', array(
                'DATE'			 => ( !empty($raid['raid_start']) ) ? date($config['bbdkp_date_format'], $raid['raid_start']) : '&nbsp;',
                'U_VIEW_RAID'	 => append_sid("{$phpbb_root_path}dkp.$phpEx" , 'page=raid&amp;' . URI_RAID . '='.$raid['raid_id']),
                'NAME'			 => $raid['event_name'],
                'NOTE'			 => ( !empty($raid['raid_note']) ) ? $raid['raid_note'] : '&nbsp;',
                'RAIDVAL'		 => $raid['raid_value'],
                'TIMEBONUS'		 => $raid['time_value'],
                'ZSBONUS'		 => $raid['zs_value'],
                'RAIDDECAY'		 => $raid['raiddecay'],
                'EARNED'		 => $raid['net_earned'],
                'CURRENT_EARNED' => sprintf("%.2f", $current_earned)
            ));
            $current_earned = $current_earned - $raid['net_earned'];
        }

        /**
         *
         * Adjustments
         *
         */
        $sort_order = array(
            0 => array('adjustment_id desc' , 'adjustment_id asc'),
            1 => array('adjustment_date desc, member_name asc' , 'adjustment_date asc, member_name asc') ,
            2 => array('adjustment_dkpid' , 'adjustment_dkpid desc') ,
            3 => array('dkpsys_name' , 'dkpsys_name desc'),
            4 => array('member_name' , 'member_name desc'),
            5 => array('adjustment_reason' , 'adjustment_reason desc') ,
            6 => array('adjustment_value desc' , 'adjustment_value') ,
            7 => array('adjustment_added_by' , 'adjustment_added_by desc'),
        );

        $result2 = $Adjustments->countadjust($member_id);
        $total_adjustments = (int) $db->sql_fetchfield('total_adjustments');
        $db->sql_freeresult($result2);

        $current_order = $Navigation->switch_order ($sort_order);
        $astart = request_var('astart', 0);
        $current_adj = $points->adjustment - $points->adj_decay;
        $result = $Adjustments->ListAdjustments($current_order['sql'], $member_id, $astart );
        while ($adj = $db->sql_fetchrow($result))
        {
            $template->assign_block_vars('adjustments_row', array(
                'DATE' => date($config['bbdkp_date_format'], $adj['adjustment_date']) ,
                'ADJID' => $adj['adjustment_id'] ,
                'DKPID' => $adj['adjustment_dkpid'] ,
                'DKPPOOL' => $adj['dkpsys_name'] ,
                'REASON' => (isset($adj['adjustment_reason'])) ? $adj['adjustment_reason'] : '' ,
                'COLOR' => ($adj['adjustment_value'] < 0) ? 'negative' : 'positive' ,
                'ADJUSTMENT' => $adj['adjustment_value'] == 0 ? '' : number_format($adj['adjustment_value'],2) ,
                'CAN_DECAY' => $adj['can_decay'],
                'ADJ_DECAY' => -1 * $adj['adj_decay'] == 0 ? '0.00' : -1 * $adj['adj_decay'],
                'ADJUSTMENT_NET' => ($adj['adjustment_value'] - $adj['adj_decay']) == 0 ? '' : number_format($adj['adjustment_value'] - $adj['adj_decay'], 2) ,
                'CURRENT_ADJ' =>  sprintf("%.2f", $current_adj),
                'COLORCURRENT' => ($current_adj > 0) ? 'positive' : 'nagative' ,
                'ADDED_BY' => $adj['adjustment_added_by']
            ));

            $current_adj = $current_adj - ($adj['adjustment_value'] - $adj['adj_decay']);

        }
        $db->sql_freeresult($result);
        $listadj_footcount = sprintf($user->lang['LISTADJ_FOOTCOUNT'], $total_adjustments, $config['bbdkp_user_alimit']);

        $adjpagination = $Navigation->generate_pagination2(append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=member&amp;' .
                URI_DKPSYS.'='. $Navigation->getDkpsysId(). '&amp;' . URI_NAMEID. '='.$member_id. '&amp;astart=' .$astart),
            $total_adjustments, $config ['bbdkp_user_alimit'], $astart, 1, 'astart');

        $template->assign_vars(array(
            'S_SHOW' => ($total_adjustments > 0) ? true : false ,
            'O_ADJID' => $current_order['uri'][0] ,
            'O_DATE' => $current_order['uri'][1] ,
            'O_DKPID' => $current_order['uri'][2] ,
            'O_DKPPOOL' => $current_order['uri'][3] ,
            'O_MEMBER' => $current_order['uri'][4] ,
            'O_REASON' => $current_order['uri'][5] ,
            'O_ADJUSTMENT' => $current_order['uri'][6] ,
            'O_ADDED_BY' => $current_order['uri'][7] ,
            'ASTART' => $astart ,
            'LISTADJ_FOOTCOUNT' => $listadj_footcount ,
            'ADJUSTMENTS_PAGINATION' => $adjpagination,
            'PAGE_NUMBER'    => on_page($total_adjustments, $config['bbdkp_user_alimit'], $astart),
        ));

        /**
         *
         * loot history
         *
         *
         **/
        $istart = request_var('istart', 0);
        if($config['bbdkp_epgp'] == '1')
        {
            $current_spent = $points->gp_net;
        }
        else
        {
            $current_spent = $points->item_net;
        }

        $loot = new \bbdkp\controller\loot\Loot();
        $lootdetails = $loot->GetAllLoot( ' i.item_date DESC ',0,  $Navigation->getDkpsysId(), 0, $istart, $member_id );
        while ( $item = $db->sql_fetchrow($lootdetails))
        {
            if ($Navigation->bbtips == true && $item['item_gameid'] == 'wow')
            {
                if($item['wowhead_id'] > 0)
                {
                    $item_name = '<strong>' . $Navigation->bbtips->parse('[itemdkp]' . $item['wowhead_id']	 . '[/itemdkp]') . '</strong>' ;
                }
                else
                {
                    $item_name = '<strong>' . $Navigation->bbtips->parse('[itemdkp]' . $item['item_name']	 . '[/itemdkp]') . '</strong>' ;
                }
            }
            else
            {
                $item_name = '<strong>' . $item['item_name'] . '</strong>';
            }

            $template->assign_block_vars('items_row', array(
                    'DATE'			=> ( !empty($item['item_date']) ) ? date($config['bbdkp_date_format'], $item['item_date']) : $item['item_date'] . '&nbsp;',
                    'U_VIEW_ITEM'	=> append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=item&amp;' . URI_ITEM . '=' . $item['item_id']),
                    'U_VIEW_RAID'	=> append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=raid&amp;' . URI_RAID . '=' . $item['raid_id']),
                    'NAME'			=> $item_name,
                    'RAID'			=> ( !empty($item['event_name']) ) ? $item['event_name'] : '&lt;<i>Not Found</i>&gt;',
                    'SPENT'			=> sprintf("%.2f", $item['item_value']),
                    'DECAY'			=> sprintf("%.2f", $item['item_decay']),
                    'SPENT_NET'		=> sprintf("%.2f", $item['item_net']),
                    'CURRENT_SPENT' => sprintf("%.2f", $current_spent))
            );
            $current_spent -= $item['item_net'];

        }
        $db->sql_freeresult($lootdetails);

        $sql_array = array(
            'SELECT'	=>	'count(*) as itemcount	',
            'FROM'		=> array(
                EVENTS_TABLE		=> 'e',
                RAIDS_TABLE			=> 'r',
                RAID_ITEMS_TABLE			=> 'i',
            ),

            'WHERE'		=>	" e.event_id = r.event_id
		AND e.event_dkpid=" . (int) $Navigation->getDkpsysId() . '
		AND r.raid_id = i.raid_id
		AND i.member_id  = ' . $member_id,
        );
        $sql6 = $db->sql_build_query('SELECT', $sql_array);
        $result6 = $db->sql_query($sql6);
        $total_purchased_items = $db->sql_fetchfield('itemcount');
        $db->sql_freeresult($result6);

        $raidpag  = $Navigation->generate_pagination2(append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=member&amp;' .
                URI_DKPSYS.'='.$Navigation->getDkpsysId(). '&amp;' . URI_NAMEID. '='.$member_id. '&amp;istart=' .$istart),
            $points->raidcount, $config ['bbdkp_user_rlimit'], $rstart, 1, 'rstart');

        $itpag =   $Navigation->generate_pagination2(append_sid("{$phpbb_root_path}dkp.$phpEx" ,'page=member&amp;'.
                URI_DKPSYS.'='.$Navigation->getDkpsysId(). '&amp;' . URI_NAMEID. '='.$member_id. '&amp;rstart='.$rstart ),
            $total_purchased_items,	  $config ['bbdkp_user_ilimit'], $istart, 1 ,'istart');

        $template->assign_vars(array(
            'RAID_PAGINATION'	  => $raidpag,
            'RSTART'			  => $rstart,
            'RAID_FOOTCOUNT'	  => sprintf($user->lang['VIEWMEMBER_RAID_FOOTCOUNT'], $points->raidcount, $config ['bbdkp_user_rlimit']),
            'ITEM_PAGINATION'	  => $itpag,
            'ISTART'			  => $istart,
            'ITEM_FOOTCOUNT'	  => sprintf($user->lang['VIEWMEMBER_ITEM_FOOTCOUNT'], $total_purchased_items, $config ['bbdkp_user_ilimit']),
            'ITEMS'				  => ( is_null($total_purchased_items) ) ? false : true,
        ));

        //output
        $url = append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=member&amp;' . URI_NAMEID . '=' . $member_id .'&amp;' . URI_DKPSYS . '=' . $Navigation->getDkpsysId());

        $template->assign_vars(array(
            'S_DISPLAY_VIEWMEMBER' => true,
            'S_SHOWZS' 		   => ($config['bbdkp_zerosum'] == '1') ? true : false,
            'S_SHOWDECAY' 	   => ($config['bbdkp_decay'] == '1') ? true : false,
            'S_SHOWEPGP' 	   => ($config['bbdkp_epgp'] == '1') ? true : false,
            'S_SHOWTIME' 	   => ($config['bbdkp_timebased'] == '1') ? true : false,

            'NAME'	   		  => $member->member_name,
            'GUILD'	   		  => $member->member_guild_name,
            'REGION'   		  => $member->member_region,
            'REALM'	   		  => $member->member_realm,
            'MEMBER_LEVEL'    => $member->member_level,
            'MEMBER_DKPID'    => $Navigation->getDkpsysId(),
            'MEMBER_DKPNAME'  => $Navigation->getDkpsysName(),
            'MEMBER_RACE'     => $member->member_race,
            'MEMBER_CLASS'    => $member->member_class,
            'COLORCODE'       => $member->colorcode,
            'CLASS_IMAGE'       => $member->class_image,
            'S_CLASS_IMAGE_EXISTS' =>  (strlen($member->class_image) > 1) ? true : false,
            'RACE_IMAGE'       => $member->race_image,
            'S_RACE_IMAGE_EXISTS' =>  (strlen($member->race_image) > 1) ? true : false,
            'MEMBER_RANK'      => $member->member_rank_id,
            'U_VIEW_MEMBER'    => $url,
            'POINTNAME'		   => $config['bbdkp_dkp_name'],
        ));

        if($config['bbdkp_epgp'] == '0')
        {
            $template->assign_vars(array(
                'RAIDVAL'       => sprintf("%.2f", $points->raid_value),
                'TIMEBONUS'     => sprintf("%.2f", $points->time_bonus),
                'ZEROSUM'      	=> sprintf("%.2f", $points->zerosum_bonus),
                'RAIDDECAY'		=> sprintf("%.2f", $points->earned_decay),
                'EARNED'        => sprintf("%.2f", $points->total_earned) ,
                'EARNED_NET'    => sprintf("%.2f", $points->earned_net),

                'SPENT'         => sprintf("%.2f", $points->spent),
                'ITEMDECAY'     => sprintf("%.2f", $points->item_decay),
                'ITEMNET'		=> sprintf("%.2f", $points->item_net),
                'CURRENT'       => sprintf("%.2f", $points->total),
                'C_CURRENT'       => ($points->total > 0) ? 'positive' : 'negative',
                'ADJUSTMENT'    => sprintf("%.2f", $points->adjustment),
                'C_ADJUSTMENT'  => ($points->adjustment > 0) ? 'positive' : 'negative',
                'ADJDECAY'		=> sprintf("%.2f", $points->adj_decay),
                'ADJNET'		=> sprintf("%.2f", $points->adj_net),
                'TOTAL_DECAY'	=> sprintf("%.2f", $points->total_decayed),
                'C_TOTAL_DECAY'	=> $points->total_decayed > 0 ? 'negative' : 'positive',
                'NETCURRENT'    => sprintf("%.2f", $points->total_net),
                'C_NETCURRENT'  => $points->total_net > 0 ? 'positive' : 'negative',
            ));
        }
        elseif($config['bbdkp_epgp'] == '1')
        {
            $template->assign_vars(array(
                'EP'    		=> $points->ep,
                'EPNET'			=> (float) $points->ep_net,
                'GP'     		=> $points->gp,
                'BGP'     		=> $config['bbdkp_basegp'],
                'GPNET'     	=> $points->gp_net,
                'PR'     		=> $points->pr,
                'PRNET'     	=> $points->pr_net,
            ));
        }

        $template->assign_vars(array(
            'RAID_FOOTCOUNT'  => sprintf($user->lang['VIEWMEMBER_RAID_FOOTCOUNT'], $points->raidcount, $config ['bbdkp_user_rlimit']),
            'RAIDS_X1_DAYS'	  => sprintf($user->lang['RAIDS_X_DAYS'], $range1),
            'RAIDS_X2_DAYS'	  => sprintf($user->lang['RAIDS_X_DAYS'], $range2),
            'RAIDS_X3_DAYS'	  => sprintf($user->lang['RAIDS_X_DAYS'], $range3),
            'RAIDS_LIFETIME'  => sprintf($user->lang['RAIDS_LIFETIME'], date($config['bbdkp_date_format'], $points->firstraid), date($config['bbdkp_date_format'], $points->lastraid)),
            'C_RAIDS_X1_DAYS'  => $mc1 .'/'. $pc1 .' : '. $pct1,
            'C_RAIDS_X2_DAYS'  => $mc2 .'/'. $pc2 .' : '. $pct2,
            'C_RAIDS_X3_DAYS'  => $mc3 .'/'. $pc3 .' : '. $pct3,
            'C_RAIDS_LIFETIME' => $mclife .'/'. $pclife .' : '. $pctlife,
        ));

        $navlinks_array = array(
            array(
                'DKPPAGE' 	=> $user->lang['MENU_STANDINGS'],
                'U_DKPPAGE' => append_sid("{$phpbb_root_path}dkp.$phpEx" , 'page=standings&amp;' . URI_DKPSYS . '=' . $Navigation->getDkpsysId()),
            ),

            array(
                'DKPPAGE' => sprintf($user->lang['MENU_VIEWMEMBER'], $member->member_name) ,
                'U_DKPPAGE' => $url,
            ));

        foreach( $navlinks_array as $name )
        {
            $template->assign_block_vars('dkpnavlinks', array(
                'DKPPAGE'	=> $name['DKPPAGE'],
                'U_DKPPAGE' => $name['U_DKPPAGE'],
            ));
        }

        $template->assign_vars(array(

        ));

        // Output page
        page_header($user->lang['MEMBER']);
    }

}
