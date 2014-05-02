<?php
/**
* ACP info class acp_dkp_game_info 
*   @package bbdkp
* @version 1.3.0
* @copyright (c) 2009 bbdkp https://github.com/bbDKP
* @license http://opensource.org/licenses/gpl-license.php GNU Public License

*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
 * info class for acp module dkp_game
 *   @package bbdkp
 */
class acp_dkp_game_info
{
	/**
	 * phpbb module function
	 */
	function module()
	{
		return array(
			'filename'	=> 'acp_dkp_game',
			'title'		=> 'ACP_DKP_GAME',
			'version'	=> '1.3.0',
			'modes'		=> array(
				'listgames'		=> array('title' => 'ACP_DKP_GAME_LIST',  'auth' => 'acl_a_dkp', 'cat' => array('ACP_DKP_GAME') , 'display' => true),
				'editgames'		=> array('title' => 'ACP_DKP_GAME_EDIT',  'auth' => 'acl_a_dkp', 'cat' => array('ACP_DKP_GAME') , 'display' => false),
				'addfaction'	=> array('title' => 'ACP_DKP_FACTION_ADD',   'auth' => 'acl_a_dkp', 'cat' => array('ACP_DKP_GAME') , 'display' => false),
				'addrace'		=> array('title' => 'ACP_DKP_RACE_ADD',   'auth' => 'acl_a_dkp', 'cat' => array('ACP_DKP_GAME') , 'display' => false),
				'addclass'		=> array('title' => 'ACP_DKP_CLASS_ADD',  'auth' => 'acl_a_dkp', 'cat' => array('ACP_DKP_GAME') , 'display' => false),
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
