<?php
/**
 * Member class file
 *
 * @package bbdkp
 * @link http://www.bbdkp.com
 * @author Sajaki@gmail.com
 * @copyright 2013 bbdkp
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.3.0
 * @since 1.3.0
 */
namespace bbdkp\controller\members;

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
if (!class_exists('\bbdkp\controller\games\Game'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/games/Game.$phpEx");
}

/**
 * manages member creation
 *
 *   @package bbdkp
 *
 */
class Members extends \bbdkp\admin\Admin
{

	/**
	 * game id
	 * @var string
	 */
	public $game_id;

	/**
	 * primary key in the bbDKP member table
	 * @var integer
	 */
	public $member_id;

	/**
	 * utF-8 member name
	 * @var string
	 */
	protected $member_name;

	/**
	 * status (0 or 1)
	 * @var bool
	 */
	protected $member_status;

	/**
	 * level
	 * @var int
	 */
	protected $member_level;

	/**
	 * race id
	 * @var integer
	 */
	protected $member_race_id;
	/**
	 * race name
	 * @var string
	 */
	protected $member_race;
	/**
	 * Class id
	 * @var integer
	 */
	protected $member_class_id;
	/**
	 * Class name
	 * @var string
	 */
	protected $member_class;

	/**
	 * guild rankid
	 * @var int
	 */
	protected $member_rank_id;

	/**
	 * administrator comment
	 * @var string
	 */
	protected $member_comment;

	/**
	 * member guild join date
	 * @var integer
	 */
	protected $member_joindate;
	/**
	 * join day
	 * @var integer
	 */
	protected $member_joindate_d;
	/**
	 * join month
	 * @var integer
	 */
	protected $member_joindate_mo;
	/**
	 * join year
	 * @var integer
	 */
	protected $member_joindate_y;
	/**
	 * out date
	 * @var int
	 */
	protected $member_outdate;
	/**
	 * out day
	 * @var integer
	 */
	protected $member_outdate_d;
	/**
	 * out month
	 * @var integer
	 */
	protected $member_outdate_mo;
	/**
	 * out year
	 * @var integer
	 */
	protected $member_outdate_y;

	/**
	 * the id of my guild
	 * @var int
	 */
	protected $member_guild_id;

	/**
	 * my guildname
	 * @var string
	 */
	protected $member_guild_name;

	/**
	 * character realm
	 * @var string
	 */
	protected $member_realm;

	/**
	 * region to which the char is on
	 * @var string
	 */
	protected $member_region;


	/**
	 * Allowed regions
	 * readonly!
	 * @var array
	 */
	protected $regionlist = array( 'eu', 'us' , 'kr', 'tw', 'cn', 'sea');

	/**
	 *gender ID 0=male, 1=female
	 * @var int
	 */
	protected $member_gender_id;

	/**
	 * Achievement points
	 * @var int
	 */
	protected $member_achiev;

	/**
	 * url to armory
	 * @var string
	 */
	protected $member_armory_url;

	/**
	 * (wow) battle.net portrait url
	 * @var unknown
	 */
	protected $member_portrait_url;

	/**
	 * The phpBB member id linked to this member
	 * @var int
	 */
	protected $phpbb_user_id;

	/**
	 * Class color
	 * @var string
	 */
	protected $colorcode;

	/**
	 * Race icon
	 * @var string
	 */
	protected $race_image;

	/**
	 * Class icon
	 * @var string
	 */
	protected $class_image;

	/**
	 * current talent builds
	 * @var string
	 */
	protected $talents;

	/**
	 * current title
	 * @var string
	 */
	protected $member_title;

	/**
	 * the role (for possible roles see role class)
	 * @var string
	 */
	protected $member_role;

	/**
	 * contains list of members for guild x
	 * @var array
	 */
	public $guildmemberlist;

	/**
	 * array of guilds
	 * @var array
	 */
	public $guildlist;


    /**
     * Member class constructor
     *
     * @param int $member_id
     * @param array $guildlist
     */
	function __construct($member_id = 0, $guildlist = null)
	{
		global $phpbb_root_path, $phpEx;
		parent::__construct();
		if(isset($member_id))
		{
			if($member_id > 0)
			{
				$this->member_id = $member_id;
				$this->Getmember();
			}
		}
		else
		{
			$this->member_id = 0;
		}

		$this->guildmemberlist = array();
		if($guildlist == null)
		{
			//include the guilds class
			if (!class_exists('\bbdkp\controller\guilds\Guilds'))
			{
				require("{$phpbb_root_path}includes/bbdkp/controller/guilds/Guilds.$phpEx");
			}
			$guild = new \bbdkp\controller\guilds\Guilds();
			$this->guildlist = $guild->guildlist(1);
		}
		else
		{
			$this->guildlist = $guildlist;
		}

	}


    /**
     * member class property getter
     * @param string $fieldName
     * @return null
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
	 * member class property setter
	 * @param string $property
	 * @param string $value
	 */
	public function __set($property, $value)
	{
		global $user;
		switch ($property)
		{
			case 'regionlist':
				// is readonly
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
	 * gets 1 member from database
	 */
	public function Getmember()
	{
		global $user, $db, $config, $phpbb_root_path;

		$sql_array = array(
			'SELECT' => 'm.*, c.colorcode , c.imagename,  c1.name AS member_class, l1.name AS member_race,
						r.image_female, r.image_male,
						g.id as guild_id, g.name as guild_name, g.realm , g.region' ,
			'FROM' => array(
					MEMBER_LIST_TABLE => 'm' ,
					CLASS_TABLE => 'c' ,
					BB_LANGUAGE => 'l1' ,
					RACE_TABLE => 'r' ,
					GUILD_TABLE => 'g') ,
			'LEFT_JOIN' => array(
					array(
						'FROM' => array(
							BB_LANGUAGE => 'c1') ,
						'ON' => "c1.attribute_id = c.class_id
						AND c1.game_id = c.game_id
						AND c1.language= '" . $config['bbdkp_lang'] . "'
						AND c1.attribute = 'class'")) ,
			'WHERE' => "
					l1.attribute_id = r.race_id AND l1.game_id = r.game_id AND l1.language= '" . $config['bbdkp_lang'] . "' AND l1.attribute = 'race'
					AND m.game_id = c.game_id
					AND m.member_class_id = c.class_id
					AND m.game_id = r.game_id
					AND m.member_race_id = r.race_id
					AND m.member_guild_id = g.id
					AND member_id = " . (int) $this->member_id);

		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);

		if ($row)
		{


			$this->member_id = $row['member_id'] ;
			$this->member_name = $row['member_name'] ;
			$this->member_race_id = $row['member_race_id'] ;
			$this->member_race = $row['member_race'] ;
			$this->member_class_id = $row['member_class_id'] ;
			$this->member_class = $row['member_class'] ;
			$this->member_level = $row['member_level'] ;
			$this->member_rank_id = $row['member_rank_id'] ;
			$this->member_comment = $row['member_comment'] ;
			$this->member_gender_id = $row['member_gender_id'] ;
			$this->member_joindate = $row['member_joindate'] ;
			$this->member_role = $row['member_role'] ;
			$this->member_joindate_d = date('j', $row['member_joindate']) ;
			$this->member_joindate_mo = date('n', $row['member_joindate']);
			$this->member_joindate_y = date('Y', $row['member_joindate']) ;
			$this->member_outdate = $row['member_outdate'];
			$this->member_outdate_d = date('j', $row['member_outdate']);
			$this->member_outdate_mo = date('n', $row['member_outdate']);
			$this->member_outdate_y = date('Y', $row['member_outdate']);
			$this->member_guild_name = $row['guild_name'];
			$this->member_guild_id = $row['guild_id'];
			$this->member_realm = $row['realm'];
			$this->member_region = $row['region'];
			$this->member_armory_url = $row['member_armory_url'];
			$this->member_portrait_url = $row['member_portrait_url'];
			$this->phpbb_user_id = $row['phpbb_user_id'];
			$this->member_status = $row['member_status'];
			$this->member_achiev = $row['member_achiev'];
			$this->game_id = $row['game_id'];
			$this->colorcode = $row['colorcode'];
			$race_image = (string) (($row['member_gender_id'] == 0) ? $row['image_male'] : $row['image_female']);
			$this->race_image = (strlen($race_image) > 1) ? $phpbb_root_path . "images/bbdkp/race_images/" . $race_image . ".png" : '';
			$this->class_image = (strlen($row['imagename']) > 1) ? $phpbb_root_path . "images/bbdkp/class_images/" . $row['imagename'] . ".png" : '';
			$this->member_title  = $row['member_title'];

			return $this->member_id;
		}
		else
		{
			// load games class
			$games = new \bbdkp\controller\games\Game();
			if(isset($games->games))
			{
				$this->games = $games->games;
				foreach($this->games as $key => $value)
				{
					$this->game_id = $key;
					break;
				}
			}
			unset($games);
			$this->member_id = 0 ;
			$this->member_name = '';
			$this->member_race_id = 0 ;
			$this->member_race = '' ;
			$this->member_class_id = 0;
			$this->member_class = '' ;
			$this->member_level = 1;
			$this->member_rank_id = 0;
			$this->member_comment = '' ;
			$this->member_gender_id = 0;
			$this->member_role = 'NA';
			$this->member_joindate = $this->time;
			$this->member_joindate_d = date('j', $this->time) ;
			$this->member_joindate_mo = date('n', $this->time);
			$this->member_joindate_y = date('Y', $this->time) ;
			$this->member_outdate = 0;
			$this->member_outdate_d = date('j', 0);
			$this->member_outdate_mo = date('n', 0);
			$this->member_outdate_y = date('Y', 0);
			$this->member_guild_name = '';
			$this->member_guild_id = 0;
			$this->member_realm = '';
			$this->member_region = '';
			$this->member_armory_url = '';
			$this->member_portrait_url = '';
			$this->phpbb_user_id = '';
			$this->member_status = 1;
			$this->member_achiev = 0;
			$this->colorcode = "#8899aa";
			$this->race_image = '';
			$this->class_image = '';
			$this->member_title = '';
            return 0;
		}


	}

    /**
     * get member id given a member name and guild
     *
     * @param string $membername
     * @param int $guild_id optional
     * @return int
     */
	public function get_member_id ($membername, $guild_id = 0)
	{
		global $db;
		if($guild_id !=0)
		{
			$sql = 'SELECT member_id
                FROM ' . MEMBER_LIST_TABLE . '
                WHERE member_name ' . $db->sql_like_expression($db->any_char . $db->sql_escape($membername) . $db->any_char) . '
                AND member_guild_id = ' . (int) $db->sql_escape($guild_id);
		}
		else
		{
			$sql = 'SELECT member_id
                FROM ' . MEMBER_LIST_TABLE . '
                WHERE member_name ' . $db->sql_like_expression($db->any_char . $db->sql_escape($membername) . $db->any_char);
		}

		$result = $db->sql_query($sql);
        //take first one
		while ($row = $db->sql_fetchrow($result))
		{
			$membid = $row['member_id'];
			break;
		}

		$db->sql_freeresult($result);
		if (isset($membid))
		{
			return $membid;
		}
		else
		{
			return 0;
		}
	}

	/**
	 * update member
	 * @param \bbdkp\controller\members\Members $old_member
	 * @return boolean
	 */
	public function Updatemember(\bbdkp\controller\members\Members $old_member)
	{
		global $user, $db;
        global $config;

		if ($this->member_id == 0)
		{
			return false;
		}

		// if user chooses other name then check if the new name already exists. if so refuse update
		// namechange to existing membername is not allowed
		if($this->member_name != $old_member->member_name)
		{
			$sql = 'SELECT count(*) as memberexists
				FROM ' . MEMBER_LIST_TABLE . '
				WHERE member_id <> ' . $this->member_id . "
				AND UPPER(member_name) = UPPER('" . $db->sql_escape($this->member_name) . "')";
			$result = $db->sql_query($sql);
			$countm = $db->sql_fetchfield('memberexists');
			$db->sql_freeresult($result);
			if ($countm != 0)
			{
				trigger_error(sprintf($user->lang['ADMIN_UPDATE_MEMBER_FAIL'], ucwords($this->member_name)) , E_USER_WARNING);
			}
		}

			// check if rank exists
		$sql = 'SELECT count(*) as rankccount
				FROM ' . MEMBER_RANKS_TABLE . '
				WHERE rank_id=' . (int) $this->member_rank_id . ' and guild_id = ' . $this->member_guild_id;
		$result = $db->sql_query($sql);
		$countm = $db->sql_fetchfield('rankccount');
		$db->sql_freeresult($result);
		if ($countm == 0)
		{
			trigger_error($user->lang['ERROR_INCORRECTRANK'], E_USER_WARNING);
		}

		// check level
		$sql = 'SELECT max(class_max_level) as maxlevel FROM ' . CLASS_TABLE;
		$result = $db->sql_query($sql);
		$maxlevel = $db->sql_fetchfield('maxlevel');
		$db->sql_freeresult($result);
		if ($this->member_level > $maxlevel)
		{
			$this->member_level = $maxlevel;
		}

		switch ($this->game_id)
		{
			case 'aion':
				if(trim($this->member_portrait_url) == '')
				{
					$this->generate_portraitlink();
				}
				break;
		}

		// Get first and last raiding dates from raid table.
		$sql = "SELECT b.member_id,
			MIN(a.raid_start) AS startdate,
			MAX(a.raid_start) AS enddate
			FROM " . RAIDS_TABLE . " a
			INNER JOIN " . RAID_DETAIL_TABLE . " b on a.raid_id = b.raid_id
			WHERE  b.member_id = " . $this->member_id . "
			GROUP BY b.member_id ";
		$result = $db->sql_query($sql);
		$startraiddate = (int) $db->sql_fetchfield('startdate', false, $result);
		$endraiddate = (int) $db->sql_fetchfield('enddate', false, $result);
		$db->sql_freeresult($result);

		// if first recorded raiddate is before joindate then update joindate
		if ($startraiddate != 0 && ($this->member_joindate == 0 || $this->member_joindate > $startraiddate))
		{
			$this->member_joindate = $startraiddate;
		}

		// if last raiddate is after outdate or outdate is in future then reset it
		if ($endraiddate !=0 && ($this->member_outdate < $endraiddate || $this->member_outdate > time()))
		{
			$this->member_outdate = mktime(0, 0, 0, 12, 31, 2030);
		}

		// update the data including the phpbb userid
		$query = $db->sql_build_array('UPDATE', array(
			'member_name' => $this->member_name ,
			'member_realm' => $this->member_realm,
			'member_region' => $this->member_region,
			'member_status' => $this->member_status ,
			'member_level' => $this->member_level ,
			'member_race_id' => $this->member_race_id ,
			'member_class_id' => $this->member_class_id,
			'member_role' => $this->member_role,
			'member_rank_id' => $this->member_rank_id ,
			'member_gender_id' => $this->member_gender_id ,
			'member_comment' => $this->member_comment ,
			'member_guild_id' => $this->member_guild_id,
			'member_outdate' => $this->member_outdate,
			'member_joindate' => $this->member_joindate,
			'phpbb_user_id' => $this->phpbb_user_id,
			'member_armory_url' => $this->member_armory_url,
			'member_portrait_url' => $this->member_portrait_url,
			'member_achiev' => $this->member_achiev,
			'game_id' => $this->game_id,
			'member_title' => $this->member_title
			));

		$db->sql_query('UPDATE ' . MEMBER_LIST_TABLE . ' SET ' . $query . '
			WHERE member_id= ' . $this->member_id);

		// if status was 1 before then add a line in user comments and set an adjustment
		if ($this->member_status == 0 && $old_member->member_status == 1)
		{
			// update the comment including the phpbb userid
			$query = $db->sql_build_array('UPDATE', array(
				'member_comment' => $this->member_comment . '
' . sprintf($user->lang['BBDKP_MEMBERDEACTIVATED'] , $user->data['username'], date( 'd.m.y G:i:s', $this->time ))  ,
			));

			$db->sql_query('UPDATE ' . MEMBER_LIST_TABLE . ' SET ' . $query . '
				WHERE member_id= ' . $this->member_id);

            $this->AddMemberAdjustment($config['bbdkp_inactive_point_adj'],$user->lang['ACTION_MEMBER_DEACTIVATED'] );
		}

        // if status was 0 before then add a line in user comments and set an adjustment
        if ($this->member_status == 1 && $old_member->member_status == 0)
        {
            // update the comment including the phpbb userid
            $query = $db->sql_build_array('UPDATE', array(
                'member_comment' => $this->member_comment . '
' . sprintf($user->lang['BBDKP_MEMBERACTIVATED'] , $user->data['username'], date( 'd.m.y G:i:s', $this->time ))  ,
            ));
            $db->sql_query('UPDATE ' . MEMBER_LIST_TABLE . ' SET ' . $query . '
				WHERE member_id= ' . $this->member_id);

            $this->AddMemberAdjustment($config['bbdkp_active_point_adj'],$user->lang['ACTION_MEMBER_ACTIVATED'] );
        }



        $log_action = array(
			'header' => 'L_ACTION_MEMBER_UPDATED' ,
			'L_NAME' => $this->member_name ,
			'L_NAME_BEFORE' => $old_member->member_name,
			'L_LEVELBEFORE' => $old_member->member_level,
			'L_RACE_BEFORE' => $old_member->member_race_id,
			'L_RANK_BEFORE' => $old_member->member_rank_id,
			'L_CLASS_BEFORE' => $old_member->member_class_id,
			'L_GENDER_BEFORE' => $old_member->member_gender_id,
			'L_ACHIEV_BEFORE' => $old_member->member_achiev,
			'L_NAME_AFTER' => $this->member_name,
			'L_LEVELAFTER' => $this->member_level,
			'L_RACE_AFTER' => $this->member_race_id ,
			'L_RANK_AFTER' => $this->member_rank_id,
			'L_CLASS_AFTER' => $this->member_class_id ,
			'L_GENDER_AFTER' => $this->member_gender_id,
			'L_ACHIEV_AFTER' => $this->member_achiev,
			'L_UPDATED_BY' => $user->data['username']);

		$this->log_insert(array(
			'log_type' => $log_action['header'] ,
			'log_action' => $log_action));

        return true;

	}

	/**
	 * delete member from all tables
	 */
	public function Deletemember()
	{
		global $user, $db, $config, $phpEx, $phpbb_root_path;

		$sql = 'DELETE FROM ' . RAID_DETAIL_TABLE . ' where member_id = ' . (int) $this->member_id;
		$db->sql_query($sql);
		$sql = 'DELETE FROM ' . RAID_ITEMS_TABLE . ' where member_id = ' . (int) $this->member_id;
		$db->sql_query($sql);
		$sql = 'DELETE FROM ' . MEMBER_DKP_TABLE . ' where member_id = ' . (int) $this->member_id;
		$db->sql_query($sql);
		$sql = 'DELETE FROM ' . ADJUSTMENTS_TABLE . ' where member_id = ' . (int) $this->member_id;
		$db->sql_query($sql);
		$sql = 'DELETE FROM ' . MEMBER_LIST_TABLE . ' where member_id = ' . (int) $this->member_id;
		$db->sql_query($sql);

		$log_action = array(
			'header' => sprintf($user->lang['ACTION_MEMBER_DELETED'], $this->member_name) ,
			'L_NAME' => $this->member_name ,
			'L_LEVEL' => $this->member_level ,
			'L_RACE' => $this->member_race_id ,
			'L_CLASS' => $this->member_class_id);

		$this->log_insert(array(
			'log_type' => $log_action['header'] ,
			'log_action' => $log_action));

	}

    /**
     * Insert new member
     * @return number
     */
    public function Makemember()
    {
        global $user, $db, $config;

        $error = array ();

        //perform checks

        // check if membername exists
        $sql = 'SELECT count(*) as memberexists
				FROM ' . MEMBER_LIST_TABLE . "
				WHERE member_name= '" . $db->sql_escape(ucwords($this->member_name)) . "'
				AND member_guild_id = " . $this->member_guild_id;
        $result = $db->sql_query($sql);
        $countm = $db->sql_fetchfield('memberexists');
        $db->sql_freeresult($result);
        if ($countm != 0)
        {
            $error[]= $user->lang['ERROR_MEMBEREXIST'];
        }

        if($this->member_rank_id === null)
        {
            $error[]= $user->lang['ERROR_INCORRECTRANK'];
        }

        if($this->member_status === null )
        {
            $this->member_status = 1;
        }

        if($this->member_title === null )
        {
            $this->member_title = ' ';
        }

        // check if rank exists
        $sql = 'SELECT count(*) as rankccount
			FROM ' . MEMBER_RANKS_TABLE . '
			WHERE rank_id=' . (int) $this->member_rank_id . '
			AND guild_id = ' . (int) $this->member_guild_id;
        $result = $db->sql_query($sql);
        $countm = $db->sql_fetchfield('rankccount');
        $db->sql_freeresult($result);
        if ($countm == 0)
        {
            $error[]= $user->lang['ERROR_INCORRECTRANK'];
        }

        if (count($error) > 0)
        {
            $log_action = array(
                'header' 	 => 'L_ACTION_MEMBER_ADDED' ,
                'L_NAME' 	 => ucwords($this->member_name) . implode(',', $error)  ,
                'L_GAME' 	 => $this->game_id,
                'L_LEVEL' 	 => $this->member_level,
                'L_RACE' 	 => $this->member_race_id,
                'L_CLASS' 	 => $this->member_class_id,
                'L_ADDED_BY' => $user->data['username']);

            $this->log_insert(array(
                'log_type' 		=> 'L_ACTION_MEMBER_ADDED' ,
                'log_result' 	=> 'L_FAILED' ,
                'log_action' 	=> $log_action));

            return 0;
        }

        // check level
        $sql = 'SELECT max(class_max_level) as maxlevel FROM ' . CLASS_TABLE;
        $result = $db->sql_query($sql);
        $maxlevel = $db->sql_fetchfield('maxlevel');
        $db->sql_freeresult($result);
        if ($this->member_level > $maxlevel)
        {
            $this->member_level = $maxlevel;
        }

        // if region/realm is nil then default it from guild
        if($this->member_realm =='' or  $this->member_region =='')
        {
            $sql = 'SELECT realm, region FROM ' . GUILD_TABLE . ' WHERE id = ' . (int) $this->member_guild_id;
            $result = $db->sql_query($sql);
            $this->member_realm  = $config['bbdkp_default_realm'];
            $this->member_region = '';
            while ($row = $db->sql_fetchrow($result))
            {
                $this->member_realm = $row['realm'];
                $this->member_region = $row['region'];
            }
        }

        $game = new \bbdkp\controller\games\Game;
        $game->game_id = $this->game_id;
        $game->Get();

        switch ($this->game_id)
        {
            case 'aion':
                $this->member_portrait_url = $this->generate_portraitlink();
        }


        $query = $db->sql_build_array('INSERT', array(
            'member_name' => ucwords($this->member_name) ,
            'member_status' => $this->member_status ,
            'member_level' => $this->member_level,
            'member_race_id' => $this->member_race_id ,
            'member_class_id' => $this->member_class_id ,
            'member_rank_id' => $this->member_rank_id ,
            'member_role' => $this->member_role,
            'member_realm' => $this->member_realm,
            'member_region' => $this->member_region,
            'member_comment' => (string) $this->member_comment ,
            'member_joindate' => (int) $this->member_joindate ,
            'member_outdate' => (int) $this->member_outdate ,
            'member_guild_id' => $this->member_guild_id ,
            'member_gender_id' => $this->member_gender_id ,
            'member_achiev' => $this->member_achiev ,
            'member_armory_url' => (string) $this->member_armory_url ,
            'phpbb_user_id' => (int) $this->phpbb_user_id ,
            'game_id' => (string) $this->game_id ,
            'member_portrait_url' => (string) $this->member_portrait_url,
            'member_title' => $this->member_title
        ));

        $db->sql_query('INSERT INTO ' . MEMBER_LIST_TABLE . $query);

        $this->member_id = $db->sql_nextid();

        // add starting points
        $this->AddMemberAdjustment($config['bbdkp_starting_dkp'],$user->lang['STARTING_DKP'] );

        $log_action = array(
            'header' 	 => 'L_ACTION_MEMBER_ADDED' ,
            'L_NAME' 	 => ucwords($this->member_name)  ,
            'L_GAME' 	 => $this->game_id,
            'L_LEVEL' 	 => $this->member_level,
            'L_RACE' 	 => $this->member_race_id,
            'L_CLASS' 	 => $this->member_class_id,
            'L_ADDED_BY' => $user->data['username']);

        $this->log_insert(array(
            'log_type' => 'L_ACTION_MEMBER_ADDED' ,
            'log_action' => $log_action));

        return $this->member_id;

    }


	/**
	 * fetch info from Armory
	 *
	 * @return integer
	 */
	public function Armory_getmember()
	{
		global $phpEx, $phpbb_root_path;

		$game = new \bbdkp\controller\games\Game;
		$game->game_id = $this->game_id;
		$game->Get();

		if ($this->game_id != 'wow')
		{
			return -1;
		}

		if ($game->getArmoryEnabled() == 0)
		{
			return -1;
		}

        //Initialising the class
        if (!class_exists('\bbdkp\controller\wowapi\BattleNet'))
        {
            require($phpbb_root_path . 'includes/bbdkp/controller/wowapi/BattleNet.' . $phpEx);
        }

		/**
			* available extra fields :
		 * 'guild','stats','talents','items','reputation','titles','professions','appearance',
		 * 'companions','mounts','pets','achievements','progression','pvp','quests'
		 */
		$api = new \bbdkp\controller\wowapi\BattleNet('character', $this->member_region);
		$params = array('guild', 'titles', 'talents' );

		$data = $api->Character->getCharacter($this->member_name, $this->member_realm, $params);
		unset($api);

		// if $data == false then there is no character data, so
		if($data != false)
		{
			$this->member_level = isset($data['level']) ? $data['level'] : $this->member_level;
			$this->member_race_id = isset($data['race']) ? $data['race'] : $this->member_race_id;
			$this->member_class_id = isset($data['class']) ? $data['class'] : $this->member_class_id;

			/*
			 * select the build
			*/
			$buildid = 0;
			if(isset($data['talents'][0]) && isset( $data['talents'][1]) )
			{
				if( isset($data['talents'][0]['selected']))
				{
					$buildid = 0;
				}
				elseif(isset($data['talents'][1]['selected']))
				{
					$buildid = 1;
				}
			}
			elseif(isset( $data['talents'][0]))
			{
				$buildid = 0;
			}
			elseif(isset( $data['talents'][1]))
			{
				$buildid = 1;
			}
			$role = isset($data['talents'][$buildid]['spec']['role']) ? $data['talents'][$buildid]['spec']['role'] : 'NA';
			$this->member_role = $role;

			$this->member_gender_id = isset($data['gender']) ? $data['gender'] : $this->member_gender_id;
			$this->member_achiev = isset($data['achievementPoints']) ? $data['achievementPoints'] : $this->member_achiev;

			if(isset($data['name']))
			{
				$this->member_armory_url = sprintf('http://%s.battle.net/wow/en/', $this->member_region) . 'character/' . $this->member_realm . '/' . $data ['name'] . '/simple';
			}

			if(isset($data['thumbnail']))
			{
				$this->member_portrait_url = sprintf('http://%s.battle.net/static-render/%s/', $this->member_region, $this->member_region) . $data['thumbnail'];
			}

			if(isset($data['realm']))
			{
				$this->member_realm = $data['realm'];
			}

			if (isset($data['guild']))
			{
				$found=false;
				foreach($this->guildlist as $guild)
				{
					if($guild['name'] == $data['guild']['name'])
					{
						$this->member_guild_id = $guild['id'];
						$this->member_guild_name = $guild['name'];
						$this->member_rank_id = $guild['joinrank'];
						$found=true;
						break;
					}
				}
				if($found==false)
				{
					$this->member_guild_id = 0;
					$this->member_rank_id = 99;
				}
			}
			else
			{
				$this->member_guild_id = 0;
				$this->member_rank_id = 99;
			}

			if (isset($data['titles']))
			{
				foreach($data['titles'] as $key => $title)
				{
					if (isset($title['selected']))
					{
						$this->member_title = $title['name'];
						break;
					}
				}
			}

			//if the last logged-in date is > 3 months ago then disable the account
			if( isset($data['lastModified']))
			{
				$latest = $data['lastModified']/1000;
				$diff = \round( \abs ( \time() - $latest) / 60 / 60 / 24, 2) ;
				if($diff > 90 && $this->member_status == 1)
				{
					$this->deactivate_wow($diff);
                   	return 0;
				}

			}

			return 1;

		}
		else
		{
            //not found in armory
			$this->deactivate_wow('API Error');
			return - 1;
		}

	}


	/**
	 * if player not found in BATTLE.NET or if last activity > 90 days ago
     * then deactivate member if currently active.
     *
	 * @param (integer or string) $daysago
	 */
	private function deactivate_wow($daysago)
	{
		global $config, $user;
        if($this->member_status == "1" )
        {
            $this->member_status = 0;
            $log_action = array(
                'header' 	 => 'L_ACTION_MEMBER_DEACTIVATED' ,
                'L_NAME' 	 => \ucwords($this->member_name)  ,
                'L_DAYSAGO'  => $daysago,
                'L_ADDED_BY' => $user->data['username']);

            $this->log_insert(array(
                'log_type' 		=> 'L_ACTION_MEMBER_DEACTIVATED' ,
                'log_result' 	=> 'L_SUCCESS' ,
                'log_action' 	=> $log_action));
        }

	}

	/**
	 * activates all checked members
     *
     *  for each member in $mwindow
     *   get member
     *   if member is in $mlist
     *       add activation dkp
     *   else
     *      if member status was active then deactivate and add deactivation dkp
     *
     * @param array $mlist
	 * @param array $mwindow
	 */
	public function Activatemembers(array $mlist, array $mwindow)
	{
        global $config, $db, $user;
		foreach($mwindow as $member_id)
        {
            $changed = false;
            $this->member_id = $member_id;
            $this->Getmember();
            if (in_array($member_id,$mlist))
            {
                //found in $POST, so if inactive before then activate
                if($this->member_status == "0")
                {
                    $changed = true;
                    $this->member_status = "1";
                    $this->AddMemberAdjustment($config['bbdkp_active_point_adj'],$user->lang['ACTION_MEMBER_ACTIVATED'] );
                }
            }
            else
            {
                //not found in $POST, so if active before then deactivate
                if($this->member_status == "1")
                {
                    $changed = true;
                    $this->member_status = "0";
                    $this->AddMemberAdjustment($config['bbdkp_inactive_point_adj'],$user->lang['ACTION_MEMBER_DEACTIVATED'] );
                }
            }

            if ($changed)
            {
                $query = $db->sql_build_array('UPDATE', array(
                    'member_status' => $this->member_status ,
                ));

                $db->sql_query('UPDATE ' . MEMBER_LIST_TABLE . ' SET ' . $query . '
                WHERE member_id= ' . $this->member_id);
            }

        }


	}

	/**
	 * generates a standard portrait image url for aion based on characterdata
	 */
	private function generate_portraitlink()
	{
		global $phpbb_root_path;
	    $this->member_portrait_url = $phpbb_root_path . 'images/roster_portraits/aion/' . $this->member_race_id . '_' . $this->member_gender_id . '.jpg';

	}

	/**
	 * function for removing member from guild but leave him in the member table.;
	 * @param string $member_name
	 * @param int $guild_id
	 * @return boolean
	 * @todo fix this
	 */
	public function GuildKick($member_name, $guild_id)
	{
		global $db, $user;
		// find id for existing member name
		$sql = "SELECT *
				FROM " . MEMBER_LIST_TABLE . "
				WHERE member_name = '" . $db->sql_escape($member_name) . "' and member_guild_id = " . (int) $guild_id;
		$result = $db->sql_query($sql);
		// get old data
		while ($row = $db->sql_fetchrow($result))
		{
			$this->old_member = array(
					'member_id' => $row['member_id'] ,
					'member_rank_id' => $row['member_rank_id'] ,
					'member_guild_id' => $row['member_guild_id'] ,
					'member_comment' => $row['member_comment']);
		}
		$db->sql_freeresult($result);
		$sql_arr = array(
			'member_rank_id' => 99 ,
			'member_comment' => "Member left " . date("F j, Y, g:i a") . ' by Armory plugin' ,
			'member_outdate' => $this->time ,
			'member_guild_id' => 0);
		$sql = 'UPDATE ' . MEMBER_LIST_TABLE . '
			SET ' . $db->sql_build_array('UPDATE', $sql_arr) . '
			WHERE member_id = ' . (int) $this->old_member['member_id'] . ' and member_guild_id = ' . (int) $this->old_member['member_guild_id'];

		$db->sql_query($sql);

		$log_action = array(
			'header' => 'L_ACTION_MEMBER_UPDATED' ,
			'L_NAME' => $member_name ,
			'L_RANK_BEFORE' => $this->old_member['member_rank_id'] ,
			'L_COMMENT_BEFORE' => $this->old_member['member_comment'] ,
			'L_RANK_AFTER' => 99 ,
			'L_COMMENT_AFTER' => "Member left " . date("F j, Y, g:i a") . ' by Armory plugin' ,
			'L_UPDATED_BY' => $user->data['username']);

		$this->log_insert(array(
			'log_type' => $log_action['header'] ,
			'log_action' => $log_action));

		return true;
	}

	/**
	 * Process Blizzard Battle.NET Character API data
	 * @param array $memberdata
	 * @param int $guild_id
	 * @param char $region
	 * @param int $min_armory
	 */
	public function WoWArmoryUpdate($memberdata, $guild_id, $region, $min_armory)
	{
		global $user, $db;

		$member_ids = array();
		$oldmembers = array();
		$newmembers = array();

		/* GET OLD RANKS */
		$sql = ' select member_name, member_id FROM ' . MEMBER_LIST_TABLE . '
				WHERE member_guild_id =  ' . (int) $guild_id . "
				AND game_id='wow' order by member_name ASC";
		$result = $db->sql_query ($sql);
		while ($row = $db->sql_fetchrow ($result))
		{
			$oldmembers[] = $row['member_name'];

			//this is to find the memberindex when updating
			$member_ids[ bin2hex($row['member_name'])] = $row['member_id'];

		}
		$db->sql_freeresult ( $result );

		foreach($memberdata as $mb)
		{
			$newmembers[] = $mb['character']['name'];
		}

		// get the new members to insert
		$to_add = array_diff($newmembers, $oldmembers);

		// start transaction
		$db->sql_transaction('begin');
		$query = array();
		foreach($memberdata as $mb)
		{
			if (in_array($mb['character']['name'], $to_add) && $mb['character']['level'] >= $min_armory )
			{
				if(!isset( $mb['character']['realm']))
				{
					$realm = 'unknown';
				}
				else
				{
					$realm = $mb['character']['realm'];
				}

				if(isset($mb['character']['realm']))
				{
					$this->member_realm = $mb['character']['realm'];
				}
				$this->game_id ='wow';
				$this->member_region = $region;
				$this->member_guild_id = $guild_id;
				$this->member_rank_id = isset($mb['rank']) ? $mb['rank'] : 1;
				$this->member_name = $mb['character']['name'];
				$this->member_level = (int) $mb['character']['level'];
				$this->member_gender_id = (int) $mb['character']['gender'];
				$this->member_race_id = (int) $mb['character']['race'];
				$this->member_class_id = (int) $mb['character']['class'];
				$this->member_achiev = (int) $mb['character']['achievementPoints'];
				$this->member_armory_url = sprintf('http://%s.battle.net/wow/en/', $region) . 'character/' . $realm . '/' . $this->member_name . '/simple';
				$this->member_status = 1;
                $this->member_role = 'NA';
				$this->member_comment = sprintf($user->lang['ADMIN_ADD_MEMBER_SUCCESS'], $this->member_name, date("F j, Y, g:i a") );
				$this->member_joindate = $this->time;
				$this->member_outdate = mktime ( 0, 0, 0, 12, 31, 2030 );
				$this->member_portrait_url = sprintf('http://%s.battle.net/static-render/%s/', $region, $region) . $mb['character']['thumbnail'];
				$this->member_title = '';
				if (isset($mb['titles']))
				{
					foreach($mb['titles'] as $key => $title)
					{
						if (isset($title['selected']))
						{
							$this->member_title = $title['name'];
						}
					}
				}
                $this->Makemember();
			}
		}


		// get the members to update
		$to_update = array_intersect($newmembers, $oldmembers);
		foreach($memberdata as $mb)
		{

			if(!isset( $mb['character']['realm']))
			{
				$realm = 'unknown';
			}
			else
			{
				$realm = $mb['character']['realm'];
			}

			if (in_array($mb['character']['name'], $to_update))
			{
				$member_id =  (int) $member_ids[bin2hex($mb['character']['name'])];
				$this->game_id ='wow';
				$this->member_region = $region;
				$this->member_realm = $mb['character']['realm'];
				$this->member_rank_id = $mb['rank'];
				$this->member_name = $mb['character']['name'];
				$this->member_guild_id = $guild_id;
				$this->member_level = (int) $mb['character']['level'];
				$this->member_gender_id = (int) $mb['character']['gender'];
				$this->member_race_id = (int) $mb['character']['race'];
				$this->member_class_id = (int) $mb['character']['class'];
				$this->member_achiev = (int) $mb['character']['achievementPoints'];
				$this->member_armory_url = sprintf('http://%s.battle.net/wow/en/', $region) . 'character/' . $realm  . '/' . $this->member_name . '/simple';
				$this->member_portrait_url = sprintf('http://%s.battle.net/static-render/%s/', $region, $region) . $mb['character']['thumbnail'];

				$sql_ary = array (
					'member_name' => ucwords($this->member_name) ,
					'member_level' => $this->member_level,
					'member_race_id' => $this->member_race_id ,
					'member_realm' => $this->member_realm,
					'member_region' => $this->member_region,
					'member_class_id' => $this->member_class_id ,
					'member_rank_id' => $this->member_rank_id ,
					'member_guild_id' => $this->member_guild_id ,
					'member_gender_id' => $this->member_gender_id ,
					'member_achiev' => $this->member_achiev ,
					'member_armory_url' => (string) $this->member_armory_url ,
					'member_portrait_url' => (string) $this->member_portrait_url,
				);


				$sql = 'UPDATE ' . MEMBER_LIST_TABLE . '
						SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
						WHERE member_id = ' . $member_id;
				$db->sql_query($sql);
			}
		}
	}

	/**
	 * Enter joindate for guildmember (query is cached for 1 week !)
	 *
	 * @param int $member_id
	 * @return unknown
	 *
	 */
	public function get_joindate($member_id)
	{
		// get member joindate
		global $db;
		$sql = 'SELECT member_joindate  FROM ' . MEMBER_LIST_TABLE . ' WHERE member_id = ' . $member_id;
		$result = $db->sql_query($sql,3600);
		$joindate = $db->sql_fetchfield('member_joindate');

		$db->sql_freeresult($result);
		return $joindate;

	}


    /**
     * ACP listmembers grid
     * get a member list for given guild
     *
     * @param int $guild_id
     * @param bool $assignedonly
     */
	public function listallmembers($guild_id = 0, $assignedonly=false)
	{
		global $db;

		$sql_array = array(
			'SELECT'    => 'r.rank_name, m.member_id ,m.member_name ',
			'FROM'      => array(
					MEMBER_LIST_TABLE 	  => 'm',
					MEMBER_RANKS_TABLE    => 'r',
			),

			'WHERE'     =>  ' m.member_guild_id = r.guild_id
								AND m.member_rank_id = r.rank_id
								AND r.rank_hide != 1 ',
			'ORDER_BY' => 'm.member_rank_id asc, m.member_level desc, m.member_name asc'
		);

		if($assignedonly == true)
		{
			$sql_array['WHERE'] .= ' AND m.phpbb_user_id = 0 ';
		}

		if($guild_id != 0)
		{
			$sql_array['WHERE'] .= ' AND m.member_guild_id = ' . $guild_id;
		}

		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query ( $sql );

		while ( $row = $db->sql_fetchrow ( $result ) )
		{
			$this->guildmemberlist[] = array(
				'member_id' 	=> $row['member_id'],
				'member_name' 	=> $row['member_name'],
				'rank_name'  	=> $row['rank_name'],
			);
		}

		$db->sql_freeresult( $result );
	}

	/**
	 * Claim a character to your phpbb User
	 */
	public function Claim_Member()
	{
		global $db, $user;

		$sql_ary = array(
				'phpbb_user_id'	=> $user->data['user_id'],
		);

		$sql = 'UPDATE ' . MEMBER_LIST_TABLE . '
						SET ' . $db->sql_build_array('UPDATE', $sql_ary) . '
						WHERE member_id = ' . $this->member_id;
		$db->sql_query($sql);


	}

	/**
	 * check if user exceeded allowed character count
	 *
	 * @return boolean
	 */
	public function has_reached_maxbbdkpaccounts()
	{
		global $config, $user, $db;
		$sql = 'SELECT count(*) as charcount
				FROM ' . MEMBER_LIST_TABLE . '
				WHERE phpbb_user_id = ' . (int) $user->data['user_id'];
		$result = $db->sql_query($sql);
		$countc = $db->sql_fetchfield('charcount');
		$db->sql_freeresult($result);

		if ($countc >= $config['bbdkp_maxchars'])
		{
			return true;
		}
		else
		{
			return false;
		}
	}

    /**
     * Frontview : member Roster listing
     *
     * @param int $start
     * @param string $mode ('listing' or 'class')
     * @param boolean $query_by_armor
     * @param boolean $query_by_class
     * @param string $filter
     * @param number $game_id
     * @param int|number $guild_id optional=0
     * @param int|number $class_id optional=0
     * @param int|number $race_id optional=0
     * @param int|number $level1 optional =1
     * @param int|number $level2 optional = 200
     * @param boolean $mycharsonly optional = false
     * @return array  (membercount, sql_fetchrowset of all rows)
     */
    public function getmemberlist($start, $mode, $query_by_armor, $query_by_class, $filter,
			$game_id, $guild_id = 0, $class_id = 0, $race_id = 0, $level1=0, $level2=200, $mycharsonly=false)
	{
		global $db, $config, $user, $phpbb_root_path;
		$sql_array = array();

		$sql_array['SELECT'] =  'm.member_id, m.game_id, m.member_guild_id,  m.member_name, m.member_level, m.member_race_id, e1.name as race_name,
    		m.member_class_id, m.member_gender_id, m.member_rank_id, m.member_achiev, m.member_armory_url, m.member_portrait_url, m.member_status,
    		r.rank_prefix , r.rank_name, r.rank_suffix, e.image_female, e.image_male,
    		g.name as guildname, g.realm, g.region, c1.name as class_name, c.colorcode, c.imagename, m.phpbb_user_id, u.username, u.user_colour  ';

		$sql_array['FROM'] = array(
				MEMBER_LIST_TABLE    =>  'm',
				CLASS_TABLE          =>  'c',
				GUILD_TABLE          =>  'g',
				MEMBER_RANKS_TABLE   =>  'r',
				RACE_TABLE           =>  'e',
				BB_LANGUAGE			 =>  'e1');

		$sql_array['LEFT_JOIN'] = array(
				array(
						'FROM'  => array(USERS_TABLE => 'u'),
						'ON'    => 'u.user_id = m.phpbb_user_id '),
				array(
						'FROM'  => array(BB_LANGUAGE => 'c1'),
						'ON'    => "c1.attribute_id = c.class_id AND c1.language= '" . $config['bbdkp_lang'] . "' AND c1.attribute = 'class'  and c1.game_id = c.game_id "
				));

		$sql_array['WHERE'] = " c.class_id = m.member_class_id
			AND c.game_id = m.game_id
			AND e.race_id = m.member_race_id
			AND e.game_id = m.game_id
			AND g.id = m.member_guild_id
			AND m.member_status = 1
			AND r.guild_id = m.member_guild_id
			AND r.rank_id = m.member_rank_id AND r.rank_hide = 0 
			 ";

		if ($mycharsonly ==false)
		{
			$sql_array['WHERE'] .= " AND m.member_level >= ".  intval($config['bbdkp_minrosterlvl']) ;
		}

		$sql_array['WHERE'] .= " AND m.member_rank_id != 99
			AND e1.attribute_id = e.race_id AND e1.language= '" . $config['bbdkp_lang'] . "'
			AND e1.attribute = 'race' and e1.game_id = e.game_id";


		if($game_id != '' )
		{
			$sql_array['WHERE'] .= " AND m.game_id =  '" .  $db->sql_escape($game_id) . "'";
		}

		if($mycharsonly == true)
		{
			$sql_array['WHERE'] .= ' AND m.phpbb_user_id =  ' . $user->data['user_id'];
		}

		if($guild_id > 0)
		{
			$sql_array['WHERE'] .= " AND m.member_guild_id =  " . $guild_id;
		}

		if($class_id > 0 && $query_by_class == true)
		{
			$sql_array['WHERE'] .= " AND m.member_class_id =  " . $class_id;
		}

		if($race_id > 0)
		{
			$sql_array['WHERE'] .= " AND m.member_race_id =  " . $race_id;
		}

		if($level1 > 0)
		{
			$sql_array['WHERE'] .= " AND m.member_level >=  " . $level1;
		}

		if($level2 != 200)
		{
			$sql_array['WHERE'] .= " AND m.member_level <=  " . $level2;
		}

		if ($filter != '' && $query_by_armor == true)
		{
			$sql_array['WHERE'] .= " AND c.class_armor_type =  '" . $db->sql_escape ( $filter ) . "'";
		}

		// order
		$sort_order = array(
				0 => array('m.member_name', 'm.member_name desc'),
				1 => array('m.game_id', 'm.member_name desc'),
				2 => array('m.member_class_id', 'm.member_class_id desc'),
				3 => array('m.member_rank_id', 'm.member_rank_id desc'),
				4 => array('m.member_level', 'm.member_level  desc'),
				5 => array('u.username', 'u.username desc'),
				6 => array('m.member_achiev', 'm.member_achiev  desc')
		);

		$current_order = $this->switch_order($sort_order);

		if( $mode == 1)
		{
			$sql_array['ORDER_BY']  = " m.member_class_id, " . $current_order['sql'];
		}
		elseif ($mode == 0)
		{
			$sql_array['ORDER_BY']  = $current_order['sql'];
		}

		$sql = $db->sql_build_query('SELECT', $sql_array);

		if ($mode == 0)
		{
			// LISTING MODE
			$member_count=0;

			// get membercount in selection
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$member_count++;
			}

			//now get wanted window
			$result = $db->sql_query_limit ( $sql, $config ['bbdkp_user_llimit'], $start );
			$dataset = $db->sql_fetchrowset($result);
		}
		elseif ($mode == 1)
		{
			// CLASS mode
			$result = $db->sql_query($sql);
			$dataset = $db->sql_fetchrowset($result);
			$member_count = count($dataset);
		}

		$db->sql_freeresult($result);

		$characters = array();

		foreach ($dataset as $row)
		{
			$characters[]= array(
					'game_id' => $this->games[$row['game_id']],
					'member_id' => $row['member_id'],
					'member_name' => $row['member_name'],
					'guildname' => $row['guildname'],
					'realm'	=> $row['realm'],
					'region'	=> $row['region'],
					'colorcode' => $row['colorcode'],
					'member_class_id' => $row['member_class_id'],
					'class_name' => $row['class_name'],
					'class_image' => $phpbb_root_path . 'images/bbdkp/class_images/' . $row['imagename'] . '.png',
					'race_name' => $row['race_name'],
					'member_gender_id' => $row['member_gender_id'],
					'race_image' =>  $phpbb_root_path . 'images/bbdkp/race_images/' . (($row['member_gender_id']==0) ? $row['image_male'] : $row['image_female']) . '.png',
					'image_female' => $row['image_female'],
					'image_male' => $row['image_male'],
					'member_rank'	=> $row['rank_prefix'] . ' ' . $row['rank_name'] . ' ' . $row['rank_suffix'],
					'member_level' => $row['member_level'],
					'username' => get_username_string('full', $row['phpbb_user_id'], $row['username'], $row['user_colour']),
					'member_portrait_url' => $row['member_portrait_url'],
					'member_armory_url' => $row['member_armory_url'],
					'member_achiev' => $row['member_achiev'],
                    'member_status' => $row['member_status'],
			);
		}
		$db->sql_freeresult($result);

		return array($characters, $current_order, $member_count);
	}

	/**
	 * Frontview : gets all classes in roster selection
	 *
	 * @param string $filter
	 * @param bool $query_by_armor
	 * @param int $classid
	 * @param string $game_id
	 * @param int $guild_id
	 * @param int $race_id
	 * @param int $level1
	 * @param int $level2
	 * @return array
	 */
	public function get_classes($filter, $query_by_armor, $classid, $game_id, $guild_id = 0,  $race_id = 0, $level1=0, $level2=200)
	{
		global $db, $user, $config;
		$sql_array = array(
				'SELECT'    => 'c.class_id, c1.name as class_name, c.imagename, c.colorcode' ,
				'FROM'      => array(
						MEMBER_LIST_TABLE    =>  'm',
						CLASS_TABLE          =>  'c',
						BB_LANGUAGE			=>  'c1',
						MEMBER_RANKS_TABLE   =>  'r',
				),
				'WHERE'     => " c.class_id = m.member_class_id
    							AND c.game_id = m.game_id
    							AND r.guild_id = m.member_guild_id
    							AND r.rank_id = m.member_rank_id AND r.rank_hide = 0
    							AND c1.attribute_id =  c.class_id AND c1.language= '" . $config['bbdkp_lang'] . "' AND c1.attribute = 'class'
    							AND (c.game_id = '" . $db->sql_escape($game_id) . "')
    							AND c1.game_id=c.game_id

    							",
				'ORDER_BY'  =>  'c1.name asc',
				'GROUP_BY'  =>  'c.class_id, c1.name, c.imagename, c.colorcode'
		);

		// filters
		if ($filter != $user->lang['ALL'] && $query_by_armor == true)
		{
			$sql_array['WHERE'] .= " AND c.class_armor_type =  '" . $db->sql_escape ( $filter ) . "'";
		}

		if($guild_id > 0)
		{
			$sql_array['WHERE'] .= " AND m.member_guild_id =  " . $guild_id;
		}

		if($filter != $user->lang['ALL']  && $classid > 0)
		{
			$sql_array['WHERE'] .= " AND m.member_class_id =  " . $classid;
		}

		if($race_id > 0)
		{
			$sql_array['WHERE'] .= " AND m.member_race_id =  " . $race_id;
		}

		if($level1 > 0)
		{
			$sql_array['WHERE'] .= " AND m.member_level >=  " . $level1;
		}

		if($level2 != 200)
		{
			$sql_array['WHERE'] .= " AND m.member_level <=  " . $level2;
		}

		$sql = $db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query($sql);
		$dataset = $db->sql_fetchrowset($result);
		$db->sql_freeresult($result);
		unset ($result);
		return $dataset;
	}

    /**
     * @param float $adj
     * @param string $reason
     */
    private function AddMemberAdjustment($adj, $reason)
    {
        global $user;
        if (!class_exists('\bbdkp\controller\adjustments\Adjust')) {
            global $phpbb_root_path, $phpEx;
            require("{$phpbb_root_path}includes/bbdkp/controller/adjustments/Adjust.$phpEx");
        }

        $newadjust = new \bbdkp\controller\adjustments\Adjust;
        $newadjust->setMemberId($this->member_id);
        $newadjust->setAdjustmentValue($adj);
        $newadjust->setAdjustmentReason($reason);
        $newadjust->setCanDecay(0);
        $newadjust->setAdjDecay(0);
        $newadjust->setDecayTime(0);
        $newadjust->setAdjustmentDate($this->member_joindate);
        $newadjust->setAdjustmentGroupkey($this->gen_group_key($newadjust->getAdjustmentDate(), $newadjust->getAdjustmentReason(), $newadjust->getAdjustmentValue()));
        $newadjust->setAdjustmentAddedBy($user->data['username']);
        foreach ($newadjust->getDkpsys() as $pool) {
            $newadjust->setAdjustmentDkpid($pool['id']);
            $newadjust->add();
        }
        unset($newadjust);
    }

}