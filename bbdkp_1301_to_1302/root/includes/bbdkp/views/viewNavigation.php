<?php
/**
 * left front navigation block
 *
 * @link http://www.bbdkp.com
 * @author Sajaki@gmail.com
 * @copyright 2013 bbdkp
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.3.0
 */
namespace bbdkp\views;
/**
 * @ignore
 */
if ( !defined('IN_PHPBB') OR !defined('IN_BBDKP') )
{
	exit;
}

class viewNavigation extends \bbdkp\admin\Admin implements iViews
{

    /**
     * @return array
     */
    public function getArmorType()
    {
        return $this->armor_type;
    }

    /**
     * @return mixed
     */
    public function getClassId()
    {
        return $this->class_id;
    }

    /**
     * @return array
     */
    public function getClassarray()
    {
        return $this->classarray;
    }

    /**
     * @return array
     */
    public function getClassname()
    {
        return $this->classname;
    }

    /**
     * @return int
     */
    public function getDefaultpool()
    {
        return $this->defaultpool;
    }

    /**
     * @return int
     */
    public function getDkpsysId()
    {
        return $this->dkpsys_id;
    }

    /**
     * @return string
     */
    public function getDkpsysName()
    {
        return $this->dkpsys_name;
    }

    /**
     * @return string
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @return string
     */
    public function getGameId()
    {
        return $this->game_id;
    }

    /**
     * @return int
     */
    public function getGuildId()
    {
        return $this->guild_id;
    }

    /**
     * @return \bbdkp\views\unknown
     */
    public function getQueryByClass()
    {
        return $this->query_by_class;
    }

    /**
     * @return boolean
     */
    public function getQueryByArmor()
    {
        return $this->query_by_armor;
    }

    /**
     * @return boolean
     */
    public function getQueryByPool()
    {
        return $this->query_by_pool;
    }

    /**
     * @return mixed
     */
    public function getRaceId()
    {
        return $this->race_id;
    }

    /**
     * @return boolean
     */
    public function getShowAll()
    {
        return $this->show_all;
    }

    /**
     * @return mixed
     */
    public function getLevel1()
    {
        return $this->level1;
    }

    /**
     * @return mixed
     */
    public function getLevel2()
    {
        return $this->level2;
    }

    /**
     * guild id
     * @var int
     */
    private $guild_id;
    /**
     * game id
     * @var string
     */
    private $game_id;
    /**
     * filter by pool ?
     * @var boolean
     */
    private $query_by_pool = true;
    /**
     * filter by armor ?
     * @var boolean
     */
    private $query_by_armor = false;
    /**
     * filter by class ?
     * @var unknown
     */
    private $query_by_class = false;
    /**
     * the filter string
     * @var string
     */
    private $filter = '';
    /**
     * show all members even not active ?
     * @var boolean
     */
    private $show_all = false;

    /**
     * pool id
     * @var integer
     */
    private $dkpsys_id = 0;
    private $defaultpool = 0;

    /**
     * name of pool
     * @var string
     */
    private $dkpsys_name = '';

    private $level1;
    private $level2;

    /**
     * values of armor types
     * @var array
     */
    private $armor_type = array();
    private $classname = array();
    private $classarray = array();
    /**
     * race id from pulldown
     */
    private $race_id = 0;

    /**
     * class id from pulldown
     */
    private $class_id = 0;

    private $page;

    function __construct($page)
    {

        $this->page = $page;
        $this->buildNavigation();
    }

    public function buildpage(viewNavigation $Navigation)
    {

    }

