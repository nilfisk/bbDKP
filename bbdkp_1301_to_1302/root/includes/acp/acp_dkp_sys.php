<?php
/**
 * dkp pool acp file
 *
 *   @package bbdkp
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
if (!class_exists('\bbdkp\admin\Admin'))
{
	require ("{$phpbb_root_path}includes/bbdkp/admin/admin.$phpEx");
}
if (!class_exists('\bbdkp\controller\points\Pool'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/points/Pool.$phpEx");
}
if (!class_exists('\bbdkp\controller\raids\Events'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/raids/Events.$phpEx");
}


/**
 * This class manages admin settings
 *
 *   @package bbdkp
 */
 class acp_dkp_sys extends \bbdkp\admin\Admin
{
	/**
	 * link in trigger window
	 * @var string
	 */
	var $link;
	/**
	 * dkp pool
	 * @var int
	 */
	var $dkpsys;

	/**
	 * acp entrypoint
	 * @param int $id
	 * @param int $mode
	 */
	function main($id, $mode)
	{
		global $user, $template, $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;


		$this->tpl_name = 'dkp/acp_' . $mode;

		switch ($mode)
		{
			case 'adddkpsys' :
				$add = (isset ( $_POST ['add'] )) ? true : false;
				if ($add)
				{
                  	if (!check_form_key('adddkpsys'))
					{
						trigger_error('FORM_INVALID');
					}
					$this->dkpsys = new \bbdkp\controller\points\Pool();
					$this->dkpsys->dkpsys_name = utf8_normalize_nfc (request_var ( 'dkpsys_name', '', true ));
					$this->dkpsys->dkpsys_status = request_var ( 'dkpsys_status', 'Y' );
					$this->dkpsys->dkpsys_default = request_var ( 'dkpsys_default', 'Y' );
					$this->dkpsys->add();
					$success_message = sprintf ( $user->lang ['ADMIN_ADD_DKPSYS_SUCCESS'], $this->dkpsys->dkpsys_name );
					meta_refresh(1, append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_sys&amp;mode=listdkpsys" ) );
					trigger_error ( $success_message . $this->link );
				}

				$template->assign_vars ( array (

						'L_TITLE' 	=> $user->lang ['ACP_ADDDKPSYS'],
						'L_EXPLAIN' => $user->lang ['ACP_ADDDKPSYS_EXPLAIN'],
						'MSG_NAME_EMPTY' => $user->lang ['FV_REQUIRED_NAME'],
						'MSG_STATUS_EMPTY' => $user->lang ['FV_REQUIRED_STATUS'],
						'S_ADD' => true
					));

				add_form_key('adddkpsys');
				$this->page_title = 'ACP_ADDDKPSYS';

				break;
			case 'addevent':
				$update = false;
				$event_id = request_var(URI_EVENT, 0);
				$event  = new \bbdkp\controller\raids\Events($event_id);

				$url = append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_sys&amp;mode=editdkpsys&amp;" . URI_DKPSYS . "={$event->dkpsys_id}" );
				$this->link = '<br /><a href="' . $url .'"><h3>'. $user->lang['RETURN_DKPPOOLINDEX'].'</h3></a>';

				if(isset($event->dkpsys))
				{
					foreach ($event->dkpsys as $pool)
					{
						if($pool['id'] == $event->dkpsys_id)
						{
							$a = 1;
						}

						$template->assign_block_vars('event_dkpid_row', array(
								'VALUE' 	=> $pool['id'],
								'SELECTED' 	=> ($pool['id'] == $event->dkpsys_id) ? ' selected="selected"' : '' ,
								'OPTION'	=> $pool['name'])
						);
					}
				}
				else
				{
					trigger_error('ERROR_NOPOOLS', E_USER_WARNING );
				}

				$add = (isset($_POST['add'])) ? true : false;
				$submit	= (isset($_POST['update'])) ? true : false;
				$delete	= (isset($_POST['delete'])) ? true : false;
				$addraid = (isset($_POST['newraid'])) ? true : false;

				if ( $add || $submit || $addraid)
				{
				    if (!check_form_key('addevent'))
					{
						trigger_error('FORM_INVALID');
					}
				}

				if ($addraid)
				{
					redirect(append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_raid&amp;mode=addraid&amp;".URI_DKPSYS . '=' .
							$event->dkpsys_id . '&amp;' . URI_EVENT . '=' . $event->event_id ));
				}

				if ($add)
				{

					/*
					 if (isset($config['bbdkp_gameworld_version']))
					 {
					if (isset($config['bbdkp_gameworld_version']))
					{
					$zone= utf8_normalize_nfc(request_var('zoneevent','', true));
					if ($zone != "--")
					{
					$event->event_name= $zone;
					}
					}
					}
					*/


					$event->dkpsys_id = request_var('event_dkpid',0);
					$event->event_name = utf8_normalize_nfc(request_var('event_name','', true));
					$event->event_imagename = utf8_normalize_nfc(request_var('event_image','', true));
					$event->event_color = utf8_normalize_nfc(request_var('event_color','', true));
					$event->event_value= request_var('event_value', 0.0);
					$event->add();
					$url = append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_sys&amp;mode=editdkpsys&amp;" . URI_DKPSYS . "={$event->dkpsys_id}" );
					$success_message = sprintf($user->lang['ADMIN_ADD_EVENT_SUCCESS'], $event->event_value , $event->event_name );

					meta_refresh(1,$url);
					trigger_error($success_message . $this->link);


				}

				if ($submit)
				{
					// get old event name, value from db
					$oldevent = new \bbdkp\controller\raids\Events($event_id);
					$newvent = new \bbdkp\controller\raids\Events($event_id);

					$newvent->event_name = utf8_normalize_nfc(request_var('event_name','', true));

					if (strlen($newvent->event_name) < 3)
					{
						trigger_error($user->lang['ERROR_INVALID_EVENT_PROVIDED'] . $this->link, E_USER_WARNING);
					}

					/*
					 if (isset($config['bbdkp_gameworld_version']))
					 {
					$zone= utf8_normalize_nfc(request_var('zoneevent','', true));
					if ($zone != "--")
					{
					$new_event_name = $zone;
					}
					}
					*/

					$newvent->dkpsys_id = request_var('event_dkpid', '');
					$newvent->event_imagename = utf8_normalize_nfc(request_var('event_image','', true));
					$newvent->event_color  = utf8_normalize_nfc(request_var('event_color','', true));
					$newvent->event_value = request_var('event_value', 0.0);
					$newvent->update($oldevent);

					$success_message = sprintf($user->lang['ADMIN_UPDATE_EVENT_SUCCESS'], $newvent->event_value, $newvent->event_name);

					unset($newvent);
					unset($oldevent);

					meta_refresh(1,$url);
					trigger_error($success_message . $this->link);


				}

				if ($delete)
				{
					// give a warning that raids cant be without event
					if (confirm_box(true))
					{
						$event = new \bbdkp\controller\raids\Events(request_var(URI_EVENT,0));
						$url = request_var( 'url', '');
						$event->delete();
						$success_message = sprintf($user->lang['ADMIN_DELETE_EVENT_SUCCESS'], $event->event_value, $event->event_name);

						meta_refresh(1,$url);
						trigger_error($success_message);
					}
					else
					{
						$s_hidden_fields = build_hidden_fields(array(
								'delete'	=> true,
								'event_id'	=> $event->event_id ,
								'url' 		=> $url
						)
						);
						$template->assign_vars(array(
								'S_HIDDEN_FIELDS'	 => $s_hidden_fields)
						);
						confirm_box(false, $user->lang['CONFIRM_DELETE_EVENT'], $s_hidden_fields);
					}


				}

				add_form_key('addevent');
				$template->assign_vars(array(
						'L_TITLE'			=> $user->lang['ACP_ADDEVENT'],
						'L_EXPLAIN' 		=> $user->lang['ACP_ADDEVENT_EXPLAIN'],
						'EVENT_ID'			=> $event->event_id,
						'EVENT_DKPPOOLNAME'	=> $event->dkpsys_name,
						'EVENT_NAME'		=> $event->event_name,
						'S_EVENT_STATUS'	=> $event->event_status == 1 ? true : false,
						'EVENT_VALUE'		=> $event->event_value,
						'EVENT_COLOR'		=> ($event->event_color == '') ? '#FFFFFF' : $event->event_color,
						'EVENT_IMAGENAME'	=> $event->event_imagename,
						'IMAGEPATH' 		=> $phpbb_root_path . "images/bbdkp/event_images/" . $event->event_imagename . ".png",
						'S_EVENT_IMAGE_EXISTS' 	=> (strlen($event->event_imagename) > 1) ? true : false,
						'L_DKP_VALUE'		=> sprintf($user->lang['DKP_VALUE'], $config['bbdkp_dkp_name']),
						'MSG_NAME_EMPTY'	=> $user->lang['FV_REQUIRED_NAME'],
						'MSG_VALUE_EMPTY' 	=> $user->lang['FV_REQUIRED_VALUE'],
						'S_ADD' 			=> $event->event_id  == 0 ? true : false
				)
				);


				break;

			case 'editdkpsys' :
				//edit Pool name, list events, and add event
				$submit = (isset ( $_POST ['update'] )  or isset ( $_POST ['dkpsys_status'] ) ) ? true : false;
				$dkpsys_id = request_var ( URI_DKPSYS, 0 );
				$activate = (isset ( $_POST ['deactivate'] )) ? true : false;
				$addevent = (isset($_POST['addevent'])) ? true : false;

				if($addevent)
				{
					redirect(append_sid("{$phpbb_admin_path}index.$phpEx", "i=dkp_sys&amp;mode=addevent"));
					break;
				}

				if ($submit)
				{
					// update point pool
					if (!check_form_key('editdkpsys'))
					{
						trigger_error('FORM_INVALID');
					}
					$this->dkpsys = new \bbdkp\controller\points\Pool($dkpsys_id);
					$olddkpsys = new \bbdkp\controller\points\Pool($dkpsys_id);

					$this->dkpsys->dkpsys_name =utf8_normalize_nfc (request_var ( 'dkpsys_name', '', true ));
					$this->dkpsys->dkpsys_status = request_var ( 'dkpsys_status', 'Y' );
					$this->dkpsys->dkpsys_default = request_var ( 'dkpsys_default', 'Y');

					$this->dkpsys->update($olddkpsys);
					unset($olddkpsys);
					$success_message = sprintf ( $user->lang ['ADMIN_UPDATE_DKPSYS_SUCCESS'], $this->dkpsys->dkpsys_id, $this->dkpsys->dkpsys_name, $this->dkpsys->dkpsys_status);
					meta_refresh(1, append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_sys&amp;mode=editdkpsys&amp;" . URI_DKPSYS . "={$dkpsys_id}" ));
					trigger_error ( $success_message . $this->link );
				}

				$event  = new \bbdkp\controller\raids\Events();
				$event->countevents($dkpsys_id);

				if ($activate)
				{
					// all events in this window
					$all_events = explode(',', request_var ( 'idlist', '') );
					// all checked events in this window
					$active_events = request_var ( 'activate_ids', array (0));
					//activate selected events
					$event->activateevents(1, $active_events);
					//deactivate unselected events
					$event->activateevents(0,  array_diff($all_events, $active_events) );

				}

				//show pool

				$this->dkpsys = new \bbdkp\controller\points\Pool($dkpsys_id);
				$template->assign_vars ( array (
						'DKPSYS_ID' => $this->dkpsys->dkpsys_id,
						'L_TITLE' 	=> $user->lang ['ACP_ADDDKPSYS'],
						'L_EXPLAIN' => $user->lang ['ACP_ADDDKPSYS_EXPLAIN'],
						'DKPSYS_NAME' => $this->dkpsys->dkpsys_name,
						'DKPSYS_STATUS' => $this->dkpsys->dkpsys_status,
						'MSG_NAME_EMPTY' => $user->lang ['FV_REQUIRED_NAME'],
						'MSG_STATUS_EMPTY' => $user->lang ['FV_REQUIRED_STATUS'],
						'S_ADD' => false ));

				add_form_key('editdkpsys');


				// Event list

				$sort_order = array(
						0 => array('event_name', 'dkpsys_name, event_name desc'),
						1 => array('event_value desc', 'dkpsys_name, event_value desc'),
						2 => array('event_status desc', 'dkpsys_name, event_status, event_name desc'),
						3 => array('event_status desc', 'dkpsys_name, event_status, event_name desc'),
				);

				$current_order = $this->switch_order($sort_order);
				$start = request_var('start',0);
				$event->listevents($start, $current_order['sql'], $dkpsys_id, true);

				$idlist = array();

				if(isset($event->events))
				{
					foreach ($event->events as $id => $listevent)
					{
						$template->assign_block_vars('events_row', array(
								'EVENT_ID' => $listevent ['event_id'],
								'U_VIEW_EVENT' =>append_sid("{$phpbb_admin_path}index.$phpEx", "i=dkp_sys&amp;mode=addevent&amp;" . URI_EVENT ."={$id}"),
								'COLOR' => $listevent['event_color'],
								'IMAGEPATH' 	=> $phpbb_root_path . "images/bbdkp/event_images/" . $listevent['event_imagename'] . ".png",
								'S_EVENT_IMAGE_EXISTS' => (strlen($listevent['event_imagename']) > 1) ? true : false,
								'S_EVENT_STATUS' => ($listevent ['event_status'] == 1) ? 'checked="checked" ' : '',
								'IMAGENAME' => $listevent['event_imagename'],
								'NAME' => $listevent['event_name'],
								'VALUE' => $listevent['event_value'])
						);
						$idlist[] = $listevent ['event_id'];
					}
				}

				$template->assign_vars(array(
						'IDLIST'		=> implode(",", $idlist),
						'L_TITLE'		=> $user->lang['ACP_LISTEVENTS'],
						'L_EXPLAIN'		=> $user->lang['ACP_LISTEVENTS_EXPLAIN'],
						'O_DKPSYS'		=> $current_order['uri'][0],
						'O_NAME'		=> $current_order['uri'][1],
						'O_VALUE'		=> $current_order['uri'][2],
						'START'			=> $start,
						'U_LIST_EVENTS' => append_sid("{$phpbb_admin_path}index.$phpEx", "i=dkp_sys&amp;mode=editdkpsys&amp;" . URI_DKPSYS . "={$dkpsys_id}"   ),
						'LISTEVENTS_FOOTCOUNT' => sprintf($user->lang['LISTEVENTS_FOOTCOUNT'], $event->total_events, $config['bbdkp_user_elimit']),
						'EVENT_PAGINATION'	=> generate_pagination(append_sid("{$phpbb_admin_path}index.$phpEx", "i=dkp_sys&amp;mode=editdkpsys&amp;" . URI_DKPSYS . "={$dkpsys_id}&amp;" .
						URI_ORDER . '='.$current_order['uri']['current']), $event->total_events, $config['bbdkp_user_elimit'],$start, true))

				);

				break;
			case 'listdkpsys' :

				if(count($this->games) == 0)
				{
					trigger_error($user->lang['ERROR_NOGAMES'], E_USER_WARNING);
				}

				// list of pools
				$showadd = (isset ( $_POST ['dkpsysadd'] )) ? true : false;
				$delete = (isset ( $_GET ['delete'] ) && isset ( $_GET [URI_DKPSYS] )) ? true : false;
				$submit = (isset ( $_POST ['defaultsys'] )  ) ? true : false; //pulldown js submit
				//add new pool
				if ($showadd)
				{
					redirect ( append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_sys&amp;mode=adddkpsys" ) );
					break;
				}

				//user clicked on red button
				if ($delete)
				{
					if (confirm_box ( true ))
					{
						$this->dkpsys = new \bbdkp\controller\points\Pool( request_var ( 'hidden_dkpsys_id' , 0 ) );
						$this->dkpsys->delete();

						$success_message = sprintf ($user->lang ['ADMIN_DELETE_DKPSYS_SUCCESS'], $this->dkpsys->dkpsys_name);
						trigger_error ($success_message . $this->link );
					}
					else
					{
						$s_hidden_fields = build_hidden_fields ( array (
							'delete' => true,
							'hidden_dkpsys_id' => request_var ( URI_DKPSYS, 0 ) ) );
						$template->assign_vars ( array ('S_HIDDEN_FIELDS' => $s_hidden_fields ) );
						confirm_box ( false, $user->lang ['CONFIRM_DELETE_DKPSYS'], $s_hidden_fields );
					}
				}


				// DEFAULT DKPSYS submit buttonhandler
				if ($submit)
				{
					$this->dkpsys = new \bbdkp\controller\points\Pool(request_var ( 'defaultsys', 0 ));
					$olddkpsys = new \bbdkp\controller\points\Pool(request_var ( 'defaultsys', 0 ));
					$this->dkpsys->dkpsys_default = 'Y';
					$this->dkpsys->update($olddkpsys);
					unset($olddkpsys);
					$success_message = sprintf ( $user->lang ['ADMIN_DEFAULTPOOL_SUCCESS'],  $this->dkpsys->dkpsys_name );
					meta_refresh(1, append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_sys&amp;mode=listdkpsys"));
					trigger_error ( $success_message . $this->link) ;
				}

				// template

				$this->dkpsys = new \bbdkp\controller\points\Pool();
				$listpools = $this->dkpsys->listpools();
				foreach($listpools as $dkpsys_id => $pool)
				{
					$template->assign_block_vars ( 'dkpsysdef_row',
						array (
							'VALUE' => $dkpsys_id,
							'SELECTED' => ('Y' == $pool ['dkpsys_default']) ? ' selected="selected"' : '',
							'OPTION' => $pool['dkpsys_name'] ));
				}

				$sort_order = array (
					0 => array ('dkpsys_name', 'dkpsys_name desc' ),
					1 => array ('dkpsys_id desc', 'dkpsys_id' ) );
				$current_order = $this->switch_order ( $sort_order );
				$start = request_var ( 'start', 0 );
				$listpools = $this->dkpsys->listpools($current_order['sql'], $start, 1);

				foreach($listpools as $dkpsys_id => $pool)
				{
					$template->assign_block_vars ( 'dkpsys_row',
						array (
							'U_VIEW_DKPSYS' => append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_sys&amp;mode=editdkpsys&amp;" . URI_DKPSYS . "={$dkpsys_id}" ),
							'U_DELETE_DKPSYS' => append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_sys&amp;mode=listdkpsys&amp;delete=1&amp;" . URI_DKPSYS . "={$dkpsys_id}" ),
							'ID' => $pool['dkpsys_id'],
							'NUMEVENTS' => $pool['numevents'],
							'NAME' => $pool['dkpsys_name'],
							'STATUS' => $pool['dkpsys_status'],
							'DEFAULT' => $pool['dkpsys_default'] ));
				}

				$template->assign_vars ( array (
					'L_TITLE' 		=> $user->lang ['ACP_LISTDKPSYS'],
					'L_EXPLAIN' 	=> $user->lang ['ACP_LISTDKPSYS_EXPLAIN'],
					'O_NAME' 		=> $current_order ['uri'] [0],
					'O_STATUS' 		=> $current_order ['uri'] [1],
					'U_LIST_DKPSYS' => append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_sys&amp;mode=listdkpsys&amp;" ),
					'START' 		=> $start,
					'UA_UPDATEPOOLSTATUS' => append_sid($phpbb_admin_path . "style/dkp/updatedefaultpools.$phpEx") ,
					'LISTDKPSYS_FOOTCOUNT' => sprintf ( $user->lang ['LISTDKPSYS_FOOTCOUNT'], $this->dkpsys->poolcount, $config ['bbdkp_user_elimit'] ),
					'DKPSYS_PAGINATION' => generate_pagination ( append_sid ( "{$phpbb_admin_path}index.$phpEx", "i=dkp_sys&amp;mode=listdkpsys&amp;" ) . "&amp;o=" .
						$current_order ['uri'] ['current'], $this->dkpsys->poolcount, $config ['bbdkp_user_elimit'], $start )), true );

				$this->page_title = 'ACP_LISTDKPSYS';
				break;
		}
	}




}

?>