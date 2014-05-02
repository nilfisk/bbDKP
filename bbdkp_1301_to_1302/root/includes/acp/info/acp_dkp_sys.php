<?php
/**
* ACP info class acp_dkp_sys_info 
* @author Sajaki
* @copyright (c) 2009 bbdkp https://github.com/bbDKP
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @version 1.3.0
*   @package bbdkp
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}


/**
 * info class for acp module dkp_sys
*   @package bbdkp
*/
class acp_dkp_sys_info
{
	/**
	 * phpbb module function
	 */
	function module()
	{
		return array(
			'filename'	=> 'acp_dkp_sys',
			'title'		=> 'ACP_DKP_RAIDS',
			'version'	=> '1.3.0',
			'modes'		=> array(
				'adddkpsys'		=> array('title' => 'ACP_DKP_POOL_ADD', 'auth' => 'acl_a_dkp', 'cat' => array('ACP_DKP_RAIDS') , 'display' => false),
				'editdkpsys'	=> array('title' => 'ACP_DKP_POOL_EDIT', 'auth' => 'acl_a_dkp', 'cat' => array('ACP_DKP_RAIDS') , 'display' => false),
				'listdkpsys'	=> array('title' => 'ACP_DKP_POOL_LIST', 'auth' => 'acl_a_dkp', 'cat' => array('ACP_DKP_RAIDS') , 'display' => true ),
				'addevent'		=> array('title' => 'ACP_DKP_EVENT_ADD', 'auth' => 'acl_a_dkp', 'cat' => array('ACP_DKP_EVENT') , 'display' => false),
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