    private function buildNavigation()
    {
        global $phpbb_root_path, $phpEx, $user, $db, $template, $config;

        if (isset($config['bbdkp_plugin_bbtips_version']))
        {
            //check if config value and parser file exist.
            if($config['bbdkp_plugin_bbtips_version'] >= '1.0.4')
            {
                if ( !class_exists('bbtips_parser'))
                {
                    require($phpbb_root_path . 'includes/bbdkp/bbtips/bbtips_parser.' . $phpEx);
                }
                $this->bbtips = new \bbtips_parser;
            }
        }

        // get inputs
        $this->guild_id = request_var(URI_GUILD, request_var('hidden_guild_id', 0) );

        $this->show_all = ( request_var ( 'show', request_var ( 'hidden_show', '' )) == $user->lang['ALL']) ? true : false;

        //include the guilds class
        if (!class_exists('\bbdkp\controller\guilds\Guilds'))
        {
            require("{$phpbb_root_path}includes/bbdkp/controller/guilds/Guilds.$phpEx");
        }
        $guilds = new \bbdkp\controller\guilds\Guilds();

        $guildlist = $guilds->guildlist(1);
        if(count($guildlist) > 0)
        {
            foreach ($guildlist as $g)
            {
                //assign guild_id property
                if($this->guild_id==0)
                {
                    //if there is a default guild
                    if($g['guilddefault'] == 1)
                    {
                        $this->guild_id = $g['id'];
                    }
                    elseif($g['membercount'] > 1)
                    {
                        $this->guild_id = $g['id'];
                    }

                    //if guild id field still 0
                    if($this->guild_id == 0 && $g['id'] > 0)
                    {
                        $this->guild_id = $g['id'];
                    }
                }

                //populate guild popup
                if($g['id'] > 0) // exclude guildless
                {
                    $template->assign_block_vars('guild_row', array(
                        'VALUE' => $g['id'] ,
                        'SELECTED' => ($g['id'] == $this->guild_id ) ? ' selected="selected"' : '' ,
                        'OPTION' =>  $g['name']));
                }
            }

        }
        else
        {
            trigger_error('ERROR_NOGUILD', E_USER_WARNING );
        }

        $guilds->guildid = $this->guild_id;
        $guilds->Getguild();
        $this->game_id = $guilds->game_id;


        $this->race_id =  request_var('race_id',0);
        $this->level1 =  request_var('$level1',0);
        $this->level2 =  request_var('classid', 200);

        $this->filter= request_var('filter', $user->lang['ALL']);
        $this->query_by_armor = false;
        $this->query_by_class = false;

        $this->armor();
        if ($this->filter!= $user->lang['ALL'])
        {
            if (array_key_exists ( $this->filter, $this->armor_type ))
            {
                // looking for an armor type
                $this->filter= preg_replace ( '/ Armor/', '', $this->filter);
                $this->query_by_armor = true;
                $this->query_by_class = false;
            }
            elseif (array_key_exists ( $this->filter, $this->classname ))
            {
                // looking for a class
                $this->query_by_class = true;
                $t = explode("_", $this->filter);
                $this->class_id = count($t) > 1 ? $t[2]: 0;
                $this->query_by_armor = false;
            }
        }

        $this->query_by_pool = false;
        $this->dkpsys_id = 0;
        $this->dkpsys_name = $user->lang['ALL'];

        $sql_array = array(
            'SELECT'    => 'a.dkpsys_id, a.dkpsys_name, a.dkpsys_default',
            'FROM'		=> array(
                DKPSYS_TABLE => 'a',
                MEMBER_DKP_TABLE => 'd',
                MEMBER_LIST_TABLE => 'l'
            ),

            'WHERE'  => " a.dkpsys_id = d.member_dkpid
							AND a.dkpsys_status != 'N'
							AND d.member_id = l.member_id
							AND l.member_guild_id = " . $this->guild_id ,
            'GROUP_BY'  => 'a.dkpsys_id, a.dkpsys_name, a.dkpsys_default',
            'ORDER_BY'  => 'a.dkpsys_id '
        );
        $sql = $db->sql_build_query('SELECT', $sql_array);
        $result = $db->sql_query ($sql);
        $index = 3;
        $dkpvalues = array();
        while ( $row = $db->sql_fetchrow ( $result ) )
        {
            $dkpvalues[$index]['id'] = $row ['dkpsys_id'];
            $dkpvalues[$index]['text'] = $row ['dkpsys_name'];
            $index +=1;
            if (strtoupper ( $row ['dkpsys_default'] ) == 'Y')
            {
                $this->defaultpool = $row ['dkpsys_id'];
                break;
            }
        }
        $db->sql_freeresult ( $result );

        if(isset( $_POST ['pool']) or isset ( $_GET [URI_DKPSYS] ) )
        {
            if (isset( $_POST ['pool']) )
            {
                //user changed pulldown
                $pulldownval = request_var('pool',  $user->lang['ALL']);
                if(is_numeric($pulldownval))
                {
                    $this->query_by_pool = true;
                    $this->dkpsys_id = intval($pulldownval);
                }
            }
            elseif (isset ( $_GET [URI_DKPSYS] ))
            {
                //use get value
                $pulldownval = request_var(URI_DKPSYS,  $user->lang['ALL']);
                if(is_numeric($pulldownval))
                {
                    $this->query_by_pool = true;
                    $this->dkpsys_id = request_var(URI_DKPSYS, 0);
                }
                else
                {
                    $this->query_by_pool = false;
                    $this->dkpsys_id = $this->defaultpool;
                }
            }
        }
        else
        {
            // if no parameters passed to this page then show default pool
            $this->query_by_pool = true;
            $this->dkpsys_id = $this->defaultpool;
        }

        $this->dkppulldown($dkpvalues);

        $mode = request_var('rosterlayout', 0);

        $template->assign_vars(array(
            // Form values
            'S_GUILDDROPDOWN'	=> count($guildlist) > 1 ? true : false,
            'U_NEWS'  			=> append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=news&amp;guild_id=' . $this->guild_id),
            'U_LISTMEMBERS'  	=> append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=standings&amp;guild_id=' . $this->guild_id),
            'U_LOOTDB'     		=> append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=lootdb&amp;guild_id=' . $this->guild_id),
            'U_LOOTHIST'  		=> append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=loothistory&amp;guild_id=' . $this->guild_id),
            'U_LISTEVENTS'  	=> append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=listevents&amp;guild_id=' . $this->guild_id),
            'U_LISTRAIDS'   	=> append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=listraids&amp;guild_id=' . $this->guild_id),
            'U_VIEWITEM'   		=> append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=item&amp;guild_id=' . $this->guild_id),
            'U_VIEWMEMBER'   	=> append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=member&amp;guild_id=' . $this->guild_id),
            'U_VIEWRAID'   		=> append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=raid&amp;guild_id=' . $this->guild_id),
            'U_BP'   			=> append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=bossprogress&amp;guild_id=' . $this->guild_id),
            'U_ROSTER'   		=> append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=roster&amp;guild_id=' . $this->guild_id . '&amp;rosterlayout='.$mode),
            'U_STATS'   		=> append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=stats&amp;guild_id=' . $this->guild_id),
            'U_PLANNER'   		=> append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=planner&amp;guild_id=' . $this->guild_id),
            'U_ABOUT'         	=> append_sid("{$phpbb_root_path}aboutbbdkp.$phpEx"),
            'GAME_ID'			=> $guilds->game_id,
            'GUILD_ID' 			=> $this->guild_id,
            'GUILD_NAME' 		=> $guilds->name,
            'REALM' 			=> $guilds->realm,
            'REGION' 			=> $guilds->region,
            'MEMBERCOUNT' 		=> $guilds->membercount ,
            'ARMORY_URL' 		=> $guilds->guildarmoryurl ,
            'MIN_ARMORYLEVEL' 	=> $guilds->min_armory ,
            'SHOW_ROSTER' 		=> $guilds->showroster,
            'EMBLEM'			=> $guilds->emblempath,
            'EMBLEMFILE' 		=> basename($guilds->emblempath),
            'ARMORY'			=> $guilds->guildarmoryurl,
            'ACHIEV'			=> $guilds->achievementpoints,
            'SHOWALL'			=> ($this->show_all) ? $user->lang['ALL']: '',
            'F_NAVURL' 			=> append_sid("{$phpbb_root_path}dkp.$phpEx", 'page=roster&amp;guild_id=' . $this->guild_id),
        ));


    }

