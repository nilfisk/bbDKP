<?php
/**
 * bbdkp acp language file for News (EN)
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
	'ACP_DKP_NEWS'			=> 'Gestione News',
	'ACP_ADD_NEWS_EXPLAIN' 	=> 'Qui puoi aggiungere / modificare le news di gilda.',
	'ACP_DKP_NEWS_ADD'		=> 'Aggiungi News',  
	'ACP_DKP_NEWS_LIST'		=> 'News',
	'ACP_DKP_NEWS_LIST_EXPLAIN'	=> 'Lista items/news',
));
