<?php
/**
 * Loot class file
 *
 *   @package bbdkp
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

/**
 * this class manages the Item transaction table (phpbb_bbdkp_raid_items)
 * Items do not have to be tied to raids
 * Items can be crafted, bought from vendors or gotten from the guild bank, etc.
 * In that case the raid_id is '0'
 *
 *   @package bbdkp
 */
class Loot
{

	/**
	 * CREATE TABLE `phpbb_bbdkp_raid_items` (
		  `item_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
		  `raid_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
		  `item_name` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
		  `member_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
		  `item_date` int(11) unsigned NOT NULL DEFAULT '0',
		  `item_added_by` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
		  `item_updated_by` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
		  `item_group_key` varchar(255) COLLATE utf8_bin NOT NULL DEFAULT '',
		  `item_gameid` varchar(8) NOT NULL DEFAULT '',
		  `item_value` decimal(11,2) NOT NULL DEFAULT '0.00',
		  `item_decay` decimal(11,2) NOT NULL DEFAULT '0.00',
		  `item_zs` tinyint(1) unsigned NOT NULL DEFAULT '0',
		  `decay_time` decimal(11,2) NOT NULL DEFAULT '0.00',
		  PRIMARY KEY (`item_id`),
		  KEY `raid_id` (`raid_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
	 */


	/**
	 * primary key
	 * @var int
	 */
	public $item_id;

	/**
	 * raid in which item dropped.
	 * non unique key
	 * @var int
	 */
	public $raid_id;

	/**
	 * dkp pool
	 * @var int
	 */
	public $dkpid;

	/**
	 * given name
	 * @var string
	 */
	public $item_name;

	/**
	 * to who does it belong ? if 0 then it belongs to noone
	 * @var int
	 */
	public $member_id;

	/**
	 * name of buyer
	 * @var string
	 */
	public $member_name;

	/**
	 * when was it acquired ?
	 * @var int
	 */
	public $item_date;


	/**
	 * memberid of who added it to the inventory ?
	 * @var string
	 */
	public $item_added_by;

	/**
	 * memberid of who updated it to the inventory ?
	 * @var string
	 */
	public $item_updated_by;

	/**
	 * if item was added to multiple persons at the same time then the group_key will be shared.
	 * @var int
	 */
	public $item_group_key;

	/**
	 * what game does item belong to ?
	 * @var string
	 */
	public $game_id;

	/**
	 * wowhead id. if other game then this is the ingame id for the loot
	 * @var int
	 */
	public $wowhead_id;


	/**
	 * how much is it bought for ?
	 * prix d'achat
	 * @var float
	 */
	public $item_value;

	/**
	 * how much did it depreciate ?
	 * @var float
	 */
	public $item_decay;

	/**
	 * net value after accounting for depreciation
	 * var float
	 */
	public $item_net;

	/**
	 * if the item purchaseprice was offset by an equal "zero sum" earning then this flag is 1
	 * @var int
	 */
	public $item_zs;


	/**
	 * last time of depreciation
	 * @var int
	 */
	public $decay_time;

	/**
	 * array with loot details
	 * @var array
	 */
	public $lootdetails;

    /**
     * Loot class constructor
     * @param int|number $item_id
     */
	function __construct($item_id = 0)
	{
		if($item_id > 0)
		{
			$this->Getloot($item_id);
		}

	}


	public function GetGroupLoot($item_id)
	{
		global $db;
		$sql = 'SELECT i.item_id FROM ' . RAID_ITEMS_TABLE . ' i WHERE i.item_group_key = ( ';
		$sql .= ' SELECT a.item_group_key FROM ' . RAID_ITEMS_TABLE . ' a WHERE a.item_id = ' . (int) $item_id . ') ';
		$result = $db->sql_query ($sql);
		$item_ids= array();
		while ( $row = $db->sql_fetchrow ( $result ) )
		{
			$item_ids[]=$row['item_id'];
		}
		return $item_ids;
	}

