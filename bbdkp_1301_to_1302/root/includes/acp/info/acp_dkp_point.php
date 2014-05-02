<?php
/**
* ACP info class acp_dkp_point_info 
* @author Sajaki
* @copyright (c) 2012 bbdkp https://github.com/bbDKP
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
 * info class for acp module dkp_point
 *   @package bbdkp
 */
class acp_dkp_point_info
{
	/**
	 * phpbb module function
	 */
	function module()
	{
		return array(
			'filename'	=> 'acp_dkp_point',
			'title'		=> 'ACP_DKP_POINT_CONFIG',
			'version'	=> '1.3.0',
			'modes'		=> array(
				'pointconfig'			=> array(
					'title' => 'ACP_DKP_POINT_CONFIG', 	
					'auth' => 'acl_a_dkp', 
					'cat' => array('ACP_DKP_MAINPAGE')),
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
