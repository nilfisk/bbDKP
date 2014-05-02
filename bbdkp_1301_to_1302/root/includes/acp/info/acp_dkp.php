<?php
/**
* ACP info class acp_dkp_info 
* @author Ippehe, Sajaki
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
* info class for acp module dkp
*   @package bbdkp
*/
class acp_dkp_info
{
	/**
	 * phpbb module function
	 */
	function module()
	{
		return array(
			'filename'	=> 'acp_dkp',
			'title'		=> 'ACP_DKP_MAINPAGE',
			'version'	=> '1.3.0',
			'modes'		=> array(
				'mainpage'				=> array('title' => 'ACP_DKP_PANEL', 	'auth' => 'acl_a_dkp', 'cat' => array('ACP_DKP_MAINPAGE')),
				'dkp_config'			=> array('title' => 'ACP_DKP_CONFIG', 	'auth' => 'acl_a_dkp', 'cat' => array('ACP_DKP_MAINPAGE')),
				'dkp_indexpageconfig'   => array('title' => 'ACP_DKP_INDEX', 	'auth' => 'acl_a_dkp', 'cat' => array('ACP_DKP_MAINPAGE')),
				'dkp_logs'				=> array('title' => 'ACP_DKP_LOGS', 	'auth' => 'acl_a_dkp', 'cat' => array('ACP_DKP_MAINPAGE')),
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