	/**
	 * get one item from raid
	 * @uses acp
	 * @access public
	 * @param int $item_id
	 * @return  bbdkp\controller\Loot\Loot
	 */
	public function Getloot($item_id)
	{
		global $db;

		$sql = 'SELECT * FROM ' .
				RAID_ITEMS_TABLE . ' i, ' .
				MEMBER_LIST_TABLE . ' m, ' .
				RAIDS_TABLE . ' r, ' .
				EVENTS_TABLE . ' e
				WHERE i.member_id = m.member_id
				AND i.raid_id = r.raid_id
				AND r.event_id = e.event_id
				AND i.item_id= ' . (int) $item_id;

		$result = $db->sql_query ( $sql );

		while ( $row = $db->sql_fetchrow ( $result ) )
		{
			$this->item_id = $item_id;
			$this->dkpid = $row['event_dkpid'];
			$this->item_name = (string) $row['item_name'];
			$this->wowhead_id = (int) 	$row['wowhead_id'];
			$this->game_id = $row['game_id'];
			$this->member_id = (int) 	$row['member_id'];
			$this->member_name = (string)	$row['member_name'];
			$this->raid_id = (int) 	$row['raid_id'];
			$this->item_group_key = $row['item_group_key'];
			$this->item_date = (int) 	$row['item_date'];
			$this->item_value = (float) $row['item_value'];
			$this->item_decay =   (float) $row['item_decay'];
			$this->item_net =   (float) $row['item_value'] - $row['item_decay'];
			$this->item_zs = (bool)  $row['item_zs'];
			$this->item_added_by = (string)  $row['item_added_by'];
			$this->item_updated_by = (string)  $row['item_updated_by'];
		}
		$db->sql_freeresult ($result);
		return $this;

	}


    /**
     * get array of all loot for 1 guild, raid or member
     *
     * note: member_name is only included for sorting
     * @param string $order
     * @param int|number $guild_id
     * @param int|number $dkpsys_id
     * @param int|number $raid_id
     * @param int|number $istart
     * @param int|number $member_id
     * @return
     */
	public function GetAllLoot($order = ' i.item_date desc', $guild_id=0, $dkpsys_id=0, $raid_id = 0, $istart = 0, $member_id = 0)
	{
		global $config, $db;

		$sql_array = array(
				'SELECT'    => 'm.member_name, i.item_id, i.item_name, i.item_gameid, i.member_id, i.raid_id, i.item_date, e.event_name, e.event_dkpid, i.wowhead_id, e.event_color,
								SUM(i.item_value) as item_value,
								SUM(i.item_decay) as item_decay,
								SUM(i.item_value - i.item_decay) as item_net , i.item_zs',
				'FROM'      => array(
						MEMBER_LIST_TABLE   => 'm',
						RAID_ITEMS_TABLE    => 'i',
						RAIDS_TABLE 		=> 'r' ,
						EVENTS_TABLE 		=> 'e',
				),
				'WHERE'     =>  " e.event_status = 1
								  AND m.member_id = i.member_id
								  AND i.raid_id = r.raid_id
								  AND r.event_id = e.event_id ",
				'GROUP_BY'	=> 'm.member_name, i.item_id, i.item_name, i.item_gameid, i.member_id, i.raid_id, i.item_date, e.event_name, e.event_dkpid',
				'ORDER_BY'  => $order ,
		);

		if($dkpsys_id > 0)
		{
			$sql_array['WHERE'] .= ' AND e.event_dkpid=' . (int) $dkpsys_id;
		}

		if($raid_id > 0)
		{
			$sql_array['WHERE'] .= " AND i.raid_id = " . (int) $raid_id ." ";
		}

		if($member_id > 0)
		{
			$sql_array['WHERE'] .= " AND i.member_id = '" . (int) $member_id ."'";
		}

		if ($guild_id > 0)
		{
			$sql_array['WHERE'] .= ' and m.member_guild_id = ' . $guild_id . ' ';
		}

		$sql = $db->sql_build_query('SELECT', $sql_array);
		return  $db->sql_query_limit ( $sql, $config ['bbdkp_user_ilimit'], $istart );

	}

