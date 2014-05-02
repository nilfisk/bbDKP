<?php
/**
 * Game ACP file
 *
 * @package bbdkp
 * @link http://www.bbdkp.com
 * @author Sajaki@gmail.com
 * @copyright 2009 bbdkp
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.3.0
 */
// don't add this file to namespace bbdkp
/**
 * @ignore
 */
if (! defined ( 'IN_PHPBB' ))
{
	exit ();
}
if (! defined ( 'EMED_BBDKP' ))
{
	$user->add_lang ( array ('mods/dkp_admin' ) );
	trigger_error ( $user->lang ['BBDKPDISABLED'], E_USER_WARNING );
}

// Include the base class
if (!class_exists('\bbdkp\admin\Admin'))
{
	require("{$phpbb_root_path}includes/bbdkp/admin/admin.$phpEx");
}
if (!class_exists('\bbdkp\controller\games\Faction'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/games/factions/Faction.$phpEx");
}
if (!class_exists('\bbdkp\controller\games\Classes'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/games/classes/Classes.$phpEx");
}
if (!class_exists('\bbdkp\controller\games\Races'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/games/races/Races.$phpEx");
}
if (!class_exists('\bbdkp\controller\games\Game'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/games/Game.$phpEx");
}

/**
 *
 * This class manages Game settings
 *
 *   @package bbdkp
 */
class acp_dkp_game extends \bbdkp\admin\Admin
{
	/**
	 * link in trigger window
	 * @var string
	 */
	private $link;
	/**
	 * main ACP game function
	 * @param int $id the id of the node who parent has to be returned by function
	 * @param int $mode id of the submenu
	 * @access public
	 */
	function main($id, $mode)
	{
		global $user, $template, $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$form_key = 'acp_dkp_game';
		add_form_key ( $form_key );
		$this->tpl_name = 'dkp/acp_' . $mode;
		switch ($mode)
		{
			case 'listgames' :

				$this->link = '<br /><a href="' . append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_game&amp;mode=listgames" ) . '"><h3>' .
								$user->lang ['RETURN_GAMELIST'] . '</h3></a>';
				//game dropdown
				$listgames = new \bbdkp\controller\games\Game;

				$newpresetgame = (isset ( $_POST ['addgame1'] )) ? true : false;
				$newcustomgame = (isset ( $_POST ['addgame2'] )) ? true : false;
				if ($newpresetgame || $newcustomgame)
				{
					// ask for permission
					if (confirm_box ( true ))
					{
						$editgame = new \bbdkp\controller\games\Game;
						$editgame->game_id = request_var ( 'hidden_game_id','' );
						$editgame->setName(utf8_normalize_nfc(request_var('hidden_game_name', '', true)));
						$editgame->install();

						trigger_error ( sprintf ( $user->lang ['ADMIN_INSTALLED_GAME_SUCCESS'], $editgame->getName() ) . $this->link, E_USER_NOTICE );
					}
					else
					{
						// get field content
						$listgames->game_id = request_var('ngame_id' , '');
						if($newpresetgame)
						{
							$listgames->setName($listgames->preinstalled_games[$listgames->game_id]) ;
						}
						elseif($newcustomgame)
						{
							$listgames->setName(utf8_normalize_nfc(request_var('ngame_name', '', true)));
						}

						$s_hidden_fields = build_hidden_fields ( array (
								'addgame1' => $newpresetgame,
								'addgame2' => $newcustomgame,
								'hidden_game_id' => $listgames->game_id,
								'hidden_game_name' => $listgames->getName(),

						));
						confirm_box ( false, sprintf ( $user->lang ['CONFIRM_INSTALL_GAME'], $listgames->getName() ), $s_hidden_fields );
					}
				}

				///template load
				$can_install_count = 0;

				//is anything isntalled ?
				if(count($this->games) > 0)
				{
					$not_installed = array_diff($listgames->preinstalled_games, $listgames->games);
				}
				else
				{
					// brand new install
					$not_installed = $listgames->preinstalled_games;
				}

				foreach ($not_installed as $key => $game)
			    {
					$can_install_count +=1;
			        $template->assign_block_vars('gamelistrow', array(
			            'VALUE'      => $key,
			            'OPTION'     => $game,
			        	'SELECTED'   => '',
			        ));
			    }

			    //list installed games
			    $sort_order = array(
			    		0 => array(	'id' , 'id desc') ,
			    		1 => array('game_id' , 'game_id desc') ,
			    		2 => array('game_name' , 'game_name desc'));
			    $current_order = $this->switch_order($sort_order);
			    $sort_index = explode('.', $current_order['uri']['current']);
			    $gamelist = $listgames->listgames($current_order['sql']);

				foreach($gamelist as $game_id => $game)
			    {
			    	$template->assign_block_vars('gamerow', array(
			    			'ID' => $game['id'] ,
			    			'NAME' => $game['name'] ,
			    			'GAME_ID' => $game['game_id'] ,
			    			'U_VIEW_GAME' => append_sid("{$phpbb_admin_path}index.$phpEx", "i=dkp_game&amp;mode=editgames&amp;" . URI_GAME . '=' . $game['game_id'] ),
			    			'STATUS' => $game['status'],
			    	));
			    }

			    $template->assign_vars ( array (
			    		'U_LIST_GAME' => append_sid("{$phpbb_admin_path}index.$phpEx", "i=dkp_game&amp;mode=listgames") ,
			    		'CANINSTALL' => ($can_install_count == 0) ? false : true,
			    		'O_ID' => $current_order['uri'][0] ,
			    		'O_GAMEID' => $current_order['uri'][1] ,
			    		'O_GAMENAME' => $current_order['uri'][2] ,
					));

			    $form_key = '30U05YJ4IfeHxY';
			    add_form_key($form_key);

			    $this->page_title = 'ACP_LISTGAME';
			    break;

			case 'editgames' :

				$editgame = new \bbdkp\controller\games\Game;
				$editgame->game_id = request_var(URI_GAME, request_var ( 'hidden_game_id','' ));
				$editgame->Get();

				$this->link = '<br /><a href="' . append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_game&amp;mode=editgames&amp;" . URI_GAME ."={$editgame->game_id}" ) . '"><h3>' .
						$user->lang ['RETURN_GAMEVIEW'] . '</h3></a>';

				$gamereset = (isset ( $_POST ['gamereset'] )) ? true : false;
				$gamedelete = (isset ( $_POST ['gamedelete'] )) ? true : false;

				$addfaction = (isset ( $_POST ['showfactionadd'] )) ? true : false;
				$deletefaction = (isset ( $_GET ['factiondelete'] )) ? true : false;

				$addrace = (isset ( $_POST ['showraceadd'] )) ? true : false;
				$raceedit = (isset ( $_GET ['raceedit'] )) ? true : false;
				$racedelete = (isset ( $_GET ['racedelete'] )) ? true : false;

				$addclass = (isset ( $_POST ['showclassadd'] )) ? true : false;
				$classedit = (isset ( $_GET ['classedit'] )) ? true : false;
				$classdelete = (isset ( $_GET ['classdelete'] )) ? true : false;

				$gamesettings = (isset ( $_POST ['gamesettings'] )) ? true : false;

                $template->assign_vars ( array (
                    'U_BACK' => append_sid("{$phpbb_admin_path}index.$phpEx", "i=dkp_game&amp;mode=listgames") ,
                ));

				if($gamereset)
				{
					// ask for permission
					if (confirm_box ( true ))
					{
						$editgame = new \bbdkp\controller\games\Game;
						$editgame->game_id = request_var ( 'hidden_game_id','' );
						$editgame->get();
						$editgame->Delete();
						$editgame->install();

						meta_refresh(1, append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_game&amp;mode=listgames" ));
						trigger_error ( sprintf ( $user->lang ['ADMIN_RESET_GAME_SUCCESS'], $editgame->getName() ) . $this->link, E_USER_WARNING);
					}
					else
					{
						// get field content
						$s_hidden_fields = build_hidden_fields ( array (
								'gamereset' => true,
								'hidden_game_id' => $editgame->game_id,
						));
						confirm_box ( false, sprintf ( $user->lang ['CONFIRM_RESET_GAME'], $editgame->getName() ), $s_hidden_fields );
					}
				}

				// save game settings
				if($gamesettings)
				{
					$editgame = new \bbdkp\controller\games\Game;
					$editgame->game_id = request_var ( 'game_id','' );
					$editgame->Get();

					$editgame->setImagename(request_var('imagename',''));
					$editgame->setArmoryEnabled(request_var('enable_armory', 0));
                    $editgame->setBossbaseurl(request_var('bossbaseurl','' ));
                    $editgame->setZonebaseurl(request_var('zonebaseurl','' ));
					$editgame->update();
				}

				// delete actions
				if($gamedelete)
				{
					// ask for permission
					if (confirm_box ( true ))
					{
						$deletegame = new \bbdkp\controller\games\Game;
						$deletegame->game_id = request_var ( 'hidden_game_id','' );
                        $deletegame->Get();
						$deletegame->Delete();

						//meta_refresh(1, append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_game&amp;mode=listgames") );
						trigger_error ( sprintf ( $user->lang ['ADMIN_DELETE_GAME_SUCCESS'], $deletegame->getName() ) , E_USER_WARNING);
					}
					else
					{

						// get field content
						$s_hidden_fields = build_hidden_fields ( array (
								'gamedelete' => true,
								'hidden_game_id' => $editgame->game_id,
						));

						confirm_box ( false, sprintf ( $user->lang ['CONFIRM_DELETE_GAME'], $editgame->getName() ), $s_hidden_fields );
					}
				}

				// user pressed delete faction
				if ($deletefaction)
				{
					// ask for permission
					if (confirm_box ( true ))
					{
						$faction = new \bbdkp\controller\games\Faction();
						$faction->game_id = request_var ( 'hidden_game_id','' );
						$faction->faction_id = request_var ( 'hidden_faction_id', 0 );
						$faction->get();
						$faction->Delete();

						meta_refresh(1, append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_game&amp;mode=editgames&amp;" . URI_GAME ."={$faction->game_id}" ) );
						trigger_error ( sprintf ( $user->lang ['ADMIN_DELETE_FACTION_SUCCESS'], $faction->game_id , $faction->faction_name ) . $this->link, E_USER_WARNING );
					}
					else
					{
						$faction = new \bbdkp\controller\games\Faction();
						$faction->game_id = $editgame->game_id;
						$faction->faction_id = request_var ( 'id', 0 );
						$faction->get();

						// get field content
						$s_hidden_fields = build_hidden_fields ( array (
								'factiondelete' => true,
								'hidden_faction_id' => $faction->faction_id,
								'hidden_game_id' => $faction->game_id,
								));

						confirm_box ( false, sprintf ( $user->lang ['CONFIRM_DELETE_FACTION'], $faction->faction_name ), $s_hidden_fields );
					}

				}

				// user pressed delete race
				if ($racedelete)
				{
					// ask for permission
					if (confirm_box(true))
					{
						$deleterace = new \bbdkp\controller\games\Races();
						$deleterace->race_id = request_var ( 'hidden_raceid', 0 );
						$deleterace->game_id = request_var ( 'hidden_gameid', '' );
						$deleterace->get();
						$deleterace->Delete();

						$success_message = sprintf($user->lang['ADMIN_DELETE_RACE_SUCCESS'], $deleterace->game_id, $deleterace->race_name);
						meta_refresh(1, append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_game&amp;mode=editgames&amp;" . URI_GAME ."={$deleterace->game_id}" ) );
						trigger_error($success_message . $this->link, E_USER_WARNING);
					}
					else
					{
						$deleterace = new \bbdkp\controller\games\Races;
						$deleterace->race_id = request_var ( 'id', 0 );
						$deleterace->game_id = request_var ( 'game_id', '' );
						$deleterace->get();

						$s_hidden_fields = build_hidden_fields(array(
								'racedelete' => true ,
								'hidden_raceid' => $deleterace->race_id,
								'hidden_gameid' => $editgame->game_id
						));

						$template->assign_vars(array(
								'S_HIDDEN_FIELDS' => $s_hidden_fields));
						confirm_box(false, sprintf ( $user->lang ['CONFIRM_DELETE_RACE'], $deleterace->game_id, $deleterace->race_name )  , $s_hidden_fields);
					}

				}

				// user pressed delete class
				if ($classdelete)
				{
					// ask for permission
					if (confirm_box ( true ))
					{
						$deleteclass = new \bbdkp\controller\games\Classes();
						$deleteclass->class_id = request_var ( 'hidden_class_id', 0 );
						$deleteclass->game_id = request_var ( 'hidden_game_id', '' );
						$deleteclass->get();
						$deleteclass->Delete();
						meta_refresh(1, append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_game&amp;mode=editgames&amp;" . URI_GAME ."={$deleteclass->game_id}" ) );
						trigger_error ( sprintf ( $user->lang ['ADMIN_DELETE_CLASS_SUCCESS'], $deleteclass->classname ) . $this->link, E_USER_WARNING );
					}
					else
					{
						$deleteclass = new \bbdkp\controller\games\Classes();
						$deleteclass->class_id = request_var ( 'id', 0 );
						$deleteclass->game_id = $editgame->game_id;
						$deleteclass->get();

						// get field content
						$s_hidden_fields = build_hidden_fields ( array (
								'classdelete' => true,
								'hidden_game_id' => $deleteclass->game_id,
								'hidden_class_id' => $deleteclass->class_id)
						);

						confirm_box ( false, sprintf ( $user->lang ['CONFIRM_DELETE_CLASS'], $deleteclass->classname ), $s_hidden_fields );
					}
				}

				//add actions
				if ($addfaction)
				{
					redirect ( append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_game&amp;mode=addfaction&amp;". URI_GAME . '=' . $editgame->game_id  ) );
					break;
				}


				// user pressed race add / edit, load acp_addrace
				if ($raceedit || $addrace)
				{
					// Load template for adding/editing

					//Edit
					if (isset ( $_GET ['id'] ))
					{
						// edit this race
						$listraces = new \bbdkp\controller\games\Races();
						$listraces->race_id = request_var ( 'id', 0 );
						$listraces->game_id = $editgame->game_id;
						$listraces->get();

						foreach ($this->games as $key => $gamename )
						{
							$template->assign_block_vars ( 'game_row', array (
								'VALUE' => $key,
								'SELECTED' => ($listraces->game_id == $key) ? ' selected="selected"' : '',
								'OPTION' => $gamename));
						}

						// faction dropdown
						$listfactions = new \bbdkp\controller\games\Faction();
						$listfactions->game_id = $editgame->game_id;
						$fa = $listfactions->getfactions();

						$s_faction_options = '';
						foreach($fa as $faction_id => $faction)
						{
							$selected = ($faction_id == $listraces->race_faction_id) ? ' selected="selected"' : '';
							$s_faction_options .= '<option value="' . $faction['faction_id'] . '" ' . $selected . '> ' . $faction['faction_name'] . '</option>';
						}
						unset($listfactions);

						// send parameters to template

						$template->assign_vars ( array (
							'GAME_ID' => $listraces->game_id,
							'RACE_ID' => $listraces->race_id,
							'RACE_NAME' => $listraces->race_name,
							'RACE_IMAGENAME_M' => $listraces->image_male,
							'RACE_IMAGE_M' => (strlen ( $listraces->image_male ) > 1) ? $phpbb_root_path . "images/bbdkp/race_images/" . $listraces->image_male . ".png" : '',
							'RACE_IMAGENAME_F' => $listraces->image_female,
							'RACE_IMAGE_F' => (strlen ( $listraces->image_female ) > 1) ? $phpbb_root_path . "images/bbdkp/race_images/" . $listraces->image_female . ".png" : '',
							'S_RACE_IMAGE_M_EXISTS' => (strlen ( $listraces->image_male ) > 1) ? true : false,
							'S_RACE_IMAGE_F_EXISTS' => (strlen ( $listraces->image_female ) > 1) ? true : false,
							'S_FACTIONLIST_OPTIONS' => $s_faction_options,
							'S_ADD' => FALSE,
							'U_ACTION' => append_sid ( "{$phpbb_admin_path}index.$phpEx", 'i=dkp_game&amp;mode=addrace' ),
							'MSG_NAME_EMPTY' => $user->lang ['FV_REQUIRED_NAME'] ) );
						unset($listraces);
					}
					else
					{
						// load template add race
						$listraces = new \bbdkp\controller\games\Races();
						$listraces->game_id = $editgame->game_id;

						//list factions
						$listfactions = new \bbdkp\controller\games\Faction();
						$listfactions->game_id = $editgame->game_id;

						$fa = $listfactions->getfactions();

						if(count($fa) == 0)
						{
							trigger_error ('ERROR_NOFACTION' , E_USER_WARNING);
						}

						$s_faction_options = '';
						foreach($fa as $faction_id => $faction)
						{
							$s_faction_options .= '<option value="' . $faction['faction_id'] . '" > ' . $faction['faction_name'] . '</option>';
						}
						unset($listfactions);

						$template->assign_vars ( array (
							'GAME_ID' => $listraces->game_id,
							'GAME_NAME' => $editgame->getName(),
							'S_FACTIONLIST_OPTIONS' => $s_faction_options,
							'S_ADD' => TRUE,
							'U_ACTION' => append_sid ( "{$phpbb_admin_path}index.$phpEx", 'i=dkp_game&amp;mode=addrace' ),
							'MSG_NAME_EMPTY' => $user->lang ['FV_REQUIRED_NAME'] ) );

					}

					$template->assign_vars ( array (
						'LA_ALERT_AJAX' => $user->lang ['ALERT_AJAX'],
						'LA_ALERT_OLDBROWSER' => $user->lang ['ALERT_OLDBROWSER'],
						'UA_FINDFACTION' => append_sid ( $phpbb_admin_path . "style/dkp/findfaction.$phpEx" ) ) );

					$this->page_title = 'ACP_LISTGAME';
					$this->tpl_name = 'dkp/acp_addrace';
					break;
				}

				if ($classedit || $addclass)
				{
					// Load template for adding/editing
					if (isset ( $_GET ['id'] ))
					{
						//edit this class_id
						$listclasses = new \bbdkp\controller\games\Classes;
						$listclasses->class_id = request_var ( 'id', 0 );
						$listclasses->game_id = $editgame->game_id;

						// list installed games
						foreach ($this->games as $key => $gamename )
						{
							$template->assign_block_vars ( 'game_row', array (
									'VALUE' => $key,
									'SELECTED' => ($listclasses->game_id == $key) ? ' selected="selected"' : '',
									'OPTION' => $gamename));
						}

						//list armor types
						$s_armor_options = '';
						foreach ( $listclasses->armortypes as $armor => $armorname )
						{
							$selected = ($armor == $armorname['class_armor_type']) ? ' selected="selected"' : '';
							$s_armor_options .= '<option value="' . $armor . '" ' . $selected . '> ' . $armorname . '</option>';
						}

						$cl = $listclasses->listclasses();
						$template->assign_vars ( array (
							'GAME_ID' => $listclasses->game_id,
							'C_INDEX' => $cl[$listclasses->class_id]['c_index'],
							'CLASS_ID' => $cl[$listclasses->class_id]['class_id'],
							'CLASS_NAME' => $cl[$listclasses->class_id]['class_name'],
							'CLASS_MIN' => $cl[$listclasses->class_id]['class_min_level'],
							'CLASS_MAX' => $cl[$listclasses->class_id]['class_max_level'],
							'S_ARMOR_OPTIONS' => $s_armor_options,
							'CLASS_IMAGENAME' => $cl[$listclasses->class_id]['imagename'],
							'COLORCODE' => ($cl[$listclasses->class_id]['colorcode'] == '') ? '#254689' : $cl[$listclasses->class_id]['colorcode'],
							'CLASS_IMAGE' => (strlen ( $cl[$listclasses->class_id]['imagename'] ) > 1) ? $phpbb_root_path . "images/bbdkp/class_images/" . $cl[$listclasses->class_id]['imagename'] . ".png" : '',
							'S_CLASS_IMAGE_EXISTS' => (strlen ( $cl[$listclasses->class_id]['imagename'] ) > 1) ? true : false,
							'S_ADD' => FALSE,
							'U_ACTION' => append_sid ( "{$phpbb_admin_path}index.$phpEx", 'i=dkp_game&amp;mode=addclass' ),
							'MSG_NAME_EMPTY' => $user->lang ['FV_REQUIRED_NAME'],
							'MSG_ID_EMPTY' => $user->lang ['FV_REQUIRED_ID'] ) );

					}
					else
					{
						// load template add class
						$listclasses = new \bbdkp\controller\games\Classes;
						$listclasses->game_id = $editgame->game_id;

						$s_armor_options = '';
						foreach ($listclasses->armortypes as $armor => $armorname )
						{
							$s_armor_options .= '<option value="' . $armor . '" > ' . $armorname . '</option>';
						}

						// send parameters to template
						$template->assign_vars ( array (
							'GAME_ID' => $listclasses->game_id,
							'GAME_NAME' => $editgame->getName(),
							'S_ARMOR_OPTIONS' => $s_armor_options,
							'S_ADD' => TRUE,
							'COLORCODE' => '#EE8611',
							'U_ACTION' => append_sid ( "{$phpbb_admin_path}index.$phpEx", 'i=dkp_game&amp;mode=addclass' ),
							'MSG_NAME_EMPTY' => $user->lang ['FV_REQUIRED_NAME'],
							'MSG_ID_EMPTY' => $user->lang ['FV_REQUIRED_ID'] ) );

					}

					$this->page_title = 'ACP_LISTGAME';
					$this->tpl_name = 'dkp/acp_addclass';
					break;
				}

				$this->showgame($editgame);
				$this->page_title = 'ACP_ADDGAME';

				break;

			case 'addfaction' :
				//action
				$faction = new \bbdkp\controller\games\Faction();
				$faction->game_id = request_var ( 'game_id', request_var ( 'hidden_game_id', '' ) );
				$editgame = new \bbdkp\controller\games\Game;
				$editgame->game_id = $faction->game_id;
				$editgame->Get();

				$addnew = (isset ( $_POST ['factionadd'] )) ? true : false;
				if ($addnew)
				{
					if (! check_form_key ( 'acp_dkp_game' ))
					{
						trigger_error ( 'FORM_INVALID' );
					}

					$faction->faction_name = utf8_normalize_nfc ( request_var ( 'factionname', '', true ) );
					$faction->Make();

					meta_refresh(1, append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_game&amp;mode=editgames&amp;" . URI_GAME ."={$faction->game_id}" ) );
					trigger_error ( sprintf ( $user->lang ['ADMIN_ADD_FACTION_SUCCESS'], $faction->faction_name ), E_USER_NOTICE );
				}


				// send parameters to template
				$template->assign_vars ( array (
					'GAME_ID' => $faction->game_id,
					'GAME_NAME' => $editgame->getName(),
					'U_ACTION' => append_sid ( "{$phpbb_admin_path}index.$phpEx", 'i=dkp_game&amp;mode=addfaction' ),
					'MSG_NAME_EMPTY' => $user->lang ['FV_REQUIRED_NAME'] ) );

				$this->page_title = 'ACP_LISTGAME';
				break;

			case 'addrace' :
				$raceadd = (isset ( $_POST ['add'] )) ? true : false;
				$raceupdate = (isset ( $_POST ['update'] )) ? true : false;

				if ($raceadd || $raceupdate)
				{
					if (! check_form_key ( 'acp_dkp_game' ))
					{
						trigger_error ( 'FORM_INVALID' );
					}
				}

				if ($raceadd)
				{
					$race = new \bbdkp\controller\games\Races();
					$race->game_id = request_var ( 'game_id', request_var ( 'hidden_game_id', '' ) );
					$race->race_id = request_var ( 'race_id',  request_var ( 'hidden_race_id', '' )  );
					$race->race_name = utf8_normalize_nfc ( request_var ( 'racename', '', true ) );
					$race->race_faction_id = request_var ( 'faction', 0 );
					$race->image_male = utf8_normalize_nfc ( request_var ( 'image_male', '', true ) );
					$race->image_female = utf8_normalize_nfc ( request_var ( 'image_female', '', true ) );
					$race->Make();

					meta_refresh(1, append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_game&amp;mode=editgames&amp;" . URI_GAME ."={$race->game_id}" ) );
					trigger_error ( sprintf ( $user->lang ['ADMIN_ADD_RACE_SUCCESS'], $race->race_name ) . $this->link, E_USER_NOTICE );
				}
				elseif ($raceupdate)
				{
					$oldrace = new \bbdkp\controller\games\Races();
					$oldrace->game_id = request_var ( 'game_id', request_var ( 'hidden_game_id', '' ) );
					$oldrace->race_id = request_var ( 'race_id', 0 );
					$oldrace->Get();

					$race = new \bbdkp\controller\games\Races();
					$race->game_id = $oldrace->game_id;
					$race->race_id = $oldrace->race_id;
					$race->Get();

					$race->race_name = utf8_normalize_nfc ( request_var ( 'racename', '', true ) );
					$race->race_faction_id = request_var ( 'faction', 0 );
					$race->image_male = utf8_normalize_nfc ( request_var ( 'image_male', '', true ) );
					$race->image_female = utf8_normalize_nfc ( request_var ( 'image_female', '', true ) );
					$race->Update($oldrace);

					meta_refresh(1, append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_game&amp;mode=editgames&amp;" . URI_GAME ."={$race->game_id}" ) );
					trigger_error ( sprintf ( $user->lang ['ADMIN_UPDATE_RACE_SUCCESS'], $race->race_name ) . $this->link, E_USER_NOTICE );
				}

				$this->page_title = 'ACP_LISTGAME';
				break;

			case 'addclass' :
				// collects data, calls class updater
				$classadd = (isset ( $_POST ['add'] )) ? true : false;
				$classupdate = (isset ( $_POST ['update'] )) ? true : false;

				if ($classadd || $classupdate)
				{
					if (! check_form_key ( 'acp_dkp_game' ))
					{
						trigger_error ( 'FORM_INVALID' );
					}
				}

				if ($classadd)
				{
					$newclass = new \bbdkp\controller\games\Classes();
					$newclass->game_id = request_var ( 'game_id', request_var ( 'hidden_game_id', '' ) );
					$newclass->classname = utf8_normalize_nfc ( request_var ( 'class_name', '', true ) );
					$newclass->class_id = request_var ( 'class_id', 0 );
					$newclass->min_level = request_var ( 'class_level_min', 0 );
					$newclass->max_level = request_var ( 'class_level_max', 0 );
					$newclass->armor_type = request_var ( 'armory', '' );
					$newclass->imagename = request_var ( 'image', '' );
					$newclass->colorcode = request_var ( 'classcolor', '' );
					$newclass->faction_id = '';
					$newclass->dps = '';
					$newclass->heal = '';
					$newclass->tank = '';
					$newclass->Make();
					meta_refresh(1, append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_game&amp;mode=editgames&amp;" . URI_GAME ."={$newclass->game_id}" ) );
					trigger_error ( sprintf ( $user->lang ['ADMIN_ADD_CLASS_SUCCESS'], $newclass->classname ) . $this->link, E_USER_NOTICE );
				}
				elseif ($classupdate)
				{

					$oldclass = new \bbdkp\controller\games\Classes();
					$oldclass->game_id = request_var ( 'game_id', request_var ( 'hidden_game_id', '' ) );
					$oldclass->class_id = request_var ( 'class_id0', 0 );
					$oldclass->c_index = request_var ( 'c_index', 0 );
					$oldclass->Get();

					$newclass = new \bbdkp\controller\games\Classes();
					$newclass->game_id = request_var ( 'game_id', request_var ( 'hidden_game_id', '' ) );
					$newclass->class_id = request_var ( 'class_id0', 0 );
					$newclass->c_index = request_var ( 'c_index', 0 );
					$newclass->Get();
					$newclass->class_id = request_var ( 'class_id', 0 );
					$newclass->classname = utf8_normalize_nfc ( request_var ( 'class_name', '', true ) );
					$newclass->min_level = request_var ( 'class_level_min', 0 );
					$newclass->max_level = request_var ( 'class_level_max', 0 );
					$newclass->armor_type = request_var ( 'armory', '' );
					$newclass->imagename = request_var ( 'image', '' );
					$newclass->colorcode = request_var ( 'classcolor', '' );
					$newclass->faction_id = '';
					$newclass->dps = '';
					$newclass->heal = '';
					$newclass->tank = '';
					$newclass->Update($oldclass);
					meta_refresh(1, append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_game&amp;mode=editgames&amp;" . URI_GAME ."={$newclass->game_id}" ) );
					trigger_error ( sprintf ( $user->lang ['ADMIN_UPDATE_CLASS_SUCCESS'], $newclass->classname ) . $this->link, E_USER_NOTICE );

				}
				$this->page_title = 'ACP_LISTGAME';
				break;


		}
	}

	/**
	 * lists game parameters
	 *
	 * @param \bbdkp\controller\games\game $editgame
	 */
	private function showgame( \bbdkp\controller\games\game $editgame)
	{
		global $user, $phpbb_admin_path, $phpbb_root_path, $phpEx, $config, $template;

		//populate dropdown
		foreach ($this->games as $key => $game)
		{
			$template->assign_block_vars('gamelistrow', array(
					'VALUE'      => $key,
					'OPTION'     => $game,
					'SELECTED'   => $editgame->game_id == $key ? ' selected="selected"' : '' ,
			));
		}

		// list the factions
		$listfactions = new \bbdkp\controller\games\Faction();
		$listfactions->game_id = $editgame->game_id;
		$fa = $listfactions->getfactions();
		$total_factions = 0;
		foreach($fa as $faction_id => $faction)
		{
			$total_factions ++;
			$template->assign_block_vars ( 'faction_row', array (
			'ID' => $faction['f_index'],
			'FACTIONGAME' => $editgame->game_id,
			'FACTIONID' => $faction['faction_id'],
			'FACTIONNAME' => $faction['faction_name'],
			'U_DELETE' => append_sid ( "{$phpbb_admin_path}index.$phpEx",
                    "i=dkp_game&amp;mode=editgames&amp;factiondelete=1&amp;id={$faction['f_index']}&amp;" . URI_GAME . '=' . $editgame->game_id)
				));
		}

		// list the races
		$sort_order = array (
			0 => array ('game_id asc, race_id asc', 'game_id desc, race_id asc' ),
			1 => array ('race_id', 'race_id desc' ),
			2 => array ('race_name', 'race_name desc' ),
			3 => array ('faction_name desc', 'faction_name, race_name desc'));
		$current_order = $this->switch_order ( $sort_order );
		$total_races = 0;

		$listraces = new \bbdkp\controller\games\Races();
		$listraces->game_id = $editgame->game_id;
		$ra = $listraces->listraces($current_order ['sql']);
		foreach ( $ra as $race_id => $race )
		{
			$total_races ++;
			$template->assign_block_vars ( 'race_row', array (
				'GAME' => $race['game_name'],
				'RACEID' => $race['race_id'],
				'RACENAME' => $race['race_name'],
				'FACTIONNAME' => $race['faction_name'],
				'RACE_IMAGE_M' => (strlen ( $race['image_male'] ) > 1) ? $phpbb_root_path . "images/bbdkp/race_images/" . $race['image_male'] . ".png" : '',
				'RACE_IMAGE_F' => (strlen ( $race['image_female'] ) > 1) ? $phpbb_root_path . "images/bbdkp/race_images/" . $race['image_female'] . ".png" : '',
				'S_RACE_IMAGE_M_EXISTS' => (strlen ( $race['image_male'] ) > 1) ? true : false,
				'S_RACE_IMAGE_F_EXISTS' => (strlen ( $race['image_female'] ) > 1) ? true : false,
				'U_VIEW_RACE' => append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_game&amp;mode=addrace&amp;r=" . $race['race_id'] . "&amp;" . URI_GAME ."={$listraces->game_id}" ),
				'U_DELETE' => append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_game&amp;mode=editgames&amp;racedelete=1&amp;id={$race['race_id']}&amp;" . URI_GAME ."={$listraces->game_id}" ),
				'U_EDIT' => append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_game&amp;mode=editgames&amp;raceedit=1&amp;id={$race['race_id']}&amp;" . URI_GAME ."={$listraces->game_id}" ) ) );
		}
		unset($listraces, $ra);

		// list the classes
		$sort_order2 = array(
			0 => array ('c.game_id asc, c.class_id asc', 'c.game_id desc, c.class_id asc'),
			1 => array ('class_id', 'class_id desc' ),
			2 => array ('class_name', 'class_name desc'),
			3 => array ('class_armor_type', 'class_armor_type, class_id desc'),
			4 => array ('class_min_level', 'class_min_level, class_id desc' ),
			5 => array ('class_max_level', 'class_max_level, class_id desc' ));
		$current_order2 = $this->switch_order ( $sort_order2, "o1" );
		$total_classes = 0;

		$listclasses = new  \bbdkp\controller\games\Classes();
		$listclasses->game_id = $editgame->game_id;
		$cl = $listclasses->listclasses($current_order2['sql'], 1);
		foreach ( $cl as $c_index => $class )
		{
			$total_classes ++;
			$template->assign_block_vars ( 'class_row', array (
				'GAME' => $class['game_name'],
				'C_INDEX' => $c_index,
				'CLASSID' => $class ['class_id'],
				'CLASSNAME' => $class ['class_name'],
				'COLORCODE' => $class ['colorcode'],
				'CLASSARMOR' => (isset ( $user->lang [$class ['class_armor_type']] ) ? $user->lang [$class ['class_armor_type']] : ' '),
				'CLASSMIN' => $class ['class_min_level'],
				'CLASSMAX' => $class ['class_max_level'],
				'CLASSHIDE' => $class ['class_hide'],
				'S_CLASS_IMAGE_EXISTS' => (strlen ( $class ['imagename'] ) > 1) ? true : false,
				'CLASSIMAGE' => (strlen ( $class ['imagename'] ) > 1) ? $phpbb_root_path . "images/bbdkp/class_images/" . $class ['imagename'] . ".png" : '',
				'U_VIEW_CLASS' => append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_game&amp;mode=addclass&amp;r=" . $class ['class_id'] . "&amp;game_id={$listclasses->game_id}" ),
				'U_DELETE' => append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_game&amp;mode=editgames&amp;classdelete=1&amp;id={$class['class_id']}&amp;game_id={$listclasses->game_id }" ),
				'U_EDIT' => append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_game&amp;mode=editgames&amp;classedit=1&amp;id={$class['class_id']}&amp;game_id={$listclasses->game_id}" ) ) );
		}

		unset ( $listclasses, $cl );

		$imgexists = file_exists($phpbb_root_path. 'images/bbdkp/gameworld/'. $editgame->game_id. '/'. $editgame->getImagename() . '.png');

		$template->assign_vars ( array (
				'F_ENABLEARMORY' => $editgame->getArmoryEnabled() ,
				'GAMEIMAGEEXPLAIN' => sprintf($user->lang['GAME_IMAGE_EXPLAIN'], $editgame->game_id),
				'GAMEIMAGE' => $editgame->getImagename(),
				'GAMEPATH' => $phpbb_root_path. 'images/bbdkp/gameworld/'. $editgame->game_id. '/'. $editgame->getImagename() . '.png',
				'S_GAMEIMAGE_EXISTS' => (strlen($editgame->getImagename()) > 0 && $imgexists  ) ? true : false,
				'EDITGAME' => sprintf($user->lang['ACP_EDITGAME'], $editgame->getName()  ) ,
                'BOSSBASEURL' => $editgame->getBossbaseurl(),
                'ZONEBASEURL' => $editgame->getZonebaseurl(),
				'GAME_ID' => $editgame->game_id,
				'URI_GAME' => URI_GAME,
				'O_RACEGAMEID' => $current_order ['uri'] [0],
				'O_RACEID' => $current_order ['uri'] [1],
				'O_RACENAME' => $current_order ['uri'] [2],
				'O_FACTIONNAME' => $current_order ['uri'] [3],

				'O_CLASSGAMEID' => $current_order2 ['uri'] [0],
				'O_CLASSID' => $current_order2 ['uri'] [1],
				'O_CLASSNAME' => $current_order2 ['uri'] [2],
				'O_CLASSARMOR' => $current_order2 ['uri'] [3],
				'O_CLASSMIN' => $current_order2 ['uri'] [4],
				'O_CLASSMAX' => $current_order2 ['uri'] [5],

				'U_ADD_GAMES' => append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_game&amp;mode=editgames&amp;" ),
				'LISTFACTION_FOOTCOUNT' => sprintf ( $user->lang ['LISTFACTION_FOOTCOUNT'], $total_factions ),
				'LISTRACE_FOOTCOUNT' => sprintf ( $user->lang ['LISTRACE_FOOTCOUNT'], $total_races ),
				'LISTCLASS_FOOTCOUNT' => sprintf ( $user->lang ['LISTCLASS_FOOTCOUNT'], $total_classes ),
				'U_ACTION' => $this->u_action ) );
	}
}

?>