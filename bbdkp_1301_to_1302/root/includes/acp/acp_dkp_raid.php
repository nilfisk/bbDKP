<?php
/**
 * Raid acp file
 *
 * @package acp\bbdkp
 * @link http://www.bbdkp.com
 * @author Sajaki@gmail.com
 * @copyright 2009 bbdkp
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.3.0
 */

// don't add this file to namespace bbdkp
/**
 * @ignore
 */
if (! defined ( 'IN_PHPBB' ))
{
	exit ();
}
if (! defined('EMED_BBDKP'))
{
	$user->add_lang ( array ('mods/dkp_admin' ));
	trigger_error ( $user->lang['BBDKPDISABLED'] , E_USER_WARNING );
}
if (!class_exists('\bbdkp\admin\Admin'))
{
	require("{$phpbb_root_path}includes/bbdkp/admin/admin.$phpEx");
}

if (!class_exists('\bbdkp\controller\raids\RaidController'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/raids/RaidController.$phpEx");
}
if (!class_exists('\bbdkp\controller\loot\LootController'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/loot/LootController.$phpEx");
}
if (!class_exists('\bbdkp\controller\points\PointsController'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/points/PointsController.$phpEx");
}
if (!class_exists('\bbdkp\controller\members\Members'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/members/Members.$phpEx");
}
//include the guilds class
if (!class_exists('\bbdkp\controller\guilds\Guilds'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/guilds/Guilds.$phpEx");
}
 /**
 *  This ACP class manages Raids
 *  @package acp\bbdkp
 */
 class acp_dkp_raid extends \bbdkp\admin\Admin
{
	/**
	 * url in triggers
	 * @var string
	 */
	private $link;

	/**
	 * instance of Raidcontroller class
	 * @var \bbdkp\Raidcontroller
	 */
	private $RaidController;
	/**
	 * instance of lootcontroller class
	 * @var \bbdkp\controller\loot\Lootcontroller
	 */
	private $LootController;
	/**
	 * instance of PointsController class
	 * @var \bbdkp\controller\points\PointsController
	 */
	private $PointsController;

	/**
	 * main Raid function
	 *
	 * @param int $id
	 * @param string $mode
	 */
	public function main($id, $mode)
	{
		global $user, $template, $config, $phpbb_admin_path, $phpEx;

		$this->link = '<br /><a href="' . append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_raid&amp;mode=listraids" ) . '"><h3>'.$user->lang['RETURN_DKPINDEX'].'</h3></a>';
		$this->RaidController = new \bbdkp\controller\raids\RaidController();
		$this->LootController = new \bbdkp\controller\loot\Lootcontroller();
		$this->PointsController = new \bbdkp\controller\points\PointsController();
		$this->tpl_name = 'dkp/acp_' . $mode;

		switch ($mode)
		{
			case 'addraid' :
				/* newpage */
				$this->page_title = 'ACP_DKP_RAID_ADD';

				$submit = (isset ( $_POST ['add'] )) ? true : false;
				if($submit)
				{
					// add raid to database
					$this->addraid();
				}
				// show add raid form
				$this->loadnewraid();
				break;

			case 'editraid' :
				$this->page_title = 'ACP_DKP_RAID_EDIT';

				$update = (isset ( $_POST ['update'] )) ? true : false;
				$delete = (isset ( $_POST ['delete'] )) ? true : false;
				$addraider = (isset ( $_POST ['addattendee'] )) ? true : false;
				$editraider = (isset ( $_GET ['editraider'] ) ) ? true : false;
				$updateraider = (isset ( $_POST ['editraider'] ) ) ? true : false;
				$deleteraider = (isset ( $_GET ['deleteraider'] )) ? true : false;
				$additem = (isset ( $_POST ['additem'] )) ? true : false;
				$deleteitem = (isset ( $_GET ['deleteitem'] )) ? true : false;
				$decayraid = (isset ( $_POST ['decayraid'] )) ?true : false;
				$raid_id = request_var ( 'hidden_id', 0 );

				/* handle actions */
				if($update)
				{
					//update raid
					$this->updateraid($raid_id);
					$this->displayraid($raid_id);
				}
				elseif($delete)
				{
					//delete the raid
					$this->deleteraid($raid_id);
				}
				elseif($additem)
				{
					//show form for adding items
					redirect(append_sid("{$phpbb_admin_path}index.$phpEx", 'i=dkp_item&amp;mode=additem&amp;' . URI_RAID .'=' . $raid_id));
				}
				elseif($deleteitem)
				{
					$this->deleteitem($raid_id);
				}
				elseif($addraider)
				{
					//adds raider
					$this->addraider($raid_id);
					$this->displayraid($raid_id);
				}
				elseif($editraider || $updateraider)
				{
					//show the form for editing a raider (get params from $get)
					$attendee_id = request_var(URI_NAMEID, 0);
					$raid_id = request_var (URI_RAID, 0);
					$this->editraider($raid_id, $attendee_id);
				}
				elseif($deleteraider)
				{
					//show the form for editing a raider (get params from $get)
					$raid_id = request_var (URI_RAID, 0);
					$attendee_id = request_var(URI_NAMEID, 0);
					$this->deleteraider($raid_id, $attendee_id);
				}
				elseif($decayraid)
				{
					$dkpid = request_var('hidden_dkpid', 0);
					$this->PointsController->decayraid($raid_id, $dkpid);
					$this->displayraid($raid_id);
				}
				else
				{
					// show edit form
					$raid_id = request_var (URI_RAID, 0);
					$this->displayraid($raid_id);
				}
				break;

			case 'listraids' :
				if(count($this->games) == 0)
				{
					trigger_error($user->lang['ERROR_NOGAMES'], E_USER_WARNING);
				}

				$this->page_title = 'ACP_DKP_RAID_LIST';
				$action = request_var ('action', '');
				$raid_id = request_var (URI_RAID, 0);
				switch ($action)
				{
					case 'duplicate':
						$this->duplicateraid($raid_id);
						break;
					case 'delete':
						$this->deleteraid($raid_id);
						break;
				}
				$this->listraids();

				break;
		}

	}

	/**
	 * display new raid creation screen
	 *
	 */
	private function loadnewraid()
	{
		global $user, $config, $template, $phpbb_admin_path, $phpEx ;

		/* dkp pool */
		$dkpsys_id=0;
		if (isset($_GET[URI_DKPSYS]) OR isset ( $_POST[URI_DKPSYS]))
		{
			//user clicked on add raid from event editscreen
			$dkpsys_id = request_var ( URI_DKPSYS, 0 );
		}

		if($dkpsys_id==0)
		{
			//get default dkp pool
			foreach ($this->RaidController->dkpsys as $pool)
			{
				if ($pool['default'] == 'Y' )
				{
					$dkpsys_id = $pool['id'];
					break;
				}
			}

			//if still 0 then get first one
			if($dkpsys_id==0)
			{
				foreach ($this->RaidController->dkpsys as $pool)
				{
					$dkpsys_id = $pool['id'];
					break;
				}
			}
		}

		foreach ($this->RaidController->dkpsys as $pool)
		{
			$template->assign_block_vars ( 'dkpsys_row', array (
				'VALUE' 	=> $pool['id'],
				'SELECTED' 	=> ($pool['id'] == $dkpsys_id) ? ' selected="selected"' : '',
				'OPTION' 	=> (! empty ( $pool['name'] )) ? $pool['name'] : '(None)' )
			);
		}

		$this->RaidController->dkpid = $dkpsys_id;

		//guild dropdown
		$Guild = new \bbdkp\controller\guilds\Guilds();
		$guildlist = $Guild->guildlist(1);
		foreach ( (array) $guildlist as $g )
		{
			if($g['guilddefault'] == 1 )
			{
				$this->RaidController->guildid  = $g['id'];
				$this->RaidController->game_id  = $g['game_id'];
			}

			$template->assign_block_vars('guild_row', array(
					'VALUE' => $g['id'] ,
					'SELECTED' => $g['guilddefault'] == 1 ? ' selected="selected"' : '',
					'OPTION' => $g['name']));
		}

		$this->RaidController->init_newraid();

		$membercount = 0;
		foreach ( (array) $this->RaidController->memberlist as $member )
		{
			$class_colorcode = $member['member_id'] == '' ? '#254689' : $member['member_id'];
			$membercount++;
			$template->assign_block_vars ( 'members_row', array (
				'VALUE' 	=> $member['member_id'],
				'OPTION' 	=> $member['rank_name'] . ' '.  $member['member_name'],
			));
		}

		$eventvalue = 0;
		foreach ($this->RaidController->eventinfo as $event )
		{
			$select_check = false;
			if (isset ($_GET[URI_EVENT]))
			{
				$select_check = ( $event['event_id'] == request_var(URI_EVENT, 0)) ? true : false;
				$eventvalue = $event['event_value'];
			}

			$template->assign_block_vars (
				'events_row', array (
					'VALUE' => $event['event_id'],
					'SELECTED' => ($select_check) ? ' selected="selected"' : '',
					'OPTION' => $event['event_name'] . ' - (' . sprintf ( "%01.2f", $event['event_value'] ) . ')'
			));
		}


		// build presets for raiddate and hour pulldown

		//RAID START DATE
		$now = getdate();
		$s_raid_day_options = '<option value="0">--</option>';
		for ($i = 1; $i < 32; $i++)
		{
			$day = $now['mday'] ;
			$selected = ($i == $day ) ? ' selected="selected"' : '';
			$s_raid_day_options .= "<option value=\"$i\"$selected>$i</option>";
		}

		$s_raid_month_options = '<option value="0">--</option>';
		for ($i = 1; $i < 13; $i++)
		{
			$month = $now['mon'] ;
			$selected = ($i == $month ) ? ' selected="selected"' : '';
			$s_raid_month_options .= "<option value=\"$i\"$selected>$i</option>";
		}

		$s_raid_year_options = '<option value="0">--</option>';
		for ($i = $now['year'] - 10; $i <= $now['year']; $i++)
		{
			$yr = $now['year'] ;
			$selected = ($i == $yr ) ? ' selected="selected"' : '';
			$s_raid_year_options .= "<option value=\"$i\"$selected>$i</option>";
		}

		//raid time
		$s_raid_hh_options = '<option value="0">--</option>';
		for ($i = 0; $i < 24; $i++)
		{
			$hh = $now['hours'] ;
			$selected = ($i == $hh ) ? ' selected="selected"' : '';
			$s_raid_hh_options .= "<option value=\"$i\"$selected>$i</option>";
		}

		$s_raid_mi_options = '<option value="0">--</option>';
		for ($i = 0; $i <= 59; $i++)
		{
			$mi = $now['minutes'] ;
			$selected = ($i == $mi ) ? ' selected="selected"' : '';
			$s_raid_mi_options .= "<option value=\"$i\"$selected>$i</option>";
		}

		$s_raid_s_options = '<option value="0">--</option>';
		for ($i = 0; $i <= 59; $i++)
		{
			$s = $now['seconds'] ;
			$selected = ($i == $s ) ? ' selected="selected"' : '';
			$s_raid_s_options .= "<option value=\"$i\"$selected>$i</option>";
		}

		// RAID END DATE
		//end raid time
		$hourduration = max(0, round( (float) $config['bbdkp_standardduration'],0));
		$minutesduration = max(0, ((float) $config['bbdkp_standardduration'] - floor((float) $config['bbdkp_standardduration'])) * 60 );
		$endtime = mktime(idate("H") + $hourduration, idate("i") + $minutesduration);

		$s_raidend_day_options = '<option value="0">--</option>';
		for ($i = 1; $i < 32; $i++)
		{
			$day = idate('d', $endtime);
			$selected = ($i == $day ) ? ' selected="selected"' : '';
			$s_raidend_day_options .= "<option value=\"$i\"$selected>$i</option>";
		}

		$s_raidend_month_options = '<option value="0">--</option>';
		for ($i = 1; $i < 13; $i++)
		{
			$month = idate('m', $endtime);
			$selected = ($i == $month ) ? ' selected="selected"' : '';
			$s_raidend_month_options .= "<option value=\"$i\"$selected>$i</option>";
		}

		$s_raidend_year_options = '<option value="0">--</option>';
		for ($i = $now['year'] - 10; $i <= $now['year']; $i++)
		{
			$yr = idate('Y', $endtime);
			$selected = ($i == $yr ) ? ' selected="selected"' : '';
			$s_raidend_year_options .= "<option value=\"$i\"$selected>$i</option>";
		}

		$s_raidend_hh_options = '<option value="0">--</option>';
		for ($i = 0; $i < 24; $i++)
		{
			$hh = idate('H', $endtime);
			$selected = ($i == $hh ) ? ' selected="selected"' : '';
			$s_raidend_hh_options .= "<option value=\"$i\"$selected>$i</option>";
		}

		$s_raidend_mi_options = '<option value="0">--</option>';
		for ($i = 0; $i <= 59; $i++)
		{
			$mi = idate('i', $endtime);
			$selected = ($i == $mi ) ? ' selected="selected"' : '';
			$s_raidend_mi_options .= "<option value=\"$i\"$selected>$i</option>";
		}

		$s_raidend_s_options = '<option value="0">--</option>';
		for ($i = 0; $i <= 59; $i++)
		{
			$s = $now['seconds'] ;
			$selected = ($i == $s ) ? ' selected="selected"' : '';
			$s_raidend_s_options .= "<option value=\"$i\"$selected>$i</option>";
		}

		//difference between start & end in seconds
	    $timediff = $endtime - mktime($now['hours'], $now['minutes'], $now['seconds'], $now['mon'], $now['mday'], $now['year']) ;
	    $b = date('r', mktime($now['hours'], $now['minutes'], $now['seconds'], $now['mon'], $now['mday'], $now['year']));
	    $e = date('r', $endtime);

		// express difference in minutes
		$timediff=round($timediff/60, 2) ;
		$time_bonus = 0;
		//if we have a $config interval bigger than 0 minutes then calculate time bonus
		if(	(int) $config['bbdkp_timeunit'] > 0)
		{
			$time_bonus = round($config['bbdkp_dkptimeunit'] * $timediff / $config['bbdkp_timeunit'], 2) ;
		}


		add_form_key('acp_dkp_addraid');

		$template->assign_vars ( array (
				'U_BACK'			=> append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_raid&amp;mode=listraids" ),
				'L_TITLE' 			=> $user->lang ['ACP_ADDRAID'],
				'L_EXPLAIN' 		=> $user->lang ['ACP_ADDRAID_EXPLAIN'],
				'F_ADD_RAID' 		=> append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_raid&amp;mode=addraid" ),
				'U_ADD_EVENT' 		=> append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_event&amp;mode=addevent" ),
				'RAID_VALUE'		=> $eventvalue,

				//raiddate START
				'S_RAIDDATE_DAY_OPTIONS'	=> $s_raid_day_options,
				'S_RAIDDATE_MONTH_OPTIONS'	=> $s_raid_month_options,
				'S_RAIDDATE_YEAR_OPTIONS'	=> $s_raid_year_options,

				//start
				'S_RAIDSTART_H_OPTIONS'		=> $s_raid_hh_options,
				'S_RAIDSTART_MI_OPTIONS'	=> $s_raid_mi_options,
				'S_RAIDSTART_S_OPTIONS'		=> $s_raid_s_options,

				//raiddate END
				'S_RAIDENDDATE_DAY_OPTIONS'	=> $s_raidend_day_options,
				'S_RAIDENDDATE_MONTH_OPTIONS'	=> $s_raidend_month_options,
				'S_RAIDENDDATE_YEAR_OPTIONS'	=> $s_raidend_year_options,

				//end
				'S_RAIDEND_H_OPTIONS'		=> $s_raidend_hh_options,
				'S_RAIDEND_MI_OPTIONS'		=> $s_raidend_mi_options,
				'S_RAIDEND_S_OPTIONS'		=> $s_raidend_s_options,

				'RAID_DURATION' 			=> $config['bbdkp_standardduration'],
				'DKPTIMEUNIT'				=> $config['bbdkp_dkptimeunit'],
				'TIMEUNIT' 					=> $config['bbdkp_timeunit'],
		 		'DKPPERTIME'				=> sprintf($user->lang['DKPPERTIME'], $config['bbdkp_dkptimeunit'], $config['bbdkp_timeunit'] ),
				// Form values
				'RAID_DKPSYSID' 			=> $dkpsys_id,
				'TIME_BONUS'				=> $time_bonus,

			 	'S_SHOWTIME' 	=> ($config['bbdkp_timebased'] == '1') ? true : false,

              	'L_DATE' => $user->lang ['DATE'] . ' dd/mm/yyyy',
				'L_TIME' => $user->lang ['TIME'] . ' hh:mm:ss',

				// Javascript messages
				'MSG_ATTENDEES_EMPTY' => $user->lang ['FV_REQUIRED_ATTENDEES'],
				'MSG_NAME_EMPTY' 	  => $user->lang ['FV_REQUIRED_EVENT_NAME'],
				'LA_ALERT_AJAX' => $user->lang['ALERT_AJAX'] ,
				'LA_ALERT_OLDBROWSER' => $user->lang['ALERT_OLDBROWSER'] ,
				'UA_FINDMEMBERS' => append_sid($phpbb_admin_path . "style/dkp/findmembers.$phpEx") ,
				'UA_FINDEVENTS'  => append_sid($phpbb_admin_path . "style/dkp/findevents.$phpEx") ,

		));
	}

	/**
	 * displays a raid
	 *
	 * @param  int $raid_id the raid to display
	 */
	private function displayraid($raid_id)
	{
		global $user, $config, $template, $phpbb_admin_path, $phpbb_root_path, $phpEx;

		$this->RaidController->displayraid($raid_id);
		$raid = $this->RaidController->raid;

		foreach ($this->RaidController->eventinfo as $event )
		{
			$select_check = ( $event['event_id'] == $raid->event_id) ? true : false;
			$template->assign_block_vars (
					'events_row', array (
							'VALUE' => $event['event_id'],
							'SELECTED' => ($select_check) ? ' selected="selected"' : '',
							'OPTION' => $event['event_name'] . ' - (' . sprintf ( "%01.2f", $event['event_value'] ) . ')'
					));
		}

		$raid_value = 0.00;
		$time_bonus = 0.00;
		$zerosum_bonus = 0.00;
		$raid_decay = 0.00;
		$raid_total = 0.00;
		$countattendees = 0;
		foreach((array) $this->RaidController->raiddetail as $member_id => $raid_detail)
		{
			$template->assign_block_vars ('raids_row', array (
					'U_VIEW_ATTENDEE' => append_sid ("{$phpbb_admin_path}index.$phpEx" , "i=dkp_mdkp&amp;mode=mm_editmemberdkp&amp;" . URI_NAMEID . "={$member_id}&amp;" . URI_DKPSYS. "=" . $raid->event_dkpid),
					'U_EDIT_ATTENDEE' => append_sid ("{$phpbb_admin_path}index.$phpEx", "i=dkp_raid&amp;mode=editraid&amp;editraider=1&amp;". URI_RAID . "=" .$raid_id . "&amp;" . URI_NAMEID . "=" . $member_id),
					'U_DELETE_ATTENDEE' => append_sid ("{$phpbb_admin_path}index.$phpEx", "i=dkp_raid&amp;mode=editraid&amp;deleteraider=1&amp;". URI_RAID . "=" .$raid_id . "&amp;" . URI_NAMEID . "=" . $member_id),
					'NAME' 		 => $raid_detail['member_name'],
					'COLORCODE'  => ($raid_detail['colorcode'] == '') ? '#254689' : $raid_detail['colorcode'],
					'CLASS_IMAGE' 	=> (strlen($raid_detail['imagename']) > 1) ? $phpbb_root_path . "images/bbdkp/class_images/" . $raid_detail['imagename'] . ".png" : '',
					'S_CLASS_IMAGE_EXISTS' => (strlen($raid_detail['imagename']) > 1) ? true : false,
					'RACE_IMAGE' 	=> (strlen($raid_detail['raceimage']) > 1) ? $phpbb_root_path . "images/bbdkp/race_images/" . $raid_detail['raceimage'] . ".png" : '',
					'S_RACE_IMAGE_EXISTS' => (strlen($raid_detail['raceimage']) > 1) ? true : false,
					'CLASS_NAME' => $raid_detail['classname'],
					'RAIDVALUE'  => $raid_detail['raid_value'],
					'TIMEVALUE'  => $raid_detail['time_bonus'],
					'ZSVALUE' 	 => $raid_detail['zerosum_bonus'],
					'DECAYVALUE' => $raid_detail['raid_decay'],
					'TOTAL' 	 => $raid_detail['raid_value'] + $raid_detail['time_bonus']  + $raid_detail['zerosum_bonus'] - $raid_detail['raid_decay'],
			)
			);
			$raid_value += $raid_detail['raid_value'];
			$time_bonus += $raid_detail['time_bonus'];
			$zerosum_bonus += $raid_detail['zerosum_bonus'];
			$raid_decay += $raid_detail['raid_decay'];
			$raid_total = $raid_value + $time_bonus + $zerosum_bonus - $raid_decay;
			$countattendees += 1;
		}

		$s_memberlist_options = '';
		foreach( (array) $this->RaidController->nonattendees as $member_id => $nonattendee)
		{
			$s_memberlist_options .= '<option value="' . $member_id . '"> ' . $nonattendee . '</option>';
		}

		// populate item buyer list

		$number_items = 0;
		$item_value = 0.00;
		$item_decay = 0.00;
		$item_total = 0.00;

		foreach((array) $this->RaidController->lootlist as $item_id => $lootdetail)
		{

			if ($this->bbtips == true && $lootdetail['item_gameid'] == 'wow')
			{
				$item_name = '<strong>' .  $this->bbtips->parse('[itemdkp]' . $lootdetail['item_name']  . '[/itemdkp]') . '</strong>';
			}
			else
			{
				$item_name = '<strong>' .$lootdetail['item_name'] . '</strong>';
			}

			$buyer = new \bbdkp\controller\members\Members($lootdetail['member_id']);

			$template->assign_block_vars ( 'items_row', array (
					'DATE' 			=> (! empty ( $lootdetail ['item_date'] )) ? $user->format_date($lootdetail['item_date']) : '&nbsp;',
					'COLORCODE'  	=> ($buyer->colorcode == '') ? '#8899aa' : $buyer->colorcode,
					'CLASS_IMAGE' 	=> (strlen($buyer->class_image) > 1) ? $buyer->class_image : '',
					'S_CLASS_IMAGE_EXISTS' => (strlen($buyer->class_image ) > 1) ? true : false,
					'RACE_IMAGE' 	=> (strlen($buyer->race_image ) > 1) ? $buyer->race_image : '',
					'S_RACE_IMAGE_EXISTS' => (strlen($buyer->race_image) > 1) ? true : false,
					'BUYER' 		=> $buyer->member_name,
					'ITEMNAME'      => $item_name,
					'ITEM_ID'		=> $lootdetail['item_id'],
				 /* 'ITEM_ZS'      	=> ($lootdetail['item_zs'] == 1) ? ' checked="checked"' : '',  */
					'U_VIEW_BUYER' 	=> append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_raid&amp;mode=editraid&amp;editraider=1&amp;". URI_RAID . "=" . $raid_id . "&amp;" . URI_NAMEID . "=" . $lootdetail['member_id']) ,
					'U_VIEW_ITEM' 	=> append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_item&amp;mode=additem&amp;" . URI_ITEM . "={$lootdetail['item_id']}&amp;" . URI_RAID . "={$raid_id}" ),
					'U_DELETE_ITEM' => append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_raid&amp;mode=editraid&amp;deleteitem=1&amp;" . URI_ITEM . "={$lootdetail['item_id']}&amp;" . URI_DKPSYS. "=" . $raid->event_dkpid . "&amp;" . URI_RAID . "={$raid_id}"),
					'ITEMVALUE' 	=> $lootdetail['item_value'],
					'DECAYVALUE' 	=> $lootdetail['item_decay'],
					'TOTAL' 		=> $lootdetail['item_net'],
			));

			$number_items++;
			$item_value += $lootdetail['item_value'];
			$item_decay += $lootdetail['item_decay'];
			$item_total += $lootdetail['item_net'];
		}

		// build presets for raiddate and hour pulldown
		$now = getdate();
		// raid start

		$s_raid_day_options = '<option value="0">--</option>';
		for ($i = 1; $i < 32; $i++)
		{
			$day = date('j', $raid->raid_start) ;
			$selected = ($i == $day ) ? ' selected="selected"' : '';
			$s_raid_day_options .= "<option value=\"$i\"$selected>$i</option>";
		}

		$s_raid_month_options = '<option value="0">--</option>';
		for ($i = 1; $i < 13; $i++)
		{
			$month = date('n', $raid->raid_start);
			$selected = ($i == $month ) ? ' selected="selected"' : '';
			$s_raid_month_options .= "<option value=\"$i\"$selected>$i</option>";
		}

		$s_raid_year_options = '<option value="0">--</option>';
		for ($i = $now['year'] - 10; $i <= $now['year']; $i++)
		{
			$yr = date('Y',$raid->raid_start) ;
			$selected = ($i == $yr ) ? ' selected="selected"' : '';
			$s_raid_year_options .= "<option value=\"$i\"$selected>$i</option>";
		}

		// raid end
		$s_raidend_day_options = '<option value="0">--</option>';
		for ($i = 1; $i < 32; $i++)
		{
			$day = date('j', $raid->raid_end) ;
			$selected = ($i == $day ) ? ' selected="selected"' : '';
			$s_raidend_day_options .= "<option value=\"$i\"$selected>$i</option>";
		}

		$s_raidend_month_options = '<option value="0">--</option>';
		for ($i = 1; $i < 13; $i++)
		{
			$month = date('n', $raid->raid_end) ;
			$selected = ($i == $month ) ? ' selected="selected"' : '';
			$s_raidend_month_options .= "<option value=\"$i\"$selected>$i</option>";
		}

		$s_raidend_year_options = '<option value="0">--</option>';
		for ($i = $now['year'] - 10; $i <= $now['year']; $i++)
		{
			$yr = date('Y',$raid->raid_end);
			$selected = ($i == $yr ) ? ' selected="selected"' : '';
			$s_raidend_year_options .= "<option value=\"$i\"$selected>$i</option>";
		}


		//start raid time
		$s_raid_hh_options = '<option value="0"	>--</option>';
		for ($i = 0; $i < 24; $i++)
		{
			$hh = date('H', $raid->raid_start) ;
			$selected = ($i == $hh ) ? ' selected="selected"' : '';
			$s_raid_hh_options .= "<option value=\"$i\"$selected>$i</option>";
		}

		$s_raid_mi_options = '<option value="0">--</option>';
		for ($i = 1; $i <= 59; $i++)
		{
			$mi = date('i', $raid->raid_start);
			$selected = ($i == $mi ) ? ' selected="selected"' : '';
			$s_raid_mi_options .= "<option value=\"$i\"$selected>$i</option>";
		}

		$s_raid_s_options = '<option value="0">--</option>';
		for ($i = 1; $i <= 59; $i++)
		{
			$s = date('s',$raid->raid_start) ;
			$selected = ($i == $s ) ? ' selected="selected"' : '';
			$s_raid_s_options .= "<option value=\"$i\"$selected>$i</option>";
		}

		//end raid time
		$s_raidend_hh_options = '<option value="0"	>--</option>';
		for ($i = 0; $i < 24; $i++)
		{
			$hh = date('H', $raid->raid_end) ;
			$selected = ($i == $hh ) ? ' selected="selected"' : '';
			$s_raidend_hh_options .= "<option value=\"$i\"$selected>$i</option>";
		}

		$s_raidend_mi_options = '<option value="0">--</option>';
		for ($i = 1; $i <= 59; $i++)
		{
			$mi = date('i',$raid->raid_end) ;
			$selected = ($i == $mi ) ? ' selected="selected"' : '';
			$s_raidend_mi_options .= "<option value=\"$i\"$selected>$i</option>";
		}

		$s_raidend_s_options = '<option value="0">--</option>';
		for ($i = 1; $i <= 59; $i++)
		{
			$s = date('s',$raid->raid_end) ;
			$selected = ($i == $s ) ? ' selected="selected"' : '';
			$s_raidend_s_options .= "<option value=\"$i\"$selected>$i</option>";
		}


		// add form key
		add_form_key('acp_dkp_addraid');

		//fill template
		$template->assign_vars ( array (
			'U_BACK'			=> append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_raid&amp;mode=listraids" ),
			'L_TITLE' 			=> $user->lang ['ACP_ADDRAID'],
			'F_EDIT_RAID' 		=> append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_raid&amp;mode=editraid&amp;". URI_RAID . "=" .$raid_id ),
			'F_ADDATTENDEE' 	=> append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_raid&amp;mode=editraid&amp;". URI_RAID . "=" .$raid_id ),
			'RAIDTITLE' 		=> sprintf($user->lang['RAIDDESCRIPTION'], $raid_id, $raid->event_name,
							  	 $user->format_date($raid->raid_start)),
			'EVENT_VALUE'		=> $raid->event_value,
			'RAID_VALUE' 		=> $raid_value,
			'INDIVIDUAL_RAID_VALUE' => $raid_value / (($countattendees > 0 ) ? $countattendees :1) ,
			'INDIVIDUAL_TIMEVALUE' 	=> $time_bonus  / (($countattendees > 0 ) ? $countattendees :1),
			'TIMEVALUE' 		=> $time_bonus ,
			'ZSVALUE' 			=> $zerosum_bonus,
			'DECAYVALUE' 		=> $raid_decay,
			'TOTAL'				=> $raid_total,

			'RAID_NOTE' 		=> $raid->raid_note,
			'RAID_ID' 			=> $raid_id,
			'EVENT_DKPID'		=> $raid->event_dkpid,
			'RAID_DKPPOOL' 		=> $raid->dkpsys_name,
			'DKPTIMEUNIT'		=> $config['bbdkp_dkptimeunit'],
			'TIMEUNIT' 			=> $config['bbdkp_timeunit'],
	 		'DKPPERTIME'		=> sprintf($user->lang['DKPPERTIME'], $config['bbdkp_dkptimeunit'], $config['bbdkp_timeunit'] ),
			'ITEM_VALUE'		=> $item_value,
			'ITEMDECAYVALUE'	=> $item_decay,
			'ITEMTOTAL'			=> $item_total,

			'S_MEMBERLIST_OPTIONS'  	=> $s_memberlist_options,

			// raid start day
			'S_RAIDSTARTDATE_DAY_OPTIONS'	=> $s_raid_day_options,
			'S_RAIDSTARTDATE_MONTH_OPTIONS'	=> $s_raid_month_options,
			'S_RAIDSTARTDATE_YEAR_OPTIONS'	=> $s_raid_year_options,

			// raid start day
			'S_RAIDENDDATE_DAY_OPTIONS'		=> $s_raidend_day_options,
			'S_RAIDENDDATE_MONTH_OPTIONS'	=> $s_raidend_month_options,
			'S_RAIDENDDATE_YEAR_OPTIONS'	=> $s_raidend_year_options,

			//start
			'S_RAIDSTART_H_OPTIONS'		=> $s_raid_hh_options,
			'S_RAIDSTART_MI_OPTIONS'	=> $s_raid_mi_options,
			'S_RAIDSTART_S_OPTIONS'		=> $s_raid_s_options,

			//end
			'S_RAIDEND_H_OPTIONS'		=> $s_raidend_hh_options,
			'S_RAIDEND_MI_OPTIONS'		=> $s_raidend_mi_options,
			'S_RAIDEND_S_OPTIONS'		=> $s_raidend_s_options,

			// attendees
			'O_NAME' 			  => $this->RaidController->raiddetailorder['uri'] [0],
			'O_RAIDVALUE' 		  => $this->RaidController->raiddetailorder['uri'] [1],
			'O_TIMEVALUE' 		  => $this->RaidController->raiddetailorder['uri'] [2],
			'O_ZSVALUE' 		  => $this->RaidController->raiddetailorder['uri'] [3],
			'O_DECAYVALUE' 		  => $this->RaidController->raiddetailorder['uri'] [4],
			'O_TOTALVALUE' 		  => $this->RaidController->raiddetailorder['uri'] [5],

			//items
			'O_BUYER' 		  	  => $this->RaidController->lootlistorder['uri'] [0],
			'O_ITEMNAME' 		  => $this->RaidController->lootlistorder['uri'] [1],
			'O_ITEMTOTAL' 		  => $this->RaidController->lootlistorder['uri'] [2],

			'LISTRAIDS_FOOTCOUNT' => sprintf ( $user->lang ['LISTATTENDEES_FOOTCOUNT'], $countattendees),
			'ITEMSFOOTCOUNT' => sprintf ( $user->lang['RAIDITEMS_FOOTCOUNT'], $number_items),

			'L_DATE' => $user->lang ['DATE'] . ' dd/mm/yyyy',
			'L_TIME' => $user->lang ['TIME'] . ' hh:mm:ss',

			'ADDEDBY'	 		=> sprintf ( $user->lang ['ADDED_BY'], $raid->raid_added_by ),
		  	'UPDATEDBY' 		=> ($raid->raid_updated_by  != ' ') ? sprintf ( $user->lang ['UPDATED_BY'], $raid->raid_updated_by ) : '..',

			//switches
			'S_SHOWADDATTENDEE'	=> ($s_memberlist_options == '') ? false: true,
			'S_EDITRAID'		=> true,
			'S_SHOWZS' 			=> ($config['bbdkp_zerosum'] == '1') ? true : false,
			'S_SHOWTIME' 		=> ($config['bbdkp_timebased'] == '1') ? true : false,
			'S_SHOWDECAY' 		=> ($config['bbdkp_decay'] == '1') ? true : false,

			'S_ADDRAIDER'   	=> false,
			'S_SHOWITEMPANE' 	=> ($number_items > 0 ) ? true : false,

			// Javascript messages
			'MSG_ATTENDEES_EMPTY' => $user->lang ['FV_REQUIRED_ATTENDEES'],
			'LA_ALERT_AJAX' => $user->lang['ALERT_AJAX'] ,
			'LA_ALERT_OLDBROWSER' => $user->lang['ALERT_OLDBROWSER'] ,
			'UA_FINDMEMBERS' => append_sid($phpbb_admin_path . "style/dkp/findmembers.$phpEx") ,

			));

	}

	/**
	 * lists all raids
	 *
	 */
	private function listraids()
	{
		global $user, $config, $template, $phpbb_admin_path, $phpEx;

		// add dkpsys button redirect
		$showadd = (isset($_POST['raidadd'])) ? true : false;
        if($showadd)
        {
			redirect(append_sid("{$phpbb_admin_path}index.$phpEx", "i=dkp_raid&amp;mode=addraid"));
        }
        // guild dropdown
        $submit = isset ( $_POST ['member_guild_id'] )  ? true : false;
        $Guild = new \bbdkp\controller\guilds\Guilds();
        $guildlist = $Guild->guildlist(1);

        if($submit)
        {
        	$Guild->guildid = request_var('member_guild_id', 0);
        }
        else
        {
			foreach ($guildlist as $g)
			{
				$Guild->guildid = $g['id'];
	        	$Guild->name = $g['name'];

	        	if ($Guild->guildid == 0 && $Guild->name == 'Guildless' )
	        	{
	        		trigger_error('ERROR_NOGUILD', E_USER_WARNING );
	        	}
	        	break;
	        }
        }

        foreach ($guildlist as $g)
        {
        	$template->assign_block_vars('guild_row', array(
        			'VALUE' => $g['id'] ,
        			'SELECTED' => ($g['id'] == $Guild->guildid) ? ' selected="selected"' : '' ,
        			'OPTION' => (! empty($g['name'])) ? $g['name'] : '(None)'));
        }

        /* dkp pool */
        $dkpsys_id=0;
        if (isset($_GET[URI_DKPSYS]) OR isset ( $_POST[URI_DKPSYS]))
        {
        	//user clicked on add raid from event editscreen
        	$dkpsys_id = request_var ( URI_DKPSYS, 0 );
        }

        if($dkpsys_id==0)
        {
        	if( count((array) $this->RaidController->dkpsys) == 0 )
        	{
        		trigger_error('ERROR_NOPOOLS', E_USER_WARNING );
        	}

        	//get default dkp pool
        	foreach ($this->RaidController->dkpsys as $pool)
        	{
        		if ($pool['default'] == 'Y' )
        		{
        			$dkpsys_id = $pool['id'];
	        		break;
        		}
        	}

        	//if still 0 then get first one
        	if($dkpsys_id==0)
        	{
        		foreach ($this->RaidController->dkpsys as $pool)
        		{
        			$dkpsys_id = $pool['id'];
        			break;
        		}
        	}
        }

        foreach ($this->RaidController->dkpsys as $pool)
        {
        	$selected = ($pool['id'] == $dkpsys_id) ? true: false;
        	$template->assign_block_vars ( 'dkpsys_row', array (
        			'VALUE' 	=> $pool['id'],
        			'SELECTED' 	=> $selected ? ' selected="selected"' : '',
        			'OPTION' 	=> (! empty ( $pool['name'] )) ? $pool['name'] : '(None)' )
        	);
        }

        $this->RaidController->dkpid = $dkpsys_id;
        $this->RaidController->guildid = $Guild->guildid;

		$start = \request_var ( 'start', 0, false );
		$this->RaidController->listraids($this->RaidController->dkpid, $start);

		foreach((array) $this->RaidController->raidlist as $raid_id => $raid)
		{
			$template->assign_block_vars ( 'raids_row', array (
				'ID' 		 =>  $raid['raid_id'],
				'DATE' 		 =>  $raid['date'],
				'NAME' 		 => $raid['event_name'],
				'NOTE' 		 => $raid['note'],
				'RAIDVALUE'  => $raid['raidvalue'],
				'TIMEVALUE'  => $raid['timevalue'],
				'ZSVALUE' 	 => $raid['zsvalue'],
				'DECAYVALUE' => $raid['decayvalue'],
				'TOTAL' 	 => $raid['total'],
				'U_VIEW_RAID' => $raid['viewlink'],
				'U_COPY_RAID' => $raid['copylink'],
				'U_DELETE_RAID' => $raid['deletelink'],
				)
			);
		}

		$template->assign_vars ( array (
			'L_TITLE' 			  => $user->lang ['ACP_LISTRAIDS'],
			'L_EXPLAIN' 		  => $user->lang ['ACP_LISTRAIDS_EXPLAIN'],
			'O_ID' 			  	  => $this->RaidController->raidlistorder['uri'] [0],
			'O_DATE' 			  => $this->RaidController->raidlistorder['uri'] [1],
			'O_NAME' 			  => $this->RaidController->raidlistorder['uri'] [2],
			'O_NOTE' 			  => $this->RaidController->raidlistorder['uri'] [3],
			'O_RAIDVALUE' 		  => $this->RaidController->raidlistorder['uri'] [4],
			'O_TIMEVALUE' 		  => $this->RaidController->raidlistorder['uri'] [5],
			'O_ZSVALUE' 		  => $this->RaidController->raidlistorder['uri'] [6],
			'O_DECAYVALUE' 		  => $this->RaidController->raidlistorder['uri'] [7],
			'O_TOTALVALUE' 		  => $this->RaidController->raidlistorder['uri'] [8],
			'S_SHOWTIME' 		  => ($config['bbdkp_timebased'] == '1') ? true : false,
			'S_SHOWZS' 			  => ($config['bbdkp_zerosum'] == '1') ? true : false,
			'S_SHOWDECAY' 		  => ($config['bbdkp_decay'] == '1') ? true : false,
			'U_LIST_RAIDS' 		  => append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_raid&amp;mode=listraids&amp;dkpsys_id=". $this->RaidController->dkpid ),
			'START' 			  => $start,
			'LISTRAIDS_FOOTCOUNT' => sprintf ( $user->lang ['LISTRAIDS_FOOTCOUNT'], $this->RaidController->totalraidcount, $config ['bbdkp_user_rlimit'] ),
			'RAID_PAGINATION' 	  => generate_pagination ( append_sid
					( "{$phpbb_admin_path}index.$phpEx", "i=dkp_raid&amp;mode=listraids&amp;dkpsys_id=". $this->RaidController->dkpid ."&amp;o=" . $this->RaidController->raidlistorder['uri'] ['current']) ,
					$this->RaidController->totalraidcount,
					$config ['bbdkp_user_rlimit'], $start, true ),
			'ICON_RCOPY'		  => '<img src="' . $phpbb_admin_path . 'images/file_new.gif" alt="' . $user->lang['DUPLICATE_RAID'] . '" title="' . $user->lang['DUPLICATE_RAID'] . '" />',
			));

	}

	/**
	 * adds raid to database
	 *
	 */
	public function addraid()
	{
		global $user, $phpbb_admin_path, $template, $phpEx;
		if (confirm_box ( true ))
		{
			// recall hidden vars
			$raidinfo = array(
					'raid_note' 		=> utf8_normalize_nfc (request_var ( 'hidden_raid_note', ' ', true )),
					'raid_event'		=> utf8_normalize_nfc (request_var ( 'hidden_raid_event', ' ', true )),
					'raid_start' 		=> request_var ('hidden_startraid_date', 0),
					'raid_end'			=> request_var ('hidden_endraid_date', 0),
					'event_id' 			=> request_var ('hidden_event_id', 0),
			);

			$raid_attendees = request_var ('hidden_raid_attendees', array ( 0 => 0 ));

			foreach ($raid_attendees as $member_id)
			{
				$raiddetail[] = array (
					'member_id' => $member_id,
					'raid_value'   => (float) request_var ('hidden_raid_value', 0.00 ),
					'time_bonus'   => (float) request_var ('hidden_raid_timebonus', 0.00 ),
				);
			}

			//fill raid and raiddetail table
			$raid_id = $this->RaidController->add_raid($raidinfo, $raiddetail);
			//fill dkp table
			$this->PointsController->add_points($raid_id);

			//
			// Success message
			//
			$success_message = sprintf ( $user->lang ['ADMIN_ADD_RAID_SUCCESS'],
			$user->format_date($this->time), $raidinfo['raid_event'] ) . '<br />';

			//show message and redirect to raid after 1 second
			$link = append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_raid&amp;mode=editraid&amp;". URI_RAID . "={$raid_id}" );
				meta_refresh(1, $link);
			trigger_error ($success_message, E_USER_NOTICE);

		}
		else
		{
			$this->RaidController = new \bbdkp\controller\raids\RaidController(request_var ( 'dkpsys_id', 0));
			$event_id = request_var ( 'event_id', 0);
			if (($event_id == 0))
			{
				trigger_error ( $user->lang ['ERROR_INVALID_EVENT_PROVIDED'], E_USER_WARNING );
			}

			// store raidinfo as hidden vars
			$this->RaidController->init_newraid();
			$event = $this->RaidController->eventinfo[$event_id];

			$s_hidden_fields = build_hidden_fields(array(
				'event_id'					=> $event_id,
				'hidden_raid_note' 			=> utf8_normalize_nfc ( request_var ( 'raid_note', ' ', true ) ),
				'hidden_raid_event'			=> $event['event_name'],
				'hidden_event_id' 			=> $event_id,
				'hidden_raid_value' 		=> request_var ( 'raid_value', 0.00 ),
				'hidden_raid_timebonus' 	=> request_var ( 'time_bonus', 0.00 ),
				'hidden_startraid_date' 	=> mktime(request_var('sh', 0), request_var('smi', 0), request_var('ss', 0),
					request_var('mo', 0), request_var('d', 0), request_var('Y', 0)),
				'hidden_endraid_date' 		=> mktime(request_var('eh', 0), request_var('emi', 0), request_var('es', 0),
					request_var('emo', 0), request_var('ed', 0), request_var('eY', 0)),
				'hidden_raid_attendees' 	=> request_var ( 'raid_attendees', array ( 0 => 0 )),
				'add'    					=> true,)
				);

			$template->assign_vars ( array ('S_HIDDEN_FIELDS' => $s_hidden_fields ) );

			confirm_box(false, sprintf($user->lang['CONFIRM_CREATE_RAID'], $event['event_name']) , $s_hidden_fields);
		}
	}

	/**
	 * update a raid
	 *
	 * @param int $raid_id the raid to update
	 */
	public function updateraid($raid_id)
	{
		global $user, $phpbb_admin_path, $phpEx;
		if(!check_form_key('acp_dkp_addraid'))
		{
			trigger_error($user->lang['FV_FORMVALIDATION'], E_USER_WARNING);
		}

		$raidinfo = array (
			'raid_id' 	 => $raid_id,
			'event_id' 	 => request_var ('event_id', 0),
			'raid_value' => request_var ('raid_value', 0.00 ),
			'time_bonus' => request_var ('time_bonus', 0.00 ),
			'raid_note'  => utf8_normalize_nfc ( request_var ( 'raid_note', ' ', true ) ),
			'raid_start' => mktime(request_var('sh', 0), request_var('smi', 0), request_var('ss', 0), request_var('mo', 0), request_var('d', 0), request_var('Y', 0)),
			'raid_end' 	 => mktime(request_var('eh', 0), request_var('emi', 0), request_var('es', 0), request_var('emo', 0), request_var('ed', 0), request_var('eY', 0)),
		);

		$this->RaidController->update_raid($raidinfo);

		$success_message = sprintf ( $user->lang ['ADMIN_UPDATE_RAID_SUCCESS'], request_var ( 'mo', ' ' ), request_var ( 'd', ' ' ),
				request_var ( 'Y', ' ' ), request_var ( 'event_id', 0 ));
		$this->link = append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_raid&amp;mode=editraid&amp;". URI_RAID . "={$raid_id}" );
		meta_refresh(1, $this->link );
		trigger_error ( $success_message . $this->link, E_USER_NOTICE );
	}

	/**
	 *
	 * duplicate a raid
	 *
	 * @param int $raid_id
	 */
	private function duplicateraid($raid_id)
	{
		global $db, $user, $config, $template, $phpbb_admin_path, $phpEx;
		if (confirm_box ( true ))
		{
			$raid_id = request_var('raid_id', 0);

			$new_id = $this->RaidController->duplicateraid($raid_id);
            //fill dkp table
            $this->PointsController->add_points($new_id);

			$this->RaidController->displayraid($new_id);
			$success_message = sprintf ($user->lang ['ADMIN_DUPLICATE_RAID_SUCCESS'],
			$user->format_date($this->RaidController->raid->raid_start), $this->RaidController->raid->event_name ) . '<br />';

			meta_refresh(1, append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_raid&amp;mode=listraids&amp;dkpsys_id=". $this->RaidController->raid->event_dkpid ));
			trigger_error($success_message);

		}
		else
		{
			$this->RaidController->displayraid($raid_id);

			$s_hidden_fields = build_hidden_fields ( array (
					'delete' 			=> true,
					'raid_id'			=> $raid_id,
			));
			$template->assign_vars ( array ('S_HIDDEN_FIELDS' => $s_hidden_fields ) );

			confirm_box ( false, sprintf($user->lang ['CONFIRM_DUPLICATE_RAID'],
			$raid_id , $this->RaidController->raid->event_name), $s_hidden_fields );


		}

	}


	/**
	 *
	 * delete a raid
	 *
	 * @param int $raid_id
	 */
	private function deleteraid($raid_id)
	{
		global $db, $user, $config, $template, $phpEx;
		if (confirm_box ( true ))
		{
			$raid_id = request_var('raid_id', 0);
			// accounting
			$this->PointsController->removeraid_delete_dkprecord($raid_id);
			//carry on with fact tables
			$this->RaidController->delete_raid($raid_id);
			$this->LootController->delete_raid($raid_id);

			$success_message = $user->lang ['ADMIN_DELETE_RAID_SUCCESS'];
			trigger_error ( $success_message . $this->link, E_USER_NOTICE );
		}
		else
		{

			$this->RaidController->displayraid($raid_id);

			$s_hidden_fields = build_hidden_fields ( array (
				'delete' 			=> true,
				'raid_id'			=> $raid_id,
				));
			$template->assign_vars ( array ('S_HIDDEN_FIELDS' => $s_hidden_fields ) );
			confirm_box ( false, sprintf($user->lang ['CONFIRM_DELETE_RAID'],
				$raid_id ,
				$this->RaidController->raid->event_name,
				date ( $config['bbdkp_date_format'], $this->RaidController->raid->raid_start )), $s_hidden_fields );


		}

	}

	/**
	 *
	 * this function adds attendee
	 * @param int $raid_id
	 * @return boolean
	 */
	private function addraider($raid_id)
	{
        global $user;
		if(!check_form_key('acp_dkp_addraid'))
		{
			trigger_error($user->lang['FV_FORMVALIDATION'], E_USER_WARNING);
		}
        $raid_value = request_var('raid_value', 0.00);
        $time_bonus = request_var('time_bonus', 0.00);
		$dkpid = request_var('hidden_dkpid', 0);
		$member_id =  request_var('attendee_id', 0);
		$raid_start = mktime(request_var('sh', 0), request_var('smi', 0), request_var('ss', 0), request_var('mo', 0), request_var('d', 0), request_var('Y', 0));
		$this->RaidController->addraider($raid_id, $raid_value, $time_bonus, $dkpid, $member_id, $raid_start);
		$this->PointsController->add_points($raid_id,$member_id);

		return true;
	}


	/**
	 *
	 * this function deletes 1 attendee from a raid
	 * dkp account is then updated
	 *
	 * @param int $raid_id
	 * @param int $attendee_id
	 * @return boolean
	 */
	private function deleteraider($raid_id, $attendee_id)
	{
		global $user, $config, $template, $phpbb_admin_path, $phpEx;
		$link = '<br /><a href="' . append_sid ("{$phpbb_admin_path}index.$phpEx", "i=dkp_raid&amp;mode=editraid&amp;". URI_RAID . "=" .$raid_id) . '"><h3>'.$user->lang['RETURN_RAID'].'</h3></a>';

		if (confirm_box(true))
		{
			//recall vars
			$raid_id = request_var('raid_idx', 0);
			$member_id = request_var('attendee', 0);
			$this->PointsController->removeraid_delete_dkprecord($raid_id, $member_id);
			//carry on with fact tables
			$this->RaidController->deleteraider($raid_id, $member_id);

			trigger_error( sprintf( $user->lang['ADMIN_RAID_ATTENDEE_DELETED_SUCCESS'],  utf8_normalize_nfc(request_var('attendeename', '', true)) , $raid_id) . $link, E_USER_WARNING);
			//update fact tables
			// do accounting
		}
		else
		{
			if($this->LootController->Countloot($raid_id, $attendee_id) > 0 )
			{
				trigger_error( sprintf( $user->lang['ADMIN_RAID_ATTENDEE_DELETED_FAILED'],  utf8_normalize_nfc(request_var('attendeename', '', true)) , $raid_id) . $link, E_USER_WARNING);
			}

			$attendee = new \bbdkp\controller\members\Members($attendee_id);

			$s_hidden_fields = build_hidden_fields(array(
				'deleteraider'	=> true,
				'raid_idx'		=> $raid_id,
				'attendee'		=> $attendee_id,
				'attendeename'	=> $attendee->member_name,
				)
			);
			$template->assign_vars ( array ('S_HIDDEN_FIELDS' => $s_hidden_fields ) );
			confirm_box(false, sprintf($user->lang['CONFIRM_DELETE_ATTENDEE'], $attendee->member_name, $raid_id), $s_hidden_fields);
		}
		return true;
	}


	/**
	 * Deletes one item
	 * called from raid acp item list (red button)
	 *
	 */
	private function deleteitem()
	{
		global $user, $template, $phpbb_admin_path, $phpEx;

		if (confirm_box ( true ))
		{
			//retrieve info
			$raid_id = request_var('raid_id', 0);
			$item_id = request_var('item_id', 0);
			$this->LootController->deleteloot($item_id);

			$this->link = append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_raid&amp;mode=editraid&amp;". URI_RAID . "={$raid_id}" );
			meta_refresh(1, $this->link );

		}
		else
		{
			$raid_id = request_var ( URI_RAID, 0);
			$dkpid = request_var ( URI_DKPSYS, 0);
			$item_id = request_var( URI_ITEM, 0);
			if($item_id==0)
			{
				trigger_error ( $user->lang ['ERROR_INVALID_ITEM_PROVIDED'] , E_USER_WARNING);
			}

			$loot = (array) $this->LootController->Getloot($item_id);
			$s_hidden_fields = build_hidden_fields ( array (
					'raid_id'		  => $raid_id,
					'deleteitem' 	  => true,
					'item_id' 		  => $item_id,
			));
			$template->assign_vars ( array ('S_HIDDEN_FIELDS' => $s_hidden_fields ) );
			confirm_box ( false, sprintf($user->lang ['CONFIRM_DELETE_ITEM'], $loot['item_name'], $loot['member_name'] ), $s_hidden_fields );
		}

	}

	/**
	 * this function shows raider editform
	 *
	 * @param int $raid_id raid to edit
	 * @param int $attendee_id  raider to edit
	 * @return boolean
	 */
	private function editraider($raid_id, $attendee_id)
	{
		global $db, $user, $config, $template, $phpbb_admin_path, $phpEx;
		if (isset ( $_POST ['editraider'] ) )
		{
			// update his raid record
			$attendee_id = request_var('hidden_memberid', 0);
			$raid_id = request_var ('hidden_raidid', 0);
			$dkpid = request_var ('hidden_dkpid', 0);

			$old_raid_value = request_var('old_raid_value', 0.00);
			$old_time_bonus = request_var('old_time_bonus', 0.00);
			$old_zerosum_bonus = request_var('old_zerosum_bonus', 0.00);
			$old_raid_decay = request_var('old_raid_decay', 0.00);

			$raid_value = request_var('raid_value', 0.00);
			$time_bonus = request_var('time_bonus', 0.00);
			$zerosum_bonus = request_var('zerosum_bonus', 0.00);
			$raid_decay = request_var('raid_decay', 0.00);

			$d_raid_value = $old_raid_value - $raid_value;
			$d_time_bonus = $old_time_bonus - $time_bonus;
			$d_zerosum_bonus = $old_zerosum_bonus - $zerosum_bonus;
			$d_tot = $d_raid_value + $d_time_bonus + $d_zerosum_bonus;
			$d_raid_decay = $old_raid_decay - $raid_decay;

			$query = $db->sql_build_array ( 'UPDATE', array (
				'raid_value' 		=> $raid_value,
				'time_bonus' 		=> $time_bonus,
				'zerosum_bonus' 	=> $zerosum_bonus,
				'raid_decay' 		=> $raid_decay
			));

			$db->sql_transaction('begin');

			$db->sql_query ( 'UPDATE ' . RAID_DETAIL_TABLE . ' SET ' . $query . " WHERE raid_id = " . ( int ) $raid_id . ' and member_id = ' . (int) $attendee_id );

			// update his dkp account
            $sql  = 'UPDATE ' . MEMBER_DKP_TABLE . '
	         SET member_raid_value = member_raid_value + ' .  (string) $d_raid_value. ',
	         member_time_bonus = member_time_bonus + ' . (string)  $d_time_bonus . ', member_zerosum_bonus = member_zerosum_bonus + ' . (string)  $d_zerosum_bonus . ',
	         member_earned = member_earned + ' . (string) $d_tot . ' , member_raid_decay = member_raid_decay + ' . (string) $d_raid_decay  . '
	         WHERE member_dkpid = ' . (string)  $dkpid . ' AND member_id = ' . (string) $attendee_id;

            $db->sql_query($sql);
			$db->sql_transaction('commit');
			$this->displayraid($raid_id);
			return true;
		}

		$sql_array = array(
	    'SELECT'    => 	' e.event_dkpid, e.event_name,r.raid_start, ra.member_id, l.member_name, ra.raid_value, ra.time_bonus, ra.zerosum_bonus, ra.raid_decay ',
	    'FROM'      => array(
				MEMBER_LIST_TABLE 	=> 'l',
				RAID_DETAIL_TABLE   => 'ra',
				RAIDS_TABLE   		=> 'r',
				EVENTS_TABLE   		=> 'e',
					),
		'WHERE'		=> ' e.event_id = e.event_id and r.raid_id = ra.raid_id and l.member_id = ra.member_id and ra.raid_id = ' . (int) $raid_id . ' and  ra.member_id = ' . (int) $attendee_id,
	 );

	    $sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query($sql);
		while ( $row = $db->sql_fetchrow($result) )
		{
			$event_dkpid = $row['event_dkpid'];
			$member_name=$row['member_name'];
			$raid_value=$row['raid_value'];
			$time_bonus=$row['time_bonus'];
			$zerosum_bonus=$row['zerosum_bonus'];
			$raid_decay=$row['raid_decay'];
			$eventname=$row['event_name'];
			$raidstart=$row['raid_start'];
		}
		$db->sql_freeresult($result);

		$template->assign_vars ( array (
			'U_BACK'			=> append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_raid&amp;mode=editraid&amp;". URI_RAID . "=" .$raid_id ),
			'EVENT_DKPID'		=> $event_dkpid,
			'MEMBERID'			=> $attendee_id,
			'MEMBERNAME'		=> $member_name,
			'RAID_VALUE' 		=> $raid_value,
			'TIMEVALUE' 		=> $time_bonus,
			'ZEROSUMSVALUE' 	=> $zerosum_bonus,
			'DECAYVALUE' 		=> $raid_decay,
			'TOTAL'				=> $raid_value+$time_bonus+$zerosum_bonus-$raid_decay,
			'RAID_ID' 			=> $raid_id,
			'RAIDTITLE' 		=> sprintf($user->lang['RAIDERDESCRIPTION'], $raid_id, $eventname, $user->format_date($raidstart), $member_name),
			'S_EDITRAIDER'   	=> true,
			'S_SHOWZS'			=> ($config['bbdkp_zerosum'] == '1') ? true : false,
		));

		return true;
	}









}

?>