    /**
     * get all purchased items with the same name
     * @usedby views
     * @param $item_name
     * @return array
     * @internal param int $item_id
     */
	public function Loothistory($item_name)
	{
		global $db;
		$sql_array = array(
				'SELECT'    => 	'i.item_id',
				'FROM'      => array(
						EVENTS_TABLE 		=> 'e',
						RAIDS_TABLE 		=> 'r',
						CLASS_TABLE			=> 'c',
						MEMBER_LIST_TABLE 	=> 'l',
						RAID_ITEMS_TABLE 		=> 'i',
				),

				'WHERE'     =>  " e.event_id = r.event_id
						AND e.event_status = 1
    					AND r.raid_id = i.raid_id
	    				AND l.member_class_id = c.class_id
	    				AND l.game_id = c.game_id
	    				AND i.member_id = l.member_id
				        AND i.item_name='". $db->sql_escape($item_name) . "'",
				'ORDER_BY'	=> 'i.item_date desc ',
		);

		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query($sql);
		$purchased_items = array();
		while ($row = $db->sql_fetchrow($result) )
		{
			$loot = $this->Getloot($row['item_id']);
			$purchased_items[ $row['item_id']] = (array) $loot;
			unset($loot);
		}
		$db->sql_freeresult($result);
		return $purchased_items;


	}

	/**
	 * list acquired loot in left pane loot acp
	 * @usedby acp
	 * @param int $guild_id
	 * @return multitype:unknown
	 */
	public function browseLoot($guild_id)
	{
		global $db;
		$sql_array = array(
				'SELECT'    => 'MAX(i.item_id) as item_id, i.item_name, i.item_gameid, i.wowhead_id',
				'FROM'      => array(
						MEMBER_LIST_TABLE   => 'm',
						RAID_ITEMS_TABLE    => 'i',
						RAIDS_TABLE 		=> 'r' ,
						EVENTS_TABLE 		=> 'e',
				),
				'WHERE'     =>  ' e.event_status = 1
								  AND m.member_id = i.member_id
								  AND m.member_guild_id = ' . $guild_id . '
								  AND i.raid_id = r.raid_id
								  AND r.event_id = e.event_id ',
				'GROUP_BY'	=> ' i.item_name, i.item_gameid, e.event_dkpid, i.wowhead_id',
				'ORDER_BY'  => ' i.item_date ',
		);
		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query($sql);
		$catalogue = array();
		while ($row = $db->sql_fetchrow($result) )
		{
			$catalogue[ $row['item_id']]=$row['item_name'];
		}
		$db->sql_freeresult($result);
		return $catalogue;

	}

	/**
	 * insert new loot in database
	 */
	public function insert()
	{
		global $db, $config;
		// note : itemid is generated with primary key autoincrease
		$sql_ary = array (
				'item_name' 		=> (string) $this->item_name ,
				'member_id' 		=> (int) $this->member_id,
				'raid_id' 			=> (int) $this->raid_id,
				'item_value' 		=> (float) $this->item_value,
				'item_decay' 		=> (float) $this->item_decay,
				'item_date' 		=> (int) $this->item_date,
				'item_group_key' 	=> (string) $this->item_group_key,
				'item_zs'			=> (int) $config['bbdkp_zerosum'],
				'item_added_by' 	=> (string) $this->item_added_by,
				'item_gameid' 		=> $this->game_id,
				'wowhead_id'		=> $this->wowhead_id
		);

		$sql = 'INSERT INTO ' . RAID_ITEMS_TABLE . ' ' . $db->sql_build_array('INSERT', $sql_ary);

		$db->sql_query($sql);

	}

	/**
	 * remove a loot from database
	 * @usedby update()
	 */
	public function delete_loot()
	{
		global $db;
		$sql = 'DELETE FROM ' . RAID_ITEMS_TABLE . ' WHERE item_id = ' . $this->item_id ;
		$db->sql_query ($sql);
	}

	/**
	 * remove loot for raid
	 * @usedby \bbdkp\controller\loot\LootController::delete_raid()
	 */
	public function delete_raid()
	{
		global $db;
		$sql = 'DELETE FROM ' . RAID_ITEMS_TABLE . ' WHERE raid_id = ' . $this->raid_id ;
		$db->sql_query ($sql);
	}

	/**
	 * update loot in database
	 * @uses delete_loot()
	 */
	public function update()
	{
		$this->delete_loot();
		$this->insert();
	}

