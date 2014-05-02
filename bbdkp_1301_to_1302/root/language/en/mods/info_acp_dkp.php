<?php
/**
 * bbdkp acp language file for mainmenu
 * 
 * 
 * @copyright 2009 bbdkp <https://github.com/bbDKP>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License 
 * @version 1.3.0 * 
 */

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

// Create the lang array if it does not already exist
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

// Merge the following language entries into the lang array
$lang = array_merge($lang, array(
    'ACP_CAT_DKP'			=> 'BBDKP',
	'ACP_DKP_MAINPAGE'		=> 'General Settings',  
	'ACP_DKP_PANEL'		    	=> 'Adminpanel',  
	'ACP_DKP_CONFIG'		=> 'bbdkp Settings',
	'ACP_DKP_INDEX'			=> 'Portal Settings',
	'ACP_DKP_LOGS'			=> 'View Logs',
));
