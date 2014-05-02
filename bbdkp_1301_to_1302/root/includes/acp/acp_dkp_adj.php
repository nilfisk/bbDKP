<?php
/**
 * DKP Adjustments ACP file
 *
 * @package bbdkp
 * @link http://www.bbdkp.com
 * @author Sajaki@gmail.com
 * @copyright 2009 bbdkp
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.3.0
 *
 */
// don't add this file to namespace bbdkp
/**
 * @ignore
 */
if (! defined('IN_PHPBB'))
{
	exit();
}
if (! defined('EMED_BBDKP'))
{
	$user->add_lang(array('mods/dkp_admin'));
	trigger_error($user->lang['BBDKPDISABLED'], E_USER_WARNING);
}

// Include the abstract base
if (!class_exists('\bbdkp\admin\Admin'))
{
	require ("{$phpbb_root_path}includes/bbdkp/admin/admin.$phpEx");
}
// Include the adjust class
if (!class_exists('\bbdkp\controller\adjustments\Adjust'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/adjustments/Adjust.$phpEx");
}
// Include the members class
if (!class_exists('\bbdkp\controller\members\Members'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/members/Members.$phpEx");
}
// Include the validator class
if (!class_exists('\bbdkp\admin\Validator'))
{
	require("{$phpbb_root_path}includes/bbdkp/admin/validator.$phpEx");
}
//include the guilds class
if (!class_exists('\bbdkp\controller\guilds\Guilds'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/guilds/Guilds.$phpEx");
}
/**
 * This acp class manages guildmembers dkp adjustments
 *
 *   @package bbdkp
 */
class acp_dkp_adj extends \bbdkp\admin\Admin
{

	/**
	 * adjustment class instance
	 * @var \bbdkp\controller\adjustments\Adjust
	 */
	private $adjustment;

	/**
	 * url in triggers
	 * @var string
	 */
	private $link;

	/**
	 * main dkp_adj acp function
	 * @param integer $id
	 * @param string $mode
	 */
	public function main ($id, $mode)
	{
		global $db, $user, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$this->adjustment = new \bbdkp\controller\adjustments\Adjust;  //always late binding in php
		$this->link = '<br /><a href="' . append_sid("{$phpbb_admin_path}index.$phpEx", "i=dkp_adj&amp;mode=listiadj") . '"><h3>' . $user->lang['RETURN_DKPINDEX'] . '</h3></a>';
		$this->tpl_name = 'dkp/acp_' . $mode;

		switch ($mode)
		{
			case 'listiadj':

				if(count($this->games) == 0)
				{
					trigger_error($user->lang['ERROR_NOGAMES'], E_USER_WARNING);
				}

				$showadd = (isset($_POST['addiadj'])) ? true : false;
				if ($showadd)
				{
					redirect(append_sid("{$phpbb_admin_path}index.$phpEx", "i=dkp_adj&amp;mode=addiadj"));
					break;
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
				$this->adjustment->setAdjustmentDkpid(0);
                if (isset($_GET[URI_DKPSYS]) OR isset ( $_POST[URI_DKPSYS]))
				{
                    $this->adjustment->setAdjustmentDkpid(request_var ( URI_DKPSYS, 0 ));
				}

				if($this->adjustment->getAdjustmentDkpid() == 0)
				{

					if( count((array) $this->adjustment->getDkpsys()) == 0 )
					{
						trigger_error('ERROR_NOPOOLS', E_USER_WARNING );
					}

					//get default dkp pool
					foreach ($this->adjustment->getDkpsys() as $pool)
					{
						if ($pool['default'] == 'Y' )
						{
							$this->adjustment->setAdjustmentDkpid($pool['id']);
							break;
						}
					}
					//if still 0 then get first one
					if($this->adjustment->getAdjustmentDkpid() == 0)
					{
						foreach ($this->adjustment->getDkpsys() as $pool)
						{
                            $this->adjustment->setAdjustmentDkpid($pool['id']);
							break;
						}
					}
				}

				foreach ($this->adjustment->getDkpsys() as $pool)
				{
					$template->assign_block_vars ( 'dkpsys_row', array (
							'VALUE' 	=> $pool['id'],
							'SELECTED' 	=> ($pool['id'] == $this->adjustment->getAdjustmentDkpid()) ? ' selected="selected"' : '',
							'OPTION' 	=> (! empty ( $pool['name'] )) ? $pool['name'] : '(None)' )
					);
				}

				/*** end DKPSYS drop-down ***/
				$sort_order = array(
                    0 => array('adjustment_id desc' , 'adjustment_id asc'),
					1 => array('adjustment_date desc, member_name asc' , 'adjustment_date asc, member_name asc') ,
					2 => array('adjustment_dkpid' , 'adjustment_dkpid desc') ,
					3 => array('dkpsys_name' , 'dkpsys_name desc') ,
					4 => array('member_name' , 'member_name desc') ,
					5 => array('adjustment_reason' , 'adjustment_reason desc') ,
					6 => array('adjustment_value desc' , 'adjustment_value') ,
					7 => array('adjustment_added_by' , 'adjustment_added_by desc'),
                );

				$members = new \bbdkp\controller\members\Members();
				$member_filter = utf8_normalize_nfc(request_var('member_name', '', true));
				$member_id_filter = '';
				if ($member_filter != '')
				{
					$member_id_filter = $members->get_member_id(trim($member_filter));
				}

				$result2 = $this->adjustment->countadjust($member_id_filter);
				$total_adjustments = (int) $db->sql_fetchfield('total_adjustments');
				$db->sql_freeresult($result2);

				$u_list_adjustments = append_sid("{$phpbb_admin_path}index.$phpEx", "i=dkp_adj&amp;mode=listiadj") . '&amp;' . URI_PAGE;
				$current_order = $this->switch_order($sort_order);
				$start = request_var('start', 0);

				$result = $this->adjustment->ListAdjustments($current_order['sql'], $member_id_filter, $start, $Guild->guildid);
				while ($adj = $db->sql_fetchrow($result))
				{
					$template->assign_block_vars('adjustments_row', array(
						'U_ADD_ADJUSTMENT' => append_sid("{$phpbb_admin_path}index.$phpEx", "i=dkp_adj&amp;mode=addiadj") . '&amp;' . URI_ADJUSTMENT . '=' . $adj['adjustment_id'] . '&amp;' . URI_DKPSYS . '=' . $adj['adjustment_dkpid'] ,
						'DATE' => date($config['bbdkp_date_format'], $adj['adjustment_date']) ,
                        'ADJID' => $adj['adjustment_id'] ,
                        'DKPID' => $adj['adjustment_dkpid'] ,
						'DKPPOOL' => $adj['dkpsys_name'] ,
						'COLORCODE' => ($adj['colorcode'] == '') ? '#254689' : $adj['colorcode'] ,
						'CLASS_IMAGE' => (strlen($adj['imagename']) > 1) ? $phpbb_root_path . "images/bbdkp/class_images/" . $adj['imagename'] . ".png" : '' ,
						'S_CLASS_IMAGE_EXISTS' => (strlen($adj['imagename']) > 1) ? true : false ,
						'U_VIEW_MEMBER' => (isset($adj['member_name'])) ? append_sid("{$phpbb_root_path}dkp.$phpEx", "page=member&amp;" . URI_NAMEID . '=' . $adj['member_id'] . '&amp;' . URI_DKPSYS . '=' . $adj['adjustment_dkpid']) : '' ,
						'MEMBER' => (isset($adj['member_name'])) ? $adj['member_name'] : '' ,
						'REASON' => (isset($adj['adjustment_reason'])) ? $adj['adjustment_reason'] : '' ,
						'CAN_DECAY' => $adj['can_decay'],
						'ADJUSTMENT' => $adj['adjustment_value'] == 0 ? '' : number_format($adj['adjustment_value'],2) ,
						'ADJ_DECAY' => -1 * $adj['adj_decay'] == 0 ? '' : -1 * $adj['adj_decay'],
						'ADJUSTMENT_NET' => ($adj['adjustment_value'] - $adj['adj_decay']) == 0 ? '' : number_format($adj['adjustment_value'] - $adj['adj_decay'], 2) ,
						'DECAY_TIME' => ($adj['decay_time'] != 0 ?  date($config['bbdkp_date_format'], $adj['decay_time']) : '') ,
						'ADDED_BY' => $adj['adjustment_added_by']
					));

				}
				$db->sql_freeresult($result);
				$listadj_footcount = sprintf($user->lang['LISTADJ_FOOTCOUNT'], $total_adjustments, $config['bbdkp_user_alimit']);

				$pagination = \generate_pagination(append_sid("{$phpbb_admin_path}index.$phpEx", "i=dkp_adj&amp;mode=listiadj&amp;dkpsys_id=" .
					$this->adjustment->getAdjustmentDkpid()) . '&amp;' . URI_PAGE,
					$total_adjustments,
					$config['bbdkp_user_alimit'],
					$start, true);

				$template->assign_vars(array(
					'L_TITLE' => $user->lang['ACP_LISTIADJ'] ,
					'L_EXPLAIN' => $user->lang['ACP_LISTIADJ_EXPLAIN'] ,
					'S_SHOW' => ($total_adjustments > 0) ? true : false ,
                    'O_ADJID' => $current_order['uri'][0] ,
                    'O_DATE' => $current_order['uri'][1] ,
					'O_DKPID' => $current_order['uri'][2] ,
					'O_DKPPOOL' => $current_order['uri'][3] ,
					'O_MEMBER' => $current_order['uri'][4] ,
					'O_REASON' => $current_order['uri'][5] ,
					'O_ADJUSTMENT' => $current_order['uri'][6] ,
					'O_ADDED_BY' => $current_order['uri'][7] ,
					'U_LIST_ADJUSTMENTS' => $u_list_adjustments ,
					'MEMBER_NAME' => $member_filter ,
					'START' => $start ,
					'S_GROUP_ADJ' => false ,
					'LISTADJ_FOOTCOUNT' => $listadj_footcount ,
					'ADJUSTMENT_PAGINATION' => $pagination,
					'TOTAL_ADJUSTMENTS' => 'Total Adjustments',
					'PAGE_NUMBER'    => on_page($total_adjustments, $config['bbdkp_user_alimit'], $start),
						));

				$this->page_title = 'ACP_LISTIADJ';

				break;

			case 'addiadj':
				$form_key = 'acp_dkp_adj';
				add_form_key($form_key);

				//  begin DKPSYS drop-down
				$dkpsys_id = 1;
				$sql = 'SELECT dkpsys_id, dkpsys_name, dkpsys_default
                    FROM ' . DKPSYS_TABLE . "
		            WHERE dkpsys_status = 'Y'
                    ORDER BY dkpsys_name";
				$resultdkpsys = $db->sql_query($sql);

				$showadj = new \bbdkp\controller\adjustments\Adjust;

				$adjust_id = request_var(URI_ADJUSTMENT, 0);
				$dkpsys_id = request_var(URI_DKPSYS, 0);

				if ($adjust_id != 0 && $dkpsys_id != 0)
				{
					$showadj->get($adjust_id);

					while ($row2 = $db->sql_fetchrow($resultdkpsys))
					{
						$template->assign_block_vars('adj_dkpid_row', array(
							'VALUE' => $row2['dkpsys_id'] ,
							'SELECTED' => ($row2['dkpsys_id'] == $showadj->getAdjustmentDkpid() ? ' selected="selected"' : '' ),
							'OPTION' => (! empty($row2['dkpsys_name'])) ? $row2['dkpsys_name'] : '(None)'));
					}

				}
				else
				{
					// we dont have a GET so put default dkp pool in pulldown
					while ($row2 = $db->sql_fetchrow($resultdkpsys))
					{
						//dkpsys_default
						$template->assign_block_vars('adj_dkpid_row', array(
							'VALUE' => $row2['dkpsys_id'] ,
							'SELECTED' => ($row2['dkpsys_default'] == 'Y') ? ' selected="selected"' : '' ,
							'OPTION' => (! empty($row2['dkpsys_name'])) ? $row2['dkpsys_name'] : '(None)'));
						if ($row2['dkpsys_default'] == 'Y')
						{
							$dkpsys_id = $row2['dkpsys_id'];
						}
					}
				}
				//  end DKPSYS drop-down
				$submit = (isset($_POST['add'])) ? true : false;
				$update = (isset($_POST['update'])) ? true : false;
				$delete = (isset($_POST['delete'])) ? true : false;
				if ($submit || $update)
				{
					if (! check_form_key('acp_dkp_adj'))
					{
						trigger_error('FORM_INVALID');
					}
				}

				$now = getdate();
				$s_day_options = '';

				$day = $showadj->getAdjustmentDate() > 0 ? date('j', $showadj->getAdjustmentDate()) : $now['mday'] ;
				for ($i = 1; $i < 32; $i++)
				{
					$selected = ($i == $day ) ? ' selected="selected"' : '';
					$s_day_options .= "<option value=\"$i\"$selected>$i</option>";
				}

				$s_month_options = '';
				$month = $showadj->getAdjustmentDate() > 0 ? date('n', $showadj->getAdjustmentDate()) : $now['mon'] ;
				for ($i = 1; $i < 13; $i++)
				{
					$selected = ($i == $month ) ? ' selected="selected"' : '';
					$s_month_options .= "<option value=\"$i\"$selected>$i</option>";
				}

				$s_year_options = '';
				$yr = $showadj->getAdjustmentDate() > 0 ? date('Y', $showadj->getAdjustmentDate()) : $now['year'] ;
				for ($i = $now['year'] - 10; $i <= $now['year']; $i++)
				{
					$selected = ($i == $yr ) ? ' selected="selected"' : '';
					$s_year_options .= "<option value=\"$i\"$selected>$i</option>";
				}


				if ($submit)
				{
					global $user;
					$this->error_check();

					$newadjust = new \bbdkp\controller\adjustments\Adjust;

					$temp = str_replace(".", "", request_var('adjustment_value', 0.0));
					$temp2 = (float) str_replace(",", ".", $temp);

					$newadjust->setAdjustmentValue($temp2);
					$newadjust->setAdjustmentReason(utf8_normalize_nfc(request_var('adjustment_reason', '', true)) )  ;
					$newadjust->setCanDecay(request_var('adj_decayable', 1));
					$newadjust->setAdjDecay(0);
					$newadjust->setDecayTime(0);
					$newadjust->setAdjustmentDate(mktime(12, 0, 0, request_var('adjustment_month', 0), request_var('adjustment_day', 0), request_var('adjustment_year', 0)));
					$newadjust->setAdjustmentDkpid(request_var('adj_dkpid', 0));
					$newadjust->setAdjustmentGroupkey($this->gen_group_key($this->time, $newadjust->getAdjustmentReason(), $newadjust->getAdjustmentValue()));
					$newadjust->setAdjustmentAddedBy($user->data['username']);

					$members = request_var('member_names', array(0 => 0), true);
					$member_names = array();
					foreach ($members as $member_id)
					{
						$member = new \bbdkp\controller\members\Members;
						$member->member_id = $member_id;
						$member->Getmember();
						$newadjust->setMemberId($member_id);
						$newadjust->setMemberName($member->member_name);
						$member_names[] = $member->member_name;
						$newadjust->add();
					}

					//
					// Logging
					//
					$log_action = array(
						'header' => 'L_ACTION_INDIVADJ_ADDED' ,
						'L_ADJUSTMENT' => $newadjust->getAdjustmentValue()  ,
						'L_REASON' => $newadjust->getAdjustmentReason() ,
						'L_MEMBERS' => implode(', ', $member_names) ,
						'L_ADDED_BY' => $newadjust->getAdjustmentAddedBy() );

					$this->log_insert(array(
						'log_type' => 'L_ACTION_INDIVADJ_ADDED',
						'log_action' => $log_action));

					$success_message = sprintf($user->lang['ADMIN_ADD_IADJ_SUCCESS'], $config['bbdkp_dkp_name'], $newadjust->getAdjustmentValue(), implode(', ', $member_names));
					trigger_error($success_message . $this->link);

				}

				if ($update)
				{
					$this->error_check();

					$oldadjust = new \bbdkp\controller\adjustments\Adjust;
					$oldadjust->adjustment_id = request_var('hidden_id', 0);
					$oldadjust->get($oldadjust->adjustment_id);
					foreach($oldadjust->getMembersSamegroupkey() as $member_id)
					{
						$oldmembers = new \bbdkp\controller\members\Members;
						$oldmembers->member_id = $member_id;
						$oldmembers->Getmember();
						$oldmember_names[] = $oldmembers->member_name;
						unset($oldmembers);

						// remove old adjustment
						$oldadjust->delete();
					}

					$updadjust = new \bbdkp\controller\adjustments\Adjust;
					$temp = str_replace(".", "", request_var('adjustment_value', 0.0));
					$temp2 = (float) str_replace(",", ".", $temp);
					$updadjust->setAdjustmentValue($temp2);
					$updadjust->setAdjustmentReason( utf8_normalize_nfc(request_var('adjustment_reason', '', true)));
					$updadjust->setCanDecay(request_var('adj_decayable', 1)) ;
					$updadjust->setAdjDecay(request_var('adjustment_decay', 0.0));
					$updadjust->setDecayTime($oldadjust->getDecayTime());
					$updadjust->setAdjustmentDate(mktime(12, 0, 0, request_var('adjustment_month', 0), request_var('adjustment_day', 0), request_var('adjustment_year', 0)));
					$updadjust->setAdjustmentDkpid(request_var('adj_dkpid', 0));
					$updadjust->setAdjustmentAddedBy($user->data['username']);
					$updadjust->setAdjustmentGroupkey($updadjust->gen_group_key($this->time, $updadjust->getAdjustmentReason(), $updadjust->getAdjustmentValue()));

					$members = request_var('member_names', array(0 => 0), true);

					foreach ($members as $member_id)
					{
						$member = new \bbdkp\controller\members\Members;
						$member->member_id = $member_id;
						$updadjust->setMembersSamegroupkey($member_id);
						$member->Getmember();
						$updadjust->setMemberId($member_id);
						$updadjust->setMemberName($member->member_name);
						$member_names[] = $member->member_name;
						$updadjust->add();
						unset($member);
					}

					//
					// Logging
					//
					$log_action = array(
						'header' => 'L_ACTION_INDIVADJ_UPDATED' ,
						'id' => $adjust_id ,
						'L_ADJUSTMENT_BEFORE' => $oldadjust->getAdjustmentValue() ,
						'L_REASON_BEFORE' => $oldadjust->getAdjustmentReason() ,
						'L_MEMBERS_BEFORE' => implode(', ', $oldmember_names) ,
						'L_ADJUSTMENT_AFTER' => $updadjust->getAdjustmentValue()  ,
						'L_REASON_AFTER' => $updadjust->getAdjustmentReason() ,
						'L_MEMBERS_AFTER' => implode(', ', $member_names) ,
						'L_UPDATED_BY' => $user->data['username']);

					$this->log_insert(array(
						'log_type' => $log_action['header'] ,
						'log_action' => $log_action));

					$success_message = sprintf($user->lang['ADMIN_UPDATE_IADJ_SUCCESS'], $config['bbdkp_dkp_name'], $updadjust->getAdjustmentValue(), implode(', ', $member_names));
					trigger_error($success_message . $this->link);
				}



				if ($delete)
				{
					if (confirm_box(true))
					{
						// get form vars
						$adjust_id = request_var('xhidden_id', 0);
						$deleteadj = new \bbdkp\controller\adjustments\Adjust;
						$deleteadj->get($adjust_id);
						$deleteadj->delete();

						// Logging
						$log_action = array(
							'header' => 'L_ACTION_INDIVADJ_DELETED' ,
							'id' => $adjust_id ,
							'L_ADJUSTMENT' => $deleteadj->getAdjustmentValue() ,
							'L_REASON' => $deleteadj->getAdjustmentReason() ,
							'L_MEMBERS' =>  $deleteadj->getMemberName() );

						$this->log_insert(array(
							'log_type' => $log_action['header'] ,
							'log_action' => $log_action));
						//
						// Success messages
						$success_message = sprintf($user->lang['ADMIN_DELETE_IADJ_SUCCESS'], $config['bbdkp_dkp_name'],
								$deleteadj->getAdjustmentValue() , $deleteadj->getMemberName());

						trigger_error($success_message . $this->link);

					}
					else
					{
						$s_hidden_fields = build_hidden_fields(array(
							'delete' => true ,
							'xhidden_id' => request_var('hidden_id', 0) ,
							));
						$template->assign_vars(array(
							'S_HIDDEN_FIELDS' => $s_hidden_fields));
						confirm_box(false, $user->lang['CONFIRM_DELETE_IADJ'], $s_hidden_fields);
					}
				}


				//guild dropdown
				$guildid  = request_var('member_guild_id', 0);
				$Guild = new \bbdkp\controller\guilds\Guilds();
				$guildlist = $Guild->guildlist(1);
				foreach ( (array) $guildlist as $g )
				{

					if ($guildid  == 0)
					{
						$guildid  = $g['id'];
					}

					if($g['guilddefault'] == 1 )
					{
						$guildid  = $g['id'];
					}

					$template->assign_block_vars('guild_row', array(
							'VALUE' => $g['id'] ,
							'SELECTED' => ($guildid == $g['id']) ? ' selected="selected"' : '',
							'OPTION' => $g['name']));
				}

				/* mark members as selected */
				$sql = 'SELECT member_id, member_name FROM ' . MEMBER_LIST_TABLE . '
						WHERE member_guild_id = ' . $guildid . '
						ORDER BY member_name ';
				$result = $db->sql_query($sql);

				while ($row = $db->sql_fetchrow($result))
				{
					if ($adjust_id)
					{
						//editmode
						$selected = (@in_array($row['member_id'],  $showadj->getMembersSamegroupkey()  )) ? ' selected="selected"' : '';
					}
					else
					{
						//newmode
						$selected = (@in_array($row['member_id'],
						 utf8_normalize_nfc(request_var('member_names', array(0 => 0))))) ? ' selected="selected"' : '';
					}

					$template->assign_block_vars('members_row', array(
						'VALUE' => $row['member_id'] ,
						'SELECTED' => $selected ,
						'OPTION' => $row['member_name']));
				}


				$db->sql_freeresult($result);
				$template->assign_vars(array(
					'L_TITLE' =>  ($showadj->adjustment_id == 0) ? $user->lang['ADD_IADJ_TITLE'] : $user->lang['EDIT_IADJ_TITLE'],
					'L_EXPLAIN' => $user->lang['ACP_ADDIADJ_EXPLAIN'] ,
					// Form vars
					'F_ADD_ADJUSTMENT' => append_sid("{$phpbb_admin_path}index.$phpEx", "i=dkp_adj&amp;mode=addiadj") ,
					'ADJUSTMENT_ID' => $showadj->adjustment_id,
					'DKP_ID' => $showadj->getAdjustmentDkpid() ,
					// Form values
					'ADJUSTMENT_VALUE' => number_format($showadj->getAdjustmentValue(), 2) ,
					'ADJUSTMENT_REASON' => $showadj->getAdjustmentReason(),
					'ADJUSTMENT_DECAY' => number_format($showadj->getAdjDecay(), 2) ,

					'S_DAY_OPTIONS'		=> $s_day_options,
					'S_MONTH_OPTIONS'	=> $s_month_options,
					'S_YEAR_OPTIONS'	=> $s_year_options,

					'CAN_DECAY_NO_CHECKED' => ( $showadj->getCanDecay() == 0) ? ' checked="checked"' : '' ,
					'CAN_DECAY_YES_CHECKED' => ($showadj->getCanDecay() == 1) ? ' checked="checked"' : '' ,

					// Javascript messages
					'MSG_VALUE_EMPTY' => $user->lang['FV_REQUIRED_ADJUSTMENT'] ,
					// Buttons
					'UA_FINDMEMBERS' => append_sid($phpbb_admin_path . "style/dkp/findmembers.$phpEx") ,
					'S_ADD' => (! $showadj->adjustment_id) ? true : false));

				$this->page_title = 'ACP_ADDIADJ';

				break;
		}
	}


	/**
	 * validationfunction for adjustment values : required and numeric, date is in range
	 * @access private
	 */
	private function error_check()
	{
		global $user;
		$validator = new \bbdkp\admin\Validator();
		//setup validation rules
		$validator->addRule('member_names', array('required'));
		$validator->addRule('adjustment_value', array('required'));
		$validator->addRule('adjustment_day', array('required', 'min' => 1, 'max' => 31 ));
		$validator->addRule('adjustment_month',array('required', 'min' => 1, 'max' => 12));
		$validator->addRule('adjustment_year', array('required', 'min' => 2000, 'max' => 2020));
		$member_names = 'x';
		if (! isset($_POST['member_names']))
		{
			$member_names = '';
		}
		$data = array(
				'member_names' => $member_names,
				'adjustment_value' => request_var('adjustment_value', 0.00),
				'adjustment_day' => request_var('adjustment_day', 0),
				'adjustment_month' => request_var('adjustment_month', 0),
				'adjustment_year' => request_var('adjustment_year', 0),
		);
		$validator->setData($data);
		$validator->displayerrors();


	}

}