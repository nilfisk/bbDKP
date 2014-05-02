<?PHP
/**
 * Game clas file
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

if (!class_exists('\bbdkp\admin\Admin'))
{
    require("{$phpbb_root_path}includes/bbdkp/admin/admin.$phpEx");
}

/**
 * Games
 *
 * Manages creation of Game
 *
 *   @package bbdkp
 */
class Game extends \bbdkp\admin\Admin
{

    /**
     * primary key in games table
     * @var unknown_type
     */
    private $id;


    /**
     * the game_id (unique key)
     * @var string
     */
    public $game_id;

    /**
     * name of game
     * @var string
     */
    protected $name;

    /**
     * game status (not used atm)
     * @var boolean
     */
    protected $status;

    /**
     * date at which this game was installed
     * @var int
     */
    protected $install_date;

    /**
     * name of game logo png
     * in /images/bbdkp/games/<game_id>
     * @var string
     */
    protected $imagename;

    /**
     * true if armory is on
     * @var boolean
     */
    protected $armory_enabled;

    /**
     * base boss database url
     * @var boolean
     */
    protected $bossbaseurl;

    /**
     * base zone database url
     * @var string
     */
    protected $zonebaseurl;



    /**
     * Game class constructor
     */
    function __construct()
    {
        parent::__construct();
    }

    /**
     * @param boolean $basebossurl
     */
    public function setBossbaseurl($basebossurl)
    {
        $this->bossbaseurl = $basebossurl;
    }

    /**
     * @return boolean
     */
    public function getBossbaseurl()
    {
        return $this->bossbaseurl;
    }

    /**
     * @param string $zonebaseurl
     */
    public function setZonebaseurl($zonebaseurl)
    {
        $this->zonebaseurl = $zonebaseurl;
    }

    /**
     * @return string
     */
    public function getZonebaseurl()
    {
        return $this->zonebaseurl;
    }


    /**
     * @param string $imagename
     */
    public function setImagename($imagename)
    {
        $this->imagename = $imagename;
    }

