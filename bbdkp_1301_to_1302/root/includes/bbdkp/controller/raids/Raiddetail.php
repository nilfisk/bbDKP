<?php
/**
 * 
 * Raiddetail Class
 * 
 *   @package bbdkp
 * @link http://www.bbdkp.com
 * @author Sajaki@gmail.com
 * @copyright 2013 bbdkp
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.3.0
 * @since 1.3.0
 */
namespace bbdkp\controller\Raids;
/**
 * @ignore
 */
if (! defined('IN_PHPBB'))
{
	exit();
}

/**
 * Raid detail table Class
 * 
*   @package bbdkp
 */
class Raiddetail
{
	/**
	 * Raid pk
	 * @var Int
	 */
	public $raid_id;
	/**
	 * member id
	 * @var int
	 */ 
	public $member_id;
	/**
	 * guild of the attendee
	 * @var int
	 */
	public $member_guild_id;
	/**
	 * event value of this raid
	 * @var float
	 */
	public $raid_value; 
	/**
	 * time bonus earned
	 * @var float
	 */
	public $time_bonus;
	/**
	 * Zero sum value earned on loot
	 * @var float
	 */
	public $zerosum_bonus;
	/**
	 * raid decay on sum of raiddertail, timebonus, zerosumbonus 
	 * @var float
	 */ 
	public $raid_decay;
	/**
	 * time of decay
	 * @var int
	 */
	public $raid_decay_time;
	/**
	 * dkp pool id
	 * @var int
	 */
	public $dkpid;
	/**
	 * name of member account
	 * @var String
	 */
	public $member_name;
	/**
	 * hexadecimal value of class color
	 * @var string
	 */
	public $colorcode;
	/**
	 * class image
	 * @var string
	 */
	public $imagename;
	/**
	 * array of guildmembers not attending the raid
	 * @var array
	 */
	public $nonattendees; 
	
	/**
	 * array with attendees for one raid
	 * @var array
	 */
	public $raid_details;

    /**
     * Constructor
     * @param int|number $raid_id
     */
	public function __construct($raid_id=0) 
	{
		if ($raid_id > 0)
		{
			$this->raid_id = $raid_id;
			$this->Get($raid_id); 
		}
	}


    /**
     *
     * gets detail for one member or for all
     * @param number $raid_id
     * @param int|number $member_id
     * @param string $order
     * @return multitype:
     */
	public function Get($raid_id, $member_id = 0, $order = ' m.member_id' )
	{
		global $config, $db;

		$sql_array = array(
				'SELECT'    => 'm.member_id ,m.member_name, c.colorcode, c.imagename, l.name, m.member_gender_id, a.image_female, a.image_male, m.member_guild_id,
    						r.raid_value, r.time_bonus, r.zerosum_bonus, r.decay_time,
    						r.raid_decay, (r.raid_value + r.time_bonus + r.zerosum_bonus - r.raid_decay) as total  ',
				'FROM'      => array(
						MEMBER_LIST_TABLE 	=> 'm',
						RACE_TABLE  		=> 'a',
						RAID_DETAIL_TABLE   => 'r',
						CLASS_TABLE 		=> 'c',
						BB_LANGUAGE 		=> 'l',
				),
		
				'WHERE'     =>  " c.class_id = m.member_class_id and c.game_id = m.game_id
    						AND c.class_id = l.attribute_id and l.game_id = c.game_id AND l.attribute='class'
							AND m.member_race_id =  a.race_id and m.game_id = a.game_id
							AND l.language= '" . $config['bbdkp_lang'] ."'
							AND m.member_id = r.member_id and r.raid_id = " . (int) $raid_id  ,
				'ORDER_BY' 	=>  $order,
		);
		
		if($member_id > 0)
		{
			$sql_array['WHERE'] .= " AND m.member_id = '" . $member_id ."'"; 
		}
		
		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query ( $sql );
		$this->raid_details = array ();
		while ( $row = $db->sql_fetchrow ( $result ) )
		{
			
			$race_image = (string) (($row['member_gender_id']==0) ? $row['image_male'] : $row['image_female']);
		
			$this->raid_details[$row['member_id']]['member_id'] = $row['member_id'];
			$this->raid_details[$row['member_id']]['member_guild_id'] = $row['member_guild_id'];
			$this->raid_details[$row['member_id']]['colorcode'] = $row['colorcode'];
			$this->raid_details[$row['member_id']]['imagename'] = $row['imagename'];
			$this->raid_details[$row['member_id']]['classname'] = $row['name'];
			$this->raid_details[$row['member_id']]['raceimage'] = $race_image;
			$this->raid_details[$row['member_id']]['member_name'] = $row['member_name'];
			$this->raid_details[$row['member_id']]['raid_value'] = $row['raid_value'];
			$this->raid_details[$row['member_id']]['time_bonus'] = $row['time_bonus'];
			$this->raid_details[$row['member_id']]['zerosum_bonus'] = $row['zerosum_bonus'];
			$this->raid_details[$row['member_id']]['raid_decay'] = $row['raid_decay'];
			
			if($member_id > 0)
			{
				$this->member_id = $row['member_id'];
				$this->member_guild_id = $row['member_guild_id'];
				$this->colorcode = $row['colorcode'];
				$this->imagename = $row['imagename'];
				$this->member_name = $row['member_name'];
				$this->raid_value = $row['raid_value'];
				$this->time_bonus = $row['time_bonus'];
				$this->zerosum_bonus = $row['zerosum_bonus'];
				$this->raid_decay = $row['raid_decay'];
				$this->raid_decay_time = $row['decay_time']; 
			}
		}
		
		return $this->raid_details;
		
	}
	
