<?php
/**
* ACP info class acp_dkp_item_info 
* @author ippehe
* @version 1.3.0
* @copyright (c) 2009 bbdkp https://github.com/bbDKP
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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
 * info class for acp module dkp_item
*   @package bbdkp
*/
class acp_dkp_item_info
{
	/**
	 * phpbb module function
	 */
	function module()
	{
		return array(
			'filename'	=> 'acp_dkp_item',
			'title'		=> 'ACP_DKP_ITEM',
			'version'	=> '1.3.0',
			'modes'		=> array(
				'additem'			=> array('title' => 'ACP_DKP_ITEM_ADD', 'auth' => 'acl_a_dkp', 'cat' => array('ACP_DKP_RAIDS'), 'display' => false ),
				'listitems'			=> array('title' => 'ACP_DKP_ITEM_LIST', 'auth' => 'acl_a_dkp', 'cat' => array('ACP_DKP_RAIDS') , 'display' => true ),
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
