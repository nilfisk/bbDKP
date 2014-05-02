<?php
/**
 *  bbdkp acp language file for News - german
 * 
 * 
 * @copyright 2009 bbdkp <http://code.google.com/p/bbdkp/>
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
'ACP_DKP_NEWS'			=> 'Nachrichtenverwaltung',
'ACP_ADD_NEWS_EXPLAIN' 		=> 'Hier kannst du Gildennachrichten hinzufügen und löschen.',
'ACP_DKP_NEWS_ADD'		=> 'Nachricht hinzufügen',  
'ACP_DKP_NEWS_LIST'		=> 'Nachrichten',
'ACP_DKP_NEWS_LIST_EXPLAIN'	=> 'Liste der Gildennachrichten ',
));
