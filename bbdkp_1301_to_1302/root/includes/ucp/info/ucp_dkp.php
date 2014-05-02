<?php
/** 
* info class file for ucp module dkp
*   @package bbdkp
* @copyright (c) 2007 phpBB Group 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/
							
/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}


/**
 * info class for ucp module dkp
 *   @package bbdkp
 */
class ucp_dkp_info
{	/**
	 * phpbb module function
	 */
	function module()
	{
		return array(
			'filename'	=> 'ucp_dkp',
			'title'		=> 'UCP_DKP',
			'version'	=> '1.3.0',
			'modes'		=> array(
				'characters'	=> array('title' => 'UCP_DKP_CHARACTERS', 'auth' => 'acl_u_dkp', 'cat' => array('UCP_DKP')),
				'characteradd'	=> array('title' => 'UCP_DKP_CHARACTER_ADD', 'auth' => 'acl_u_dkp', 'cat' => array('UCP_DKP')),				
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