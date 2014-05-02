<?php
/**
 * Events Class file
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

$phpEx = substr(strrchr(__FILE__, '.'), 1);
global $phpbb_root_path;

if (!class_exists('\bbdkp\admin\Admin'))
{
	require ("{$phpbb_root_path}includes/bbdkp/admin/admin.$phpEx");
}

/**
 * Events are Types of raids. Raids are hooked up to Events
 *
 * example of events: 10-man raids, progress raids, Flex raid, recurring farm raid , RBG, Flashpoints, ...
 *
 *   @package bbdkp
 *
 */
class Events extends \bbdkp\admin\Admin
{
	/**
	 * Event id primary key
	 * @var integer
	 */
	public $event_id;
	/**
	 * dkpsys id
	 * @var integer
	 */
	private $dkpsys_id;
	/**
	 * name of dkp pool
	 * @var string
	 */
	private $dkpsys_name;
	/**
	 * Name of the event
	 * @var string
	 */
	private $event_name;
	/**
	 * Standard event value
	 * @var float
	 */
	private $event_value;
	/**
	 * Event color hex
	 * @var string
	 */
	private $event_color;
	/**
	 * image name. provide images it looks nicer
	 * @var string
	 */
	private $event_imagename;
	/**
	 * Event status
	 * @var integer 1 or 0
	 */
	private $event_status;
	/**
	 * total number of evnets within a Pool
	 * @var integer
	 */
	public $total_events;
	/**
	 * Pool array
	 * @var array
	 */
	public $dkpsys;
	/**
	 * array of all events in Pool
	 * @var array
	 */
	public $events;

    /**
     * Event class constructor
     * @param int|number $event_id
     */
	function __construct($event_id = 0)
	{
		global $db;
		parent::__construct();
		if($event_id != 0)
		{
			$this->get($event_id);
		}
		else
		{
			$this->event_id = 0;
			$this->dkpsys_id = 0;
			$this->dkpsys_name = '';
			$this->event_name = '';
			$this->event_value = 0.0;
			$this->event_color = #FFF;
			$this->event_imagename = '';
			$this->event_status = '';
		}

		// dkp pools
		$sql = 'SELECT dkpsys_id, dkpsys_name, dkpsys_default
            FROM ' . DKPSYS_TABLE . "
            ORDER BY dkpsys_name";
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result) )
		{
			$this->dkpsys[] = array(
					'id' => $row['dkpsys_id'],
					'name' => $row['dkpsys_name'],
					'default' => $row['dkpsys_default']);
		}
		$db->sql_freeresult($result);

	}


