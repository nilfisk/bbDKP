<?php
/**
 * Points class file
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
namespace bbdkp\controller\points;
/**
 * @ignore
 */
if (! defined('IN_PHPBB'))
{
	exit();
}

/**
 * Points Class
 *
 *  this class manages the points summary table where the 3
 *  transaction tables are centralised (phpbb_bbdkp_memberdkp)
 *  transaction tables : raid_detail, adjustments, items
 *
 *   @package bbdkp
 */
class Points
{
	/**
	 * pk for points class
	 * @var int
	 */
	public $member_id; // pk

	/**
	 * pk : dkp id
	 * @var int
	 */
	public $dkpid;  //pk

	/**
	 * name of dkp pool
	 * @var unknown
	 */
	public $dksys_name;

	/*
	 * Classic DKP points
	 */

	/**
	 * raid value (from event)
	 * @var float
	 */
	public $raid_value;

	/**
	 * time bonus received for being on time in raid
	 * @var float
	 */
	public $time_bonus;

	/**
	 * zerosum bonus earned when loot was given to even out debit/credit
	 * @var float
	 */
	public $zerosum_bonus;

	/**
	 * sum of raid_value, time bonus and zero sum bonus
	 * @var float
	 */
	public $total_earned;

	/**
	 * earned amount depreciation
	 * @var float
	 */
	public $earned_decay;

	/**
	 * net earned
	 * @var float
	 */
	public $earned_net;

	/**
	 * holds value spent on item purchases
	 * @var float
	 */
	public $spent;

	/**
	 * purchase depreciation
	 * @var float
	 */
	public $item_decay;

	/**
	 * net loot value
	 * @var float
	 */
	public $item_net;

	/**
	 * holds adjustment value
	 * @var float
	 */
	public $adjustment;

	/**
	 * adjustment depreciation
	 * @var float
	 */
	public $adj_decay;

	/**
	 * net adjustment
	 * @var float
	 */
	public $adj_net;

	/**
	 * sum of $earned - $spent + $adjustment,
	 * @var float
	 */
	public $total;

	/**
	 * sum of earned, spent, adjustment decay
	 * @var float
	 */
	public $total_decayed;

	/**
	 * net total after decay
	 * @var float
	 */
	public $total_net;

	/******************************
	 * EPGP points
	 */

	/**
	 * Effort Points
	 *
	 * @var float
	 */
	public $ep;

	/**
	 * Effort Points after decay
	 * @var float
	 */
	public $ep_net;

	/**
	 * Gear Points
	 * @var float
	 */
	public $gp;

	/**
	 * GP after decay
	 * @var float
	 */
	public $gp_net;

	/**
	 * ratio of Effort to Gear
	 * @var float
	 */
	public $pr;

	/**
	 * ratio of net Effort to net Gear
	 * @var float
	 */
	public $pr_net;

	/**
	 * start date of this account
	 * @var date
	 */
	public $firstraid;

	/**
	 * last raid recording
	 * @var date
	 */
	public $lastraid;

	/**
	 * number of raids attended
	 * @var int
	 */
	public $raidcount;

	/**
	 * instance of Pool class
	 * @var unknown_type
	 */
	public $pool;

    /**
     * Points Class constructor
     * @param int|number $member_id
     * @param int|number $dkpid
     */
	function __construct($member_id=0, $dkpid=0)
	{
		global $phpbb_root_path, $phpEx;
		if (!class_exists('\bbdkp\controller\points\Pool'))
		{
			require("{$phpbb_root_path}includes/bbdkp/controller/points/Pool.$phpEx");
		}

		if($member_id > 0 && $dkpid > 0)
		{
			$this->member_id = $member_id;
			$this->dkpid = $dkpid;
			$this->pool = new \bbdkp\controller\points\Pool($dkpid);
			$this->read_account();
		}
		elseif  ($dkpid > 0)
		{
			$this->dkpid = $dkpid;
			$this->pool = new \bbdkp\controller\points\Pool($dkpid);
		}

	}

