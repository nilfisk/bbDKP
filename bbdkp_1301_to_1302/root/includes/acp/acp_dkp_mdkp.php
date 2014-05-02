<?php
/**
 * dkp ACP file
 *
 * @package bbdkp
 * @link http://www.bbdkp.com
 * @author Sajaki@gmail.com
 * @copyright 2009 bbdkp
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.2.8
 */
// don't add this file to namespace bbdkp
/**
 * @ignore
 */
if (! defined ('IN_PHPBB'))
{
	exit ();
}

// don't add this file to namespace bbdkp
if (! defined ( 'EMED_BBDKP' ))
{
	$user->add_lang ( array ('mods/dkp_admin'));
	trigger_error ( $user->lang ['BBDKPDISABLED'], E_USER_WARNING );
}
if (!class_exists('\bbdkp\admin\Admin'))
{
	require("{$phpbb_root_path}includes/bbdkp/admin/admin.$phpEx");
}
if (!class_exists('\bbdkp\controller\points\PointsController'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/points/PointsController.$phpEx");
}
//include the guilds class
if (!class_exists('\bbdkp\controller\guilds\Guilds'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/guilds/Guilds.$phpEx");
}

/**
 * This class manages member DKP
 *
 *   @package bbdkp
 */
class acp_dkp_mdkp extends \bbdkp\admin\Admin
{

	/**
	 * trigger url
	 * @var string
	 */
	private $link;

	/**
	 * instance of PointsController class
	 * @var \bbdkp\controller\points\PointsController
	 */
	private $PointsController;

	/**
	 * pool id
	 * @var int
	 */
	public $dkpsys_id;

	/**
	 * Main acp function
	 * @param int $id
	 * @param string $mode
	 */
	public function main($id, $mode)
	{
		global $db, $user, $auth, $template, $sid, $cache;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$this->PointsController = new \bbdkp\controller\points\PointsController();
		$this->link = '<br /><a href="' . append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_mdkp&mode=mm_listmemberdkp" ) . '"><h3>' . $user->lang ['RETURN_DKPINDEX'] . '</h3></a>';

		switch ($mode)
		{
			case 'mm_listmemberdkp':

				if(count($this->games) == 0)
				{
					trigger_error($user->lang['ERROR_NOGAMES'], E_USER_WARNING);
				}

				$this->list_memberdkp();

				$this->page_title = 'ACP_DKP_LISTMEMBERDKP';
				if ($config ['bbdkp_epgp'] == '1')
				{
					$this->tpl_name = 'dkp/acp_mm_listmemberepgp';
				}
				else
				{
					$this->tpl_name = 'dkp/acp_mm_listmemberdkp';
				}
				break;

			case 'mm_editmemberdkp' :
				// invisible module

				$member_id = request_var ( URI_NAMEID, 0 );
				$dkp_id = request_var ( URI_DKPSYS, 0 );

				$update = (isset ( $_POST ['update'] )) ? true : false;
				if ($update)
				{
					if (! check_form_key ( 'mm_editmemberdkp' ))
					{
						trigger_error ( 'FORM_INVALID' );
					}

					$member_id = request_var ( 'hidden_id', 0 );
					$dkp_id = request_var ( 'hidden_dkpid', 0 );
					$this->PointsController->dkpsys_id = $dkp_id;
					$this->PointsController->update_dkpaccount($member_id);
				}

				$delete = (isset ( $_POST ['delete'] )) ? true : false;
				if ($delete)
				{
					if (confirm_box (true))
					{
						$this->PointsController->dkpsys_id = request_var ( 'hidden_dkpid', 0 );
						$this->PointsController->delete_dkpaccount(request_var ('hidden_id', 0 ));

					}
					else
					{
						// Include the member class
						if (!class_exists('\bbdkp\controller\members\Members'))
						{
							require("{$phpbb_root_path}includes/bbdkp/controller/members/Members.$phpEx");
						}
						$member= new \bbdkp\controller\members\Members(request_var ('hidden_id', 0));

						$s_hidden_fields = build_hidden_fields ( array (
							'delete' => true,
							'hidden_id' => request_var ('hidden_id', 0),
							'hidden_dkpid' => request_var ('hidden_dkpid',0)
								));
						confirm_box ( false, sprintf($user->lang ['CONFIRM_DELETE_MEMBERDKP'] , $member->member_name  ), $s_hidden_fields );
					}

				}

				/* template filling */

				// Get their correct earned
				$sql_array = array (
					'SELECT' => 'sum(ra.raid_value) AS raid_value, sum(ra.time_bonus) AS time_bonus,
				    		sum(ra.zerosum_bonus) AS zerosum_bonus, sum(ra.raid_decay) AS raid_decay   ',
				    'FROM' => array (
						EVENTS_TABLE => 'e',
						RAIDS_TABLE => 'r',
						RAID_DETAIL_TABLE => 'ra' ),
					'WHERE' => ' ra.raid_id = r.raid_id
				    	and e.event_id = r.event_id
						and ra.member_id=' . $member_id . '
						and e.event_dkpid=' . (int) $dkp_id );

				$sql = $db->sql_build_query ( 'SELECT', $sql_array );
				$result = $db->sql_query ( $sql );
				while ( $row = $db->sql_fetchrow ( $result ) )
				{
					$correct_raid_value = $row ['raid_value'];
					$correct_time_bonus = $row ['time_bonus'];
					$correct_zerosum_bonus = $row ['zerosum_bonus'];
					$correct_raid_decay = $row ['raid_decay'];
				}
				$db->sql_freeresult ( $sql );

				// Get their correct spent

				$sql_array = array (
					'SELECT' => 'SUM(i.item_value) AS item_value,
							SUM(i.item_decay) AS item_decay  ',
					'FROM' => array (
						EVENTS_TABLE => 'e',
						RAIDS_TABLE => 'r',
						RAID_ITEMS_TABLE => 'i' ),
					'WHERE' => 'e.event_id = r.event_id
	    				and r.raid_id = i.raid_id
						and i.member_id=' . $member_id . '
						and e.event_dkpid=' . ( int ) $dkp_id );
				$sql = $db->sql_build_query ( 'SELECT', $sql_array );
				$result = $db->sql_query ( $sql );
				while ( $row = $db->sql_fetchrow ( $result ) )
				{
					$correct_spent = $row ['item_value'];
					$correct_itemdecay = $row ['item_decay'];
				}
				$db->sql_freeresult ( $sql );

				// get Actual dkp points from account

				$sql_array = array (
					'SELECT' => '
				    	a.*,
						m.member_id,
						m.member_dkpid,
						m.member_raid_value,
						m.member_time_bonus,
						m.member_zerosum_bonus,
						m.member_earned,
						m.member_raid_decay,
						m.member_adjustment,
						(m.member_earned + m.member_adjustment - m.adj_decay) AS ep	,
						m.member_spent,
						m.member_item_decay,
						(m.member_spent - m.member_item_decay  + ' . max ( 0, $config ['bbdkp_basegp'] ) . ' ) AS gp,
						(m.member_earned + m.member_adjustment - m.member_spent + m.member_item_decay - m.adj_decay ) AS member_current,
						case when (m.member_spent - m.member_item_decay + ' . max ( 0, $config ['bbdkp_basegp'] ) . ' ) = 0 then 1
						else round( (m.member_earned + m.member_adjustment - m.adj_decay)
							/ ( ' . max ( 0, $config ['bbdkp_basegp'] ) . ' + m.member_spent - m.member_item_decay),2) end as pr,
						m.adj_decay,
						m.member_lastraid,
						r1.name AS member_race,
						s.dkpsys_name,
						l.name AS member_class,
						r.rank_name,
						r.rank_prefix,
						r.rank_suffix,
						c.class_armor_type AS armor_type ,
						c.colorcode,
						c.imagename ',
					'FROM' => array (
						MEMBER_LIST_TABLE => 'a',
						MEMBER_DKP_TABLE => 'm',
						MEMBER_RANKS_TABLE => 'r',
						CLASS_TABLE => 'c',
						BB_LANGUAGE => 'l',
						DKPSYS_TABLE => 's' ),

					'LEFT_JOIN' => array (
						array (
							'FROM' => array (
								BB_LANGUAGE => 'r1' ),
							'ON' => "r1.attribute_id = a.member_race_id
								AND r1.language= '" . $config ['bbdkp_lang'] . "'
								AND r1.attribute = 'race'
								AND r1.game_id = a.game_id" ) ),
							'WHERE' => " a.member_rank_id = r.rank_id
			    				AND a.member_guild_id = r.guild_id
								AND a.member_id = m.member_id
								AND a.game_id = c.game_id
								AND a.member_class_id = c.class_id
								AND m.member_dkpid = s.dkpsys_id
								AND l.game_id = c.game_id and l.attribute_id = c.class_id AND l.language= '" . $config ['bbdkp_lang'] . "' AND l.attribute = 'class'
								AND s.dkpsys_id = " . $dkp_id . '
							    AND a.member_id = ' . $member_id );
				$sql = $db->sql_build_query ( 'SELECT', $sql_array );

				$result = $db->sql_query ( $sql );
				$row = $db->sql_fetchrow ( $result );

				// make object

				$this->member = array (
					'member_id' => $row ['member_id'],
					'member_dkpid' => $row ['member_dkpid'],
					'member_dkpname' => $row ['dkpsys_name'],
					'member_name' => $row ['member_name'],
					'member_raid_value' => $row ['member_raid_value'],
					'member_time_bonus' => $row ['member_time_bonus'],
					'member_zerosum_bonus' => $row ['member_zerosum_bonus'],
					'member_earned' => $row ['member_earned'],
					'member_raid_decay' => $row ['member_raid_decay'],
					'member_adjustment' => $row ['member_adjustment'],
					'ep' => $row ['ep'],
					'member_spent' => $row ['member_spent'],
					'member_item_decay' => $row ['member_item_decay'],
					'gp' => $row ['gp'],
					'pr' => $row ['pr'],
					'adj_decay' => $row ['adj_decay'],
					'member_current' => $row ['member_current'],
					'member_race_id' => $row ['member_race_id'],
					'member_race' => $row ['member_race'],
					'member_class_id' => $row ['member_class_id'],
					'member_class' => $row ['member_class'],
					'member_level' => $row ['member_level'],
					'member_rank_id' => $row ['member_rank_id'],
					'member_rank' => $row ['rank_name'],
					'imagename' => $row ['imagename'],
					'colorcode' => $row ['colorcode'] );
				$db->sql_freeresult ( $result );
				/******************/
				$form_key = 'mm_editmemberdkp';
				add_form_key ( $form_key );

				$template->assign_vars ( array (
					'L_TITLE' => $user->lang ['ACP_DKP_EDITMEMBERDKP'],
					'L_EXPLAIN' => $user->lang ['ACP_MM_EDITMEMBERDKP_EXPLAIN'],
					'F_EDIT_MEMBER' => append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_mdkp&amp;mode=mm_editmemberdkp&amp;" ),
					'MEMBER_NAME' => $this->member ['member_name'],
					'V_MEMBER_ID' => (isset ( $_POST ['add'] )) ? '' : $this->member ['member_id'],
					'V_MEMBER_DKPID' => (isset ( $_POST ['add'] )) ? '' : $this->member ['member_dkpid'],
					'MEMBER_ID' => $this->member ['member_id'],

					'RAIDVAL' => $this->member ['member_raid_value'],
					'TIMEBONUS' => $this->member ['member_time_bonus'],
					'ZEROSUM' => $this->member ['member_zerosum_bonus'],
					'EARNED' => $this->member ['member_earned'],
					'RAIDDECAY' => $this->member ['member_raid_decay'],
					'ADJUSTMENT' => $this->member ['member_adjustment'],
					'RAIDDECAY' => $this->member ['member_raid_decay'],
					'ADJDECAY' => $this->member ['adj_decay'],
					'EP' => $this->member ['ep'],
					'SPENT' => $this->member ['member_spent'],
					'BGP' => $config['bbdkp_basegp'],
					'ITEMDECAY' => $this->member ['member_item_decay'],
					'GP' => $this->member ['gp'],
					'PR' => $this->member ['pr'],

					'MEMBER_EARNED' => $this->member ['member_earned'],
					'MEMBER_SPENT' => $this->member ['member_spent'],
					'MEMBER_ADJUSTMENT' => $this->member ['member_adjustment'],
					'MEMBER_CURRENT' => (! empty ( $this->member ['member_current'] )) ? $this->member ['member_current'] : '0.00',
					'MEMBER_LEVEL' => $this->member ['member_level'],
					'MEMBER_DKPID' => $this->member ['member_dkpid'],
					'MEMBER_DKPNAME' => $this->member ['member_dkpname'],
					'MEMBER_RACE' => $this->member ['member_race'],
					'MEMBER_CLASS' => $this->member ['member_class'],
					'COLORCODE' => $this->member ['colorcode'],
					'IMAGENAME' => (strlen ( $this->member ['imagename'] ) > 1) ? $phpbb_root_path . "images/bbdkp/class_images/" . $this->member ['imagename'] . ".png" : '',
					'MEMBER_RANK' => $this->member ['member_rank'],
					'CORRECT_RAIDVAL' => (! empty ( $correct_raid_value )) ? $correct_raid_value : '0.00',
					'CORRECT_TIMEBONUS' => (! empty ( $correct_time_bonus )) ? $correct_time_bonus : '0.00',
					'CORRECT_ZEROSUM' => (! empty ( $correct_zerosum_bonus )) ? $correct_zerosum_bonus : '0.00',
					'CORRECT_RAIDDECAY' => (! empty ( $correct_raid_decay )) ? $correct_raid_decay : '0.00',
					'CORRECT_MEMBER_SPENT' => (! empty ( $correct_spent )) ? $correct_spent : '0.00',
					'CORRECT_ITEMDECAY' => (! empty ( $correct_itemdecay )) ? $correct_itemdecay : '0.00',
					'CORRECT_EARNED' => $correct_raid_value + $correct_time_bonus + $correct_zerosum_bonus - $correct_raid_decay,
					'S_SHOWZS' => ($config ['bbdkp_zerosum'] == '1') ? true : false,
					'S_SHOWDECAY' => ($config ['bbdkp_decay'] == '1') ? true : false,
					'S_SHOWEPGP' => ($config ['bbdkp_epgp'] == '1') ? true : false,
					'S_SHOWTIME' => ($config ['bbdkp_timebased'] == '1') ? true : false )
				 );

				$this->page_title = 'ACP_DKP_EDITMEMBERDKP';
				$this->tpl_name = 'dkp/acp_' . $mode;
				break;

			/***************************************/
			// member dkp transfer
			// this transfers dkp account from one member account to another member account.
			// the old member account will still exist
			/***************************************/

			case 'mm_transfer' :

				if(count($this->games) == 0)
				{
					trigger_error($user->lang['ERROR_NOGAMES'], E_USER_WARNING);
				}


				$submit = (isset ( $_POST ['transfer'] )) ? true : false;
				$submitdkp = (isset ( $_POST ['dkpsys_id'] ) || isset ( $_GET ['dkpsys_id'] )) ? true : false;

				/***  DKPSYS drop-down query ***/
				$sql = 'SELECT dkpsys_id, dkpsys_name , dkpsys_default
		                FROM ' . DKPSYS_TABLE . "
		                WHERE dkpsys_status = 'Y'
		                GROUP BY dkpsys_id, dkpsys_name , dkpsys_default  ";
				$result = $db->sql_query ( $sql );
				$dkpsys_id = 0;

				if ($submitdkp)
				{
					$dkpsys_id = request_var ( 'dkpsys_id', 0 );
				}
				else
				{
					while ( $row = $db->sql_fetchrow ( $result ) )
					{
						if ($row ['dkpsys_default'] == "Y")
						{
							$dkpsys_id = $row ['dkpsys_id'];
						}
					}

					if ($dkpsys_id == 0)
					{
						$result = $db->sql_query_limit ( $sql, 1 );
						while ( $row = $db->sql_fetchrow ( $result ) )
						{
							$dkpsys_id = $row ['dkpsys_id'];
						}
					}
				}

				$result = $db->sql_query ( $sql );
				while ( $row = $db->sql_fetchrow ( $result ) )
				{
					$template->assign_block_vars ( 'dkpsys_row', array (
						'VALUE' => $row ['dkpsys_id'],
						'SELECTED' => ($row ['dkpsys_id'] == $dkpsys_id) ? ' selected="selected"' : '',
						'OPTION' => (! empty ( $row ['dkpsys_name'] )) ? $row ['dkpsys_name'] : '(None)' ) );
					$dkpsys_name [$row ['dkpsys_id']] = $row ['dkpsys_name'];
				}
				$db->sql_freeresult ( $result );
				/***  end drop-down query ***/


				// build template

                if ($submit && $submitdkp == false)
                {
                    $this->transfer_dkp ($dkpsys_id);
                }
				// from member dkp table

				$member_from = request_var ( 'transfer_from', 0 );
				$member_to = request_var ( 'transfer_to', 0 );

				$sql = 'SELECT m.member_id, l.member_name
						FROM ' . MEMBER_LIST_TABLE . ' l, ' . MEMBER_DKP_TABLE . ' m
						WHERE m.member_id = l.member_id
						AND m.member_dkpid = ' . $dkpsys_id . '
						ORDER BY l.member_name';
				$resultfrom = $db->sql_query ($sql);
				$maara = 0;
				while ( $row = $db->sql_fetchrow ( $resultfrom ) )
				{
					$maara ++;
					$template->assign_block_vars ( 'transfer_from_row', array (
						'VALUE' => $row ['member_id'],
						'SELECTED' => ($member_from == $row ['member_id']) ? ' selected="selected"' : '',
						'OPTION' => $row ['member_name'] ) );

				}
				$db->sql_freeresult ( $resultfrom );

				// to member table

				$sql = 'SELECT m.member_id, l.member_name FROM ' .
						 MEMBER_LIST_TABLE . ' l, ' . MEMBER_DKP_TABLE . ' m, ' . MEMBER_RANKS_TABLE . ' k
						WHERE l.member_rank_id = k.rank_id
						AND k.rank_hide != 1
						AND m.member_id = l.member_id
						AND m.member_dkpid = ' . $dkpsys_id . '
						ORDER BY l.member_name';
				$resultto = $db->sql_query ( $sql );
				$teller_to = 0;
				while ( $row = $db->sql_fetchrow ( $resultto ) )
				{
					$teller_to ++;
					$template->assign_block_vars ( 'transfer_to_row', array (
						'VALUE' => $row ['member_id'],
						'SELECTED' => ($member_to == $row ['member_id']) ? ' selected="selected"' : '',
						'OPTION' => $row ['member_name']));
				}
				$db->sql_freeresult ( $resultto );

				$show = true;
				if ($maara == 0)
				{
					$show = false;
				}

				$template->assign_vars ( array (
					'L_TITLE' => $user->lang ['ACP_MM_TRANSFER'],
					'ERROR_MSG' => $user->lang ['ERROR_NODKPACCOUNT'],
					'L_EXPLAIN' => $user->lang ['TRANSFER_MEMBER_HISTORY_DESCRIPTION'],
					'S_SHOW' => $show,
					'F_TRANSFER' => append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_mdkp&amp;mode=mm_transfer" ),
					'F_DKP' => append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_mdkp&amp;mode=mm_transfer&ampi=setdkp" ),
					'L_SELECT_1_OF_X_MEMBERS' => sprintf ( $user->lang ['SELECT_1OFX_MEMBERS'], $maara ),
					'L_SELECT_1_OF_Y_MEMBERS' => sprintf ( $user->lang ['SELECT_1OFX_MEMBERS'], $teller_to ) ) );
				$this->page_title = 'ACP_MM_TRANSFER';
				$this->tpl_name = 'dkp/acp_' . $mode;

				break;

			default :
				$this->page_title = 'ACP_DKP_MAINPAGE';
				$this->tpl_name = 'dkp/acp_mainpage';
				$success_message = 'Error';
				trigger_error ( $success_message . $this->link );

		}
	}

	/**
	 * list dkp points per pool and member
	 */
	private function list_memberdkp()
	{
		global $user, $template, $config, $phpbb_admin_path, $phpEx;

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

		$this->PointsController->guild_id = $Guild->guildid;

		/* dkp pool */
		$this->PointsController->dkpsys_id=0;
		if (isset($_GET[URI_DKPSYS]) OR isset ( $_POST[URI_DKPSYS]))
		{
			//user clicked on add raid from event editscreen
			$this->PointsController->dkpsys_id = request_var ( URI_DKPSYS, 0 );
		}

		if($this->PointsController->dkpsys_id==0)
		{

			if( count((array) $this->PointsController->dkpsys) == 0 )
			{
				trigger_error('ERROR_NOPOOLS', E_USER_WARNING );
			}

			//get default dkp pool
			foreach ($this->PointsController->dkpsys as $pool)
			{
				if ($pool['default'] == 'Y' )
				{
					$this->PointsController->dkpsys_id = $pool['id'];
					break;
				}
			}
			//if still 0 then get first one
			if($this->PointsController->dkpsys_id==0)
			{
				foreach ($this->PointsController->dkpsys as $pool)
				{
					$this->PointsController->dkpsys_id = $pool['id'];
					break;
				}
			}
		}
		foreach ($this->PointsController->dkpsys as $pool)
		{
			$template->assign_block_vars ( 'dkpsys_row', array (
					'VALUE' 	=> $pool['id'],
					'SELECTED' 	=> ($pool['id'] == $this->PointsController->dkpsys_id) ? ' selected="selected"' : '',
					'OPTION' 	=> (! empty ( $pool['name'] )) ? $pool['name'] : '(None)' )
			);
		}

		/***  end drop-down query ***/

		$activate = (isset ( $_POST ['submit_activate'] )) ? true : false;
		if ($activate)
		{
			// all members in this window
			$all_members = explode(',', request_var ( 'idlist', ''));
			// all checked members in this window
			$active_members = request_var ( 'activate_ids', array (0));
			$this->PointsController->activate($all_members, $active_members);
		}

		if ($config ['bbdkp_epgp'] == '1')
		{
			$memberlist = $this->PointsController->listEPGPaccounts();
		}
		else
		{
			$memberlist = $this->PointsController->listdkpaccounts();
		}
		$current_order = $memberlist[1];
		$membersids = array();
		$lines = 0;
		foreach ($memberlist[0]  as $member_id => $dkp)
		{
			$lines += 1;
			$template->assign_block_vars ('members_row', $dkp);
			$membersids[$member_id] = 1;
		}

		/***  Labels  ***/
		$footcount_text = sprintf ( $user->lang ['LISTMEMBERS_FOOTCOUNT'], $lines );

		$output = array (
				'IDLIST'	=> implode(",", $membersids),
				'F_MEMBERS' => append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_mdkp&amp;mode=mm_listmemberdkp&amp;" ) . '&amp;mode=mm_editmemberdkp',
				'L_TITLE' => $user->lang ['ACP_DKP_LISTMEMBERDKP'],
				'L_EXPLAIN' => $user->lang ['ACP_MM_LISTMEMBERDKP_EXPLAIN'],
				'BUTTON_NAME' => 'delete',
				'BUTTON_VALUE' => $user->lang ['DELETE_SELECTED_MEMBERS'],
				'O_NAME' => $current_order ['uri'] [1],
				'O_RANK' => $current_order ['uri'] [2],
				'O_LEVEL' => $current_order ['uri'] [3],
				'O_CLASS' => $current_order ['uri'] [4],
				'O_RAIDVALUE' => $current_order ['uri'] [5],
				'O_ADJUSTMENT' => $current_order ['uri'] [10],
				'O_SPENT' => $current_order ['uri'] [12],
				'O_LASTRAID' => $current_order ['uri'] [17],
				'S_SHOWZS' => ($config ['bbdkp_zerosum'] == '1') ? true : false,
				'S_SHOWDECAY' => ($config ['bbdkp_decay'] == '1') ? true : false,
				'S_SHOWEPGP' => ($config ['bbdkp_epgp'] == '1') ? true : false,
				'S_SHOWTIME' => ($config ['bbdkp_timebased'] == '1') ? true : false,
				'U_LIST_MEMBERDKP' => append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_mdkp&amp;" . URI_DKPSYS . "=" . $this->PointsController->dkpsys_id . "&amp;mode=mm_listmemberdkp" ) . '&amp;mod=list&amp;',
				'S_NOTMM' => false,
				'LISTMEMBERS_FOOTCOUNT' => $footcount_text,
				'DKPSYS' => $this->PointsController->dkpsys_id,
				'DKPSYSNAME' => $this->PointsController->dkpsys[$this->PointsController->dkpsys_id]['name'] );

		if ($config ['bbdkp_timebased'] == 1)
		{
			$output ['O_TIMEBONUS'] = $current_order ['uri'] [6];

		}

		if ($config ['bbdkp_zerosum'] == 1)
		{
			$output ['O_ZSBONUS'] = $current_order ['uri'] [7];

		}

		if ($config ['bbdkp_decay'] == 1)
		{
			$output ['O_RDECAY'] = $current_order ['uri'] [9];
			$output ['O_IDECAY'] = $current_order ['uri'] [13];
		}

		if ($config ['bbdkp_epgp'] == 1)
		{
			$output ['O_EP'] = $current_order ['uri'] [11];
			$output ['O_GP'] = $current_order ['uri'] [14];
			$output ['O_PR'] = $current_order ['uri'] [15];
		}
		else
		{
			$output ['O_EARNED'] = $current_order ['uri'] [8];
			$output ['O_CURRENT'] = $current_order ['uri'] [16];
		}

		$template->assign_vars ( $output );


	}


	/**
	 * transfer dkp to other member
	 * @param int $dkpsys_id
	 */
	function transfer_dkp($dkpsys_id)
	{
		global $user, $db;

		if (confirm_box ( true ))
		{
			//fetch hidden variables
			$member_from = request_var ( 'hidden_idfrom', 0 );
			$member_to = request_var ( 'hidden_idto', 0 );
			$dkpsys_id = request_var ( 'hidden_dkpid', 0 );
			$this->PointsController->transfer_points($member_from, $member_to, $dkpsys_id, $this->link);
		}
		else
		{
			// first check if user tries to transfer from one to the same

			$member_from = request_var ( 'transfer_from', 0 );
			$member_to = request_var ( 'transfer_to', 0 );
			if ($member_from == $member_to)
			{
				trigger_error ( $user->lang ['ERROR_TRFSAME'], E_USER_WARNING );
			}

			if ($member_from == 0 || $member_to == 0)
			{
				trigger_error ( $user->lang ['ERROR_NOSELECT'], E_USER_WARNING );
			}

			// prepare some logging information

			$sql = 'SELECT member_name FROM ' . MEMBER_LIST_TABLE . '
					WHERE member_id =  ' . $member_from;
			$result = $db->sql_query ( $sql, 0 );
			$member_from_name = ( string ) $db->sql_fetchfield ( 'member_name' );
			$db->sql_freeresult ( $result );

			$sql = 'SELECT member_name FROM ' . MEMBER_LIST_TABLE . '
					WHERE member_id =  ' . $member_to;
			$result = $db->sql_query ( $sql, 0 );
			$member_to_name = ( string ) $db->sql_fetchfield ( 'member_name' );
			$db->sql_freeresult ( $result );

			$s_hidden_fields = build_hidden_fields ( array (
				'transfer' => true,
				'hidden_name_from' => $member_from_name,
				'hidden_name_to' => $member_to_name,
				'hidden_idfrom' => $member_from,
				'hidden_idto' => $member_to,
				'hidden_dkpid' => $dkpsys_id ) );
			confirm_box ( false, sprintf ( $user->lang ['CONFIRM_TRANSFER_MEMBERDKP'],
			 $member_from_name, $member_to_name ), $s_hidden_fields );

		}

	}

}

?>