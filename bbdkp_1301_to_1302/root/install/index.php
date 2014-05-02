<?php
/**
 * bbDKP Umil Installer
 *
 * @package bbdkp
 * @link http://www.bbdkp.com
 * @author Sajaki@gmail.com
 * @copyright 2009 bbdkp
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.3.0
 */

define('UMIL_AUTO', true);
define('IN_PHPBB', true);
define('IN_INSTALL', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
require($phpbb_root_path . 'includes/functions_install.' . $phpEx);
// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();
$user->add_lang ( array ('mods/dkp_admin'));

$error= array();
// anything lower than php 5.3.3 not supported (we use namespaces since v1.3)
if (version_compare(PHP_VERSION, '5.3.3') < 0)
{
	$error[] = 'You are running an unsupported PHP version ('. PHP_VERSION . '). Please upgrade to PHP 5.3.3 or higher before trying to install bbDKP. <br />';
}

switch ($db->sql_layer)
{
	case 'mysqli':
	case 'mysql4':
	case 'mysql':
		$dbversion = $db->sql_server_version;
		if (version_compare($dbversion, '4.1.0', '<'))
		{
			$error[] = "You are running an unsupported Mysql version ($dbversion) . Please upgrade to Mysql 4.1 or higher before trying to install bbDKP. <br />";
		}
		break;
		// Untested !
	case 'firebird':
	case 'sqlite':
		$error[] = "You are running phpbb on an untested dbms version, please upgrade to a supported dbms (mysql, postgres, oracle, or mssql) to install the bbDKP Mod. <br />";
		break;
}


if (!function_exists('curl_init'))
{
	$error[] = $user->lang['CURL_REQUIRED'] . '<br />' ;
}

if (!function_exists('json_decode'))
{
	$error[] = $user->lang['JSON_REQUIRED'] . '<br />' ;
}

if(count($error) > 0)
{
	trigger_error(implode($error,"<br /> "), E_USER_WARNING);
}

// We only allow a founder install this MOD
if ($user->data['user_type'] != USER_FOUNDER)
{
    if ($user->data['user_id'] == ANONYMOUS)
    {
        login_box('', 'LOGIN');
    }

    trigger_error('NOT_AUTHORISED', E_USER_WARNING);
}

if (!file_exists($phpbb_root_path . 'umil/umil_auto.' . $phpEx))
{
    trigger_error('Please download the latest UMIL (Unified MOD Install Library) from: <a href="http://www.phpbb.com/mods/umil/">phpBB.com/mods/umil</a>', E_USER_WARNING);
}


//check old version. if lower then 128 then trigger error
check_oldbbdkp();

// The name of the mod to be displayed during installation.
$mod_name = 'bbDKP';

/*
* The name of the config variable which will hold the currently installed version
* You do not need to set this yourself, UMIL will handle setting and updating the version itself.
*/
$version_config_name = 'bbdkp_version';

/*
* The language file which will be included when installing
*/
$language_file = 'mods/dkp_admin';

$options = array();


/*
 * insert bbdkp portal welcome message
 */
$welcome_message = encode_message($user->lang['WELCOME_DEFAULT']);

/*
* Optionally we may specify our own logo image to show in the upper corner instead of the default logo.
* $phpbb_root_path will get prepended to the path specified
* Image height should be 50px to prevent cut-off or stretching.
*/
$logo_img = 'install/logo.png';
/*
  $user, $config, $db, $table_prefix, $umil, $bbdkp_table_prefix;
* The array of versions and actions within each.
* You do not need to order it a specific way (it will be sorted automatically), however, you must enter every version, even if no actions are done for it.
*
* You must use correct version numbering.  Unless you know exactly what you can use, only use X.X.X (replacing X with an integer).
* The version numbering must otherwise be compatible with the version_compare function - http://php.net/manual/en/function.version-compare.php
*/
$versions = array(
	//1.2.8 30-07-2012
    '1.2.8'    => array(
    	// install base bbdkp tables (this uses the layout from develop/create_schema_files.php and from phpbb_db_tools)
        'table_add' => array(

			array($table_prefix . 'bbdkp_news', array(
                    'COLUMNS'				=> array(
                        'news_id'			=> array('UINT', NULL, 'auto_increment'),
                        'news_headline'		=> array('VCHAR_UNI', ''),
                        'news_message'		=> array('TEXT_UNI', ''),
                        'news_date'			=> array('TIMESTAMP', 0),
                        'user_id'			=> array('UINT', 0),
                        'bbcode_bitfield'	=> array('VCHAR:20', ''),
                        'bbcode_uid'		=> array('VCHAR:8', ''),
                        'bbcode_options'	=> array('VCHAR:8', ''),
                    ),
                    'PRIMARY_KEY'    => 'news_id',
                ),
            ),

            array($table_prefix . 'bbdkp_language', array(
	              'COLUMNS'            => array(
	          		  'id'     	       => array('UINT', NULL, 'auto_increment'),
                   	  'game_id' 	   => array('VCHAR:10', ''),
	                  'attribute_id'   => array('UINT', 0),
	                  'language'       => array('CHAR:2', ''),
	          		  'attribute'	   => array('VCHAR:30', ''),
	                  'name'       	   => array('VCHAR_UNI:255', ''),
	                  'name_short' 	   => array('VCHAR_UNI:255', ''),
	          	),
	                'PRIMARY_KEY'     => array('id'),
	          		'KEYS'            => array('bbdkp_language' => array('UNIQUE', array('game_id', 'attribute_id', 'language', 'attribute')),
				)
	            )),

	      array($table_prefix . 'bbdkp_factions', array(
                    'COLUMNS'        => array(
	      				'game_id' 			=> array('VCHAR:10', ''),
                        'f_index'    		=> array('USINT', NULL, 'auto_increment'),
	         			'game_id' 			=> array('VCHAR', ''),

                        'faction_id'   		=> array('USINT', 0),
                        'faction_name'     	=> array('VCHAR_UNI', ''),
                        'faction_hide'		=> array('BOOL', 0),
                    ),
                    'PRIMARY_KEY'    => 'f_index',
                    'KEYS'         => array('bbdkp_factions'    => array('UNIQUE',  array('game_id', 'faction_id'))),
                ),
            ),

          array($table_prefix . 'bbdkp_classes', array(
                    'COLUMNS'        => array(
                        'c_index'    		=> array('USINT', NULL, 'auto_increment'),
          				'game_id' 			=> array('VCHAR:10', ''),
                        'class_id'   		=> array('USINT', 0),
          				'class_faction_id' 	=> array('UINT', 0),
                        'class_min_level'	=> array('USINT', 0),
                        'class_max_level'	=> array('USINT', 0),
                        'class_armor_type'	=> array('VCHAR_UNI', ''),
                        'class_hide'		=> array('BOOL', 0),
            			'dps'				=> array('USINT', 0),
            			'tank'				=> array('USINT', 0),
            			'heal'				=> array('USINT', 0),
						'imagename'			=> array('VCHAR:255', ''),
                		'colorcode'			=> array('VCHAR:10', ''),

                    ),
                    'PRIMARY_KEY'    => 'c_index',
                    'KEYS'         => array('bbclass'    => array('UNIQUE', array('game_id', 'class_id'))),
                ),
            ),

		  array($table_prefix . 'bbdkp_races', array(
                    'COLUMNS'				=> array(
                        'game_id' 			=> array('VCHAR:10', ''),
                        'race_id'			=> array('USINT', 0),
                        'race_faction_id'	=> array('USINT', 0),
                        'race_hide'			=> array('BOOL', 0),
						'image_female'	=> array('VCHAR:255', ''),
						'image_male'	=> array('VCHAR:255', ''),
                    ),
                    'PRIMARY_KEY'    => array('game_id', 'race_id') ,


                ),
            ),

            // Guild table
			//id is not auto-increment
           array($table_prefix . 'bbdkp_memberguild', array(
                    'COLUMNS'       => array(
                       'id'				=> array('USINT', 0),
                       'name'			=> array('VCHAR_UNI:255', ''),
		  			   'realm'			=> array('VCHAR_UNI:255', ''),
					   'region'  		=> array('VCHAR:2', ''),
					   'roster'  		=> array('BOOL', 0),
					   'aion_legion_id' => array('USINT', 0),
            		   'aion_server_id' => array('USINT', 0),

                      ),
                    'PRIMARY_KEY'  	=> array('id'),
					'KEYS'         => array('bbguild'    => array('UNIQUE', array('name', 'id') )),
              ),
            ),

           array($table_prefix . 'bbdkp_member_ranks', array(
                    'COLUMNS'        => array(
            			//rank_id is not auto-increment
                        'guild_id'		=> array('USINT', 0),
                        'rank_id'		=> array('USINT', 0),
                        'rank_name'		=> array('VCHAR_UNI:50', ''),
                        'rank_hide'		=> array('BOOL', 0),
                        'rank_prefix'	=> array('VCHAR:75', ''),
                        'rank_suffix'	=> array('VCHAR:75', ''),
                    ),
                    'PRIMARY_KEY'    => array('rank_id', 'guild_id'),
                ),
            ),

		array($table_prefix . 'bbdkp_memberlist', array(
                    'COLUMNS'        	   => array(
						'game_id'  		   => array('VCHAR:10', ''),
                        'member_id'        => array('UINT', NULL, 'auto_increment'),
                        'member_name'      => array('VCHAR_UNI:255', ''),
                        'member_status'    => array('BOOL', 0) ,
						'member_level'     => array('USINT', 0),
						'member_race_id'   => array('USINT', 0),
						'member_class_id'  => array('USINT', 0),
						'member_rank_id'   => array('USINT', 0),
						'member_comment'   => array('VCHAR_UNI:255', ''),
						'member_joindate'  => array('TIMESTAMP', 0),
            			'member_outdate'   => array('TIMESTAMP', 0),
            			'member_guild_id'  => array('USINT', 0),
            			'member_gender_id' => array('USINT', 0),
            			'member_achiev'    => array('UINT', 0),
            			'member_armory_url' => array('VCHAR:255', ''),
						'member_portrait_url' => array('VCHAR', ''),
						'phpbb_user_id' 	=> array('UINT', 0)

                    ),
                    'PRIMARY_KEY'  => 'member_id',
                    'KEYS'         => array('member_name'    => array('UNIQUE', 'member_name')),
                ),
            ),

            array($table_prefix . 'bbdkp_dkpsystem', array(
                    'COLUMNS'        => array(
                        'dkpsys_id'    		=> array('USINT', NULL, 'auto_increment'),
                        'dkpsys_name'   	=> array('VCHAR_UNI:255', ''),
                        'dkpsys_status'     => array('VCHAR:2', 'Y'),
                        'dkpsys_addedby'	=> array('VCHAR_UNI:255', ''),
                        'dkpsys_updatedby'	=> array('VCHAR_UNI:255', ''),
                        'dkpsys_default'	=> array('VCHAR:2', 'N'),
            			'adj_decay' 		=> array('DECIMAL:11', 0.00),
                    ),
                    'PRIMARY_KEY'    => 'dkpsys_id',
                    'KEYS'         => array('dkpsys_name'    => array('UNIQUE', 'dkpsys_name')),
                ),
            ),

       		array($table_prefix . 'bbdkp_events', array(
                    'COLUMNS'        => array(
                        'event_id'    		=> array('UINT', NULL, 'auto_increment'),
                        'event_dkpid'   	=> array('USINT', 0),
                        'event_name'     	=> array('VCHAR_UNI:255', ''),
            			'event_color'     	=> array('VCHAR:8', ''),
            			'event_imagename'   => array('VCHAR:255', ''),
                        'event_value'		=> array('DECIMAL:11', 0),
                        'event_added_by'	=> array('VCHAR_UNI:255', ''),
                        'event_updated_by'	=> array('VCHAR_UNI:255', ''),
                    	'event_status' 		=> array('BOOL', 1),
                    ),
                    'PRIMARY_KEY'    => 'event_id',
                    'KEYS'            => array('event_dkpid'    => array('INDEX', 'event_dkpid')),
                ),
            ),

		  array($table_prefix . 'bbdkp_memberdkp', array(
                    'COLUMNS'        	 => array(
                        'member_id'			=> array('UINT', 0),
                        'member_dkpid'		=> array('USINT', 0),
		  				'member_raid_value'	=> array('DECIMAL:11', 0),
		  				'member_time_bonus'	=> array('DECIMAL:11', 0),
		  				'member_zerosum_bonus'	=> array('DECIMAL:11', 0),
                        'member_earned'		=> array('DECIMAL:11', 0),
		  				'member_raid_decay'	=> array('DECIMAL:11', 0),
						'member_spent'		=> array('DECIMAL:11', 0),
		  				'member_item_decay'	=> array('DECIMAL:11', 0),
						'member_adjustment' => array('DECIMAL:11', 0),
						'member_status' 	=> array('BOOL', 0) ,
						'member_firstraid'  => array('TIMESTAMP', 0),
						'member_lastraid'	=> array('TIMESTAMP', 0),
						'member_raidcount'	=> array('UINT', 0),
		  				'adj_decay' 		=> array('DECIMAL:11', 0.00),

                    ),
                    'PRIMARY_KEY'  => array('member_dkpid', 'member_id'),
                ),
           ),

			array($table_prefix . 'bbdkp_adjustments', array(
              'COLUMNS'        => array(
                  'adjustment_id'        => array('UINT', NULL, 'auto_increment'),
				  'member_id'        	 => array('UINT', 0),
                  'adjustment_dkpid'     => array('USINT', 0),
                  'adjustment_value'     => array('DECIMAL:11', 0),
        		  'adjustment_date'      => array('TIMESTAMP', 0),
				  'adjustment_reason'    => array('VCHAR_UNI', ''),
				  'adjustment_added_by'  => array('VCHAR_UNI:255', ''),
				  'adjustment_updated_by'=> array('VCHAR_UNI:255', ''),
				  'adjustment_group_key' => array('VCHAR', ''),
        		  'adj_decay' 			 => array('DECIMAL:11', 0.00),
       			  'can_decay' 			 => array('BOOL', 0),
         		  'decay_time' 			 => array('DECIMAL:11', 0.00),
                ),
                'PRIMARY_KEY'    => 'adjustment_id',
                'KEYS'         => array('member_id'    => array('INDEX', array('member_id', 'adjustment_dkpid'))),
          )),

          array($table_prefix . 'bbdkp_raids', array(
				'COLUMNS'        	=> array(
					'raid_id'			=> array('UINT', NULL, 'auto_increment'),
					'event_id'			=> array('UINT', 0),
					'raid_note'   		=> array('VCHAR_UNI:255', ''),
					'raid_start'  		=> array('TIMESTAMP', 0) ,
         			'raid_end'  		=> array('TIMESTAMP', 0) ,
					'raid_added_by' 	=> array('VCHAR_UNI:255', ''),
					'raid_updated_by' 	=> array('VCHAR_UNI:255', ''),
					),
				'PRIMARY_KEY'  => array('raid_id'),
				'KEYS'         => array('event_id'    => array('INDEX', 'event_id')),
            )),

		  array($table_prefix . 'bbdkp_raid_detail', array(
				'COLUMNS'		=> array(
					'raid_id'		=> array('UINT', 0),
					'member_id'		=> array('UINT', 0),
					'raid_value' 	=> array('DECIMAL:11', 0),
					'time_bonus' 	=> array('DECIMAL:11', 0),
					'zerosum_bonus' => array('DECIMAL:11', 0),
		  			'raid_decay' 	=> array('DECIMAL:11', 0),
		            'decay_time' 	=> array('DECIMAL:11', 0.00),
				),
				'PRIMARY_KEY'  => array('raid_id', 'member_id')
			)),


          array($table_prefix . 'bbdkp_raid_items', array(
                    'COLUMNS'     => array(
                    'item_id'         => array('UINT', NULL, 'auto_increment'),
					'raid_id'         => array('UINT', 0),
                    'item_name'       => array('VCHAR_UNI:255', ''),
          			'member_id'		  => array('UINT', 0),
					'item_date'       => array('TIMESTAMP', 0),
					'item_added_by'   => array('VCHAR_UNI:255', ''),
					'item_updated_by' => array('VCHAR_UNI:255', ''),
           			'item_group_key'  => array('VCHAR', ''),
           			'item_gameid' 	  => array('UINT', 0),
					'item_value'      => array('DECIMAL:11', 0.00),
          			'item_decay'      => array('DECIMAL:11', 0.00), // decay of itemvalue
          			'item_zs'      	  => array('BOOL', 0), 		// if this flag is set the itemvalue will be distributed over raid
         			'decay_time' 	  => array('DECIMAL:11', 0.00),
                    ),
                    'PRIMARY_KEY'     => 'item_id',
                    'KEYS'         => array('raid_id'    => array('INDEX', 'raid_id')),
             )),

          array($table_prefix . 'bbdkp_logs', array(
                    'COLUMNS'           => array(
                       'log_id'        => array('UINT', NULL, 'auto_increment'),
                       'log_date'      => array('TIMESTAMP', 0),
                       'log_type'      => array('VCHAR_UNI:255', ''),
					   'log_action'    => array('TEXT_UNI', ''),
					   'log_ipaddress' => array('VCHAR:15', ''),
					   'log_sid'       => array('VCHAR:32', ''),
					   'log_result'    => array('VCHAR', ''),
					   'log_userid'    => array('UINT', 0),

                    ),
                    'PRIMARY_KEY'  => 'log_id',
                    'KEYS'         => array(
		               'log_userid'	=> array('INDEX', 'log_userid'),
		               'log_type'		=> array('INDEX', 'log_type'),
		               'log_ipaddress'	=> array('INDEX', 'log_ipaddress'),
                    )
                )),

		  array($table_prefix . 'bbdkp_plugins', array(
                  'COLUMNS'        	=> array(
                      'name'			=> array('VCHAR_UNI:255', ''),
                      'value'			=> array('BOOL', 0),
                      'version'  		=> array('VCHAR:50', ''),
                      'orginal_copyright' => array('VCHAR_UNI:150', ''),
                      'bbdkp_copyright'  	=> array('VCHAR_UNI:150', ''),
                  )
               )),


          array($table_prefix . 'bbdkp_welcomemsg' , array(
                  	'COLUMNS'        => array(
                      'welcome_id'    		=> array('INT:8', NULL, 'auto_increment'),
                      'welcome_title' 		=> array('VCHAR_UNI', ''),
                      'welcome_msg'   		=> array('TEXT_UNI', ''),
            		  'welcome_timestamp' 	=> array('TIMESTAMP', 0),
				   	  'bbcode_bitfield' 	=> array('VCHAR:255', ''),
				   	  'bbcode_uid' 		=> array('VCHAR:8', ''),
            		  'user_id'     		=> array('INT:8', 0),
            		  'bbcode_options'		=> array('UINT', 7),
                  ),
                  'PRIMARY_KEY'    => 'welcome_id'
                )),
       ),

       'table_row_insert'	=> array(

          // we insert a dummy guild (None) for guildless people and also the default guild
         array($table_prefix .'bbdkp_memberguild',
          array(
           		  // guildless -> do show on roster
                  array('id'  => 0,
                      'name' => 'Guildless',
                      'realm' => ( request_var('realm', ' ', true) == ' ' ? utf8_normalize_nfc(request_var('realm', ' ', true)) : 'default'),
                      'region' => (isset($_POST['region']) ? request_var('region', ' ') : 'us'),
                      'roster' => 1
                  		),

                  )
           ),

		 array($table_prefix . 'bbdkp_member_ranks',
			 array(

				// Out rank : for unguilded, undeletable rank
	       		array(
	       			'guild_id'	=> 0,
					'rank_id'	=> 99,
					'rank_name'	=> 'Out',
					'rank_hide'	=> 1,
					'rank_prefix'	=> '',
					'rank_suffix'	=> '',
				 ),
				)
			),

		  array( $table_prefix . 'bbdkp_welcomemsg',
           		array(
                  array(
                  	'welcome_title' => 'Welcome to our guild',
                  	'welcome_timestamp' => (int) time(),
                  	'welcome_msg' => $welcome_message['text'],
                  	'bbcode_uid' => $welcome_message['uid'],
                  	'bbcode_bitfield' => $welcome_message['bitfield'],
                  	'user_id' => $user->data['user_id'] ),
          	 	)
            ),
		),

    	// create two basic permissions
	   'permission_add' => array(
            array('a_dkp', true),
            array('u_dkp', true),
            array('u_dkpucp', true),
            array('u_dkp_charadd', true) ,
            array('u_dkp_chardelete', true),
            array('u_dkp_charupdate', true),
      	),

	    // unset default permissions
		'permission_unset' => array(
            array('ROLE_ADMIN_FULL', 	'a_dkp'),
            array('ROLE_ADMIN_FULL', 	'u_dkp'),
            array('ROLE_USER_FULL', 	'u_dkp'),
            array('ROLE_ADMIN_FULL', 	'u_dkpucp'),
			array('ROLE_USER_STANDARD', 'u_dkp_charadd'),
			array('ROLE_USER_STANDARD', 'u_dkp_chardelete'),
			array('ROLE_USER_STANDARD', 'u_dkp_charupdate'),
			array('ROLE_USER_STANDARD', 'u_dkpucp'),
			array('ROLE_USER_STANDARD', 'u_dkp'),
			array('ROLE_USER_FULL', 'u_dkp_charadd'),
			array('ROLE_USER_FULL', 'u_dkp_chardelete'),
			array('ROLE_USER_FULL', 'u_dkp_charupdate'),
			array('ROLE_USER_FULL', 'u_dkpucp'),
            ),

		// Assign default permissions to Full admin
        'permission_set' => array(
      		//admin can access acp
            array('GLOBAL_MODERATORS', 	'a_dkp', 'group'),
            array('ADMINISTRATORS', 	'a_dkp', 'group'),

            //can see dkp pages
            array('GUESTS', 'u_dkp', 'group'),
            array('REGISTERED', 'u_dkp', 'group'),
            array('NEWLY_REGISTERED', 'u_dkp', 'group'),

            //can claim a character
            array('ADMINISTRATORS', 'u_dkpucp', 'group'),
           	array('GLOBAL_MODERATORS', 	'u_dkpucp', 'group'),
           	array('REGISTERED', 'u_dkpucp', 'group'),

            // can delete own character
            array('ADMINISTRATORS', 'u_dkp_chardelete', 'group'),
            array('GLOBAL_MODERATORS', 'u_dkp_charadd', 'group'),

            // can add own character
            array('REGISTERED', 'u_dkp_charadd', 'group'),
            array('GLOBAL_MODERATORS', 'u_dkp_charadd', 'group'),
            array('REGISTERED', 'u_dkp_charupdate', 'group'),

            //can update own character
            array('ADMINISTRATORS', 'u_dkp_chardelete', 'group'),
            array('GLOBAL_MODERATORS', 'u_dkp_charadd', 'group'),
            array('REGISTERED', 'u_dkp_charupdate', 'group'),

        ),

        // add new parameters
        'config_add' => array(

        	//bbdkp settings

        	// guildname
			array('bbdkp_guildtag', utf8_normalize_nfc(request_var('guildtag', '', true)), true),
	        // default realm & region
	        array('bbdkp_default_realm', ( request_var('realm', ' ', true) == ' ' ? utf8_normalize_nfc(request_var('realm', ' ', true)) : 'default') , true),
	        array('bbdkp_default_region', request_var('region', ''), true),
			array('bbdkp_eqdkp_start', mktime(0, 0, 0, date("m")  , date("d"), date("Y")) , true),
			array('bbdkp_date_format', 'd.m.y', true),
			array('bbdkp_dkp_name', 'DKP', true),
			array('bbdkp_default_game', request_var('game', ''), true),
	        //default dkp language
	        array('bbdkp_lang', 'en', true),

	        // news limit
			array('bbdkp_user_nlimit', '5', true),
	        // roster layout: main parameter for steering roster layout
			array('bbdkp_roster_layout', '1', true),
	        // showachiev : show the achievement points
	        array('bbdkp_show_achiev', '0', true),
			// guildfaction : limit the possible races to be available to users to those available in the guild's chosen faction
			array('bbdkp_guild_faction', '1', true),
	        //guildmemberlist paging
	        array('bbdkp_user_llimit', '20', true),

	        //standings
			array('bbdkp_hide_inactive', '1', true),
			array('bbdkp_inactive_period', '150', true),
			array('bbdkp_list_p1', '30', true),
			array('bbdkp_list_p2', '60', true),
         	array('bbdkp_list_p3', '90', true),

			//events
			array('bbdkp_user_elimit', '30', true),

			//adjustments
			array('bbdkp_user_alimit', '30', true),
			array('bbdkp_active_point_adj', '10.00', true),
			array('bbdkp_inactive_point_adj', '-10.00', true),
			array('bbdkp_starting_dkp', '15.00', true),

			//items
			array('bbdkp_user_ilimit', '20', true),

			//raids
			array('bbdkp_user_rlimit', '20', true),

			//epgp
			array('bbdkp_epgp', 0, true),
			array('bbdkp_basegp', 0, true),

			//decay
			array('bbdkp_decay', 0, true),
			array('bbdkp_raiddecaypct', 5, true),
			array('bbdkp_itemdecaypct', 5, true),
			array('bbdkp_decayfrequency', 1, true),
			array('bbdkp_decayfreqtype', 1, true),

			//time
			array('bbdkp_timebased', 0, true),
			array('bbdkp_dkptimeunit', 5, true),
			array('bbdkp_timeunit', 30, true),
			array('bbdkp_standardduration', 1, true),

			//zerosum
			array('bbdkp_zerosum', 0, true),
			array('bbdkp_bankerid', 0, true),
			array('bbdkp_zerosumdistother', 0, true),

            // portal settings
	        // number of news
	        array('bbdkp_n_news', 5, true),
	        // news forum id
	        array('bbdkp_news_forumid', 2 , true),
	        // number of items
	        array('bbdkp_n_items',5 , true),
	        // recruitment forum id
	        array('bbdkp_recruit_forumid', 3, true),
			// 1 if open,  if closed
	        array('bbdkp_recruitment', 0, true ),
			// show loot block
	        array('bbdkp_portal_loot', 1, true ),
			// show recruitment block
	        array('bbdkp_portal_recruitment', 1, true ),
			// show link block
	        array('bbdkp_portal_links', 1, true ),
		    // show post edits in portal
	        array('bbdkp_portal_showedits', 1, true ),
	        // showing or hiding portal
	        array('bbdkp_portal_menu', 1, true),

	        array('bbdkp_games_aion', 0, true),
			array('bbdkp_games_daoc', 0, true),
			array('bbdkp_games_eq', 0, true),
			array('bbdkp_games_eq2', 0, true),
			array('bbdkp_games_FFXI', 0, true),
			array('bbdkp_games_lotro', 0, true),
			array('bbdkp_games_rift', 0, true),
			array('bbdkp_games_vanguard', 0, true),
			array('bbdkp_games_wow', 0, true),
			array('bbdkp_games_warhammer', 0, true),
			array('bbdkp_games_swtor', 0, true),

	        array('bbdkp_portal_welcomemsg', 1, true),
			array('bbdkp_maxchars', 2, true),
			array('bbdkp_games_lineage2', 0, true),

			array('bbdkp_minep', 100.0, true),
	        array('bbdkp_decaycron', 1, true),
	        array('bbdkp_lastcron', 0, true),
	        array('bbdkp_crontime', 23, true),
	        array('bbdkp_adjdecaypct', 5, true),
	        array('bbdkp_minrosterlvl', 50, true),
	        array('bbdkp_portal_rtno', 5, true),
	        array('bbdkp_portal_rtlen', 15, true),
	        array('bbdkp_portal_rtshow', 1, true),

       		array('bbdkp_games_tera', 0, true),
       		array('bbdkp_games_gw2', 0, true),
       		array('bbdkp_event_viewall', 1, true),
       		array('bbdkp_portal_newmembers', 1, true),
       		array('bbdkp_portal_maxnewmembers', 5, true),

        ),

        // add the bbdkp modules to ACP using the info files,
		'module_add' => array(
      		 /*
             * First, lets add maincategory named ACP_DKP to the root.
             * note to validation team : phpbb MOD policy wants this in
             * ACP_CAT_DOT_MODS but due to the size of our tree we place it on top (0)
             *
             */
            array('acp', 0, 'ACP_CAT_DKP'),

             /*
             * add main menu
             */
            array('acp', 'ACP_CAT_DKP', 'ACP_DKP_MAINPAGE'),


            array('acp', 'ACP_DKP_MAINPAGE', array(
           		 'module_basename' => 'dkp',
            	 'modes'           => array('mainpage', 'dkp_config', 'dkp_logs', 'dkp_indexpageconfig') ,
        		),
            ),

            array('acp', 'ACP_DKP_MAINPAGE', array(
           		 'module_basename' => 'dkp_point',
            	 'modes'           => array('pointconfig') ,
        		)),

            /*
             * add member management menu
             * note added the roster here
             */
            array('acp', 'ACP_CAT_DKP', 'ACP_DKP_MEMBER'),

            // add memberlist-add-ranks-roster
            array('acp', 'ACP_DKP_MEMBER', array(
           		 'module_basename' => 'dkp_mm',
            	 'modes'           => array('mm_listguilds', 'mm_addguild', 'mm_ranks', 'mm_listmembers', 'mm_addmember'),
        		),
            ),

           array('acp', 'ACP_DKP_MEMBER', array(
           		 'module_basename' => 'dkp_game',
            	 'modes'           => array('listgames', 'addfaction', 'addrace', 'addclass'),
        		)),

            /*
             * add raid management menu
             */
            array('acp', 'ACP_CAT_DKP', 'ACP_DKP_RAIDS'),

            // add raid pools
            array('acp', 'ACP_DKP_RAIDS', array(
           		 'module_basename' => 'dkp_sys',
            	 'modes'           => array('adddkpsys', 'listdkpsys' ),
        		),
            ),

            // add events modules
            array('acp', 'ACP_DKP_RAIDS', array(
           		 'module_basename' => 'dkp_event',
            	 'modes'           => array('addevent', 'listevents'),
        		),
            ),

            // add manual raid modules
            array('acp', 'ACP_DKP_RAIDS', array(
           		 'module_basename' => 'dkp_raid',
            	 'modes'           => array('addraid', 'editraid', 'listraids'),
        		),
            ),

            // add item modules
            array('acp', 'ACP_DKP_RAIDS', array(
           		 'module_basename' => 'dkp_item',
            	 'modes'           => array('listitems', 'edititem', 'search', 'viewitem'),
        		),
            ),

            /*
             * add member dkp menu
             * note the transfer moved to member dkp
             */
            array('acp', 'ACP_CAT_DKP', 'ACP_DKP_MDKP'),

            // add dkp - edit dkp - transfer dkp
            array('acp', 'ACP_DKP_MDKP', array(
           		 'module_basename' => 'dkp_mdkp',
            	 'modes'           => array('mm_listmemberdkp', 'mm_editmemberdkp', 'mm_transfer'),
        		),
            ),

            // add dkp adjustments
            array('acp', 'ACP_DKP_MDKP', array(
           		 'module_basename' => 'dkp_adj',
            	 'modes'           => array('addiadj', 'listiadj'),
        		),
            ),

			// Add the UCP module in which you link bbDKP memberids to your phpbb account
			array('ucp', 0, 'UCP_DKP'),

			// Add one UCP module to the new category
			array('ucp', 'UCP_DKP', array(
					'module_basename'   => 'dkp',
					'modes' => array('characters', 'characteradd'),
			))),

    ),

     '1.2.8-pl1' => array(
     //patch 1, https://github.com/bbDKP/bbDKP/commit/10277b0, no db changes

     ),
     '1.2.8-pl2' => array(
     //patch 2, to fix updater bug going from v126 to v128

     ),

	'1.2.9' => array(
		// dev version, never released
	),

	'1.3.0' => array(
    	//21-04-2014
        'table_add' => array(
            array($table_prefix . 'bbdkp_games', array(
                'COLUMNS'            => array(
                    'id'     	     => array('UINT', NULL, 'auto_increment'),
                    'game_id' 	     => array('VCHAR:10', ''),
                    'game_name'      => array('VCHAR_UNI:255', ''),
                    'status'	   	 => array('VCHAR:30', ''),
                    'imagename'	     => array('VCHAR:20', ''),
                    'armory_enabled' => array('UINT', 0),
                    'bossbaseurl'    => array('VCHAR:255', ''),
                    'zonebaseurl'    => array('VCHAR:255', ''),
                ),
                'PRIMARY_KEY'     => array('id'),
                'KEYS'            => array('bbdkp_games' => array('UNIQUE', array('game_id')))
            )),

            array($table_prefix . 'bbdkp_roles', array(
                'COLUMNS'           => array(
                    'id'     	    => array('UINT', NULL, 'auto_increment'),
                    'guild_id'     	=> array('UINT', 0),
                    'game_id'     	=> array('VCHAR:10', ''),
                    'role' 	   		=> array('VCHAR:20', ''),
                    'class_id'      => array('UINT', 0),
                    'needed'	   	=> array('USINT', 0),
                ),
                'PRIMARY_KEY'     => array('id'),
                'KEYS'            => array('bbdkp_roles' => array('UNIQUE', array('guild_id', 'game_id', 'role', 'class_id' ))),
            )),

        ),

        'table_column_remove' => array(
            array($table_prefix . 'bbdkp_classes', 'dps'),
            array($table_prefix . 'bbdkp_classes', 'tank'),
            array($table_prefix . 'bbdkp_classes', 'heal'),
            array($table_prefix . 'bbdkp_memberdkp', 'member_status'),
        ),


        'table_column_add' => array(
            array($table_prefix . 'bbdkp_plugins', 'installdate', array('TIMESTAMP', 0)),
            array($table_prefix . 'bbdkp_raid_items', 'wowhead_id', array('UINT', 0)),
            array($table_prefix . 'bbdkp_memberlist', 'member_title', array('VCHAR_UNI:255', '')),
            array($table_prefix . 'bbdkp_memberlist', 'member_role', array('VCHAR:20', '')),
            array($table_prefix . 'bbdkp_memberlist', 'member_region', array('VCHAR', '')),
            array($table_prefix . 'bbdkp_memberlist', 'member_realm', array('VCHAR', '')),
            array($table_prefix . 'bbdkp_memberguild', 'level', array('UINT', 0) ),
            array($table_prefix . 'bbdkp_memberguild', 'members', array('UINT', 0)),
            array($table_prefix . 'bbdkp_memberguild', 'achievementpoints', array('UINT', 0)),
            array($table_prefix . 'bbdkp_memberguild', 'battlegroup', array('VCHAR:255', '')),
            array($table_prefix . 'bbdkp_memberguild', 'guildarmoryurl', array('VCHAR:255', '')),
            array($table_prefix . 'bbdkp_memberguild', 'emblemurl', array('VCHAR:255', '')),
            array($table_prefix . 'bbdkp_memberguild', 'game_id', array('VCHAR:10', '')),
            array($table_prefix . 'bbdkp_memberguild', 'min_armory', array('UINT', 90)),
            array($table_prefix . 'bbdkp_memberguild', 'rec_status', array('BOOL', 0)),
            array($table_prefix . 'bbdkp_memberguild', 'guilddefault', array('BOOL', 0)),
            array($table_prefix . 'bbdkp_memberguild', 'armory_enabled', array('BOOL', 0)),
        ),

        'config_update' => array(
            array('bbdkp_roster_layout', '0', true),
        ),


        'config_remove' => array(
            array('bbdkp_guildtag') ,
            array('bbdkp_recruitment'),
            array('bbdkp_games_aion'),
            array('bbdkp_games_daoc'),
            array('bbdkp_games_eq'),
            array('bbdkp_games_eq2'),
            array('bbdkp_games_FFXI'),
            array('bbdkp_games_lotro'),
            array('bbdkp_games_rift'),
            array('bbdkp_games_vanguard'),
            array('bbdkp_games_wow'),
            array('bbdkp_games_warhammer'),
            array('bbdkp_games_swtor'),
            array('bbdkp_games_lineage2'),
            array('bbdkp_games_tera'),
            array('bbdkp_games_gw2'),
            array('bbdkp_portal_rtshow'),
        ),

        'config_add' => array(
            array('bbdkp_regid', '', true),
            array('bbdkp_portal_recent', 1, true),
            array('bbdkp_portal_whoisonline', 1, true),
            array('bbdkp_portal_onlineblockposition', 1, true),
        ),

        'module_remove' => array(
            array('acp', 'ACP_DKP_MEMBER', array(
                'module_basename' => 'dkp_mm',
                'modes'           => array('mm_listmembers', 'mm_addmember'),
            )),

            array('acp', 'ACP_DKP_MEMBER', array(
                'module_basename' => 'dkp_mm',
                'modes'           => array('mm_ranks', 'mm_listguilds', 'mm_addguild' ),
            )),


            array('acp', 'ACP_DKP_MEMBER', array(
                'module_basename' => 'dkp_game',
                'modes'           => array('listgames', 'addfaction', 'addrace', 'addclass'),
            )),

            array('acp', 'ACP_DKP_MDKP', array(
                'module_basename' => 'dkp_adj',
                'modes'           => array('addiadj', 'listiadj'),
            ),
            ),

            array('acp', 'ACP_DKP_MDKP', array(
                'module_basename' => 'dkp_mdkp',
                'modes'           => array('mm_listmemberdkp', 'mm_editmemberdkp', 'mm_transfer'),
            ),
            ),

            array('acp', 'ACP_DKP_RAIDS', array(
                'module_basename' => 'dkp_sys',
                'modes'           => array( 'adddkpsys', 'listdkpsys' ),
            ),
            ),

            array('acp', 'ACP_DKP_RAIDS', array(
                'module_basename' => 'dkp_item',
                'modes'           => array('listitems', 'search', 'viewitem'),
            ),
            ),

            array('acp', 'ACP_DKP_RAIDS', array(
                'module_basename' => 'dkp_item',
                'modes'           => array('edititem'),
            ),
            ),

            array('acp', 'ACP_DKP_RAIDS', array(
                'module_basename' => 'dkp_raid',
                'modes'           => array('addraid', 'editraid', 'listraids'),
            ),
            ),

            array('acp', 'ACP_DKP_RAIDS', array(
                'module_basename' => 'dkp_event',
                'modes'           => array('addevent', 'listevents'),
            ),
            ),

            array('acp', 'ACP_CAT_DKP', 'ACP_DKP_RAIDS'),
            array('acp', 'ACP_CAT_DKP', 'ACP_DKP_MDKP'),
        ),


        'module_add' => array(
            array('acp', 'ACP_DKP_MAINPAGE', array(
                'module_basename' => 'dkp_game',
                'modes'           => array('listgames','editgames', 'addfaction', 'addrace', 'addclass'),
            )),

            array('acp', 'ACP_DKP_MEMBER', array(
                'module_basename' => 'dkp_guild',
                'modes'           => array(  'listguilds', 'addguild', 'editguild',  ),
            ),
            ),

            array('acp', 'ACP_DKP_MEMBER', array(
                'module_basename' => 'dkp_mm',
                'modes'           => array(  'mm_listmembers', 'mm_addmember' ),
            ),
            ),


            array('acp', 'ACP_CAT_DKP', 'ACP_DKP_MDKP'),

            array('acp', 'ACP_DKP_MDKP', array(
                'module_basename' => 'dkp_mdkp',
                'modes'           => array('mm_listmemberdkp', 'mm_editmemberdkp', 'mm_transfer'),
            ),
            ),

            array('acp', 'ACP_DKP_MDKP', array(
                'module_basename' => 'dkp_adj',
                'modes'           => array('addiadj', 'listiadj'),
            ),
            ),

            array('acp', 'ACP_DKP_MDKP', array(
                'module_basename' => 'dkp_sys',
                'modes'           => array('adddkpsys', 'editdkpsys', 'listdkpsys', 'addevent' ),
            )),

            array('acp', 'ACP_CAT_DKP', 'ACP_DKP_RAIDS'),

            array('acp', 'ACP_DKP_RAIDS', array(
                'module_basename' => 'dkp_raid',
                'modes'           => array('addraid', 'editraid', 'listraids'),
            )),

            array('acp', 'ACP_DKP_RAIDS', array(
                'module_basename' => 'dkp_item',
                'modes'           => array('listitems', 'additem', 'search', 'viewitem'),
            ),
            ),

        ),
        
        
      'custom' => array(
            'tableupdates',
            'bbdkp_caches'
        ),


	),

'1.3.0.1' => array(
	// updateable version from 1.2.8-pl2 to 1.3.0.1
	// 1.3 changes were merged into 1, omitting all betas and RC.
),

'1.3.0.2' => array(
	// just some file fixes, see changelog
),


);

// Include the UMIF Auto file and everything else will be handled automatically.
include($phpbb_root_path . 'umil/umil_auto.' . $phpEx);

/**
 * encode welcome text
 *
 * @param string $text
 * @return array
 * @package bbdkp
 */
function encode_message($text)
{
	$uid = $bitfield = $options = ''; // will be modified by generate_text_for_storage
	$allow_bbcode = $allow_urls = $allow_smilies = true;
	generate_text_for_storage($text, $uid, $bitfield, $options, $allow_bbcode, $allow_urls, $allow_smilies);
	$welcome_message['text']=$text;
	$welcome_message['uid']=$uid;
	$welcome_message['bitfield']=$bitfield;
	return $welcome_message;
}

/**
 * custom SQL updates outside of UMIL
 *
 * @param string $action
 * @param string $version
 * @return multitype:string
 *   @package bbdkp
 */
function tableupdates($action, $version)
{

	global $user, $umil, $config, $db, $table_prefix;
	switch ($action)
	{

		case 'install' :
			switch ($version)
			{
				case '1.2.8':
					break;
				case '1.3.0':
					// add double PK in members table

					// remove unique index 'member_name' on member table
					$sql = "ALTER TABLE " . $table_prefix . 'bbdkp_memberlist' . " DROP INDEX member_name";
					$db->sql_query($sql);

					// make new unique composite this way double membernames are enabled
					$sql= "CREATE UNIQUE INDEX member_name ON " . $table_prefix . 'bbdkp_memberlist' . " (member_guild_id, member_name) ";
					$db->sql_query($sql);

					$sql= "ALTER TABLE  " . $table_prefix . 'bbdkp_memberguild MODIFY region VARCHAR(3) ';
					$db->sql_query($sql);

					$sql= "ALTER TABLE  " . $table_prefix . 'bbdkp_raid_items MODIFY item_gameid VARCHAR(8) ';
					$db->sql_query($sql);

					$sql= "ALTER TABLE  " . $table_prefix . 'bbdkp_memberlist MODIFY member_comment TEXT ';
					$db->sql_query($sql);

					$sql= "ALTER TABLE  " . $table_prefix . 'bbdkp_raids MODIFY raid_note TEXT ';
					$db->sql_query($sql);

					break;


			}
			break;

		case 'update':
			switch ($version)
			{
				case '1.2.8':
					break;
				case '1.3.0':
					// add double PK in members table
					// remove unique index 'member_name' on member table
					$sql = "ALTER TABLE " . $table_prefix . 'bbdkp_memberlist' . " DROP INDEX member_name";
					$db->sql_query($sql);

					// make new unique composite this way double membernames are enabled
					$sql= "CREATE UNIQUE INDEX member_name ON " . $table_prefix . 'bbdkp_memberlist' . " (member_guild_id, member_name) ";
					$db->sql_query($sql);

					$sql= "ALTER TABLE  " . $table_prefix . 'bbdkp_memberguild MODIFY region VARCHAR(3) ';
					$db->sql_query($sql);

					$sql= "ALTER TABLE  " . $table_prefix . 'bbdkp_raid_items MODIFY item_gameid VARCHAR(8) ';
					$db->sql_query($sql);

					$sql= "ALTER TABLE  " . $table_prefix . 'bbdkp_memberlist MODIFY member_comment TEXT ';
					$db->sql_query($sql);

					$sql= "ALTER TABLE  " . $table_prefix . 'bbdkp_raids MODIFY raid_note TEXT ';
					$db->sql_query($sql);
					//if the game_id is numeric consider it as a wowheadid
					$sql= "UPDATE  " . $table_prefix . "bbdkp_raid_items SET wowhead_id = (CASE WHEN item_gameid REGEXP ('^[0-9]+$') THEN item_gameid ELSE 0 END) ";
					$db->sql_query($sql);
					break;

			}
			break;
		case 'uninstall' :
			switch ($version)
			{
				case '1.2.8':
					break;
				case '1.3.0':
					//$sql= "DROP TABLE  " . $table_prefix . 'bbdkp_reporting ';
					//$db->sql_query($sql);

					break;

			}
			break;
	}
	return array('command' => sprintf($user->lang['UMIL_UPDTABLES'], $action, $version) , 'result' => 'SUCCESS');
}

/**
 * global function for clearing cache
 *   @package bbdkp
 * @param string $action
 * @param string $version
 * @return string
 */
function bbdkp_caches($action, $version)
{
    global $umil;

    $umil->cache_purge();
    $umil->cache_purge('imageset');
    $umil->cache_purge('template');
    $umil->cache_purge('theme');
    $umil->cache_purge('auth');

    return 'UMIL_CACHECLEARED';
}

/**
 * checks if there is an older install then quit
 * only updates from 1.2.8 are allowed...
 *   @package bbdkp
 */
function check_oldbbdkp()
{
	global $user, $umil, $config, $phpbb_root_path, $phpEx;

	include($phpbb_root_path . 'umil/umil.' . $phpEx);
	$umil=new umil;

	// check config
	if($umil->config_exists('bbdkp_version'))
    {
		if(version_compare($config['bbdkp_version'], '1.2.8') == -1 )
		{
			//stop here, the version is less than 1.2.8
			trigger_error( $user->lang['UMIL_128MINIMUM'], E_USER_WARNING);
		}
    }
}