	/**
	 * Chacks if user has an account
	 * @param int $member_id
	 * @param int $dkpid
	 * @return boolean
	 */
	public function has_account($member_id, $dkpid)
	{
		global $db;
		if ($member_id == 0)
		{
			return false;
		}

		$sql = 'SELECT count(member_id) as present FROM ' . MEMBER_DKP_TABLE . '
				WHERE member_id = ' . $member_id . '
				AND member_dkpid = ' . $dkpid;
		$result = $db->sql_query($sql);
		$present = (int) $db->sql_fetchfield('present', false, $result);
		$db->sql_freeresult($result);

		if($present > 0)
		{
			return true;
		}
		return false;

	}

	/**
	 * read account from an active DKP pool
	 */
	public function read_account()
	{
		global $config, $db;

		$sql_array['SELECT'] = 'm.member_id, m.member_firstraid, m.member_lastraid,
				sum(m.member_raid_value) as member_raid_value,
				sum(m.member_time_bonus) as member_time_bonus,
				sum(m.member_zerosum_bonus) as member_zerosum_bonus,
				sum(m.member_raid_decay) as member_raid_decay,
				sum(m.member_spent) as member_spent,
				sum(m.member_item_decay) as member_item_decay,
				sum(m.member_adjustment) as member_adjustment,
				sum(m.adj_decay) as adj_decay,
				sum(m.member_raidcount) as member_raidcount ';
		$sql_array['FROM'] =
            array (
                    MEMBER_DKP_TABLE 	=> 'm',
                    DKPSYS_TABLE		=> 'd',
                  );
		$sql_array['WHERE'] = " d.dkpsys_id=m.member_dkpid AND d.dkpsys_status != 'N' and m.member_id = " . (int) $this->member_id;
		if ($this->dkpid > 0)
		{
			$sql_array['SELECT'] .= ', m.member_dkpid ';
			$sql_array['WHERE'] .= ' AND m.member_dkpid = ' . (int) $this->dkpid;
		}
		$sql = $db->sql_build_query('SELECT', $sql_array);

		$result = $db->sql_query ($sql);
		while ( ($row = $db->sql_fetchrow ( $result )) )
		{
			$this->member_id = $row['member_id'];
			$this->firstraid = $row['member_firstraid'];
			$this->lastraid = $row['member_lastraid'];
			$this->raidcount = $row['member_raidcount'];
			if($config['bbdkp_epgp'] == '1')
			{

				$this->raid_value = (float)  $row['member_raid_value'];
				$this->time_bonus  = (float)  $row['member_time_bonus'];
				$this->zerosum_bonus = (float)  $row['member_zerosum_bonus'];
				$this->total_earned = $this->raid_value + $this->time_bonus + $this->zerosum_bonus;
				$this->earned_decay = (float) $row['member_raid_decay'];
				$this->earned_net =  $this->total_earned - $this->earned_decay;

				$this->spent = (float) $row['member_spent'];
				$this->item_decay = (float) $row['member_item_decay'];
				$this->item_net = $this->spent - $this->item_decay;

				$this->adjustment = (float) $row['member_adjustment'];
				$this->adj_decay = (float) $row['adj_decay'];

				$this->total = $this->total_earned - $this->spent + $this->adjustment;
				$this->total_decayed = $this->earned_decay - $this->item_decay + $this->adj_decay;

				$this->total_net = $this->total - $this->total_decayed;

				$this->ep = (float) $row['member_raid_value'] + $row['member_time_bonus'] + $row['member_adjustment'];
				$this->ep_net = (float) $this->ep - $row['member_raid_decay'];
				$this->gp = (float) $row['member_spent'] + max(0, (int) $config['bbdkp_basegp']);
				$this->gp_net = $this->gp - $row['member_item_decay'];
				if($this->gp_net == 0)
				{
					$this->pr_net = 1;
				}
				else
				{
					$this->pr_net = round($this->ep_net / $this->gp_net, 3);
				}
				if($this->gp == 0)
				{
					$this->pr = 1;
				}
				else
				{
					$this->pr = round($this->ep / $this->gp, 3);
				}
			}
			else
			{
				$this->raid_value = (float)  $row['member_raid_value'];
				$this->time_bonus  = (float)  $row['member_time_bonus'];
				$this->zerosum_bonus = (float)  $row['member_zerosum_bonus'];
				$this->total_earned = $this->raid_value + $this->time_bonus + $this->zerosum_bonus;
				$this->earned_decay = (float) $row['member_raid_decay'];
				$this->earned_net =  $this->total_earned - $this->earned_decay;

				$this->spent = (float) $row['member_spent'];
				$this->item_decay = (float) $row['member_item_decay'];
				$this->item_net = $this->spent - $this->item_decay;

				$this->adjustment = (float) $row['member_adjustment'];
				$this->adj_decay = (float) $row['adj_decay'];

				$this->total = $this->total_earned - $this->spent + $this->adjustment;
				$this->total_decayed = $this->earned_decay - $this->item_decay + $this->adj_decay;

				$this->total_net = $this->total - $this->total_decayed;
			}
		}

		$db->sql_freeresult ($result);

	}

