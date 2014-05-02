<?php
/**
 * Lootcontroller class file
 * @package bbdkp
 * @link http://www.bbdkp.com
 * @author Sajaki@gmail.com
 * @copyright 2013 bbdkp
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.3.0
 * @since 1.3.0
 *
 */
namespace bbdkp\controller\Loot;
/**
 * @ignore
 */
if (! defined('IN_PHPBB'))
{
	exit();
}

$phpEx = substr(strrchr(__FILE__, '.'), 1);
global $phpbb_root_path;

//include the abstract base interface
if (!class_exists('\bbdkp\admin\Admin'))
{
	require ("{$phpbb_root_path}includes/bbdkp/admin/admin.$phpEx");
}
if (!class_exists('\bbdkp\controller\loot\Loot'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/loot/Loot.$phpEx");
}
if (!class_exists('\bbdkp\controller\raids\Raids'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/raids/Raids.$phpEx");
}
// Include the members class
if (!class_exists('\bbdkp\controller\members\Members'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/members/Members.$phpEx");
}
if (!class_exists('\bbdkp\controller\points\PointsController'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/points/PointsController.$phpEx");
}
/**
 * this class manages the loot transaction table (phpbb_bbdkp_raid_items)
 *   @package bbdkp
 *
 */
class LootController  extends \bbdkp\admin\Admin
{
	/**
	 * instance of loot
	 * @var \bbdkp\controller\loot\Loot
	 */
	private $loot;

	/**
	 * Pool id
	 * @var int
	 */
	public $dkpsys;

	/**
	 * lootcontroller constructor
	 */
	function __construct()
	{
		global $db;
		parent::__construct();
		$this->loot = new \bbdkp\controller\loot\Loot();

		// get dkp pools
		$sql = 'SELECT dkpsys_id, dkpsys_name, dkpsys_default
            FROM ' . DKPSYS_TABLE . ' a , ' . EVENTS_TABLE . " b
			WHERE a.dkpsys_id = b.event_dkpid AND b.event_status = 1
            AND a.dkpsys_status = 'Y'";
		$result = $db->sql_query($sql);
		$this->dkpsys = array();
		while ($row = $db->sql_fetchrow($result) )
		{
			$this->dkpsys[$row['dkpsys_id']] = array(
					'id' => $row['dkpsys_id'],
					'name' => $row['dkpsys_name'],
					'default' => $row['dkpsys_default']);
		}
		$db->sql_freeresult($result);

	}

	/**
	 * get loot from database
	 * @param int $item_id
	 * @return \bbdkp\controller\loot\Loot
	 */
	public function Getloot($item_id)
	{
		$this->loot->Getloot($item_id);
		return $this->loot;
	}


	/**
	 * raid acp-> view loot index.php?i=dkp_item&mode=additem&raid_id=1;
	 * item acp-> view loot index.php?i=dkp_item&mode=additem&item=2
	 * @usedby acp_dkp_item.php acp_dkp_raid.php
	 * @param int $raid_id
	 * @param int $item_id
	 */
	public function displayloot($raid_id=0, $item_id=0)
	{
		global $db, $user, $config, $template, $phpEx, $phpbb_root_path, $phpbb_admin_path;

		if ($raid_id == 0 && $item_id != 0)
		{
			$raid_id= $db->sql_fetchfield('SELECT raid_id from ' . RAID_ITEMS_TABLE . ' WHERE item_id = ' . $item_id, 'raid_id');
		}

		$raid = new \bbdkp\controller\raids\Raids($raid_id);


		if (!class_exists('\bbdkp\controller\raids\Raiddetail'))
		{
			require("{$phpbb_root_path}includes/bbdkp/controller/raids/Raiddetail.$phpEx");
		}

		$raiddetail = new \bbdkp\controller\raids\Raiddetail($raid_id);
		$guild_id = 0;
		foreach ((array) $raiddetail->raid_details as $member_id => $rd)
		{
			$template->assign_block_vars (
				'raiders_row', array (
					'VALUE' => $rd['member_id'],
					'OPTION' => $rd['member_name'] ) );
			if($guild_id==0)
			{
				$guild_id=$rd['member_guild_id'];
			}
		}

		$template->assign_vars ( array (
			'ITEMTITLE'		=> sprintf($user->lang['LOOTADD'], $raid->event_name, $user->format_date($raid->raid_start)  ) ,
			'S_ADD' 		=> true,
			'ITEM_RAIDID'	=> $raid_id,
			'ITEM_DKPID'	=> $raid->event_dkpid,
			'U_BACK'			=> append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_raid&amp;mode=editraid&amp;". URI_RAID . "=" .$raid_id ),
			'L_TITLE' 			=> $user->lang ['ACP_ADDITEM'],
			'L_EXPLAIN' 		=> $user->lang ['ACP_ADDITEM_EXPLAIN'],
			'F_ADD_ITEM' 		=> append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_item&amp;mode=additem&amp;" . URI_RAID . '=' . $raid_id ),
			'ITEM_VALUE' 		=> isset($this->item['item_value']) ? $this->item['item_value'] : 0.00,
			'S_SHOWZS' 			=> ($config['bbdkp_zerosum'] == '1') ? true : false,
			'S_SHOWDECAY' 		=> ($config['bbdkp_decay'] == '1') ? true : false,

			'MSG_NAME_EMPTY' 	=> $user->lang ['FV_REQUIRED_ITEM_NAME'],
			'MSG_VALUE_EMPTY' 	=> $user->lang ['FV_REQUIRED_VALUE'],

			'LA_ALERT_AJAX'		  => $user->lang['ALERT_AJAX'],
			'LA_ALERT_OLDBROWSER' => $user->lang['ALERT_OLDBROWSER'],
			'LA_MSG_NAME_EMPTY'	  => $user->lang['FV_REQUIRED_NAME'],
			'UA_FINDITEMS'		  => append_sid($phpbb_admin_path . "style/dkp/finditem.$phpEx"),
			'ADMPATH' 			  => $phpbb_admin_path)
		);

		$catalogue = $this->loot->browseLoot($guild_id);
		foreach($catalogue as $key=>$stuffname)
		{
			$template->assign_block_vars (
					'items_row', array (
						'VALUE' =>  $key,
						'OPTION' => $stuffname ) );
		}

		if($item_id != 0)
		{
			//left pane
			$item_ids = $this->loot->GetGroupLoot($item_id);
			$buyers = array();
			foreach($item_ids as $i)
			{
				$this->loot->Getloot($i);
				$buyers[$this->loot->member_id] = $this->loot->member_name;
			}

			$buyers = array_unique($buyers);
			foreach($buyers as $buyer_id => $buyer_name)
			{
				$template->assign_block_vars (
					'buyers_row', array (
							'VALUE' => $buyer_id,
							'OPTION' => $buyer_name) );
			}


			if ( $this->loot->game_id == 'wow' )
			{
				$this->bbtips = true;
				if(is_numeric($this->loot->wowhead_id))
				{
					//$item_parsed = $bbtips->parse('[itemdkp]' . $this->loot->wowhead_id . '[/itemdkp]');
				}
				else
				{
					//$item_parsed = $bbtips->parse('[itemdkp]' . $this->loot->item_name  . '[/itemdkp]');
				}

			}

			$template->assign_vars ( array (
					'ITEMTITLE'		=> sprintf($user->lang['LOOTUPD'], $raid->event_name, $user->format_date($raid->raid_start)   ) ,
					'RAID_DATE'		=> $raid->raid_start,
					'ITEM_ZS'		=> ($this->loot->item_zs == 1) ? ' checked="checked"' : '',
					'ITEM_NAME' 	=> $this->loot->item_name ,
					'ITEM_GAMEID' 	=> $this->loot->game_id,
					'ITEM_WOWID' 	=> $this->loot->wowhead_id,
					'ITEM_DECAY'	=> sprintf("%.2f",  $this->loot->item_decay ),
					'ITEM_VALUE'	=> sprintf("%.2f",  $this->loot->item_value),
					'ITEM_ID' 		=> $item_id,
					'ITEM_DKPID'	=> $raid->event_dkpid,
					'ITEM_RAIDID'	=> $raid_id,
					'S_ADD' 		=> false,

			));

			//populate loot browser

		}
	}

	/**
	 * prepare Raid loot listing for ACP
	 * @param int $dkpsys_id
	 * @param int $raid_id
	 * @param string $order
	 * @return unknown
	 */
	public function listRaidLoot($dkpsys_id, $raid_id, $order)
	{
		global $db;
		//prepare item list sql
		$sql_array = array(
				'SELECT'    => 'd.dkpsys_name, i.item_id, i.item_name, i.item_gameid, e.event_dkpid, i.wowhead_id,
	    				i.member_id, l.member_name, c.colorcode, c.imagename, i.item_date, i.raid_id, i.item_value, e.event_name ',
				'FROM'      => array(
						DKPSYS_TABLE   => 'd',
						EVENTS_TABLE   => 'e',
						RAIDS_TABLE    => 'r',
						MEMBER_LIST_TABLE => 'l',
						CLASS_TABLE 		=> 'c',
						RAID_ITEMS_TABLE    => 'i',
				),
				'WHERE'     =>  'c.class_id = l.member_class_id
	    				and d.dkpsys_id = e.event_dkpid
    					and e.event_id = r.event_id
    					and i.member_id = l.member_id
    					AND l.game_id = c.game_id
    					and r.raid_id = i.raid_id
	     				and d.dkpsys_id = ' . $dkpsys_id . '
	     				AND i.raid_id = ' .  $raid_id,
				'ORDER_BY'  => $order,
		);

		$sql = $db->sql_build_query('SELECT', $sql_array);
		$items_result = $db->sql_query ( $sql );
		return $items_result;
	}


    /**
     * adds loot to a group of buyers
     *
     * @param int $raid_id
     * @param array $item_buyers
     * @param float $item_value
     * @param string $item_name
     * @param int $loottime optional
     * @param int $wowhead_id
     * @return boolean
     */
	public function addloot($raid_id, $item_buyers, $item_value, $item_name, $loottime = 0, $wowhead_id=0)
	{
		global $user, $config;

		$this->loot = new \bbdkp\controller\loot\Loot();
		$this->loot->raid_id = $raid_id;
		$this->loot->item_value = $item_value;
		$this->loot->item_name = $item_name;
		$this->loot->wowhead_id = $wowhead_id;
		$raid = new \bbdkp\controller\raids\Raids($raid_id);
		$this->loot->dkpid = $raid->event_dkpid;
		$this->loot->item_date = $loottime;
		if($loottime == 0)
		{
			$this->loot->item_date = $raid->raid_start;
		}
		$this->loot->item_group_key = $this->gen_group_key ( $this->loot->item_name, $this->loot->item_date, $raid_id + rand(10,100) );
		$this->loot->item_added_by = (string) $user->data ['username'];

		$PointsController = new \bbdkp\controller\points\PointsController($raid->event_dkpid);
		$decayarray = array(0.0, 0);
		if ($config['bbdkp_decay'] == '1')
		{
			//diff between now and the raidtime
			$now = getdate();
			$timediff = mktime($now['hours'], $now['minutes'], $now['seconds'], $now['mon'], $now['mday'], $now['year']) - $this->loot->item_date;
			$decayarray = $PointsController->decay($this->loot->item_value,$timediff,2 );
		}
		$this->loot->item_decay = $decayarray[0];
		$this->loot->decay_time = $decayarray[1];

		// Add purchase(s) to items table
		$buyernames = '';
		foreach ( $item_buyers as $key => $this_member_id )
		{
			$buyer = new \bbdkp\controller\members\Members($this_member_id);
			$this->loot->member_id = $this_member_id;
			$this->loot->member_name = $buyer->member_name;
			$this->loot->game_id = $buyer->game_id;
			if($config['bbdkp_zerosum'] == 1 )
			{
				$this->loot->item_zs = 1;
			}
			$this->loot->insert();

			$PointsController->addloot_update_dkprecord($this->loot->item_value, $this_member_id);
			if($buyernames != '')
            {
               $buyernames .= ', ';
            }
			$buyernames .= $buyer->member_name;
			unset ($buyer);
		}

		//if zerosum flag is set and  then distribute item value over raiders
		// item decay is not redistributed to zerosum earnings, because that is depreciated using raid decay
		if($config['bbdkp_zerosum'] == 1 )
		{
			foreach ( $item_buyers as $key => $this_member_id )
			{
				$PointsController->zero_balance($this_member_id, $raid_id, $this->loot->item_value);
			}
		}

		unset ($raid);

		// Logging
		$log_action = array (
				'header' 		=> 'L_ACTION_ITEM_ADDED',
				'L_NAME' 		=> $item_name,
				'L_BUYERS' 		=> $buyernames,
				'L_RAID_ID' 	=> $raid_id,
				'L_VALUE'   	=> $item_value ,
				'L_ADDED_BY' 	=> $user->data['username']);

		$this->log_insert ( array (
				'log_type' => $log_action ['header'],
				'log_action' => $log_action ) );

		return true;

	}

	/**
	 * Delete one loot
	 * @param int $item_id
	 * @return boolean
	 */
	public function deleteloot($item_id)
	{
		global $config;
		$this->loot = new \bbdkp\controller\loot\Loot($item_id);
		$this->loot->delete_loot();

		$raid = new \bbdkp\controller\raids\Raids($this->loot->raid_id);
		$PointsController = new \bbdkp\controller\points\PointsController($raid->event_dkpid);
		$PointsController->removeloot_update_dkprecord($this->loot);

		if($config['bbdkp_zerosum'] == 1)
		{
			$PointsController->zero_balance_delete($this->loot);
		}

		$log_action = array (
				'header' 	=> 'L_ACTION_ITEM_DELETED',
				'L_NAME' 	=> $this->loot->item_name,
				'L_BUYER' 	=> $this->loot->member_name,
				'L_RAID_ID' => $this->loot->raid_id,
				'L_VALUE' 	=> $this->loot->item_value );

		$this->log_insert ( array (
				'log_type' 		=> $log_action ['header'],
				'log_action' 	=> $log_action ) );

		return true;

	}


	/**
	 * delete raid from loot table
	 * @param int $raid_id
	 * @uses \bbdkp\controller\loot\Loot::delete_raid()
	 */
	public function delete_raid($raid_id)
	{
		$this->loot = new \bbdkp\controller\loot\Loot();
		$this->loot->raid_id = $raid_id;
		$this->loot->delete_raid();

	}

    /**
     * update loot
     *
     * @param int $item_id
     * @param int $dkp_id
     * @param int $raid_id
     * @param array $item_buyers
     * @param float $item_value
     * @param string $item_name
     * @param int $loot_time
     * @param int $wowhead_id
     *
     * @todo fix this
     */
	public function updateloot($item_id, $dkp_id, $raid_id, $item_buyers, $item_value, $item_name, $loot_time, $wowhead_id=0)
	{
		global $user;

		$oldloot = new \bbdkp\controller\loot\Loot($item_id);
		$item_ids = $oldloot->GetGroupLoot($item_id);
		foreach($item_ids as $i)
		{
			$this->deleteloot($i);
		}
		$group_key = $this->gen_group_key ( $item_name, $loot_time, $item_id + rand(10,100) );
		$this->addloot($raid_id, $item_buyers, $item_value, $item_name, $loot_time, $wowhead_id);

		//
		// Logging
		//
		$log_action = array (
		'header' => 		'L_ACTION_ITEM_UPDATED',
		'L_NAME_BEFORE' 	=> $oldloot->item_name,
		'L_RAID_ID_BEFORE' 	=> $oldloot->raid_id,
		'L_VALUE_BEFORE' 	=> $oldloot->item_value,
		'L_NAME_AFTER' 		=> $item_name ,
		'L_BUYERS_AFTER' 	=> (is_array($item_buyers) ? implode ( ', ', $item_buyers  ) : trim($item_buyers)),
		'L_RAID_ID_AFTER' 	=> $raid_id ,
		'L_VALUE_AFTER' 	=> $item_value ,
		'L_UPDATED_BY' 		=> $user->data ['username'] );

		$this->log_insert ( array (
			'log_type' => $log_action ['header'],
			'log_action' => $log_action ) );
	}

	/**
	 *
	 * counts the number of loot of this attendee in raid
	 * @param int $raid_id
	 * @param int $member_id
	 * @param int $guild_id optional
	 * @return int
	 */
	public function Countloot($raid_id, $member_id, $guild_id=0 )
	{

		return $this->loot->countloot('history', $guild_id, 0, $member_id, $raid_id);
	}


	/**
	 * get item info
	 *
	 * @param int $item_id
	 * @param int $dkp_id
	 * @return multitype:string multitype:number string boolean unknown  unknown
	 */
	public function getitemdeleteinfo($item_id, $dkp_id)
	{

		global $db;

		$sql_array = array(
				'SELECT' 	=> 'i2.* , m.member_name ',
				'FROM' 		=> array(
						RAID_ITEMS_TABLE => 'i2',
						MEMBER_LIST_TABLE => 'm'
				),
				'LEFT_JOIN' => array(
						array(
								'FROM' => array(RAID_ITEMS_TABLE => 'i1'),
								'ON' => ' i1.item_group_key = i2.item_group_key '
						)),
				'WHERE' => 'i2.member_id = m.member_id AND i1.item_id= '. $item_id,
		);
		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query ( $sql );


		$item_buyers = '[';
		while ( $row = $db->sql_fetchrow ( $result ) )
		{
			$old_item[$row['item_id']] = array (
					'item_id' 		=>  (int) 	 $row['item_id'] ,
					'dkpid'			=>  $dkp_id,
					'item_name' 	=>  (string) $row['item_name'] ,
					'member_id' 	=>  (int) 	 $row['member_id'] ,
					'member_name' 	=>  (string) $row['member_name'] ,
					'raid_id' 		=>  (int) 	 $row['raid_id'],
					'item_date' 	=>  (int) 	 $row['item_date'] ,
					'item_value' 	=>  (float)  $row['item_value'],
					'item_decay' 	=>  (float)  $row['item_decay'],
					'item_zs' 		=>  (bool)   $row['item_zs'],
			);

			//for confirm question
			$item_buyers = $item_buyers . ' ' . $row['member_name'] . ' ';
			$item_name = $row['item_name'];
		}
		$db->sql_freeresult ($result);
		$item_buyers .= ']';

		return array($item_buyers, $old_item, $item_name);

	}


	/**
	 *  EPGP Guild Loot Statistics
	 *
	 * @param int $time
	 * @param int $guild_id
	 * @param boolean $query_by_pool
	 * @param int $dkp_id
	 * @param boolean $show_all
	 */
	public function EPGPMemberLootStats($time, $guild_id, $query_by_pool, $dkp_id, $show_all)
	{
		global $db, $template, $config, $phpEx, $phpbb_root_path, $user;

		/**** column sorting *****/
		$sort_order = array(
				0 => array('pr desc', 'pr'),
				1 => array('member_current desc', 'member_current'),
				2 => array('member_raidcount desc', 'member_raidcount asc'),
				3 => array('member_name asc', 'member_name desc'),
				4 => array('ep desc', 'ep'),
				5 => array('ep_per_day desc', 'ep_per_day'),
				6 => array('ep_per_raid desc', 'ep_per_raid'),
				7 => array('gp desc', 'gp'),
				8 => array('gp_per_day desc', 'gp_per_day'),
				9 => array('gp_per_raid desc', 'gp_per_raid'),
				10 => array('itemcount desc', 'itemcount')
		);

		$current_order = $this->switch_order($sort_order, 'o1');
		$sort_index = explode('.', $current_order['uri']['current']);
		$previous_source = preg_replace('/( (asc|desc))?/i', '', $sort_order[$sort_index[0]][$sort_index[1]]);
		$previous_data = '';

		// Find total # drops
		$sql_array = array (
				'SELECT' => ' count(item_id) AS items ',
				'FROM' => array (
						EVENTS_TABLE => 'e',
						RAIDS_TABLE => 'r',
						RAID_ITEMS_TABLE => 'i',
						MEMBER_LIST_TABLE => 'l'
				),
				'WHERE' => ' e.event_id = r.event_id
						AND i.raid_id = r.raid_id
						AND i.item_value != 0
						AND l.member_id = i.member_id
						AND l.member_guild_id = ' . $guild_id
		);

		if ($query_by_pool)
		{
			$sql_array['WHERE'] .= ' and e.event_dkpid = '. (int) $dkp_id . ' ';
		}

		$sql = $db->sql_build_query ( 'SELECT', $sql_array );
		$result = $db->sql_query($sql);

		$this->total_drops = (int) $db->sql_fetchfield('items');

		$db->sql_freeresult($result);

		// get raidcount
		$sql_array = array (
				'SELECT' => ' count(r.raid_id) AS raidcount ',
				'FROM' => array (
						EVENTS_TABLE => 'e',
						RAIDS_TABLE => 'r',
						RAID_DETAIL_TABLE => 'd',
						MEMBER_LIST_TABLE => 'l'
				),
				'WHERE' => ' e.event_id = r.event_id
						AND d.raid_id = r.raid_id
						AND l.member_id = d.member_id
						AND l.member_guild_id = ' . $guild_id,
				'GROUP_BY' => 'r.raid_id'
		);

		if ($query_by_pool)
		{
			$sql_array['WHERE'] .= ' AND event_dkpid = '. $dkp_id;
		}
		$sql = $db->sql_build_query ( 'SELECT', $sql_array );
		$result = $db->sql_query($sql);
		$total_raids = (int) $db->sql_fetchfield('raidcount');
		$db->sql_freeresult ( $result );

		/* loot distribution per member and class */
		/* reason for long sql is order by */
		$sql = "SELECT
			c.game_id, c.colorcode,  c.imagename, c.class_id, d.member_dkpid, l.member_id, l.member_name,
			SUM(d.member_raidcount) as member_raidcount,
			SUM(CASE WHEN x.itemcount IS NULL THEN 0 ELSE x.itemcount END) as itemcount,
			SUM(d.member_earned - d.member_raid_decay + d.member_adjustment) AS ep,
			SUM(d.member_earned - d.member_raid_decay + d.member_adjustment) / SUM(d.member_raidcount) AS ep_per_raid,
			SUM(d.member_earned - d.member_raid_decay + d.member_adjustment) / ((  SUM(- d.member_firstraid + " . $time . " ) + 86400) / 86400)  AS ep_per_day,
			SUM(d.member_spent - d.member_item_decay + ( " . max(0, $config['bbdkp_basegp']) . ")) AS gp,
			SUM(d.member_spent - d.member_item_decay + ( " . max(0, $config['bbdkp_basegp']) . ") )  / SUM(d.member_raidcount) AS gp_per_raid,
			SUM(d.member_spent - d.member_item_decay + ( " . max(0, $config['bbdkp_basegp']) . ") )  / ((  SUM(  - d.member_firstraid + " . $time ." ) + 86400) / 86400) AS gp_per_day,
			SUM(d.member_earned - d.member_raid_decay + d.member_adjustment - d.member_spent + d.member_item_decay - ( " . max(0, $config['bbdkp_basegp']) . ") ) AS member_current,
			CASE WHEN SUM(d.member_spent - d.member_item_decay) <= 0
			THEN ROUND(  SUM(d.member_earned - d.member_raid_decay + d.member_adjustment) / " . max(0, $config['bbdkp_basegp']) . " , 2)
			ELSE ROUND(  SUM(d.member_earned - d.member_raid_decay + d.member_adjustment) / SUM(" . max(0, $config['bbdkp_basegp']) ." + d.member_spent - d.member_item_decay) ,2)
			END AS pr , ((- SUM(member_firstraid) + " . $time . " ) / 86400) AS zero_check  ";

		$sql .= " FROM (". MEMBER_DKP_TABLE ." d LEFT JOIN (
			SELECT i.member_id, count(i.item_id) AS itemcount
			FROM
				(". EVENTS_TABLE." e INNER JOIN " . RAIDS_TABLE ." r ON e.event_id=r.event_id
				INNER JOIN ". RAID_ITEMS_TABLE . " i ON  r.raid_id = i.raid_id
				INNER JOIN " . MEMBER_LIST_TABLE . " l ON l.member_id = i.member_id AND l.member_guild_id =  " . $guild_id . "
				) ";

		if ($query_by_pool)
		{
			$sql .= " WHERE e.event_dkpid  = " . $dkp_id;
		}

		$sql .= " GROUP BY i.member_id
			) x
			on d.member_id = x.member_id)
			INNER JOIN " . MEMBER_LIST_TABLE . " l ON l.member_id = d.member_id  AND l.member_guild_id = " . $guild_id . "
			INNER JOIN ". CLASS_TABLE ." c ON l.member_class_id = c.class_id AND l.game_id = c.game_id
			WHERE 1=1 ";

		if ($query_by_pool)
		{
			$sql .= " AND d.member_dkpid = " . $dkp_id;
		}

		if ( ($config['bbdkp_hide_inactive'] == 1) && (!$show_all) )
		{
			$sql .= " AND l.member_status='1'";
		}

		$sql .= " GROUP BY c.game_id,  c.colorcode,  c.imagename, c.class_id, d.member_dkpid, l.member_id, l.member_name ";
		$sql .= " ORDER BY " . $current_order['sql'];

		//get total lines
		$member_count = 0;
		$members_result = $db->sql_query($sql);
		while ( $row = $db->sql_fetchrow($members_result) )
		{
			$member_count++;
		}

		$startd = request_var ( 'startdkp', 0 );
		$members_result = $db->sql_query_limit ( $sql, $config ['bbdkp_user_llimit'], $startd );
		$totalcount = $db->sql_affectedrows($members_result);

		$raid_count=  0;
		$line = 0;
		while ( $row = $db->sql_fetchrow($members_result) )
		{
			$line++;
			$raid_count += $row['member_raidcount'];
			$row['er'] = (!empty($row['er'])) ? $row['er'] : '0.00';
			$member_drop_pct = (float) ( $this->total_drops > 0 ) ? round( ( (int) $row['itemcount'] / $this->total_drops) * 100, 1 ) : 0;
			$template->assign_block_vars('stats_row', array(

					'NAME' 					=> $row['member_name'],
					'U_VIEW_MEMBER' 		=> append_sid("{$phpbb_root_path}dkp.$phpEx" , 'page=member&amp;' .URI_DKPSYS . '=' . $row['member_dkpid'] . '&amp;' . URI_NAMEID . '='.$row['member_id']),
					'COLORCODE'				=> $row['colorcode'],
					'ID'            		=> $row['member_id'],
					'COUNT'         		=> $line,
					'ATTENDED_COUNT' 		=> $row['member_raidcount'],
					'ITEM_COUNT' 			=> $row['itemcount'],
					'MEMBER_DROP_PCT'		=> sprintf("%s %%", $member_drop_pct),

					'EP_TOTAL' 				=> $row['ep'],
					'EP_PER_DAY' 			=> sprintf("%.2f", $row['ep_per_day']),
					'EP_PER_RAID' 			=> sprintf("%.2f", $row['ep_per_raid']),
					'GP_TOTAL' 				=> $row['gp'],
					'GP_PER_DAY' 			=> sprintf("%.2f", $row['gp_per_day']),
					'GP_PER_RAID' 			=> sprintf("%.2f", $row['gp_per_raid']),
					'PR'			 		=> sprintf("%.2f", ($row['pr'] == 0) ? 1: $row['pr']) ,
					'CURRENT' 				=> intval($row['member_current']),
					'C_CURRENT'				=> ($row['member_current'] > 0 ? 'positive' : 'negative'),
			)
			);
			$previous_data = $row[$previous_source];

		}

		$url = append_sid("{$phpbb_root_path}dkp.$phpEx" , 'page=stats&amp;' .URI_GUILD . '=' . $guild_id);

		if ( ($config['bbdkp_hide_inactive'] == 1) && (!$show_all) )
		{
			$footcount_text = sprintf($user->lang['STATS_ACTIVE_FOOTCOUNT'], $db->sql_affectedrows($members_result),
					'<a href="' . $url . '&amp;o1='. $current_order['uri']['current']. '&amp;show=all" class="rowfoot">');

			$dkppagination = $this->generate_pagination2(
					$url . '&amp;o1=' . $current_order ['uri'] ['current'] ,
					$member_count, $config ['bbdkp_user_llimit'], $startd, true, 'startdkp'  );

		}

		else
		{
			$footcount_text = sprintf($user->lang['STATS_FOOTCOUNT'], $db->sql_affectedrows($members_result),
					'<a href="' . $url . '&amp;o1='. $current_order['uri']['current'] . '" class="rowfoot">' );

			$dkppagination = $this->generate_pagination2($url . '&amp;o1=' . $current_order ['uri'] ['current']. '&amp;show=all' ,
					$member_count, $config ['bbdkp_user_llimit'], $startd, true, 'startdkp'  );
		}

		$db->sql_freeresult($members_result);

		/* send information to template */
		$template->assign_vars(array(
				'DKPPAGINATION' 		=> $dkppagination ,
				'O_PR' => append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=stats&amp;o1=' . $current_order['uri'][0] . '&amp;' . URI_DKPSYS . '=' . ($query_by_pool ? $dkp_id : 'All')) ,
				'O_CURRENT' => append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=stats&amp;o1=' . $current_order['uri'][1] . '&amp;' . URI_DKPSYS . '=' . ($query_by_pool ? $dkp_id : 'All')) ,
				'O_NAME'       => append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=stats&amp;o1=' . $current_order['uri'][3] . '&amp;' . URI_DKPSYS . '=' . ($query_by_pool ? $dkp_id : 'All')),
				'O_EARNED' => append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=stats&amp;o1=' . $current_order['uri'][4] . '&amp;' . URI_DKPSYS . '=' . ($query_by_pool ? $dkp_id : 'All')) ,
				'O_EARNED_PER_DAY' => append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=stats&amp;o1=' . $current_order['uri'][5] . '&amp;' . URI_DKPSYS . '=' . ($query_by_pool ? $dkp_id : 'All')) ,
				'O_EARNED_PER_RAID' => append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=stats&amp;o1=' . $current_order['uri'][6] . '&amp;' . URI_DKPSYS . '=' . ($query_by_pool ? $dkp_id : 'All')) ,
				'O_SPENT' => append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=stats&amp;o1=' . $current_order['uri'][7] . '&amp;' . URI_DKPSYS . '=' . ($query_by_pool ? $dkp_id : 'All')) ,
				'O_SPENT_PER_DAY' =>append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=stats&amp;o1=' . $current_order['uri'][8] . '&amp;' . URI_DKPSYS . '=' . ($query_by_pool ? $dkp_id : 'All')) ,
				'O_SPENT_PER_RAID' => append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=stats&amp;o1=' . $current_order['uri'][9] . '&amp;' . URI_DKPSYS . '=' . ($query_by_pool ? $dkp_id : 'All')) ,
				'O_RAIDCOUNT' => append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=stats&amp;o1=' . $current_order['uri'][2] . '&amp;' . URI_DKPSYS . '=' . ($query_by_pool ? $dkp_id : 'All')) ,
				'O_ITEMCOUNT' => append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=stats&amp;o1=' . $current_order['uri'][10] . '&amp;' . URI_DKPSYS . '=' . ($query_by_pool ? $dkp_id : 'All')) ,
				'STATS_FOOTCOUNT' 	=> $footcount_text,
				'TOTAL_RAIDS' 	=> $raid_count,
				'TOTAL_DROPS' 	=> $this->total_drops,
				'S_SHOWEPGP' 	=> ($config['bbdkp_epgp'] == '1') ? true : false,
		)
		);

	}


	/**
	 *  Guild Loot Statistics
	 * @param int $time
	 * @param int $guild_id
	 * @param boolean $query_by_pool
	 * @param int $dkp_id
	 * @param boolean $show_all
	 */
	public function MemberLootStats($time, $guild_id, $query_by_pool, $dkp_id, $show_all)
	{
		global $db, $template, $config, $phpEx, $phpbb_root_path, $user;

		/**** column sorting *****/
		$sort_order = array(
				0 => array('member_current desc', 'member_current'),
				1 => array('member_raidcount desc', 'member_raidcount asc'),
				2 => array('member_name asc', 'member_name desc'),
				3 => array('itemcount desc', 'itemcount')
		);

		$current_order = $this->switch_order($sort_order, 'o1');
		$sort_index = explode('.', $current_order['uri']['current']);
		$previous_source = preg_replace('/( (asc|desc))?/i', '', $sort_order[$sort_index[0]][$sort_index[1]]);
		$previous_data = '';

		// Find total # drops
		$sql_array = array (
				'SELECT' => ' count(item_id) AS items ',
				'FROM' => array (
						EVENTS_TABLE => 'e',
						RAIDS_TABLE => 'r',
						RAID_ITEMS_TABLE => 'i',
						MEMBER_LIST_TABLE => 'l'
				),
				'WHERE' => ' e.event_id = r.event_id
						AND i.raid_id = r.raid_id
						AND i.item_value != 0
						AND l.member_id = i.member_id
						AND l.member_guild_id = ' . $guild_id
		);

		if ($query_by_pool)
		{
			$sql_array['WHERE'] .= ' and e.event_dkpid = '. (int) $dkp_id . ' ';
		}

		$sql = $db->sql_build_query ( 'SELECT', $sql_array );
		$result = $db->sql_query($sql);

		$this->total_drops = (int) $db->sql_fetchfield('items');

		$db->sql_freeresult($result);

		// get raidcount
		$sql_array = array (
				'SELECT' => ' count(r.raid_id) AS raidcount ',
				'FROM' => array (
						EVENTS_TABLE => 'e',
						RAIDS_TABLE => 'r',
						RAID_DETAIL_TABLE => 'd',
						MEMBER_LIST_TABLE => 'l'
				),
				'WHERE' => ' e.event_id = r.event_id
						AND d.raid_id = r.raid_id
						AND l.member_id = d.member_id
						AND l.member_guild_id = ' . $guild_id,
				'GROUP_BY' => 'r.raid_id'
		);

		if ($query_by_pool)
		{
			$sql_array['WHERE'] .= ' AND event_dkpid = '. $dkp_id;
		}
		$sql = $db->sql_build_query ( 'SELECT', $sql_array );
		$result = $db->sql_query($sql);
		$total_raids = (int) $db->sql_fetchfield('raidcount');
		$db->sql_freeresult ( $result );

		/* loot distribution per member and class */
		$sql = "
		   SELECT
		   d.member_dkpid,
	       l.member_id,
	       l.member_name,
	       max(d.member_firstraid) as member_firstraid,
		   max(d.member_lastraid) as member_lastraid,
	       Sum(d.member_raidcount) AS member_raidcount,
		   Sum(d.member_raid_value - d.member_raid_decay) AS member_raid_value,
		   sum(d.member_zerosum_bonus) as member_zerosum_bonus,
	       Sum(d.member_time_bonus) AS member_time_bonus,
	       Sum(d.member_adjustment - d.adj_decay) AS member_adjustment,
	       Sum(d.member_spent - d.member_item_decay) as member_spent,
	       Sum(CASE  WHEN x.itemcount IS NULL THEN 0 ELSE x.itemcount end) AS itemcount,
		   sum((d.member_earned - d.member_raid_decay) + (d.member_adjustment - d.adj_decay) - (d.member_spent - d.member_item_decay)  ) AS member_current ";

		$sql .= " FROM ". MEMBER_DKP_TABLE ." d LEFT JOIN (
			SELECT i.member_id, count(i.item_id) AS itemcount
			FROM
			". EVENTS_TABLE." e INNER JOIN " . RAIDS_TABLE ." r ON e.event_id=r.event_id
			INNER JOIN ". RAID_ITEMS_TABLE . " i ON  r.raid_id = i.raid_id
			INNER JOIN " . MEMBER_LIST_TABLE . " l ON l.member_id = i.member_id AND l.member_guild_id =  " . $guild_id;
			if ($query_by_pool)
			{
				$sql .= " WHERE e.event_dkpid  = " . $dkp_id;
			}
			$sql .= " GROUP BY i.member_id
				) x
				on d.member_id = x.member_id
				INNER JOIN " . MEMBER_LIST_TABLE . " l ON l.member_id = d.member_id  AND l.member_guild_id = " . $guild_id . "
				INNER JOIN ". CLASS_TABLE ." c ON l.member_class_id = c.class_id AND l.game_id = c.game_id
				WHERE 1=1 ";

		if ($query_by_pool)
		{
			$sql .= " AND d.member_dkpid = " . $dkp_id;
		}

		if ( ($config['bbdkp_hide_inactive'] == 1) && (!$show_all) )
		{
			$sql .= " AND l.member_status='1'";
		}

		$sql .= " GROUP BY d.member_dkpid, l.member_id, l.member_name ";
		$sql .= " ORDER BY " . $current_order['sql'];

		//get total lines
		$member_count = 0;
		$members_result = $db->sql_query($sql);
		while ( $row = $db->sql_fetchrow($members_result) )
		{
			$member_count++;
		}

		$startd = request_var ( 'startdkp', 0 );
		$members_result = $db->sql_query_limit ( $sql, $config ['bbdkp_user_llimit'], $startd );
		$totalcount = $db->sql_affectedrows($members_result);


		$raid_count=  0;
		$line = 0;
		while ( $row = $db->sql_fetchrow($members_result) )
		{
			$member = new \bbdkp\controller\members\Members($row['member_id']);
			$line++;
			$raid_count += $row['member_raidcount'];
			$row['earned_per_day'] = ( ( (!empty($row['earned_per_day']) ) && ( $row['zero_check'] > 0.01) )) ? $row['earned_per_day'] : '0.00';
			$row['earned_per_raid'] = (!empty($row['earned_per_raid'])) ? $row['earned_per_raid'] : '0.00';
			$row['spent_per_day'] = ( ( (!empty($row['spent_per_day']) ) && ($row['zero_check'] > 0.01) )) ? $row['spent_per_day'] : '0.00';
			$row['spent_per_raid'] = (!empty($row['spent_per_raid'])) ? $row['spent_per_raid'] : '0';
			$member_drop_pct = (float) ( $this->total_drops > 0 ) ? round( ( (int) $row['itemcount'] / $this->total_drops) * 100, 1 ) : 0;

			$template->assign_block_vars('stats_row', array(
					'NAME' 					=> $member->member_name,
					'U_VIEW_MEMBER' 		=> append_sid("{$phpbb_root_path}dkp.$phpEx" , 'page=member&amp;' .URI_DKPSYS . '=' . $row['member_dkpid'] . '&amp;' . URI_NAMEID . '='.$row['member_id']),
					'COLORCODE'				=> $member->colorcode,
					'ID'            		=> $row['member_id'],
					'COUNT'         		=> $line,
					'ATTENDED_COUNT' 		=> $row['member_raidcount'],
					'ITEM_COUNT' 			=> $row['itemcount'],
					'MEMBER_DROP_PCT'		=> sprintf("%s %%", $member_drop_pct),
					'CURRENT' 				=> intval($row['member_current']),
					'C_CURRENT'				=> ($row['member_current'] > 0 ? 'positive' : 'negative'),
			)
			);
			$previous_data = $row[$previous_source];

		}

		$url = append_sid("{$phpbb_root_path}dkp.$phpEx" , 'page=stats&amp;' .URI_GUILD . '=' . $guild_id);

		if ( ($config['bbdkp_hide_inactive'] == 1) && (!$show_all) )
		{
			$footcount_text = sprintf($user->lang['STATS_ACTIVE_FOOTCOUNT'], $db->sql_affectedrows($members_result),
					'<a href="' . $url . '&amp;o1='. $current_order['uri']['current']. '&amp;show=all" class="rowfoot">');

			$dkppagination = $this->generate_pagination2(
					$url . '&amp;o1=' . $current_order ['uri'] ['current'] ,
					$member_count, $config ['bbdkp_user_llimit'], $startd, true, 'startdkp'  );

		}

		else
		{
			$footcount_text = sprintf($user->lang['STATS_FOOTCOUNT'], $db->sql_affectedrows($members_result),
					'<a href="' . $url . '&amp;o1='. $current_order['uri']['current'] . '" class="rowfoot">' );

			$dkppagination = $this->generate_pagination2($url . '&amp;o1=' . $current_order ['uri'] ['current']. '&amp;show=all' ,
					$member_count, $config ['bbdkp_user_llimit'], $startd, true, 'startdkp'  );
		}

		$db->sql_freeresult($members_result);

		$sort_order = array(
				0 => array('member_current desc', 'member_current'),
				1 => array('member_raidcount desc', 'member_raidcount asc'),
				2 => array('member_name asc', 'member_name desc'),
				3 => array('itemcount desc', 'itemcount')
		);


		/* send information to template */
		$template->assign_vars(array(
				'DKPPAGINATION' 	=> $dkppagination ,
				'O_CURRENT' 		=> append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=stats&amp;o1=' . $current_order['uri'][1] . '&amp;' . URI_DKPSYS . '=' . ($query_by_pool ? $dkp_id : 'All')) ,
				'O_NAME'       		=> append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=stats&amp;o1=' . $current_order['uri'][2] . '&amp;' . URI_DKPSYS . '=' . ($query_by_pool ? $dkp_id : 'All')),
				'O_EARNED' 			=> append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=stats&amp;o1=' . $current_order['uri'][0] . '&amp;' . URI_DKPSYS . '=' . ($query_by_pool ? $dkp_id : 'All')) ,
				/*'O_EARNED_PER_DAY' 	=> append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=stats&amp;o1=' . $current_order['uri'][5] . '&amp;' . URI_DKPSYS . '=' . ($query_by_pool ? $dkp_id : 'All')) ,
				'O_EARNED_PER_RAID' => append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=stats&amp;o1=' . $current_order['uri'][6] . '&amp;' . URI_DKPSYS . '=' . ($query_by_pool ? $dkp_id : 'All')) ,
				'O_SPENT' 			=> append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=stats&amp;o1=' . $current_order['uri'][7] . '&amp;' . URI_DKPSYS . '=' . ($query_by_pool ? $dkp_id : 'All')) ,
				'O_SPENT_PER_DAY' 	=> append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=stats&amp;o1=' . $current_order['uri'][8] . '&amp;' . URI_DKPSYS . '=' . ($query_by_pool ? $dkp_id : 'All')) ,
				'O_SPENT_PER_RAID'  => append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=stats&amp;o1=' . $current_order['uri'][9] . '&amp;' . URI_DKPSYS . '=' . ($query_by_pool ? $dkp_id : 'All')) ,
				*/
				'O_RAIDCOUNT' 		=> append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=stats&amp;o1=' . $current_order['uri'][1] . '&amp;' . URI_DKPSYS . '=' . ($query_by_pool ? $dkp_id : 'All')) ,
				'O_ITEMCOUNT' 		=> append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=stats&amp;o1=' . $current_order['uri'][3] . '&amp;' . URI_DKPSYS . '=' . ($query_by_pool ? $dkp_id : 'All')) ,
				'STATS_FOOTCOUNT' 	=> $footcount_text,
				'TOTAL_RAIDS' 		=> $raid_count,
				'TOTAL_DROPS' 		=> $this->total_drops,
				'S_SHOWEPGP' 		=> ($config['bbdkp_epgp'] == '1') ? true : false,
		)
		);

	}


	/**
	 * Viewraid and Stats Class loot Statistics
	 *
	 * @param \bbdkp\controller\raids\Raids $raid
	 * @param int $guild_id
	 * @param bool $query_by_pool
	 * @param int $dkp_id
	 * @param bool $show_all
	 */
	public function ClassLootStats( \bbdkp\controller\raids\Raids $raid = NULL, $guild_id = 0, $query_by_pool=true, $dkp_id = 0, $show_all = false)
	{
		global $db, $config, $template, $phpEx, $phpbb_root_path;

		if($raid == NULL)
		{
			// Find total # members with a dkp record
			// get raidcount
			$sql_array = array (
					'SELECT' => ' count(m.member_id) AS members ',
					'FROM' => array (
							MEMBER_DKP_TABLE => 'm',
							MEMBER_LIST_TABLE => 'l'
					),
					'WHERE' => ' l.member_id = m.member_id
							 AND l.member_guild_id = ' . $guild_id,
			);

			if ($query_by_pool)
			{
				$sql_array['WHERE'] .= ' AND m.member_dkpid = '. $dkp_id;
			}

			if ( ($config['bbdkp_hide_inactive'] == 1) && (!$show_all) )
			{
				$sql_array['WHERE'] .= " AND l.member_status='1'";
			}

			$sql = $db->sql_build_query ( 'SELECT', $sql_array );
			$result = $db->sql_query($sql);
			$total_members = (int) $db->sql_fetchfield('members');
		}
		else
		{
			//find raid attendees
			$total_members = count($raid->raid_details);
		}

		if($raid == NULL)
		{
			// Find total # drops
			$sql_array = array (
					'SELECT' => ' count(item_id) AS items ',
					'FROM' => array (
							EVENTS_TABLE => 'e',
							RAIDS_TABLE => 'r',
							RAID_ITEMS_TABLE => 'i',
							MEMBER_LIST_TABLE => 'l'
					),
					'WHERE' => ' e.event_id = r.event_id
							AND i.raid_id = r.raid_id
							AND i.item_value != 0
							AND l.member_id = i.member_id
							AND l.member_guild_id = ' . $guild_id
			);

			if ($query_by_pool)
			{
				$sql_array['WHERE'] .= ' and e.event_dkpid = '. (int) $dkp_id . ' ';
			}

			$sql = $db->sql_build_query ( 'SELECT', $sql_array );
			$result = $db->sql_query($sql);

			$total_drops = (int) $db->sql_fetchfield('items');

		}
		else
		{
			// find drops from raid
			$total_drops = count($raid->loot_details);
		}


		// get #classcount, #drops per class

		$sql = "SELECT
			c1.name as class_name, c.class_id, c.game_id, c.colorcode,  c.imagename,
			count(c.class_id) AS class_count, SUM(CASE WHEN x.itemcount is NULL THEN 0 ELSE x.itemcount END) AS itemcount
			FROM " . MEMBER_DKP_TABLE . " d ";

		if($raid != NULL)
		{
			$sql .= ' INNER JOIN ' . RAID_DETAIL_TABLE . ' rd ON d.member_id = rd.member_id AND rd.raid_id = ' . $raid->raid_id;
		}

		$sql .=	" INNER JOIN " . MEMBER_LIST_TABLE . " l on l.member_id = d.member_id  ";

		if ($guild_id > 0)
		{
			$sql .= " AND l.member_guild_id = " . $guild_id;
		}

		$sql .= " INNER JOIN " . CLASS_TABLE ." c on l.member_class_id = c.class_id and l.game_id = c.game_id

			INNER JOIN " . BB_LANGUAGE . " c1 ON c.game_id = c1.game_id AND c1.attribute_id = c.class_id AND c1.language= '" . $config['bbdkp_lang'] . "' AND c1.attribute = 'class'
		";

		$sql .= " LEFT JOIN ";

		$sql .= "(
			SELECT i.member_id, count(i.item_id) AS itemcount
			FROM
			" . EVENTS_TABLE ." e INNER JOIN ". RAIDS_TABLE ." r ON e.event_id=r.event_id
			INNER JOIN " . RAID_ITEMS_TABLE . " i ON r.raid_id = i.raid_id  ";

			if($raid != NULL)
			{
				$sql .= " AND i.raid_id = " . $raid->raid_id;
			}

			if ($guild_id > 0)
			{
				$sql .= " INNER JOIN " . MEMBER_LIST_TABLE . " lg on lg.member_id = i.member_id AND lg.member_guild_id= " . $guild_id ;
			}

			if ($query_by_pool)
			{
				$sql .= ' WHERE e.event_dkpid = '. $dkp_id . ' ';
			}

			$sql .= " GROUP BY i.member_id
		) x
		ON d.member_id = x.member_id ";

		if ($query_by_pool)
		{
			$sql .= ' WHERE d.member_dkpid = '. $dkp_id . ' ';
		}

		if ( ($config['bbdkp_hide_inactive'] == 1) && (!$show_all) )
		{
			$sql .= " AND l.member_status='1'";
		}


		$sql .= " GROUP BY c.game_id,  c.colorcode,  c.imagename, c.class_id , c1.name ";

		$result = $db->sql_query($sql);

		$class_drop_pct_cum = 0;
		$classname_g = array();
		$class_drop_pct_g = array();
		$classpct_g = array();
		$classcount=0;
		while ($row = $db->sql_fetchrow($result) )
		{
			$classcount++;
			$classname_g[] = $row['class_name'];
			// get class count and pct
			$class_count = $row['class_count'];
			$classpct = (float) ($total_members > 0) ? round(($row['class_count'] / $total_members) * 100,1)  : 0;
			$classpct_g[] = $classpct;

			// get drops per class and pct
			$loot_drops = (int) $row['itemcount'];
			$class_drop_pct = (float) ( $total_drops > 0 ) ? round( ( (int) $row['itemcount'] / $total_drops) * 100, 1 ) : 0;
			$class_drop_pct_g[] = $class_drop_pct;
			$class_drop_pct_cum +=  $class_drop_pct;

			$lootoverrun =  ($class_drop_pct - $classpct);

			if ($query_by_pool)
			{
				$lmlink =  append_sid("{$phpbb_root_path}dkp.$phpEx" , 'page=standings&amp;filter='. $row['game_id'].'_class_' . $row['class_id'] . '&amp;' . URI_DKPSYS .'=' . $dkp_id);
			}
			else
			{
				$lmlink =  append_sid("{$phpbb_root_path}dkp.$phpEx" , 'page=standings&amp;filter='. $row['game_id'] .'_class_' . $row['class_id']);
			}

			$template->assign_block_vars('class_row', array(
					'U_LIST_MEMBERS' 	=> $lmlink ,
					'COLORCODE'  		=> ($row['colorcode'] == '') ? '#254689' : $row['colorcode'],
					'CLASS_IMAGE' 		=> (strlen($row['imagename']) > 1) ? $phpbb_root_path . "images/bbdkp/class_images/" . $row['imagename'] . ".png" : '',
					'S_CLASS_IMAGE_EXISTS' => (strlen($row['imagename']) > 1) ? true : false,
					'CLASS_NAME'		=> $row['class_name'],
					'CLASS_COUNT' 		=> (int) $class_count,
					'CLASS_PCT' 		=> $classpct,
					'CLASS_PCT_STR' 	=> sprintf("%s %%", $classpct ),
					'LOOT_COUNT' 		=> $loot_drops,
					'CLASS_DROP_PCT'	=> $class_drop_pct,
					'CLASS_DROP_PCT_STR' => sprintf("%s %%", $class_drop_pct  ),
					'C_LOOT_FACTOR'		=> ($lootoverrun < 	0) ? 'negative' : 'positive',
					'LOOTOVERRUN'		=> sprintf("%s %%", $lootoverrun),
			)
			);
		}

		/* send information to template */
		$template->assign_vars(array(
				'CLASSPCTCUMUL'		=> round($class_drop_pct_cum),
		)
		);


	}

}

?>