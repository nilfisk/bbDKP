<?php
/**
 * bbdkp acp language file for  DKP accounts (en)
 * 
 * 
 * @copyright 2009 bbdkp <https://github.com/bbDKP>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.3.0
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
  	'ACP_DKP_MDKP'	        	=> 'Gestione Punti Personaggio', 
   'ACP_DKP_EDITMEMBERDKP'		=> 'Modifica Punti Personaggio',
	'ACP_DKP_LISTMEMBERDKP'		=> 'Profili DKP',
	'ACP_DKP_MEMBER_TRF'		=> 'Trasferimento Punti',
));