/**
 * Event class Property Getter
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
 * Event class Property Setter
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

				switch ($property)
				{
					case 'event_name':

						if (strlen($value) < 3)
						{
							trigger_error($user->lang['FV_REQUIRED_EVENT_NAME'], E_USER_WARNING);
						}
						elseif (strlen($value) > 255)
						{
							$this->$property =  substr($value,0, 250).'...';
						}
						else
						{
							$this->$property = $value;
						}
						break;

					default:
						$this->$property = $value;
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
	 * Get Event instance from database
	 *
	 * @param int $event_id
	 */
	public function get($event_id = 0)
	{
		global $db, $user;

		$sql = 'SELECT b.dkpsys_name, b.dkpsys_id, a.event_name, a.event_value,
				a.event_id, a.event_color, a.event_imagename, a.event_status
				FROM ' . EVENTS_TABLE . ' a, ' . DKPSYS_TABLE . " b
				WHERE a.event_id = " . (int) $event_id . "
				AND b.dkpsys_id = a.event_dkpid
				AND b.dkpsys_status != 'N' ";

		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		if ($row)
		{
			$this->event_id = $row['event_id'];
			$this->dkpsys_id = $row['dkpsys_id'];
			$this->dkpsys_name = $row['dkpsys_name'];
			$this->event_name = $row['event_name'];
			$this->event_value = $row['event_value'];
			$this->event_color = $row['event_color'];
			$this->event_imagename = $row['event_imagename'];
			$this->event_status = $row['event_status'];
			$this->events = array(
				'event_dkpsys_name'	 	 => $this->dkpsys_name,
				'dkpsys_id'		 		 => $this->dkpsys_id,
				'event_name'			 => $this->event_name ,
				'event_color'			 => $this->event_color,
				'event_imagename'		 => $this->event_imagename,
				'event_value'			 => $this->event_value,
				'event_id'				 => $this->event_id,
				'event_status'			 => $this->event_status,
			);
			unset($row);
		}
		$db->sql_freeresult($result);

	}


	/**
	 * adds an event to Database
	 */
	public function add()
	{
		global $user, $db;
		// check existing
		$result = $db->sql_query("SELECT count(*) as evcount from " . EVENTS_TABLE .
				" WHERE UPPER(event_name) = '" . strtoupper($db->sql_escape( $this->event_name ))	."' ;");
		$eventexistsrow = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if($eventexistsrow['evcount'] > 0 )
		{
			trigger_error($user->lang['ERROR_RESERVED_EVENTNAME'], E_USER_WARNING);
		}

		$query = $db->sql_build_array('INSERT', array(
				'event_dkpid'		=> $this->dkpsys_id,
				'event_name'		=> $this->event_name,
				'event_imagename'	=> $this->event_imagename,
				'event_color'		=> $this->event_color,
				'event_value'		=> $this->event_value,
				'event_added_by' 	=> $user->data['username'])
		);
		$db->sql_query('INSERT INTO ' . EVENTS_TABLE . $query);

		$this->event_id = $db->sql_nextid();

		$log_action = array(
				'header'		=> 'L_ACTION_EVENT_ADDED',
				'id'			=> $this->event_id,
				'L_NAME'		=> $this->event_name,
				'L_VALUE'		=> $this->event_value,
				'L_ADDED_BY' 	=> $user->data['username']);

		$this->log_insert(array(
				'log_type'	=> $log_action['header'],
				'log_action' => $log_action)
		);

	}

	/**
	 * updates Database
	 * @var \bbdkp\controller\raids\Events $oldevent
	 */
	public function update(\bbdkp\controller\raids\Events $oldevent)
	{
		global $user, $db;

		// Update the event record
		$query = $db->sql_build_array('UPDATE', array(
				'event_dkpid' => $this->dkpsys_id,
				'event_name'=> $this->event_name,
				'event_imagename' => $this->event_imagename,
				'event_color' => $this->event_color,
				'event_value' => $this->event_value));

		$sql = 'UPDATE ' . EVENTS_TABLE . ' SET ' . $query . ' WHERE event_id=' . (int) $this->event_id;
		$db->sql_query($sql);

		if ($this->dkpsys_id != $oldevent->dkpsys_id)
		{
			//@sync
		}

		//
		// Logging
		//
		$log_action = array(
				'header'		 => 'L_ACTION_EVENT_UPDATED',
				'id'		 	 => $this->event_id,
				'L_NAME_BEFORE'  => $oldevent->event_name,
				'L_VALUE_BEFORE' => $oldevent->event_value,
				'L_NAME_AFTER'   => $this->event_name,
				'L_VALUE_AFTER'  => $oldevent->event_value,
				'L_UPDATED_BY'   => $user->data['username']);

		$this->log_insert(array(
				'log_type' => $log_action['header'],
				'log_action' => $log_action)
			);

	}

	/**
	 * delete event
	 */
	public function delete()
	{
		global $db, $user;

		$sql = 'SELECT * FROM ' . RAIDS_TABLE . ' a, ' . EVENTS_TABLE . ' b
				WHERE b.event_id = a.event_id and b.event_dkpid = ' . (int) $this->dkpsys_id . ' and a.event_id = ' . $this->event_id;

		// check for existing events, raids on this event
		$result = $db->sql_query ( $sql );
		if ($row = $db->sql_fetchrow ( $result ))
		{
			trigger_error ( $user->lang ['FV_RAIDEXIST'], E_USER_WARNING );
		}

		$sql = 'DELETE FROM ' . EVENTS_TABLE . ' WHERE event_id = ' . $this->event_id ;
		$db->sql_query($sql);

		$log_action = array(
				'header'	=> 'L_ACTION_EVENT_DELETED',
				'id'		=> $this->event_id,
				'L_NAME'	=> $this->event_name,
				'L_VALUE' 	=> $this->event_value );

		$this->log_insert(array(
				'log_type' => $log_action['header'],
				'log_action' => $log_action)
		);




	}

    /**
     * counts number of events, evtl per dkp pool
     *
     * @param int|number $dkpid
     */
	public function countevents($dkpid = 0)
	{
		global $db;
		$sql = 'SELECT count(*) as eventcount FROM ' . DKPSYS_TABLE . ' a , ' . EVENTS_TABLE . " b
			where a.dkpsys_id = b.event_dkpid AND b.event_status = 1 AND a.dkpsys_status != 'N' ";
		if($this->dkpsys_id !=0)
		{
			$sql .= ' AND a.dkpsys_id = ' . $this->dkpsys_id . ' ';
		}
		elseif ($dkpid != 0)
		{
			$sql .= ' AND a.dkpsys_id = ' . $dkpid. ' ';
		}
		$result = $db->sql_query($sql);
		$this->total_events = (int) $db->sql_fetchfield('eventcount');
		$db->sql_freeresult($result);

	}

    /**
     * counts number of events, evtl per dkp pool
     * @param int|number $dkpid
     * @return float
     */
	public function getmax($dkpid = 0)
	{
		global $db;
		$this->dkpsys_id = $dkpid;
		$sql = 'SELECT max(event_value) AS max_value FROM ' . EVENTS_TABLE . ' where event_status = 1 AND event_dkpid = ' . $this->dkpsys_id;
		$result = $db->sql_query ($sql);
		$max_value = (float) $db->sql_fetchfield('max_value', false, $result);
		$db->sql_freeresult($result);
		return $max_value;
	}

    /**
     * Get all events from pool
     *
     * @param int|number $start
     * @param string $order
     * @param int|number $dkpid
     * @param boolean $all
     */
	public function listevents($start = 0, $order = 'b.dkpsys_id, a.event_name' , $dkpid=0, $all=true  )
	{
		global $config, $db;

		$sql = 'SELECT b.dkpsys_name, b.dkpsys_id, a.event_name, a.event_value, a.event_id, a.event_color, a.event_imagename, a.event_status
				FROM ' . EVENTS_TABLE . ' a, ' . DKPSYS_TABLE . " b
				WHERE b.dkpsys_id = a.event_dkpid AND b.dkpsys_status != 'N' ";

		if (!$all)
		{
			$sql .= ' AND a.event_status= 1 ';
		}

		if($dkpid != 0)
		{
			$sql .= " AND b.dkpsys_id = " . $dkpid;
		}

		$sql .= " ORDER BY " . $order;

		$result = $db->sql_query_limit($sql, $config['bbdkp_user_elimit'], $start,0);

		while ($row = $db->sql_fetchrow($result) )
		{
			$this->events[$row['event_id']] =  array(
					'event_id'				 => $row['event_id'],
					'dkpsys_id'		 		 => $row['dkpsys_id'],
					'event_name'			 => $row['event_name'],
					'dkpsys_name'	 	 	 => $row['dkpsys_name'],
					'event_value'			 => $row['event_value'],
					'event_color'			 => $row['event_color'],
					'event_imagename'		 => $row['event_imagename'],
					'event_status'			 => $row['event_status'],
			);
		}
		$db->sql_freeresult($result);
	}


	/**
	 * front View Event listing, by guild
	 * @param integer $guild_id
	 */
	public function viewlistevents($guild_id)
	{
		global $user, $template, $db, $config, $phpbb_root_path, $phpEx;

		if ((int) $config['bbdkp_event_viewall'] == 1)
		{
			/*** get all dkp pools with events ***/
			$sql_array = array (
				'SELECT' => ' d.dkpsys_id, d.dkpsys_name, COUNT(r.raid_id) AS raidcount ',
				'FROM' => array (
					DKPSYS_TABLE		=> 'd',
					EVENTS_TABLE 		=> 'e',
					),
				 'LEFT_JOIN' => array(
			        array(
			            'FROM'  => array(RAIDS_TABLE => 'r'),
			            'ON'    => 'r.event_id = e.event_id'
			        	)
			    	),
				'WHERE' => " d.dkpsys_id = e.event_dkpid AND e.event_status = 1 AND d.dkpsys_status != 'N'  ",
				'GROUP_BY' => 'dkpsys_id, dkpsys_name ',
				'ORDER_BY' => 'COUNT(r.raid_id) DESC, dkpsys_name ASC'
			);
		}
		else
		{
			/*** get dkp pools with events with raids ***/
			$sql_array = array (
				'SELECT' => ' d.dkpsys_id, d.dkpsys_name, COUNT(r.raid_id) AS raidcount ',
				'FROM' => array (
					DKPSYS_TABLE		=> 'd',
					EVENTS_TABLE 		=> 'e',
					RAIDS_TABLE 		=> 'r',
					RAID_DETAIL_TABLE 	=> 'dt',
					MEMBER_LIST_TABLE 	=> 'l',
					),
				'WHERE' => "d.dkpsys_id = e.event_dkpid AND e.event_status = 1  AND d.dkpsys_status != 'N'
							AND r.event_id = e.event_id
							AND r.raid_id = dt.raid_id
							AND dt.member_id = l.member_id
							AND l.member_guild_id = " . $guild_id,
				'GROUP_BY' => 'dkpsys_id, dkpsys_name ',
				'ORDER_BY' => 'COUNT(r.raid_id) DESC, dkpsys_name ASC'
			);

		}

		$sql = $db->sql_build_query('SELECT', $sql_array);
		$dkppool_result = $db->sql_query($sql);

		while ( $pool = $db->sql_fetchrow($dkppool_result) )
		{
			$total_events = 0;
			/*** get events ***/
		    if ((int) $config['bbdkp_event_viewall'] == 1)
			{
				$sql = 'SELECT e.event_dkpid, e.event_id, e.event_name, e.event_value, e.event_color, e.event_imagename,
						rr.raidcount, rr.newest, rr.oldest
					FROM ' . EVENTS_TABLE . ' e
					LEFT JOIN
					(
						SELECT
							r.event_id,
							COUNT( DISTINCT r.raid_id) as raidcount,
							MAX(r.raid_start) as newest,
							MIN(r.raid_start) as oldest
						FROM ' . RAIDS_TABLE . ' r
						INNER JOIN ' . RAID_DETAIL_TABLE . ' dt ON (r.raid_id = dt.raid_id)
						INNER JOIN ' . MEMBER_LIST_TABLE . ' l ON dt.member_id = l.member_id AND member_guild_id = ' . $guild_id . '
						GROUP BY r.event_id
					) rr
					ON rr.event_id = e.event_id
					WHERE e.event_dkpid = '. (int) $pool['dkpsys_id'] .' AND e.event_status = 1
					GROUP BY e.event_dkpid, e.event_id, e.event_name, e.event_value, e.event_color, e.event_imagename
					ORDER BY e.event_id';
			}
			else
			{
				$sql_array = array (
					'SELECT' => ' e.event_dkpid, e.event_id, e.event_name, e.event_value,  e.event_color, e.event_imagename,
					COUNT( DISTINCT r.raid_id) as raidcount, MAX(raid_start) as newest, MIN(raid_start) as oldest ',
					'FROM' => array (
						EVENTS_TABLE 		=> 'e',
						RAIDS_TABLE 		=> 'r',
						RAID_DETAIL_TABLE 	=> 'dt',
						MEMBER_LIST_TABLE 	=> 'l',
						),
					'WHERE' => 'e.event_dkpid = ' . (int) $pool['dkpsys_id'] .
								' AND e.event_status = 1
								  AND r.event_id = e.event_id
								  AND r.raid_id = dt.raid_id
								  AND dt.member_id = l.member_id
								  AND l.member_guild_id = ' . $guild_id,
					'ORDER_BY' => 'e.event_id',
					'GROUP_BY' => 'e.event_dkpid, e.event_id, e.event_name, e.event_value, e.event_color, e.event_imagename',
				);
				$sql = $db->sql_build_query('SELECT', $sql_array);
			}


			$sort_order[$pool['dkpsys_id']] = array(
			    0 => array('event_name', 'event_dkpid, event_name desc'),
			    1 => array('event_value desc', 'event_dkpid, event_value desc')
			);

			$current_order[$pool['dkpsys_id']] = $this->switch_order($sort_order[$pool['dkpsys_id']]);

			$start = request_var('pool'. $pool['dkpsys_id'], 0);
			$events_result = $db->sql_query($sql);
			while ( $event = $db->sql_fetchrow($events_result))
			{
				$total_events ++;
			}
			$u_listevents = append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=listevents&amp;guild_id=' . $guild_id);
			$template->assign_block_vars(
		    	'dkpsys_row', array(
					'O_NAME' 	 => $current_order[$pool['dkpsys_id']]['uri'][0],
				    'O_VALUE'    => $current_order[$pool['dkpsys_id']]['uri'][1],
				    'START' 	 => $start,
				    'EVENT_PAGINATION' => $this->generate_pagination2( $u_listevents . '&amp;o='.$current_order[$pool['dkpsys_id']]['uri']['current'],
									$total_events, $config['bbdkp_user_elimit'], $start, true, 'pool' . $pool['dkpsys_id']),
			    	'NAME' 		 => $pool['dkpsys_name'],
					'EVENTCOUNT' => sprintf($user->lang['LISTEVENTS_FOOTCOUNT'], $total_events , $config['bbdkp_user_elimit']),
			    	'ID' 		 => $pool['dkpsys_id']
		    ));

		    $events_result = $db->sql_query_limit($sql, $config['bbdkp_user_elimit'], $start);
		    while ( $event = $db->sql_fetchrow($events_result))
			{
			    $template->assign_block_vars(
			    	'dkpsys_row.events_row', array(
			        	'U_VIEW_EVENT' =>  append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=event&amp;' . URI_EVENT . '='.$event['event_id'] . '&amp;'.URI_DKPSYS.'='.$event['event_dkpid'] . '&amp;guild_id=' . $guild_id   ) ,
			        	'NAME' 			=> $event['event_name'],
			        	'VALUE' 		=> $event['event_value'],
			        	'IMAGEPATH' 	=> $phpbb_root_path . "images/bbdkp/event_images/" . $event['event_imagename'] . ".png",
			        	'S_EVENT_IMAGE_EXISTS' => (strlen($event['event_imagename']) > 1) ? true : false,
			        	'IMAGENAME' => $event['event_imagename'],
						'EVENTCOLOR'  	=> $event['event_color'],
			        	'RAIDCOUNT' 	=> ($event['raidcount'] == 0) ? $user->lang['NORAIDS'] : $event['raidcount'],
			        	'OLDEST' 		=> ($event['oldest']=='' ? '' : date($config['bbdkp_date_format'], $event['oldest']) )  ,
			    		'NEWEST' 		=> ($event['newest']=='' ? '' : date($config['bbdkp_date_format'], $event['newest']) )
			    ));
			}
			$db->sql_freeresult($events_result);

		}
		$db->sql_freeresult($dkppool_result);
	}

	/**
	 * mark an event selection active or unactive
	 * @param integer $action
	 * @param array $selected_events
	 */
	public function activateevents($action = 1, $selected_events)
	{
		global $db;
		$db->sql_transaction ( 'begin' );

		$sql = 'UPDATE ' . EVENTS_TABLE . "
           SET event_status = '" . $action . "'
           WHERE " . $db->sql_in_set ( 'event_id', $selected_events, false, true );
		$db->sql_query ( $sql);

		$db->sql_transaction ( 'commit' );
	}

}

?>