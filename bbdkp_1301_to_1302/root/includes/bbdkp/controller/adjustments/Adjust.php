<?php
/**
 * Adjustments class file
 *
 * @package bbdkp
 * @link 		http://www.bbdkp.com
 * @author 		Sajaki@gmail.com
 * @copyright 	2013 bbdkp
 * @license 	http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 	1.3.0
 * @since 		1.3.0
 */
namespace bbdkp\controller\adjustments;
/**
 * @ignore
 */
if (! defined('IN_PHPBB'))
{
	exit();
}

$phpEx = substr(strrchr(__FILE__, '.'), 1);
global $phpbb_root_path;

if (!class_exists('\bbdkp\admin\Admin'))
{
	require ("{$phpbb_root_path}includes/bbdkp/admin/admin.$phpEx");
}

/**
 *
 * Adjustments Class
 *
 * @package bbdkp
 */
class Adjust extends \bbdkp\admin\Admin
{

	/**
	 * Pk adjustmnent identifier
	 * @var int
	 */
	public $adjustment_id;


	/**
	 * id of member to adjust
	 * @var int
	 */
	private $member_id = 0;
	/**
	 * name of member to be adjusted
	 * @var int
	 */
    private $member_name = '';
	/**
	 * adjustment dkp id
	 * @var int
	 */
    private $adjustment_dkpid = 0;
	/**
	 * value of the adjustment
	 * @var float signed
	 */
    private $adjustment_value = 0.0;
	/**
	 * date of adjustment
	 * @var int
	 */
    private $adjustment_date;
	/**
	 * reason for the adjustment
	 * @var string
	 */
    private $adjustment_reason = '';
	/**
	 * reason for adjustment
	 * @var string
	 */
    private $adjustment_added_by = '';
	/**
	 * who updated
	 * @var string
	 */
    private $adjustment_updated_by = '';
	/**
	 * unique key for identifying group of adjustments
	 * @var string
	 */
    private $adjustment_groupkey = '';
	/**
	 * amount of adjustment decay
	 * @var float
	 */
    private $adj_decay = 0.0;
	/**
	 * bool to indicate if this can be decayed
	 * @var bool
	 */
    private $can_decay = 0;
	/**
	 * time if decay
	 * @var int
	 */
    private $decay_time = 0;


	/**
	 * array with members sharing adjustment
	 * @var array
	 */
    private $members_samegroupkey = array();
	/**
	 * dkp pool for adjust
	 * @var array
	 */
    private $dkpsys = array();

