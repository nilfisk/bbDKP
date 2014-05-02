<?php
/**
* ACP info class acp_dkp_mdkp_info 
* @version 1.3.0
* @copyright (c) 2009 bbdkp https://github.com/bbDKP
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @author sajaki9@gmail.com
*   @package bbdkp
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
 * info class for acp module dkp_mdkp
*   @package bbdkp
*/
class acp_dkp_mdkp_info
{
	/**
	 * phpbb module function
	 */
	function module()
	{
		return array(
			'filename'	=> 'acp_dkp_mdkp',
			'title'		=> 'ACP_DKP_MDKP',
			'version'	=> '1.3.0',
			'modes'		=> array(
				'mm_editmemberdkp'	=> array('title' => 'ACP_DKP_EDITMEMBERDKP',  'auth' => 'acl_a_dkp', 'cat' => array('ACP_DKP_MDKP'), 'display' => false ),
				'mm_listmemberdkp'	=> array('title' => 'ACP_DKP_LISTMEMBERDKP', 'auth' => 'acl_a_dkp',  'cat' => array('ACP_DKP_MDKP'), 'display' => true),
		        'mm_transfer'	    => array('title' => 'ACP_DKP_MEMBER_TRF', 'auth' => 'acl_a_dkp', 'cat' => array('ACP_DKP_MEMBER')),
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
