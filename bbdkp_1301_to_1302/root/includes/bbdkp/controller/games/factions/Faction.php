<?php
/**
 * Faction Class file
 * 
 *   @package bbdkp
 * @link http://www.bbdkp.com
 * @author Sajaki@gmail.com
 * @copyright 2013 bbdkp
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.3.0
 * @since 1.3.0
 */
namespace bbdkp\controller\games;
/**
 * @ignore
 */
if (! defined('IN_PHPBB'))
{
	exit();
}

$phpEx = substr(strrchr(__FILE__, '.'), 1);
global $phpbb_root_path;

if (!class_exists('\bbdkp\controller\games\Game'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/games/Game.$phpEx");
}

/**
 * Faction Class
 * 
 * Manages all Game Factions
 * 
 *   @package bbdkp
 */
 class Faction extends \bbdkp\controller\games\Game 
{
	/**
	 * game id 
	 * @var string
	 */
	public $game_id;
	
	/**
	 * pk
	 * @var int
	 */
	protected $f_index; //readonly
	
	/**
	 * faction id
	 * @var int
	 */
	protected $faction_id;
	/**
	 * name of faction
	 * @var string
	 */ 
	protected $faction_name;
	/**
	 * flag to show or hide (0 or 1)
	 * @var int
	 */
	protected $faction_hide;
	
	/**
	 * Faction class constructor
	 */
	public function __construct() 
	{
		$this->game_id='';
		$this->faction_id=0;
		$this->faction_name='';
		$this->faction_hide=0;
	}

	/**
	 * get faction info
	 * 
	 */
	public function Get()
	{
		global $db;
		$sql = 'SELECT game_id, f_index, faction_id, faction_name, faction_hide
    			FROM ' . FACTION_TABLE . '
    			WHERE f_index = ' . (int) $this->faction_id . " and game_id = '" . $this->game_id . "'";
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$this->faction_name = $row['faction_name'];
			$this->faction_hide	= $row['faction_hide'];
		}
		$db->sql_freeresult($result);
		
	}
	
	/**
	 * faction property getter
	 * @param string $fieldName
	 */
	public function __get($fieldName)
	{
		global $user;
		if (property_exists($this, $fieldName))
		{
			return $this->$fieldName;
		}
		else
		{
			trigger_error($user->lang['ERROR'] . '  '. $fieldName, E_USER_WARNING);
		}
        return null;
	}
	
	/**
	 * faction property setter
	 * @param string $property
	 * @param string $value
	 */
	public function __set($property, $value)
	{
		global $user; 
		
		switch ($property)
		{
			case 'f_index':
				break;
			default:
				if (property_exists($this, $property))
				{
					$this->$property = $value;
				}
				else
				{
					trigger_error($user->lang['ERROR'] . '  '. $property, E_USER_WARNING);
				}
		}
	}
	
	
	/**
	 * adds a faction
	 */
	public function Make()
	{
		global $db, $user, $cache;
	
		$sql = 'SELECT max(faction_id) as faction_id FROM ' . FACTION_TABLE . "
				WHERE game_id = '" . $this->game_id . "' ";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$this->faction_id = (int) $row['faction_id'] + 1;
		$db->sql_freeresult ($result);
	
		$data = array (
				'game_id' => $this->game_id,
				'faction_name' => ( string ) $this->faction_name,
				'faction_id' => (int ) $this->faction_id,
				'faction_hide' => 0 );
	
		$db->sql_transaction ('begin');
	
		$sql = 'INSERT INTO ' . FACTION_TABLE . ' ' . $db->sql_build_array ( 'INSERT', $data );
		$db->sql_query ( $sql );
	
		$db->sql_transaction ( 'commit' );
		$cache->destroy ( 'sql', FACTION_TABLE );
		
		//
		// Logging
		//
		$log_action = array(
				'header' 	=> 'L_ACTION_FACTION_ADDED' ,
				'L_GAME' 	=> $this->game_id ,
				'L_FACTION' => $this->faction_name ,
		);
		
		$this->log_insert(array(
			'log_type' 		=> 'L_ACTION_FACTION_ADDED',
			'log_result' 	=> 'L_SUCCESS',
			'log_action' 	=> $log_action));
	
	}
	
	/**
	 * deletes a specific factions
	 */
	public function Delete()
	{
		global $db, $user, $cache; 
		
		/* check if there are races tied to this faction */
		$sql_array = array (
				'SELECT' => ' count(*) AS factioncount  ',
				'FROM' => array (
						RACE_TABLE => 'r', FACTION_TABLE => 'f' ),
				'WHERE' => "r.race_faction_id = f.faction_id 
				AND f.game_id = '" . $this->game_id . "'
				AND f.f_index =  " . $this->faction_id );
		
		$sql = $db->sql_build_query ( 'SELECT', $sql_array );
		$result = $db->sql_query($sql);
		$factioncount = (int) $db->sql_fetchfield('factioncount');
		
		if ($factioncount == 0)
		{
			$sql = 'DELETE FROM ' . FACTION_TABLE . ' WHERE f_index =' . $this->faction_id . " AND game_id = '" .   $this->game_id . "'"  ;
			$db->sql_query ( $sql );
			$cache->destroy ( 'sql', FACTION_TABLE );
			//
			// Logging
			//
			$log_action = array(
					'header' 	=> 'L_ACTION_FACTION_DELETED' ,
					'L_GAME' 	=> $this->game_id ,
					'L_FACTION' => $this->faction_name ,
			);
				
			$this->log_insert(array(
			'log_type' 		=> 'L_ACTION_FACTION_DELETED',
			'log_result' 	=> 'L_SUCCESS',
			'log_action' 	=> $log_action));
			
				
		}
		else
		{
			//
			// Logging failure
			//
			if (!class_exists('\bbdkp\admin\Admin'))
			{
				require("{$phpbb_root_path}includes/bbdkp/bbdkp.$phpEx");
			}
			
			$log_action = array(
					'header' => 'L_ACTION_FACTION_DELETED' ,
					'L_GAME' => $this->game_id ,
					'L_FACTION' => $this->faction_name ,
			);
			
			$this->log_insert(array(
			'log_type' 		=> 'L_ACTION_FACTION_DELETED',
			'log_result' 	=> 'L_FAILED', 
			'log_action' 	=> $log_action));
			
			trigger_error (sprintf ( $user->lang ['ADMIN_DELETE_FACTION_FAILED'], $this->game_id, $this->faction_name), E_USER_WARNING );
		}
	}
	
	/**
	 * deletes factions from a game
	 */
	public function Delete_all_factions()
	{
		global $db, $cache;
		$sql = 'DELETE FROM ' . FACTION_TABLE . " WHERE game_id = '" .   $this->game_id . "'"  ;
		$db->sql_query ( $sql );
		$cache->destroy ( 'sql', FACTION_TABLE );
	}
	
	/**
	 * get factions for this game
	 * @return array
	 */
	public function getfactions()
	{
		global $db, $user, $cache;
		$sql_array = array (
				'SELECT' => ' f.game_id, f.f_index, f.faction_id, f.faction_name, f.faction_hide ',
				'FROM' => array (FACTION_TABLE => 'f' ),
				'WHERE' => " f.game_id = '" . $this->game_id . "'",
				'ORDER_BY' => 'faction_id ASC ' );
		$sql = $db->sql_build_query ( 'SELECT', $sql_array );
		$result = $db->sql_query ( $sql );
		$fa = array(); 
		while ( $row = $db->sql_fetchrow ( $result ) )
		{
			$fa[$row ['faction_id']] = array(
				'f_index' => $row ['f_index'],
				'faction_name' => $row ['faction_name'], 
				'faction_id' => $row ['faction_id']); 
		}
		$db->sql_freeresult ( $result );
		return $fa; 
		
	}
	
	
	
}

?>