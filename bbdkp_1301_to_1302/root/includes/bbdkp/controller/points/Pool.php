<?php
/**
 * Pool class file
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

$phpEx = substr(strrchr(__FILE__, '.'), 1);
global $phpbb_root_path;

if (!class_exists('\bbdkp\admin\Admin'))
{
	require ("{$phpbb_root_path}includes/bbdkp/admin/admin.$phpEx");
}

/**
 *  Pool Class
 *  this class manages the points Pool in the phpbb_bbdkp_dkpsystem table.
 *  Pools are a superset for events and dkp accounts.
 *  this class needs no controller so it extends admin.
 *
 *   @package bbdkp
 */
class Pool extends \bbdkp\admin\Admin
 {
	/**
	 * array of pools
	 * @var int
	 */
 	public $dkpsys;

 	/**
 	 * Pool id
 	 * @var int
 	 */
 	public $dkpsys_id;
 	/**
 	 * Pool name
 	 * @var string
 	 */
 	private $dkpsys_name;
 	/**
 	 * Pool status
 	 * @var string
 	 */
 	private $dkpsys_status;
	/**
	 * pool added by
	 * @var string
	 */
 	private $dkpsys_addedby;

 	/**
 	 * pool updated by
 	 * @var string
 	 */
 	private $dkpsys_updatedby;
 	/**
 	 * if pool is default then 'Y' else 'N'
 	 * @var string
 	 */
 	private $dkpsys_default;
 	/**
 	 * number of pools
 	 * @var integer
 	 */
 	private $poolcount;

    /**
     * Pool class constructor
     * @param int|number $dkpsys
     */
	function __construct($dkpsys = 0)
	{
		global $db;
		parent::__construct(); //to load admin
		if($dkpsys > 0)
		{
			$this->dkpsys_id = $dkpsys;
			$this->read();
		}

		$sql1 = 'SELECT * FROM ' . DKPSYS_TABLE;
		$result1 = $db->sql_query ( $sql1 );
		$rows1 = $db->sql_fetchrowset ( $result1 );
		$db->sql_freeresult ( $result1 );
		$this->poolcount = count ( $rows1 );

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
	 * Pool class property getter
	 * @param string $property
	 */
	public function __get($property)
	{
		global $user;

		if (property_exists($this, $property))
		{
			return $this->$property;
		}
		else
		{
			trigger_error($user->lang['ERROR'] . '  '. $property, E_USER_WARNING);
		}

        return null;
	}

	/**
	 * Pool class property Setter
	 * @param string $property
	 * @param string $value
	 */
	public function __set($property, $value)
	{
		global $user;
		switch ($property)
		{
			default:
				if (property_exists($this, $property))
				{
					switch($property)
					{
						case 'dkpsys_id':
							if(is_numeric($value))
							{
								$this->$property = $value;
							}
							break;
						case 'dkpsys_name':
							// limit this to 255
							$this->$property = (strlen($value) > 255) ? substr($value,0, 250).'...' : $value;
							break;
						case 'dkpsys_addedby':
							$this->$property = $value;
							break;
						case 'dkpsys_updatedby':
							$this->$property = $value;
							break;
						case 'dkpsys_status':
								switch ($value)
								{
									case 'N':
									case '0':
									case false:
										$this->$property = 'N';
										break;
									case 'Y':
									case '1':
									case true:
										$this->$property = 'Y';
										break;
								}
								break;
						case 'dkpsys_default':
							switch ($value)
							{
								case 'N':
								case '0':
								case false:
									$this->$property = 'N';
									break;
								case 'Y':
								case '1':
								case true:
									$this->$property = 'Y';
									break;
							}
							break;
					}
				}
				else
				{
					trigger_error($user->lang['ERROR'] . '  '. $property, E_USER_WARNING);
				}
		}
	}

	/**
	 * updates other pool to not default
	 */
	private function updateotherdefaults()
	{
		global $db;
		$sql = 'UPDATE ' . DKPSYS_TABLE . " SET dkpsys_default = 'N' WHERE dkpsys_id != " . (int) $this->dkpsys_id;
		$db->sql_query ( $sql );
	}

	/**
	 * make a pool instance given the pool id
	 */
	private function read()
	{
		global $db;

		$sql = 'SELECT * FROM ' . DKPSYS_TABLE . ' WHERE dkpsys_id = ' . (int) $this->dkpsys_id;
		$result = $db->sql_query ($sql);
		while ( ($row = $db->sql_fetchrow ( $result )) )
		{
			$this->dkpsys_id = $row['dkpsys_id'];
			$this->dkpsys_name = $row['dkpsys_name'];
			$this->dkpsys_default = $row['dkpsys_default'];
			$this->dkpsys_status = $row['dkpsys_status'];
			$this->dkpsys_addedby = $row['dkpsys_addedby'];
			$this->dkpsys_updatedby  = $row['dkpsys_updatedby'];
		}
		$db->sql_freeresult ($result);

	}


    /**
     *
     * list pools
     * @param string $order
     * @param int|number $start
     * @param int|number $mode
     * @return multitype:multitype:unknown
     */
	public function listpools($order = 'dkpsys_name', $start = 0, $mode = 0)
	{
		global $config, $db;


		$sql = 'SELECT d.*, count(e.event_id) as numevents
				FROM  ' . DKPSYS_TABLE . ' d LEFT OUTER JOIN ' . EVENTS_TABLE . ' e
				ON d.dkpsys_id = e.event_dkpid
				GROUP BY d.dkpsys_id
				ORDER BY ' . $order;

		if($mode == 1)
		{
			$result = $db->sql_query_limit ( $sql, $config ['bbdkp_user_elimit'], $start );
		}
		else
		{
			$result = $db->sql_query($sql);
		}
		$listpools = array();
		while ( ($row = $db->sql_fetchrow ( $result )) )
		{
			$listpools [$row['dkpsys_id']]= array(
				'numevents' => $row['numevents'],
				'dkpsys_id' => $row['dkpsys_id'],
				'dkpsys_name' => $row['dkpsys_name'],
				'dkpsys_default' => $row['dkpsys_default'],
				'dkpsys_status' => $row['dkpsys_status'],
				'dkpsys_addedby' => $row['dkpsys_addedby'],
				'dkpsys_updatedby'  => $row['dkpsys_updatedby']);
		}
		$db->sql_freeresult ($result);
		return $listpools;
	}

	/**
	 * insert new pool
	 */
	public function add()
	{
		global $user, $db;
		$query = $db->sql_build_array ('INSERT',
			array (
					'dkpsys_name' => $this->dkpsys_name ,
					'dkpsys_status' => $this->dkpsys_status ,
					'dkpsys_addedby' => $user->data['username'],
					'dkpsys_default' => $this->dkpsys_default ) );
		$db->sql_query ( 'INSERT INTO ' . DKPSYS_TABLE . $query );
		$this->dkpsys_id = $db->sql_nextid();

		$log_action = array (
				'header' => 'L_ACTION_DKPSYS_ADDED',
				'L_DKPSYS_NAME' => $this->dkpsys_name,
				'L_DKPSYS_STATUS' => $this->dkpsys_status,
				'L_ADDED_BY' => $user->data['username'] );

		$this->log_insert ( array (
				'log_type' => $log_action ['header'],
				'log_action' => $log_action ) );

	}


	/**
	 * update the pool. you have to pass the old object for logging
	 * @param Pool $olddkpsys
	 */
	public function update(Pool $olddkpsys)
	{
		global $user, $db;
		$query = $db->sql_build_array (
				'UPDATE',
				array (
						'dkpsys_name' => $this->dkpsys_name,
						'dkpsys_status' => $this->dkpsys_status,
						'dkpsys_default' => $this->dkpsys_default,
						'dkpsys_updatedby' => $user->data['username']));


		$sql = 'UPDATE ' . DKPSYS_TABLE . ' SET ' . $query . ' WHERE dkpsys_id = ' . (int) $this->dkpsys_id;
		$db->sql_query ( $sql );

		if($this->dkpsys_default != $olddkpsys->dkpsys_default)
		{
			$this->updateotherdefaults();
		}
		// Logging, put old & new
		$log_action = array (
				'header' => 'L_ACTION_DKPSYS_UPDATED',
				'id' => $this->dkpsys_id,
				'L_DKPSYSNAME_BEFORE' => $olddkpsys->dkpsys_name,
				'L_DKPSYSSTATUS_BEFORE' => $olddkpsys->dkpsys_status,
				'L_DKPSYSNAME_AFTER' => $this->dkpsys_name,
				'L_DKPSYSSTATUS_AFTER' => $this->dkpsys_status,
				'L_DKPSYSUPDATED_BY' => $user->data['username'] );
		$this->log_insert (
			array ( 'log_type' => $log_action ['header'],
				'log_action' => $log_action ) );


	}

	/**
	 * delete the pool.
	 * check if there are still raids/events on the pool
	 */
	public function delete()
	{
		global $user, $db;

		$sql = 'SELECT * FROM ' . RAIDS_TABLE . ' a, ' . EVENTS_TABLE . ' b
				WHERE b.event_id = a.event_id and b.event_dkpid = ' . (int) $this->dkpsys_id;
		$result = $db->sql_query ( $sql );
		if ($row = $db->sql_fetchrow ( $result ))
		{
			$db->sql_freeresult ( $result );
			trigger_error ( $user->lang ['FV_RAIDEXIST'], E_USER_WARNING );
		}


		$sql = 'SELECT * FROM ' . EVENTS_TABLE . ' WHERE event_dkpid = ' . (int) $this->dkpsys_id;
		$result = $db->sql_query ( $sql );
		if ($row = $db->sql_fetchrow ( $result ))
		{
			$db->sql_freeresult ( $result );
			trigger_error ( $user->lang ['FV_EVENTEXIST'], E_USER_WARNING );
		}

		$sql = 'DELETE FROM ' . DKPSYS_TABLE . ' WHERE dkpsys_id = ' . (int) $this->dkpsys_id;
		$db->sql_query ($sql);

		$log_action = array (
				'header' => 'L_ACTION_DKPSYS_DELETED',
				'id' => $this->dkpsys_id,
				'L_DKPSYS_NAME' => $this->dkpsys_name,
				'L_DKPSYS_STATUS' => $this->dkpsys_status);

		$this->log_insert ( array (
				'log_type' => $log_action ['header'],
				'log_action' => $log_action ));

	}



}

?>