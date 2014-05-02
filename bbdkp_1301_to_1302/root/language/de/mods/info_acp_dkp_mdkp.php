<?php
/**
 * bbdkp acp language file for  DKP accounts - german
 * 
 * 
 * @copyright 2009 bbdkp <https://github.com/bbDKP>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.3.0
 * @translation various unknown authors, killerpommes
 * 
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
  	'ACP_DKP_MDKP'	        	=> 'Punktekontenverwaltung',
    'ACP_DKP_EDITMEMBERDKP'		=> 'Konto bearbeiten',
	'ACP_DKP_LISTMEMBERDKP'		=> 'Punktekontenliste',
	'ACP_DKP_MEMBER_TRF'		=> 'Punkteübertragung',
));
