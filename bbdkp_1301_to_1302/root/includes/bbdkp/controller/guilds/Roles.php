<?php
/**
 * Roles Classfile
 *
 *   @package bbdkp
 * @link http://www.bbdkp.com
 * @author Sajaki@gmail.com
 * @copyright 2013 bbdkp
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.3.0
 */
namespace bbdkp\controller\guilds;
/**
 * @ignore
 */
if (! defined('IN_PHPBB'))
{
	exit();
}

$phpEx = substr(strrchr(__FILE__, '.'), 1);
global $phpbb_root_path;

// Include the base class

if (!class_exists('\bbdkp\controller\games\Classes'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/games/classes/Classes.$phpEx");
}

if (!class_exists('\bbdkp\controller\guilds\Guilds'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/guilds/Guilds.$phpEx");
}

/**
 * holds vacancies per guild, game, role and class
 *   @package bbdkp
 *
 */
class Roles
{

	/**
	 * primary key
	 */
	public $id;

	/**
	 * guild for which we want to recruit
	 * @var int
	 */
	public $guild_id;

	/**
	 * game for which we want to recruit, derived from guild
	 * @var string
	 */
	private $game_id;

	/**
	 * role needed. derived from Class
	 * @var string
	 */
	public $role;

	/**
	 * Class id needed
	 * @var int
	 */
	public $class_id;

	/**
	 * how many
	 * @var int
	 */
	public $needed;

	/**
	 * holds possible roles
	 * @var unknown_type
	 */
	public $roles = array();

	/**
	 * possible recruitment statuses
	 * @var array
	 */
	protected $classrecstatus = array();

	/**
	 * possible recruitment colors
	 * @var array
	 */
	protected $classreccolor= array();

    /**
     * Roles class constructor
     *
     */
	public function __construct()
	{
		global $user;

		/**
		 * possible roles that a class can fullfill, as exist in Wow API
		 * note: roleID max 20 chars !!
		 * the role types are *not* stored in database but only in this array. so if you need something else edit this array
		 * the concrete roles are stored in the bbdkp_roles table
		 */
		$this->roles =  array (
				'DPS' 	=> $user->lang ['DAMAGE'],
				'HEAL' 	=> $user->lang ['HEAL'],
				'TANK' 	=> $user->lang ['TANK'],
				'NA' 	=> $user->lang ['NA'],
		);

		$this->classrecstatus = array(
				0 => $user->lang['NA'] ,
				1 => $user->lang['CLOSED'] ,
				2 => $user->lang['LOW'] ,
				3 => $user->lang['MEDIUM'] ,
				4 => $user->lang['HIGH']);

		$this->classreccolor = array(
				0 => "bullet_white.png" ,
				1 => "bullet_white.png" ,
				2 => "bullet_yellow.png" ,
				3 => "bullet_red.png" ,
				4 => "bullet_purple.png");
	}

	/**
	 * inserts one role
	 */
	public function make()
	{
		global $user, $db;

		if($this->guild_id == null)
		{
			trigger_error($user->lang['ERROR_GUILDEMPTY'], E_USER_WARNING);
		}

		if($this->get($this->guild_id, $this->role, $this->class_id) == 1)
		{
			return 0;
		}

		$recruitingguild = new \bbdkp\controller\guilds\Guilds($this->guild_id );
		$this->game_id = $recruitingguild->game_id;
		$query = $db->sql_build_array('INSERT', array(
				'game_id' => $this->game_id,
				'guild_id' => $this->guild_id,
				'role' => $this->role ,
				'class_id' => $this->class_id,
				'needed' => $this->needed,
		));

		$db->sql_query('INSERT INTO ' . BBDKP_ROLES_TABLE . $query);
		return 1;
	}

	/**
	 * update my roles
	 */
	public function update()
	{
		global $db;
		$query = $db->sql_build_array('UPDATE', array(
				'needed' => $this->needed,
		));
		$db->sql_query('UPDATE ' . BBDKP_ROLES_TABLE . ' SET ' . $query . ' WHERE id = ' . $this->id);

	}


	/**
	 * for all classes, initialises class roles
	 * @todo not finished
	 */
	public function init_guildroles()
	{
		global $db;

	}

	/**
	 * construct role object
	 *
	 * @param int $guild_id
	 * @param string $role
	 * @param int $class_id
	 * @return number
	 */
	public function get($guild_id, $role, $class_id)
	{
		global $user, $db;
		$sql = 'SELECT id, game_id, guild_id, role, class_id, needed
				FROM ' . BBDKP_ROLES_TABLE . '
				WHERE guild_id = ' . $this->guild_id . "
				AND role = '" . $this->role . "'
				AND class_id = " . $this->class_id;

		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
		if (! $row)
		{
			return 0;
		}
		else
		{
			$this->guild_id = $row['guild_id'];
			$this->game_id = $row['game_id'];
			$this->role = (isset($user->lang[$row['role']])) ? $user->lang[$row['role']] : $row['role'];
			$this->class_id = $row['class_id'];
			$this->needed = $row['needed'];

			return 1;
		}

	}

	/**
	 * gets all roles needed for one guild
	 */
	public function listroles()
	{
		global $config, $db;

		$sql =  'SELECT a.id as roleid, c.class_id, l.name as class_name, c.colorcode, c.imagename, ';
		$sql .= ' a.game_id, g.id as guild_id,  a.role, a.needed ';
		$sql .= ' FROM ' . CLASS_TABLE . ' c ';
		$sql .= ' INNER JOIN ' . BB_LANGUAGE. ' l ON l.attribute_id = c.class_id AND c.game_id = l.game_id ' ;
		$sql .= ' INNER JOIN ' . GUILD_TABLE . ' g ON c.game_id = g.game_id ';
		$sql .= ' LEFT OUTER JOIN ' . BBDKP_ROLES_TABLE  . ' a ON c.class_id = a.class_id AND a.game_id = c.game_id  ' ;
		$sql .= " WHERE c.class_id > 0 AND l.language= '" . $config['bbdkp_lang'] . "' AND l.attribute = 'class' ";
		$sql .= " AND g.id =  " . $this->guild_id  ;
		$sql .= " ORDER BY c.game_id, c.class_id ";

		$result = $db->sql_query($sql);
		return $result;

	}

	/**
	 * deletes a role
	 */
	public function delete()
	{
		global $db;
		$sql = 'DELETE FROM ' . BBDKP_ROLES_TABLE . '
				WHERE guild_id = ' . $this->guild_id . "
				AND role = '" . $this->role . "'
				AND class_id = " . $this->class_id;
		$db->sql_query($sql);

	}

	/**
	 * get the guilds that recruit
	 * @return array
	 */
	public function get_recruiting_guilds()
	{
		global  $db;
		$sql_array = array(
				'SELECT' => " g.id, g.name, g.emblemurl, rec_status ",
				'FROM' => array(
						GUILD_TABLE => 'g',
						BBDKP_ROLES_TABLE => 'r'),
				'WHERE' => "r.guild_id = g.id AND r.needed > 0 ",
				'GROUP_BY' => ' g.name ',
				'ORDER_BY' => ' g.name ');

		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query($sql);
		return $result;
	}

	/**
	 * get roles, classes that are being recruited
	 * @param int $guild_id
	 * @return array
	 */
	public function recruitblock($guild_id)
	{
		global $config, $db;
		// get recruitment statuses from Roles table
		$sql_array = array(
				'SELECT' => " g.id, g.name, c.class_id, r.role, r.needed, c.imagename, c.colorcode, l.name AS class_name,
					case when r.role='DPS' then r.needed else 0 end AS dps,
					case when r.role='HEAL' then r.needed else 0 end AS heal,
					case when r.role='TANK' then r.needed else 0 end AS tank " ,
				'FROM' => array(
						GUILD_TABLE => 'g',
						BBDKP_ROLES_TABLE => 'r',
						CLASS_TABLE => 'c',
						BB_LANGUAGE => 'l') ,
				'WHERE' => "r.guild_id = g.id
					AND r.guild_id = " . $guild_id . "
					AND c.class_id = r.class_id and c.game_id=r.game_id
					AND c.game_id=l.game_id
					AND l.attribute_id = c.class_id
					AND c.class_id > 0 AND l.attribute_id = c.class_id
					AND l.language = '" . $config['bbdkp_lang'] . "' and l.attribute='class'
					AND r.needed > 0 ",
				'ORDER_BY' => ' l.name ');
		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query($sql);
		return $result;

	}





}

?>