	/**
	 * inserts a raid attendee
	 */
	public function create()
	{
		global $db; 
		
		$raid_detail = array(
				'raid_id'       => (int) $this->raid_id,
				'member_id'     =>  $this->member_id,
				'raid_value'    => $this->raid_value,
				'time_bonus'    => $this->time_bonus,
				'zerosum_bonus' => $this->zerosum_bonus, 
				'raid_decay' 	=> $this->raid_decay, 
		);
		
		$sql = 'INSERT INTO ' . RAID_DETAIL_TABLE . ' ' . $db->sql_build_array('INSERT', $raid_detail);
		$db->sql_query($sql);
	}
	
	/**
	 * removes a raid attendee given member id ans raid id
	 */
	public function delete()
	{
		global $db;
		
		$sql = 'DELETE FROM ' . RAID_DETAIL_TABLE . ' WHERE raid_id= ' . $this->raid_id . ' and member_id = ' . $this->member_id ;
		$db->sql_query($sql);		
		
	}

	/**
	 * 
	 * delete attendee from raiddetail table
	 * @param number $member_id
	 * @param number $dkp_id
	 */
	public function deleteaccount($member_id, $dkp_id)
	{
		global $db;
		$sql = 'DELETE FROM ' . RAID_DETAIL_TABLE . '
				WHERE member_id= ' . $member_id . '
				AND raid_id IN ( SELECT r.raid_id
					FROM ' . RAIDS_TABLE . ' r, ' . EVENTS_TABLE . ' e
					WHERE r.event_id = e.event_id
					AND e.event_dkpid = ' . ( int ) $dkp_id . ')';
		$db->sql_query($sql);
		
	}
	
	/**
	 * deletes all attendees from a raid
	 * @param int $raid_id
	 */
	public function deleteRaid($raid_id)
	{
		global $db;
		$sql = 'DELETE FROM ' . RAID_DETAIL_TABLE . ' WHERE raid_id = ' . $raid_id;
		$db->sql_query($sql);
	}
	
	/**
	 * update a raid detail record
	 */
	public function update()
	{
		global $db, $user;
		
		$query = $db->sql_build_array ( 'UPDATE', array (
				'raid_value' => $this->raid_value,
				'time_bonus' => $this->time_bonus,
		));
		
		$sql = 'UPDATE ' . RAID_DETAIL_TABLE . ' 
				SET ' . $query . " 
				WHERE raid_id = " . ( int ) $this->raid_id . ' 
				AND member_id = ' . (int) $this->member_id;
		
		$db->sql_query ($sql);
	}
	
	/**
	 * get array of non attendees
	 */
	public function GetNonAttendees()
	{
		global $config, $db;
		
		$sql_array = array(
				'SELECT'    => 	' l.member_id, l.member_name ',
				'FROM'      => array(
						MEMBER_LIST_TABLE 		=> 'l',
						MEMBER_RANKS_TABLE    => 'r',
				),
				'WHERE'		=> ' l.member_guild_id = r.guild_id
			 AND l.member_rank_id = r.rank_id
			 AND r.rank_hide != 1
			 AND l.member_id != ' . $config['bbdkp_bankerid']  . '
			 AND NOT EXISTS ( SELECT NULL FROM ' . RAID_DETAIL_TABLE . ' ra WHERE l.member_id = ra.member_id and ra.raid_id = ' . $this->raid_id . ' ) and l.member_status = 1 ' ,
				'ORDER_BY'	=> 'member_id asc ',
		);
		
		
		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query($sql);
		$this->nonattendees = array(); 
		while ( $row = $db->sql_fetchrow($result) )
		{
			$this->nonattendees[$row['member_id']] = $row['member_name']; 
		}
		
	}
	

	
}

?>