    /**
     * count loot per pool/guild/raid/member
     *
     * @param $mode
     * @param int $guild_id
     * @param int $dkp_id
     * @param int $member_id
     * @param int $raid_id
     * @return mixed
     */
    public function countloot($mode, $guild_id=0, $dkp_id=0, $member_id=0, $raid_id=0)
	{
		global $db;
		$sql_array = array();
		switch ($mode)
		{
			case 'values' :
				$sql_array['SELECT'] = ' COUNT(DISTINCT item_name) as itemcount ' ;
				break;
			case 'history' :
				$sql_array['SELECT'] = ' COUNT(*) as itemcount ' ;
				break;
		}

		$sql_array['FROM'] = array(
				EVENTS_TABLE 		=> 'e',
				RAIDS_TABLE 		=> 'r',
				RAID_ITEMS_TABLE 		=> 'i',
				MEMBER_LIST_TABLE		=> 'l'
		);

		$sql_array['WHERE'] = ' e.event_id = r.event_id AND r.raid_id = i.raid_id and l.member_id=i.member_id ';


		if ($guild_id > 0)
		{
			$sql_array['WHERE'] .= ' and l.member_guild_id = ' . $guild_id . ' ';
		}

		if ($dkp_id > 0)
		{
			$sql_array['WHERE'] .= ' AND e.event_dkpid = ' . $dkp_id . ' ';
		}

		if($raid_id > 0)
		{
			$sql_array['WHERE'] .= ' AND r.raid_id = ' . $raid_id;
		}

		if($member_id > 0)
		{
			$sql_array['WHERE'] .= ' AND i.member_id = ' . $member_id;
		}

		if($guild_id > 0)
		{
			$sql_array['WHERE'] .= ' AND l.member_guild_id = ' . $guild_id;
		}


		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query ( $sql);
		$total_items = $db->sql_fetchfield ( 'itemcount');
		$db->sql_freeresult ($result);
		return $total_items;

	}


    /**
     * get statistic of all loot drops
     * note: member_name is only included for sorting
     *
     * @param string $order
     * @param int|number $guild_id
     * @param int|number $dkpsys_id
     * @param int|number $raid_id
     * @param int|number $istart
     * @return array
     */
	public function Lootstat($order = ' MAX(i.item_date) desc', $guild_id=0, $dkpsys_id=0, $raid_id = 0, $istart = 0)
	{
		global $config, $db;

		$sql_array = array(
				'SELECT'    => 'i.item_name, i.item_gameid, e.event_name, e.event_dkpid,
								MAX(i.raid_id) as raid_id,
								MAX(i.item_id) as item_id,
								MAX(i.item_date) as item_date,
								COUNT(i.item_id) as dropcount,
								SUM(i.item_value) as item_value,
								SUM(i.item_decay) as item_decay,
								SUM(i.item_value - i.item_decay) as item_net ',
				'FROM'      => array(
						RAID_ITEMS_TABLE    => 'i',
						RAIDS_TABLE 		=> 'r' ,
						EVENTS_TABLE 		=> 'e',
						MEMBER_LIST_TABLE		=> 'l'
				),
				'WHERE'     =>  " e.event_status = 1
								  AND i.raid_id = r.raid_id
								  AND r.event_id = e.event_id
				 				  AND l.member_id = i.member_id ",
				'GROUP_BY'	=> 'i.item_name, i.item_gameid, e.event_name, e.event_dkpid',
				'ORDER_BY'  => $order ,
		);

		if ($guild_id > 0)
		{
			$sql_array['WHERE'] .= ' and l.member_guild_id = ' .  (int) $guild_id . ' ';
		}


		if($dkpsys_id > 0)
		{
			$sql_array['WHERE'] .= ' AND e.event_dkpid=' . (int) $dkpsys_id;
		}

		if($raid_id > 0)
		{
			$sql_array['WHERE'] .= " AND i.raid_id = " . (int) $raid_id ." ";
		}

		$sql = $db->sql_build_query('SELECT', $sql_array);
		return  $db->sql_query_limit ( $sql, $config ['bbdkp_user_ilimit'], $istart );

	}


}

?>