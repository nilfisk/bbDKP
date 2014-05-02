<?php
/**
 * bbdkp acp language file for mainmenu - French
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
	'ACP_DKP_POOL_ADD'		=> 'Ajouter groupe DKP',  
	'ACP_DKP_POOL_EDIT'		=> 'Editer groupe Dkp',
	'ACP_DKP_POOL_LIST'		=> 'Groupes DKP',
	'ACP_DKP_EVENT_ADD'		=> 'Ajoute évènement' ,
));