    /**
     * build dkp dropdown, for standings/stats
     *
     * @param $dkpvalues
     */
    private function dkppulldown($dkpvalues)
    {
        global $user, $template;
        $template->assign_block_vars ( 'pool_row', array (
            'VALUE' => $user->lang['ALL'],
            'SELECTED' => (!$this->query_by_pool) ? ' selected="selected"' : '',
            'OPTION' => $user->lang['ALL'],
        ));
        $template->assign_block_vars ( 'pool_row', array (
            'VALUE' => '--------',
            'SELECTED' => '',
            'DISABLED' => ' disabled="disabled"',
            'OPTION' => '--------',
        ));

        // find only pools with dkp records that are active
        sort($dkpvalues);
        foreach ($dkpvalues as $key => $value)
        {
            $template->assign_block_vars ( 'pool_row', array (
                'VALUE' => $value['id'],
                'SELECTED' => ($this->dkpsys_id == $value['id']  && $this->query_by_pool ) ? ' selected="selected"' : '',
                'OPTION' => $value['text'],
            ));

            if($this->dkpsys_id == $value['id'] && $this->query_by_pool )
            {
                $this->dkpsys_name = $value['text'];
            }
        }
    }

    /**
     * Armor listing
     */
    private function armor()
    {
        global $config, $user, $db, $template;
        $this->filter = $user->lang['ALL'];
        /***** begin armor-class pulldown ****/
        $filtervalues = array();

        $filtervalues ['all'] = $user->lang['ALL'];
        $filtervalues ['separator1'] = '--------';

        // generic armor list
        $sql = 'SELECT class_armor_type FROM ' . CLASS_TABLE . ' GROUP BY class_armor_type';
        $result = $db->sql_query ( $sql);
        while ( $row = $db->sql_fetchrow ( $result ) )
        {
            $filtervalues [strtoupper($row ['class_armor_type'])] = $user->lang[strtoupper($row ['class_armor_type'])];
            $this->armor_type [strtoupper($row ['class_armor_type'])] = $user->lang[strtoupper($row ['class_armor_type'])];
        }
        $db->sql_freeresult ( $result );
        $filtervalues ['separator2'] = '--------';


        // get classlist, depending on page
        $sql_array = array(
            'SELECT'    => 	'  c.game_id, c.class_id, l.name as class_name, c.class_min_level, c.class_max_level, c.imagename, c.colorcode ',
            'FROM'      => array(
                CLASS_TABLE 	=> 'c',
                BB_LANGUAGE		=> 'l',
                MEMBER_LIST_TABLE	=> 'i',
            ),
            'WHERE'		=> " c.class_id > 0 and l.attribute_id = c.class_id and c.game_id = l.game_id
				 		AND l.language= '" . $config['bbdkp_lang'] . "' AND l.attribute = 'class'
				 		AND i.member_class_id = c.class_id and i.game_id = c.game_id AND i.game_id = '" .  $this->game_id . "'" ,

            'GROUP_BY'	=> 'c.game_id, c.class_id, l.name, c.class_min_level, c.class_max_level, c.imagename, c.colorcode',
            'ORDER_BY'	=> 'c.game_id, c.class_id ',
        );

        $sql_array[ 'WHERE'] .= ' AND i.member_guild_id = ' . $this->guild_id . ' ';

        if($this->page =='standings' or $this->page =='stats')
        {
            $sql_array['FROM'][MEMBER_DKP_TABLE] = 'd';
            $sql_array[ 'WHERE'] .= 'AND d.member_id = i.member_id';
            if ($config ['bbdkp_hide_inactive'] == '1' && ! $this->show_all )
            {
                // don't show inactive members
                $sql_array['WHERE'] .= ' AND i.member_status = 1 ';
            }
        }

        $sql = $db->sql_build_query('SELECT', $sql_array);
        $result = $db->sql_query ($sql);
        $this->classarray = array();
        while ( $row = $db->sql_fetchrow ( $result ) )
        {
            $this->classarray[] = $row;
            $filtervalues [$row['game_id'] . '_class_' . $row ['class_id']] = $row ['class_name'];
            $this->classname [$row['game_id'] . '_class_' . $row ['class_id']] = $row ['class_name'];
        }
        $db->sql_freeresult ( $result );

        // dump filtervalues to dropdown template
        foreach ( $filtervalues as $fid => $fname )
        {
            $template->assign_block_vars ( 'filter_row', array (
                'VALUE' => $fid,
                'SELECTED' => ($fid == $this->filter && $fname !=  '--------' ) ? ' selected="selected"' : '',
                'DISABLED' => ($fname == '--------' ) ? ' disabled="disabled"' : '',
                'OPTION' => (! empty ( $fname )) ? $fname : $user->lang['ALL'] ) );
        }

        /***** end armor - class pulldown ****/

    }





}



