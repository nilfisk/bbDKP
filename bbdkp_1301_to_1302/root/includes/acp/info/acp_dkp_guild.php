<?php
/**
* ACP info class acp_dkp_guild_info
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
 * info class for acp module dkp_guild
*   @package bbdkp
*/
class acp_dkp_guild_info
{
	/**
	 * phpbb module function
	 */
	function module()
	{
		return array(
			'filename'	=> 'acp_dkp_guild',
			'title'		=> 'ACP_DKP_GUILD',
			'version'	=> '1.3.0',
			'modes'		=> array(
				'addguild'	    => array('title' => 'ACP_DKP_GUILD_ADD', 'auth' => 'acl_a_dkp', 'cat' => array('ACP_DKP_MEMBER'), 'display' => false),
				'editguild'	    => array('title' => 'ACP_DKP_GUILD_EDIT', 'auth' => 'acl_a_dkp', 'cat' => array('ACP_DKP_MEMBER'), 'display' => false),
				'listguilds'	    => array('title' => 'ACP_DKP_GUILD_LIST', 'auth' => 'acl_a_dkp', 'cat' => array('ACP_DKP_MEMBER')),
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

