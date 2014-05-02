<?php
/**
 * ACP info class acp_dkp_event_info 
 * 
 * @version 1.3.0
 * @copyright (c) 2009 bbdkp https://github.com/bbDKP
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 *   @package bbdkp
 * @deprecated file deprecated in rel 1.3
 * @note : only needed for uninstallation of events module!
 /

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
 * info class for acp module dkp_event
 *   @package bbdkp
 */
class acp_dkp_event_info
{
	/**
	 * phpbb module function
	 */
	function module()
	{
		return array(
			'filename'	=> 'acp_dkp_event',
			'title'		=> 'ACP_DKP_EVENT',
			'version'	=> '1.3.0',
			'modes'		=> array(
				'addevent'			=> array('title' => 'ACP_DKP_EVENT_ADD', 'auth' => 'acl_a_dkp', 'cat' => array('ACP_DKP_EVENT') , 'display' => false),
				'listevents'		=> array('title' => 'ACP_DKP_EVENT_LIST', 'auth' => 'acl_a_dkp', 'cat' => array('ACP_DKP_EVENT')),
				),
		);
	}
	/**
	 * phpbb module function
	 */
	function install()
	{
	}
	/**
	 * phpbb module function
	 */
	function uninstall()
	{
	}
}

?>
