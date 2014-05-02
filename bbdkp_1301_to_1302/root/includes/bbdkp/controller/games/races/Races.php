<?php
/**
 * Races Class file
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
 * Races
 *
 * Manages creation of Game races
 *
 *   @package bbdkp
 */
 class Races extends \bbdkp\controller\games\Game
{
	/**
	 * game id
	 * @var string
	 */
	public $game_id;

	/**
	 * race id
	 * @var int
	 */
	public $race_id;

	/**
	 * race faction id
	 * @var int
	 */
	public $race_faction_id;

	/**
	 * true if race is visible
	 * @var bool
	 */
	public $race_hide;

	/**
	 * image path female icon
	 * @var string
	 */
	public $image_female;

	/**
	 * image path male icon
	 * @var string
	 */
	public $image_male;

	/**
	 * race nam
	 * @var unknown
	 */
	public $race_name;

	/**
	 * constructor
	 */
	function __construct()
	{

	}

	/**
	 * gets race from database
	 */
	public function Get()
	{
		global $db, $config;

		$sql_array = array (
			'SELECT' => ' r.game_id, r.race_id, l.name AS race_name, r.race_faction_id,  r.image_female, r.image_male, r.race_hide ',
			'FROM' => array (RACE_TABLE => 'r', BB_LANGUAGE => 'l' ),
			'WHERE' => "   r.game_id = l.game_id
						AND r.race_id = l.attribute_id
						AND l.attribute='race'
						AND l.language= '" . $config ['bbdkp_lang'] . "'
						AND l.game_id = '" . $this->game_id . "'
						AND r.race_id = " . $this->race_id
			);

		$sql = $db->sql_build_query ( 'SELECT', $sql_array );
		$result = $db->sql_query ($sql);
		while ( $row = $db->sql_fetchrow ( $result ) )
		{
			$this->race_faction_id= $row['race_faction_id'];
			$this->race_name = $row['race_name'];
			$this->image_male =$row['image_male'];
			$this->image_female = $row['image_female'];
			$this->race_hide = $row['race_hide'];
		}
		$db->sql_freeresult ( $result );
		unset($result);
	}

	/**
	 * adds a race to database
	 */
	public function Make()
	{
		global $user, $db, $config, $cache;

		$sql = 'SELECT COUNT(race_id) AS countrace
			FROM ' . RACE_TABLE . '
			WHERE race_id  = ' . $this->race_id . "
			AND game_id = '" . $this->game_id . "'";
		$resultr = $db->sql_query ( $sql );
		$a = $db->sql_fetchfield ( 'countrace', false, $resultr );
		if (( int ) $a > 0)
		{
			//uh oh that race exists
			trigger_error ( sprintf ( $user->lang ['ADMIN_ADD_RACE_FAILED'], $this->race_id  ), E_USER_WARNING );
		}
		$db->sql_freeresult ( $resultr );
		$data = array (
				'game_id' => ( string ) $this->game_id,
				'race_id' => ( int ) $this->race_id,
				'race_faction_id' => ( int ) $this->race_faction_id,
				'image_male' => ( string ) $this->image_male,
				'image_female' => ( string ) $this->image_female,
				'race_hide' => 0 );

		$db->sql_transaction ( 'begin' );
		$sql = 'INSERT INTO ' . RACE_TABLE . ' ' . $db->sql_build_array ( 'INSERT', $data );
		$db->sql_query ( $sql );

		$names = array (
				'attribute_id' => $this->race_id,
				'game_id' => $this->game_id,
				'language' => $config ['bbdkp_lang'],
				'attribute' => 'race',
				'name' => ( string ) $this->race_name,
				'name_short' => ( string ) $this->race_name);

		$sql = 'INSERT INTO ' . BB_LANGUAGE . ' ' . $db->sql_build_array ( 'INSERT', $names );
		$db->sql_query ( $sql );

		$db->sql_transaction ( 'commit' );
		$cache->destroy ( 'sql', BB_LANGUAGE );
		$cache->destroy ( 'sql', RACE_TABLE );

		//
		// Logging
		//
		$log_action = array(
				'header' 	=> 'L_ACTION_RACE_ADDED' ,
				'L_GAME' 	=> $this->game_id ,
				'L_RACE' 	=> $this->race_name ,
		);

		$this->log_insert(array(
				'log_type' 		=> 'L_ACTION_RACE_ADDED',
				'log_result' 	=> 'L_SUCCESS',
				'log_action' 	=> $log_action));

	}


