<?php
/**
* ACP info class acp_dkp_adj_info
*   @package bbdkp
* @copyright (c) 2009 bbdkp https://github.com/bbDKP
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @version 1.3.0
* 
**/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}


/**
 * info class for acp module dkp_adj
 *   @package bbdkp
 */
class acp_dkp_adj_info
{
	/**
	 * phpbb module function
	 */
	function module()
	{
		return array(
			'filename'	=> 'acp_dkp_adj',
		    'title'	=> 'ACP_DKP_MDKP',
			'version'	=> '1.3.0',
			'modes'		=> array(			
				'addiadj'	=> array('title' => 'ACP_DKP_ADDADJ', 'auth' => 'acl_a_dkp', 'cat' => array('ACP_DKP_MDKP'), 'display' => false),
				'listiadj'	=> array('title' => 'ACP_DKP_LISTADJ', 'auth' => 'acl_a_dkp', 'cat' => array('ACP_DKP_MDKP'), 'display' => true),
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