    /**
     * Adjustment class constructor
     * @param int|number $adjustment_dkpid
     */
	function __construct($adjustment_dkpid = 0)
	{
		global $db;
		parent::__construct();
		$this->adjustment_dkpid = $adjustment_dkpid;
		// get dkp pools that are active.
		$sql = 'SELECT dkpsys_id, dkpsys_name, dkpsys_default
            FROM ' . DKPSYS_TABLE . " a
			WHERE a.dkpsys_status = 'Y' ";
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
     * @return int
     */
    public function getAdjustmentDate()
    {
        return $this->adjustment_date;
    }

    /**
     * @param int $adjustment_date
     */
    public function setAdjustmentDate($adjustment_date)
    {
        $this->adjustment_date = $adjustment_date;
    }

    /**
     * @return string
     */
    public function getAdjustmentUpdatedBy()
    {
        return $this->adjustment_updated_by;
    }

    /**
     * @param string $adjustment_updated_by
     */
    public function setAdjustmentUpdatedBy($adjustment_updated_by)
    {
        $this->adjustment_updated_by = $adjustment_updated_by;
    }

    /**
     * @return string
     */
    public function getAdjustmentReason()
    {
        return $this->adjustment_reason;
    }

    /**
     * @param string $adjustment_reason
     */
    public function setAdjustmentReason($adjustment_reason)
    {
        $this->adjustment_reason = $adjustment_reason;
    }

    /**
     * @return int
     */
    public function getAdjustmentId()
    {
        return $this->adjustment_id;
    }

    /**
     * @param int $adjustment_id
     */
    public function setAdjustmentId($adjustment_id)
    {
        $this->adjustment_id = $adjustment_id;
    }

    /**
     * @return \bbdkp\controller\adjustments\unknown
     */
    public function getAdjustmentGroupkey()
    {
        return $this->adjustment_groupkey;
    }

    /**
     * @param \bbdkp\controller\adjustments\unknown $adjustment_groupkey
     */
    public function setAdjustmentGroupkey($adjustment_groupkey)
    {
        $this->adjustment_groupkey = $adjustment_groupkey;
    }

    /**
     * @return string
     */
    public function getAdjustmentAddedBy()
    {
        return $this->adjustment_added_by;
    }

    /**
     * @param string $adjustment_added_by
     */
    public function setAdjustmentAddedBy($adjustment_added_by)
    {
        $this->adjustment_added_by = $adjustment_added_by;
    }

    /**
     * @return float
     */
    public function getAdjDecay()
    {
        return $this->adj_decay;
    }

    /**
     * @param float $adj_decay
     */
    public function setAdjDecay($adj_decay)
    {
        $this->adj_decay = $adj_decay;
    }

    /**
     * @return int
     */
    public function getMemberId()
    {
        return $this->member_id;
    }

    /**
     * @param int $member_id
     */
    public function setMemberId($member_id)
    {
        $this->member_id = $member_id;
    }

    /**
     * @return int
     */
    public function getMemberName()
    {
        return $this->member_name;
    }

    /**
     * @param int $member_name
     */
    public function setMemberName($member_name)
    {
        $this->member_name = $member_name;
    }

    /**
     * @return int
     */
    public function getAdjustmentDkpid()
    {
        return $this->adjustment_dkpid;
    }

    /**
     * @param int $adjustment_dkpid
     */
    public function setAdjustmentDkpid($adjustment_dkpid)
    {
        $this->adjustment_dkpid = $adjustment_dkpid;
    }

    /**
     * @return float
     */
    public function getAdjustmentValue()
    {
        return $this->adjustment_value;
    }

    /**
     * @param float $adjustment_value
     */
    public function setAdjustmentValue($adjustment_value)
    {
        $this->adjustment_value = $adjustment_value;
    }
    /**
     * @param array $dkpsys
     */
    public function setDkpsys($dkpsys)
    {
        $this->dkpsys = $dkpsys;
    }

    /**
     * @return array
     */
    public function getDkpsys()
    {
        return $this->dkpsys;
    }

    /**
     * @param boolean $can_decay
     */
    public function setCanDecay($can_decay)
    {
        $this->can_decay = $can_decay;
    }

    /**
     * @return boolean
     */
    public function getCanDecay()
    {
        return $this->can_decay;
    }
    /**
     * @param int $decay_time
     */
    public function setDecayTime($decay_time)
    {
        $this->decay_time = $decay_time;
    }

    /**
     * @return int
     */
    public function getDecayTime()
    {
        return $this->decay_time;
    }

    /**
     * @param array $members_samegroupkey
     */
    public function setMembersSamegroupkey($members_samegroupkey)
    {
        $this->members_samegroupkey[] = $members_samegroupkey;
    }

    /**
     * @return array
     */
    public function getMembersSamegroupkey()
    {
        return $this->members_samegroupkey;
    }


    /**
	 * add a new dkp adjustment
	 */
	public function add()
	{
		global $user, $db;
		// no global scope
		if ($this->member_id == 0)
		{
			trigger_error($user->lang['ERROR_MEMBERNOTFOUND'], E_USER_WARNING);
		}
		//
		// does member have a dkp record ?
		//
		$sql = 'SELECT count(member_id) as membercount FROM  ' . MEMBER_DKP_TABLE . '
		WHERE member_id = ' . $this->member_id . '
		AND member_dkpid = ' . $this->adjustment_dkpid;
		$result = $db->sql_query($sql);
		$membercount = (int) $db->sql_fetchfield('membercount');

		$db->sql_transaction ( 'begin' );

		if ($membercount == 1)
		{
			$sql = 'UPDATE ' . MEMBER_DKP_TABLE . '
				SET member_adjustment = member_adjustment + ' . $this->adjustment_value . "
                WHERE member_id='" . $this->member_id . "'
        		AND member_dkpid = " . $this->adjustment_dkpid;
			$db->sql_query($sql);
			unset($sql);
		}
		elseif ($membercount == 0)
		{

			$query = $db->sql_build_array('INSERT', array(
					'member_dkpid' => $this->adjustment_dkpid ,
					'member_id' => $this->member_id ,
					'member_earned' => 0.00 ,
					'member_spent' => 0.00 ,
					'member_adjustment' => $this->adjustment_value ,
					'member_firstraid' => 0 ,
					'member_lastraid' => 0 ,
					'member_raidcount' => 0));
			$db->sql_query('INSERT INTO ' . MEMBER_DKP_TABLE . $query);
		}

		$query = $db->sql_build_array('INSERT', array(
				'adjustment_dkpid' => $this->adjustment_dkpid ,
				'adjustment_value' => $this->adjustment_value ,
				'adjustment_date' => $this->adjustment_date ,
				'member_id' => $this->member_id ,
				'adjustment_reason' => $this->adjustment_reason ,
				'adjustment_group_key' => $this->adjustment_groupkey ,
				'can_decay' => $this->can_decay ,
				'adj_decay' => $this->adj_decay,
				'adjustment_added_by' => $user->data['username']));

		$db->sql_query('INSERT INTO ' . ADJUSTMENTS_TABLE . $query);

		$db->sql_transaction('commit');
	}

	/**
	 * deletes adjustment
	 */
	function delete()
	{
		global $db;

		$db->sql_transaction ( 'begin' );

		$sql = 'DELETE FROM ' . ADJUSTMENTS_TABLE . ' WHERE adjustment_id = ' . $this->adjustment_id;
		$db->sql_query($sql);

		$sql = 'UPDATE ' . MEMBER_DKP_TABLE . '
			SET member_adjustment = member_adjustment - ' . (float) $this->adjustment_value . ',
			adj_decay = adj_decay - ' . (float) $this->adj_decay . '
			WHERE  member_dkpid = ' . $this->adjustment_dkpid . '
			AND member_id = ' . $this->member_id;

		$db->sql_query($sql);

		$db->sql_transaction('commit');
	}

	/**
	 * deletes all adjustments foer one member
	 */
	function delete_memberadjustments()
	{
		global $db;

		$sql = 'DELETE FROM ' . ADJUSTMENTS_TABLE . ' WHERE member_id = ' . $this->member_id . ' AND adjustment_dkpid  = ' .  $this->adjustment_dkpid;
		$db->sql_query($sql);

	}

	/**
	 * returns list of adjustments
	 *
	 * @param string $order
	 * @param int $member_id
	 * @param int $start
	 * @param int $guild_id
	 * @return array
	 */
	public function ListAdjustments($order, $member_id, $start=0, $guild_id = 0)
	{
		global $db, $config;
		$order = (string) $order;
		$member_id = (int) $member_id;

		$sql_array = array(
				'SELECT' => 'a.adjustment_dkpid, a.adjustment_reason,
			    				b.dkpsys_name, a.adjustment_id, a.adj_decay, a.decay_time, a.can_decay,
			    				a.adjustment_value, a.member_id, l.member_name,
			    				a.adjustment_date, a.adjustment_added_by, c.colorcode, c.imagename ' ,
				'FROM' => array(
						ADJUSTMENTS_TABLE => 'a' ,
						DKPSYS_TABLE => 'b' ,
						MEMBER_LIST_TABLE => 'l' ,
						CLASS_TABLE => 'c') ,
				'WHERE' => '
			    		b.dkpsys_id = a.adjustment_dkpid
			    		AND c.class_id = l.member_class_id
			    		AND l.game_id= c.game_id
						AND a.adjustment_dkpid 	= ' . (int) $this->adjustment_dkpid . '
						AND a.member_id = l.member_id
						AND a.member_id IS NOT NULL ' ,
				'ORDER_BY' => $order);

		if ($member_id != 0)
		{
			$sql_array['WHERE'] .= ' AND a.member_id = ' . $member_id;
		}

		if ($guild_id != 0)
		{
			$sql_array['WHERE'] .= ' AND l.member_guild_id = ' . $guild_id;
		}

		$sql = $db->sql_build_query ( 'SELECT', $sql_array );
		if ($start > 0)
		{
			$result = $db->sql_query_limit($sql, $config['bbdkp_user_alimit'], $start, 0);
		}
		else
		{
			$result = $db->sql_query ( $sql );
		}
		return $result;

	}

	/**
	 * Counts adjustments for a pool/member
	 *
	 * @param int $member_id
	 * @return array
	 */
	public function countadjust($member_id)
	{
		$member_id = (int) $member_id;
		global  $db;
		$sql = 'SELECT count(*) as total_adjustments
					FROM ' . ADJUSTMENTS_TABLE . '
					WHERE member_id IS NOT NULL
					and adjustment_dkpid 	= ' . (int) $this->adjustment_dkpid;
		if ($member_id != 0)
		{
			$sql .= ' and member_id  = ' . $member_id;
		}
		$result = $db->sql_query($sql);
		return $result;

	}

	/**
	 * Lists the pools with adjustments
	 * @return array
	 */
	function listAdjPools()
	{
		global $db;
		$sql = 'SELECT dkpsys_id, dkpsys_name , dkpsys_default
		          FROM ' . DKPSYS_TABLE . ' a, ' . ADJUSTMENTS_TABLE . ' j
		          WHERE a.dkpsys_id = j.adjustment_dkpid
		          GROUP BY dkpsys_id, dkpsys_name , dkpsys_default';
		$result = $db->sql_query($sql);
		return $result;
	}

	/**
	 *
	 * function to decay one specific adjustment
	 * @param int $adjust_id
	 * @return boolean
	 */
	public function decayadj ($adjust_id)
	{
		global $user, $config, $db;
		$oldadj = $this->get($adjust_id);

		$now = getdate();
		$timediff = mktime($now['hours'], $now['minutes'], $now['seconds'], $now['mon'], $now['mday'], $now['year']) - $this->adjustment_date;
		$i = (float) $config['bbdkp_adjdecaypct'] / 100;

		// get decay frequency
		$freq = $config['bbdkp_decayfrequency'];
		if ($freq == 0)
		{
			//frequency can't be 0. throw error
			trigger_error($user->lang['FV_FREQUENCY_NOTZERO'], E_USER_WARNING);
		}

		//pick decay frequency type (0=days, 1=weeks, 2=months) and convert timediff to that
		$t = 0;
		switch ($config['bbdkp_decayfreqtype'])
		{
			case 0:
				//days
				$t = (float) $timediff / 86400;
				break;
			case 1:
				//weeks
				$t = (float) $timediff / (86400 * 7);
				break;
			case 2:
				//months
				$t = (float) $timediff / (86400 * 30.44);
				break;
		}

		// take the integer part of time and interval division base 10,
		// since we only decay after a set interval
		$n = intval($t / $freq, 10);

		//calculate rounded adjustment decay, defaults to rounds half up PHP_ROUND_HALF_UP, so 9.495 becomes 9.50
		$this->adj_decay = round($this->adjustment_value * (1 - pow(1 - $i, $n)), 2);

		$db->sql_transaction ( 'begin' );

		// update adj detail to new decay value
		$sql = 'UPDATE ' . ADJUSTMENTS_TABLE . '
			SET adj_decay = ' . $this->adj_decay . ", decay_time = " . $n . "
			WHERE adjustment_id = " . (int) $adjust_id;
		$db->sql_query($sql);

		// update dkp account, deduct old, add new decay
		$sql = 'UPDATE ' . MEMBER_DKP_TABLE . ' SET adj_decay = adj_decay - '  . $oldadj->adj_decay . ' + ' . $this->adj_decay . "
			WHERE member_id = " . (int) $this->member_id . '
			and member_dkpid = ' . $this->adjustment_dkpid;

		$db->sql_query($sql);

		$db->sql_transaction('commit');

		unset ($oldadj);

		return true;
	}

	/**
	 * get an adjustment from database
	 * @param integer $adjust_id
	 * @return Adjust
	 */
	public function get($adjust_id)
	{
		global $user, $db;

		$sql_array = array(
				'SELECT' => 'a.adjustment_id,
							a.adjustment_value,
							a.adjustment_dkpid,
							a.adjustment_date,
							a.adjustment_reason,
							a.member_id,
							m.member_name,
							a.adjustment_group_key,
							a.adjustment_added_by,
							a.adjustment_updated_by,
							a.adj_decay,
							a.decay_time,
							a.can_decay' ,
				'FROM' => array(
						ADJUSTMENTS_TABLE => 'a' ,
						MEMBER_LIST_TABLE => 'm') ,
				'WHERE' => 'a.member_id = m.member_id
					AND a.adjustment_id = ' . $adjust_id
			);
		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query($sql);
		if (! $row = $db->sql_fetchrow($result))
		{
			trigger_error($user->lang['ERROR_INVALID_ADJUSTMENT'], E_USER_NOTICE);
		}
		$db->sql_freeresult($result);

		$this->adjustment_id = $row['adjustment_id'];
		$this->adjustment_value = $row['adjustment_value'];
		$this->adjustment_dkpid = $row['adjustment_dkpid'];
		$this->adjustment_date = $row['adjustment_date'];
		$this->adjustment_reason = $row['adjustment_reason'];
		$this->member_id = $row['member_id'];
		$this->member_name = $row['member_name'];
		$this->adjustment_groupkey = $row['adjustment_group_key'];
		$this->adjustment_added_by = $row['adjustment_added_by'];
		$this->adjustment_updated_by = $row['adjustment_updated_by'];
		$this->adj_decay = $row['adj_decay'];
		$this->decay_time = $row['decay_time'];
		$this->can_decay = $row['can_decay'];


		$members = array();
		$sql = 'SELECT member_id from ' . ADJUSTMENTS_TABLE . " WHERE adjustment_group_key = '" . $this->adjustment_groupkey . "'";
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$members[] = $row['member_id'];
		}

		$this->members_samegroupkey = $members;
		unset($members);
		return $this;

	}





}
?>