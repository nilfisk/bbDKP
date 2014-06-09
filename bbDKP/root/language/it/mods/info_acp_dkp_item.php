<?php
/**
 * bbdkp acp language file for file for Items (en)
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
	'ACP_DKP_ITEM'			=> 'Gestione Oggetti',  
	'ACP_DKP_ITEM_ADD'		=> 'Aggiungi Oggetto',
	'ACP_DKP_ITEM_LIST'		=> 'Oggetti',
	'ACP_DKP_ITEM_EDIT'		=> 'Modifica Oggetti', 
	'ACP_DKP_ITEM_SEARCH'		=> 'Ricerca Oggetti',
	'ACP_DKP_ITEM_VIEW'		=> 'Visualizza Oggetti',
));
