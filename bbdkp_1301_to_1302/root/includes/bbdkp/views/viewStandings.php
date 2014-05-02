<?php
/**
 * standings module
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

if (!class_exists('\bbdkp\controller\points\PointsController'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/points/PointsController.$phpEx");
}

class viewStandings implements iViews
{

    function __construct(viewNavigation $Navigation)
    {
        $this->buildpage($Navigation);
    }

    public function buildpage(viewNavigation $Navigation)
    {
        global $config, $phpbb_root_path, $phpEx, $user, $template;

        $memberpoints = new \bbdkp\controller\points\PointsController;

        $startd = request_var ( 'startdkp', 0 );
        $classes = array ();

        $memberarray = $memberpoints->get_standings($Navigation->getGuildId(), $Navigation->getDkpsysId(), $Navigation->getGameId(), $startd, $Navigation->getShowAll(),
            $Navigation->getQueryByArmor(), $Navigation->getQueryByClass(), $Navigation->getFilter(), $Navigation->getQueryByPool());

        // loop sorted member array and dump to template
        foreach ( $memberarray as $key => $member )
        {
            $classes [] = $member['class_id'];
        }

        $classes = array_unique($classes);

        //leaderboard
        foreach ($Navigation->getClassarray() as $k => $class)
        {
            if(in_array( $class['class_id'], $classes))
            {
                $template->assign_block_vars ( 'class',
                    array (
                        'CLASSNAME' 	=> $class ['class_name'],
                        'CLASSIMGPATH'	=> (strlen($class['imagename']) > 1) ? $class['imagename'] . ".png" : '',
                        'COLORCODE' 	=> $class['colorcode']
                    )
                );

                foreach ($memberarray as $member)
                {
                    if($member['class_id'] == $class['class_id'] && $member['game_id'] == $class['game_id'])
                    {
                        //dkp data per class
                        $dkprowarray= array (
                            'NAME' => ($member ['member_status'] == '0') ? '<em>' . $member ['member_name'] . '</em>' : $member ['member_name'] ,
                            'CURRENT' => $member ['member_current'],
                            'DKPCOLOUR' => ($member ['member_current'] >= 0) ? 'positive' : 'negative',
                            'U_VIEW_MEMBER' => append_sid ( "{$phpbb_root_path}dkp.$phpEx", 'page=member&amp;'.
                                URI_NAMEID . '=' . $member ['member_id'] . '&amp;' .
                                URI_DKPSYS . '=' . $Navigation->getDkpsysId() ) );

                        if($config['bbdkp_epgp'] == 1)
                        {
                            $dkprowarray[ 'PR'] = $member ['pr'] ;
                        }

                        $template->assign_block_vars ( 'class.dkp_row', $dkprowarray );
                    }
                }
            }
        }


        // loop sorted member array and dump to template
        foreach ( $memberarray as $key => $member )
        {
            $u_rank_search = append_sid ( "{$phpbb_root_path}dkp.$phpEx" , 'page=standings&amp;rank=' . urlencode ( $member ['rank_name'] ) .'&amp;guild_id=' . $Navigation->getGuildId()) ;

            // append inactive switch
            $u_rank_search .= (($config ['bbdkp_hide_inactive'] == 1) && (! $Navigation->getShowAll())) ? '&amp;show=' : '&amp;show=all';

            // append armor or class filter
            $u_rank_search .= ($Navigation->getFilter() != $user->lang['ALL']) ? '&amp;filter=' . $Navigation->getFilter() : '';

            $templatearray = array (
                'COLORCODE' 	=> $member ['colorcode'],
                'CLASS_IMAGE' 	=> $member['class_image'],
                'S_CLASS_IMAGE_EXISTS' =>  $member['class_image_exists'],
                'RACE_IMAGE' 	=> $member['race_image'],
                'S_RACE_IMAGE_EXISTS' =>  $member['race_image_exists'],
                'DKPCOLOUR1' 	=> ($member ['member_adjustment'] >= 0) ? 'positive' : 'negative',
                'DKPCOLOUR2' 	=> ($member ['member_current'] >= 0) ? 'positive' : 'negative',
                'ID' 			=> $member ['member_id'],
                'COUNT' 		=> $member ['count'],
                'NAME' 			=> $member ['rank_prefix'] . (($member ['member_status'] == '0') ?
                        '<em>' . $member ['member_name'] . '</em>' :
                        $member ['member_name']) . $member ['rank_suffix'],
                'RANK_NAME' => $member ['rank_name'],
                'RANK_HIDE' => $member ['rank_hide'],
                'RANK_SEARCH' => $u_rank_search,
                'LEVEL' => ($member ['member_level'] > 0) ? $member ['member_level'] : '&nbsp;',
                'CLASS' => (! empty ( $member ['member_class'] )) ? $member ['member_class'] : '&nbsp;',
                'ARMOR' => (! empty ( $member ['armor_type'] )) ? $member ['armor_type'] : '&nbsp;',
                'EARNED' => $member ['member_earned'],
                'ADJUSTMENT' => $member ['member_adjustment'],
                'SPENT' => $member ['member_spent'],
                'CURRENT' => $member ['member_current'],
                'LASTRAID' => (! empty ( $member ['member_lastraid'] )) ?
                        date ( 'd.m.y', $member ['member_lastraid'] ) :
                        '&nbsp;',
                'RAIDS_P1_DAYS' => $member ['attendanceP1'],
                'U_VIEW_MEMBER' => append_sid ( "{$phpbb_root_path}dkp.$phpEx",
                    'page=member' .
                    '&amp;' . URI_NAMEID . '=' . $member ['member_id'] .
                    '&amp;' . URI_DKPSYS . '=' . $Navigation->getDkpsysId()),
            );

            if($config['bbdkp_timebased'] == 1)
            {
                $templatearray['TIMEBONUS'] = $member ['member_time_bonus'];
            }
            if($config['bbdkp_zerosum'] == 1)
            {
                $templatearray['ZEROSUM'] = $member ['member_zerosum_bonus'];
            }

            if($config['bbdkp_decay'] == 1)
            {
                $templatearray['RAIDDECAY'] = $member ['member_raid_decay'];
                $templatearray['ITEMDECAY'] = $member ['member_item_decay'];
            }

            if($config['bbdkp_epgp'] == 1)
            {
                $templatearray['EP'] = $member ['ep'];
                $templatearray['GP'] = $member ['gp'];
                $templatearray['PR'] = $member ['pr'];
            }

            $template->assign_block_vars ( 'members_row', $templatearray);

        }


        // Added to the end of the sort links
        $uri_addon = '';
        $uri_addon .= '&amp;filter=' . urlencode ( $Navigation->getFilter() );
        $uri_addon .= (isset ( $_GET ['show'] )) ? '&amp;show=' . request_var ( 'show', '' ) : '';

        /* sorting links */
        $sortlink = array ();
        for($i = 1; $i <= 20; $i ++)
        {
            if (isset ( $sortorder ) && $sortorder == $i)
            {
                $j = - $i;
            }
            else
            {
                $j = $i;
            }

            {
                if ($Navigation->getQueryByPool())
                {
                    $sortlink [$i] = append_sid ( "{$phpbb_root_path}dkp.$phpEx", 'page=standings&amp;' . URI_ORDER. '=' . $j . $uri_addon .
                        '&amp;' . URI_DKPSYS . '=' . $Navigation->getDkpsysId() . '&amp;guild_id=' . $Navigation->getGuildId() );
                }
                else
                {
                    $sortlink [$i] = append_sid ( "{$phpbb_root_path}dkp.$phpEx", 'page=standings&amp;' . URI_ORDER. '=' . $j . $uri_addon .
                        '&amp;' . URI_DKPSYS . '=' . $user->lang['ALL'] . '&amp;guild_id=' . $Navigation->getGuildId());
                }
            }

        }

        $allmember_count = 0;
        // calculate pagination
        $sortorder = request_var ( URI_ORDER, 0 );

        $arg='';
        if ($Navigation->getQueryByPool())
        {
            $arg = '&amp;' . URI_DKPSYS. '=' . $Navigation->getDkpsysId();
        }

        if($Navigation->getFilter() != '')
        {
            $arg .= '&amp;filter=' . $Navigation->getFilter();
        }
        else
        {
            $arg .= '&amp;filter=all';
        }
        $u_listmembers = append_sid ( "{$phpbb_root_path}dkp.$phpEx", 'page=standings' . $arg . '&amp;guild_id=' . $Navigation->getGuildId() );

        $dkppagination = $Navigation->generate_pagination2($u_listmembers . '&amp;o=' . $sortorder ,
            $allmember_count , $config ['bbdkp_user_llimit'], $startd, true, 'startdkp'  );

        // footcount link
        if (($config ['bbdkp_hide_inactive'] == 1) && (! $Navigation->getShowAll()))
        {
            $flink = '<a href="' . append_sid ( "{$phpbb_root_path}dkp.$phpEx",
                    'page=standings' .
                    '&amp;' . URI_ORDER . '=' . $j . '&amp;show=' . $user->lang['ALL'] . '&amp;guild_id=' . $Navigation->getGuildId() .
                    '&amp;' . URI_DKPSYS . '=' . $Navigation->getDkpsysId() ) . '" class="rowfoot">';
            $footcount_text = sprintf ( $user->lang ['LISTMEMBERS_ACTIVE_FOOTCOUNT'], count($memberarray) , $flink );
        }
        else
        {
            $footcount_text = sprintf ( $user->lang ['LISTMEMBERS_FOOTCOUNT'], count($memberarray) );
        }

        $template->assign_vars ( array (
            'S_SHOWLEAD' => true,
            'F_MEMBERS' => $u_listmembers,
            'O_NAME' => $sortlink [1],
            'O_RANK' => $sortlink [2],
            'O_LEVEL' => $sortlink [3],
            'O_CLASS' => $sortlink [4],
            'O_ARMOR' => $sortlink [5],
            'O_MEMBER_RAID_VALUE' => $sortlink [6],
            'O_EARNED' => $sortlink [9],
            'O_ADJUSTMENT' => $sortlink [10],
            'O_SPENT' => $sortlink [13],
            'O_CURRENT' => $sortlink [17],
            'O_LASTRAID' => $sortlink [18],
            'O_RAIDS_P1_DAYS' => $sortlink [19],
            'O_RAIDS_P2_DAYS' => $sortlink [20],
            'RAIDS_P1_DAYS' => sprintf ( $user->lang ['RAIDS_X_DAYS'], $config ['bbdkp_list_p1'] ),
            'S_SHOWZS' 		=> ($config['bbdkp_zerosum'] == '1') ? true : false,
            'S_SHOWDECAY' 	=> ($config['bbdkp_decay'] == '1') ? true : false,
            'S_SHOWEPGP' 	=> ($config['bbdkp_epgp'] == '1') ? true : false,
            'S_SHOWTIME' 	=> ($config['bbdkp_timebased'] == '1') ? true : false,
            'S_QUERYBYPOOL' => $Navigation->getQueryByPool(),
            'S_DISPLAY_STANDINGS' => true,
            'DKPPAGINATION' 	=> $dkppagination ,
            'FOOTCOUNT' => (isset ( $_POST ['compare'] )) ?
                    sprintf ( $footcount_text, sizeof (request_var ( 'compare_ids', array ('' => 0 )))) :
                    $footcount_text,
        ));


        if($config['bbdkp_timebased'] == 1)
        {
            $template->assign_var('O_DKP_HOUR', $sortlink [7]);

        }

        if($config['bbdkp_zerosum'] == 1)
        {
            $template->assign_var('O_ZEROSUM_BONUS', $sortlink [8]);
        }

        if($config['bbdkp_decay'] == 1)
        {
            $template->assign_vars ( array (
                'O_EPDECAY' => $sortlink [11],
                'O_ITEMDECAY' => $sortlink [14]
            ));
        }

        if($config['bbdkp_epgp'] == 1)
        {
            $template->assign_vars ( array (
                'O_EP' => $sortlink [12],
                'O_GP' => $sortlink [15],
                'O_PR' => $sortlink [16],
            ));

        }

        // Output page
        page_header ( $user->lang ['LISTMEMBERS_TITLE'] );

    }
}
