<?php
/**
 * bbdkp acp language file for Guild and Members - german
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
   	'ACP_DKP_MEMBER'	=> 'Gilden und Mitgliedsverwaltung',
	'ACP_DKP_MEMBER_ADD'	=> 'Mitglied hinzufügen',  
	'ACP_DKP_MEMBER_LIST'	=> 'Mitglieder',
));