	/**
	 * Opens a new account
	 */
	public function open_account()
	{
		global $db;

		//@todo check if pool is active before adding an account...
		if ($this->pool->dkpsys_status == 'N')
		{
			return false;
		}

		$query = $db->sql_build_array('INSERT', array(
			'member_dkpid'       	=> $this->dkpid,
			'member_id'          	=> $this->member_id,
			'member_raid_value'  	=> $this->raid_value ,
			'member_time_bonus'  	=> $this->time_bonus ,
			'member_zerosum_bonus'  => $this->zerosum_bonus,
			'member_earned'      	=> $this->raid_value + $this->time_bonus + $this->zerosum_bonus,
			'member_raid_decay' 	=> $this->earned_decay,
			'member_spent'      	=> $this->spent,
			'member_item_decay' 	=> $this->item_decay,
			'member_adjustment'     => $this->adjustment,
			'adj_decay' 			=> $this->adj_decay,
			'member_firstraid'   	=> $this->firstraid,
			'member_lastraid'    	=> $this->lastraid,
			'member_raidcount'   	=> max(0, $this->raidcount)
		));

		$db->sql_query('INSERT INTO ' . MEMBER_DKP_TABLE . $query);

        return true;
	}


	/**
	 * updates an account
	 */
	public function update_account()
	{
		global $db;

		$query = $db->sql_build_array ( 'UPDATE', array (
			'member_dkpid'       	=> $this->dkpid,
			'member_id'          	=> $this->member_id,
			'member_raid_value'  	=> $this->raid_value ,
			'member_time_bonus'  	=> $this->time_bonus ,
			'member_zerosum_bonus'  => $this->zerosum_bonus,
			'member_earned'      	=> $this->raid_value + $this->time_bonus + $this->zerosum_bonus,
			'member_raid_decay' 	=> $this->earned_decay,
			'member_spent'      	=> $this->spent,
			'member_item_decay' 	=> $this->item_decay,
			'member_adjustment'     => $this->adjustment,
			'adj_decay' 			=> $this->adj_decay,
			'member_firstraid'   	=> $this->firstraid,
			'member_lastraid'    	=> $this->lastraid,
			'member_raidcount'   	=> max(0, $this->raidcount)
		));

		$db->sql_query ( 'UPDATE ' . MEMBER_DKP_TABLE . ' SET ' . $query . "
			WHERE member_id = " . (int) $this->member_id . ' AND member_dkpid = ' . $this->dkpid );

	}

	/**
	 * deletes the account
	 */
	public function delete_account()
	{
		global $db;

		$sql = "DELETE FROM " . MEMBER_DKP_TABLE . "
				WHERE member_id = " . (int) $this->member_id . " AND member_dkpid = " . $this->dkpid ;
		$db->sql_query($sql);

	}


}

?>