	/**
	 * deletes a race from database
	*/
	public function Delete()
	{
		global $user, $db, $config, $phpEx, $phpbb_root_path, $cache;

		/* check if there are members with this raceid */
		$sql_array = array (
				'SELECT' => ' count(*) as racecount  ',
				'FROM' => array (
						MEMBER_LIST_TABLE => 'm',
						RACE_TABLE => 'r' ),
				'WHERE' => 'm.member_race_id = r.race_id
		    			and r.race_id =  ' .  $this->race_id . "
		    			and r.game_id = m.game_id
		    			and r.game_id = '" . $this->game_id . "'" );

		$sql = $db->sql_build_query ( 'SELECT', $sql_array );
		$result = $db->sql_query ( $sql );
		$racecount = ( int ) $db->sql_fetchfield ( 'racecount', false, $result );

		if ($racecount != 0)
		{

			$log_action = array(
					'header' 	=> 'L_ACTION_RACE_DELETED' ,
					'L_GAME' 	=> $this->game_id ,
					'L_RACE' 	=> $this->race_name ,
			);

			$this->log_insert(array(
			'log_type' 		=> 'L_ACTION_RACE_DELETED',
			'log_result' 	=> 'L_FAILED',
			'log_action' 	=> $log_action));

			trigger_error (sprintf ( $user->lang ['ADMIN_DELETE_RACE_FAILED'], $this->race_name), E_USER_WARNING );
		}
		else
		{
			$db->sql_transaction ( 'begin' );

			$sql = 'DELETE FROM ' . RACE_TABLE . ' WHERE race_id =' . $this->race_id . " AND game_id = '" . $this->game_id . "'";
			$db->sql_query ( $sql );

			$sql = 'DELETE FROM ' . BB_LANGUAGE . " WHERE language= '" . $config ['bbdkp_lang'] . "'
							AND attribute = 'race'
							AND attribute_id= " . $this->race_id . "
							AND game_id = '" . $this->game_id . "'";

			$db->sql_query ( $sql );

			$cache->destroy ( 'sql', RACE_TABLE );
			$cache->destroy ( 'sql', BB_LANGUAGE );

			$db->sql_transaction ( 'commit' );

			//
			// Logging
			//
			$log_action = array(
					'header' 	=> 'L_ACTION_RACE_DELETED' ,
					'L_GAME' 	=> $this->game_id ,
					'L_RACE' => $this->race_name ,
			);

			$this->log_insert(array(
			'log_type' 		=> 'L_ACTION_RACE_DELETED',
			'log_result' 	=> 'L_SUCCESS',
			'log_action' 	=> $log_action));

		}

	}


	/**
	 * deletes all races from a game
	 */
	public function Delete_all_races()
	{
		global $db, $user, $cache;

		$sql = 'DELETE FROM ' . RACE_TABLE . " WHERE game_id = '" .   $this->game_id . "'"  ;
		$db->sql_query ( $sql );

		$sql = 'DELETE FROM ' . BB_LANGUAGE . " WHERE attribute = 'race'
							AND game_id = '" . $this->game_id . "'";

		$db->sql_query ( $sql );

		$cache->destroy ( 'sql', RACE_TABLE );
		$cache->destroy ( 'sql', BB_LANGUAGE );
	}

	/**
	 * updates a race to database
	 * @param Races $old_race
	 */
	public function Update(Races $old_race)
	{
		global  $db, $config, $cache;

		// note you cannot change the game to which a race belongs
		$data = array (
				'race_faction_id' => ( int ) $this->race_faction_id,
				'image_male' => ( string ) $this->image_male,
				'image_female' => ( string ) $this->image_female );

		$db->sql_transaction ( 'begin' );
		$sql = 'UPDATE ' . RACE_TABLE . ' SET ' . $db->sql_build_array ( 'UPDATE', $data ) . '
			    WHERE race_id = ' . ( int ) $this->race_id . " AND game_id = '" . $db->sql_escape ( $this->game_id ) . "'";
		$db->sql_query ( $sql );

		$names = array (
				'name' => ( string ) $this->race_name, 'name_short' => ( string ) $this->race_name );

		$sql = 'UPDATE ' . BB_LANGUAGE . ' SET ' . $db->sql_build_array ( 'UPDATE', $names ) . '
			WHERE attribute_id = ' . $this->race_id . " AND attribute='race'  AND language= '" . $config ['bbdkp_lang'] . "' AND game_id =   '" . $db->sql_escape ( $this->game_id  ) . "'";
		$db->sql_query ( $sql );

		$db->sql_transaction ( 'commit' );
		$cache->destroy ( 'sql', BB_LANGUAGE );
		$cache->destroy ( 'sql', RACE_TABLE );


		//
		// Logging
		//
		$log_action = array(
				'header' 	=> 'L_ACTION_RACE_UPDATED' ,
				'L_GAME' 	=> $this->game_id ,
				'L_RACE' 	=> $this->race_name ,
		);

		$this->log_insert(array(
				'log_type' 		=> 'L_ACTION_RACE_UPDATED',
				'log_result' 	=> 'L_SUCCESS',
				'log_action' 	=> $log_action));



	}

	/**
	 * get array with races
	 * @param string $order
	 * @return multitype:multitype:string
	 */
	public function listraces($order = 'r.race_id')
	{
		global $db, $config;

		$sql_array = array (
				'SELECT' => ' r.game_id, r.race_id, r.race_faction_id, r.race_hide, r.image_female, r.image_male,
							  l.name as race_name, f.faction_name, g.game_name ',
				'FROM' => array (
						RACE_TABLE => 'r',
						FACTION_TABLE => 'f',
						BB_LANGUAGE => 'l',
						GAMES_TABLE => 'g'
					),
				'WHERE' => " r.race_faction_id = f.faction_id
					AND f.game_id = r.game_id AND r.game_id = g.game_id AND r.game_id = '" . $this->game_id . "'
		    		AND l.attribute_id = r.race_id AND l.game_id = r.game_id and l.language= '" . $config ['bbdkp_lang'] . "'
		    		AND l.attribute = 'race' ", 'ORDER_BY' => $order );

		$sql = $db->sql_build_query ( 'SELECT', $sql_array );
		$result = $db->sql_query ( $sql );
		$ra = array();
		while ( $row = $db->sql_fetchrow ( $result ) )
		{
			$ra[$row['race_id']] = array(
					'game_name' => $row ['game_name'],
					'race_id' => $row ['race_id'],
					'race_name' => $row ['race_name'],
					'faction_name' => $row ['faction_name'],
					'image_male' => $row ['image_male'],
					'image_female' => $row ['image_female']);
		}
		$db->sql_freeresult ($result);
		return $ra;
	}


}

?>