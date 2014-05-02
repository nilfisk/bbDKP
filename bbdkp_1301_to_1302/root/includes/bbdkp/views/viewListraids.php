<?php
/**
 * list raids module
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

class viewListraids implements iViews
{
    function __construct(viewNavigation $Navigation)
    {
        $this->buildpage($Navigation);
    }

    public function buildpage(viewNavigation $Navigation)
    {
        global $db, $config, $phpbb_root_path, $phpEx, $user, $template;
        $raids = new \bbdkp\controller\raids\Raids();
        $start = request_var('start', 0);

        // get sort order
        $sort_order = array
        (
            0 => array('raid_start desc', 'raid_start'),
            1 => array('dkpsys_name', 'dkpsys_name desc'),
            2 => array('event_name', 'event_name desc'),
            3 => array('raid_note', 'raid_note desc'),
            4 => array('raid_value desc', 'raid_value')
        );

        $current_order = $Navigation->switch_order($sort_order);
        //total raids in the last year
        $total_raids = $raids->raidcount($Navigation->getDkpsysId(), 365, 0, 1, true, $Navigation->getGuildId());

        if ($Navigation->getQueryByPool())
        {
            $pagination = generate_pagination( append_sid("{$phpbb_root_path}dkp.$phpEx" , 'page=listraids&amp;' . URI_DKPSYS . '=' . $Navigation->getDkpsysId() .
                '&amp;o='.  $current_order['uri']['current'] ), $total_raids, $config['bbdkp_user_rlimit'], $start, true);

            $u_list_raids =  append_sid("{$phpbb_root_path}dkp.$phpEx" , 'page=listraids&amp;' . URI_DKPSYS . '='. $Navigation->getDkpsysId() . '&amp;guild_id=' . $Navigation->getGuildId());
        }
        else
        {
            $pagination = generate_pagination( append_sid("{$phpbb_root_path}dkp.$phpEx" , 'page=listraids&amp;' . URI_DKPSYS .  '=All&amp;o='.
                $current_order['uri']['current'] ), $total_raids, $config['bbdkp_user_rlimit'], $start, true);

            $u_list_raids =  append_sid("{$phpbb_root_path}dkp.$phpEx" , 'page=listraids&amp;guild_id=' . $Navigation->getGuildId());
        }

        $raids_result = $raids->getRaids('r.raid_start DESC', $Navigation->getDkpsysId(), 0, $start, 0, $Navigation->getGuildId());
        while ( $raid = $db->sql_fetchrow($raids_result))
        {
            $template->assign_block_vars('raids_row', array(
                    'DATE'			=> ( !empty($raid['raid_start']) ) ? date($config['bbdkp_date_format'], $raid['raid_start']) : '&nbsp;',
                    'NAME'			=> $raid['event_name'],
                    'U_VIEW_RAID'  	=> append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=raid&amp;' . URI_RAID . '='.$raid['raid_id'] . '&amp;guild_id=' . $Navigation->getGuildId() ),
                    'U_VIEW_EVENT' 	=> append_sid("{$phpbb_root_path}dkp.$phpEx" , 'page=event&amp;' . URI_EVENT . '='.  $raid['event_id'] . '&amp;' . URI_DKPSYS . '=' .
                        $raid['event_dkpid'] . '&amp;guild_id=' . $Navigation->getGuildId()),
                    'POOL' 			=> 	$Navigation->getDkpsysName(),
                    'EVENTCOLOR' 	=> ( !empty($raid['event_color']) ) ? $raid['event_color'] : '#254689',
                    'NOTE'			=> ( !empty($raid['raid_note']) ) ? $raid['raid_note'] : '&nbsp;',
                    'ATTENDEES' 	=> $raid['attendees'],
                    'RAIDVALUE' 	=> $raid['raid_value'],
                    'TIMEBONUS' 	=> $raid['time_value'],
                    'ZSBONUS' 		=> $raid['zs_value'],
                    'DECAYVALUE' 	=> $raid['raiddecay'],
                    'TOTAL'		 	=> $raid['net_earned'],

                )
            );
        }

        $sortlink = array();
        for ($i=0; $i<=4; $i++)
        {
            if ($Navigation->getQueryByPool())
            {
                $sortlink[$i] = append_sid($phpbb_root_path . 'dkp.'.$phpEx, 'page=listraids&amp;o=' . $current_order['uri'][$i] .
                    '&amp;start=' . $start . '&amp;' . URI_DKPSYS . '=' . $Navigation->getDkpsysId() );
            }
            else
            {
                $sortlink[$i] = append_sid($phpbb_root_path  . 'dkp.'.$phpEx, 'page=listraids&amp;o=' . $current_order['uri'][$i] .
                    '&amp;start=' . $start . '&amp;' . URI_DKPSYS . '=All'  );
            }
        }
        // breadcrumbs
        $template->assign_block_vars('dkpnavlinks', array(
            'DKPPAGE' 		=> $user->lang['MENU_RAIDS'],
            'U_DKPPAGE' 	=> $u_list_raids,
        ));


        $template->assign_vars(array(
            'S_SHOWZS' 			=> ($config['bbdkp_zerosum'] == '1') ? true : false,
            'S_SHOWTIME' 		=> ($config['bbdkp_timebased'] == '1') ? true : false,
            'S_SHOWDECAY' 		=> ($config['bbdkp_decay'] == '1') ? true : false,
            'S_EPGP' 			=> $config['bbdkp_epgp'] == '1' ? true: false,

            'O_DATE'  => $sortlink[0],
            'O_POOL'  => $sortlink[1],
            'O_NAME'  => $sortlink[2],
            'O_NOTE'  => $sortlink[3],
            'O_VALUE' => $sortlink[4],

            'U_LIST_RAIDS' => $u_list_raids ,
            'LISTRAIDS_FOOTCOUNT' => sprintf($user->lang['LISTRAIDS_FOOTCOUNT'], $total_raids, $config['bbdkp_user_rlimit']),

            'START' => $start,
            'RAID_PAGINATION' => $pagination,
            'S_DISPLAY_RAIDS' => true

        ));

        // Output page
        page_header($user->lang['RAIDS']);

    }
}