    /**
     * @return string
     */
    public function getImagename()
    {
        return $this->imagename;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param boolean $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return boolean
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param boolean $armory_enabled
     */
    public function setArmoryEnabled($armory_enabled)
    {
        $this->armory_enabled = $armory_enabled;
    }

    /**
     * @return boolean
     */
    public function getArmoryEnabled()
    {
        return $this->armory_enabled;
    }



    /**
     * adds a Game to database
     */
    public function install()
    {
        //insert into phpbb_bbdkp_games table
        global $user, $phpEx, $phpbb_root_path, $config;

        if ($this->game_id == '')
        {
            \trigger_error ( sprintf ( $user->lang ['ADMIN_INSTALL_GAME_FAILURE'], $this->name ) . E_USER_WARNING );
        }

        if(array_key_exists($this->game_id, $this->preinstalled_games))
        {
            //game id is one of the preinstallable games
            $this->name= $this->preinstalled_games[$this->game_id];
            //fetch installer
            if (!class_exists('\bbdkp\controller\games\install_' . $this->game_id))
            {
                include($phpbb_root_path .'includes/bbdkp/controller/games/library/install_' . $this->game_id . '.' . $phpEx);
            }

            //build name of the namespaced game installer class
            $classname = '\bbdkp\controller\games\install_' . $this->game_id;

            $installgame = new $classname;
            //call the game installer
            $installgame->Install($this->game_id, $this->name,
                $installgame->getBossbaseurl(), $installgame->getZonebaseurl() );

            //is gameworld installed ?
            if(isset($config['bbdkp_gameworld_version']))
            {
                if ($config['bbdkp_gameworld_version'] >= '1.1')
                {
                    if (!class_exists('\bbdkp\controller\games\world_' . $this->game_id))
                    {
                        include($phpbb_root_path .'includes/bbdkp/controller/games/library/world_' . $this->game_id . '.' . $phpEx);
                    }
                    $classname = '\bbdkp\controller\games\world_' . $this->game_id;
                    $installworld = new $classname;
                    $installworld->Install($this->game_id);

                }
            }

        }
        else
        {
            //custom game, this is dispatched to dummy game installer
            if ($this->name == '')
            {
                $this->name='Custom';
            }
            //fetch installer
            if (!class_exists('\bbdkp\controller\games\install_custom'))
            {
                include($phpbb_root_path .'includes/bbdkp/controller/games/library/install_custom.' . $phpEx);
            }
            $installgame = new \bbdkp\controller\games\install_custom;
            //call the game installer
            $installgame->Install($this->game_id, $this->name, $installgame->getBossbaseurl(), $installgame->getZonebaseurl() );

            //is gameworld installed ?
            if(isset($config['bbdkp_gameworld_version']))
            {
                if ($config['bbdkp_gameworld_version'] >= '1.1')
                {
                    if (!class_exists('\bbdkp\controller\games\world_custom'))
                    {
                        include($phpbb_root_path .'includes/bbdkp/controller/games/library/world_custom.' . $phpEx);
                    }
                    $installworld = new \bbdkp\controller\games\world_custom;
                    $installworld->Install($this->game_id);

                }
            }
        }

        //
        // Logging
        //
        $log_action = array(
            'header' => 'L_ACTION_GAME_ADDED' ,
            'L_GAME' => $this->game_id  ,
        );

        $this->log_insert(array(
            'log_type' =>  'L_ACTION_GAME_ADDED',
            'log_action' => $log_action));
    }

    /**
     * deletes a Game from database, including all factions, classes and races.
     */
    public function Delete()
    {

        global $user, $phpEx, $phpbb_root_path, $config;
        if ($this->game_id == '')
        {
            \trigger_error ( sprintf ( $user->lang ['ADMIN_INSTALL_GAME_FAILURE'], $this->name ) . E_USER_WARNING );
        }

        if(array_key_exists($this->game_id, $this->preinstalled_games))
        {
            //fetch installer
            if (!class_exists('\bbdkp\controller\games\install_' . $this->game_id))
            {
                include($phpbb_root_path .'includes/bbdkp/controller/games/library/install_' . $this->game_id . '.' . $phpEx);
            }
            $gameclassname = '\bbdkp\controller\games\install_' . $this->game_id;
        }
        else
        {
            if (!class_exists('\bbdkp\controller\games\install_custom'))
            {
                include($phpbb_root_path .'includes/bbdkp/controller/games/library/install_custom.' . $phpEx);
            }
            $gameclassname = '\bbdkp\controller\games\install_custom';
        }

        //is bossprogress installed ?
        if(isset($config['bbdkp_gameworld_version']))
        {
            if ($config['bbdkp_gameworld_version'] >= '1.1')
            {
                if(array_key_exists($this->game_id, $this->preinstalled_games))
                {
                    if (!class_exists('\bbdkp\controller\games\world_' . $this->game_id))
                    {
                        include($phpbb_root_path .'includes/bbdkp/controller/games/library/world_' . $this->game_id . '.' . $phpEx);
                    }
                    $gameworld_classname = '\bbdkp\controller\games\world_' . $this->game_id;
                }
                else
                {
                    if (!class_exists('\bbdkp\controller\games\install_custom'))
                    {
                        include($phpbb_root_path .'includes/bbdkp/controller/games/library/install_custom.' . $phpEx);
                    }
                    $gameworld_classname = '\bbdkp\controller\games\install_custom';
                }

                $installworld = new $gameworld_classname;
                $installworld->Uninstall($this->game_id, $this->getName());
            }
        }

        //build name of the namespaced game installer class
        $installgame = new $gameclassname;
        //call the game installer
        $installgame->Uninstall($this->game_id, $this->name );

        //
        // Logging
        //
        $log_action = array(
            'header' => 'L_ACTION_GAME_DELETED' ,
            'L_GAME' => $this->game_id  ,
        );

        $this->log_insert(array(
            'log_type' =>  'L_ACTION_GAME_DELETED',
            'log_action' => $log_action));

    }

    /**
     * gets Game info from database
     * read phpbb_bbdkp_games table
     *
     */
    public function Get()
    {
        global $db;
        $sql = 'SELECT id, game_id, game_name, status, imagename, armory_enabled, bossbaseurl, zonebaseurl
    			FROM ' . GAMES_TABLE . "
    			WHERE game_id = '" . $this->game_id . "'";

        $result = $db->sql_query($sql);
        while ($row = $db->sql_fetchrow($result))
        {
            $this->id = $row['id'];
            $this->name = $row['game_name'];
            $this->status= ($row['status'] == 1) ? true : false;
            $this->imagename = $row['imagename'];
            $this->armory_enabled = $row['armory_enabled'];
            $this->bossbaseurl = $row['bossbaseurl'];
            $this->zonebaseurl = $row['zonebaseurl'];
        }
        $db->sql_freeresult($result);

    }

    /**
     * update this game
     */
    public function update()
    {

        //delete from phpbb_bbdkp_games table
        global $db;

        $db->sql_transaction ( 'begin' );

        $query = $db->sql_build_array('UPDATE', array(
            'imagename'      => substr($this->imagename, 0, 20) ,
            'armory_enabled' => $this->armory_enabled,
            'bossbaseurl'    => $this->bossbaseurl,
            'zonebaseurl'    => $this->zonebaseurl
        ));

        $sql = 'UPDATE ' . GAMES_TABLE . ' SET ' . $query . " WHERE game_id = '" . $this->game_id . "'";
        $db->sql_query($sql);

        $db->sql_transaction ('commit');
    }

    /**
     * lists all games
     *
     * @param string $order
     * @return array
     */
    public function listgames($order= 'game_id ASC')
    {
        global $db;
        $gamelist = array();
        $sql = 'SELECT id, game_id, game_name, status, imagename, bossbaseurl, zonebaseurl FROM ' . GAMES_TABLE . ' ORDER BY ' . $order;
        $result = $db->sql_query ( $sql );
        while ($row = $db->sql_fetchrow($result))
        {
            $gamelist[$row['game_id']] = array(
                'id' => $row['id'] ,
                'name' => $row['game_name'] ,
                'game_id' => $row['game_id'] ,
                'status' => $row['status'],
                'imagename' => $row['imagename'],
                'bossbaseurl'   => $row['bossbaseurl'],
                'zonebaseurl'   => $row['zonebaseurl'],
            );
        }
        $db->sql_freeresult($result);

        return $gamelist;

    }



}
