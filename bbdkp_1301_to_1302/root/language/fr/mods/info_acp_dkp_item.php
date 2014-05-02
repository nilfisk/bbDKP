<?php
/**
 * bbdkp acp language file for Items (FR)
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
	'ACP_DKP_ITEM'			=> 'Gestion des Objets',  
	'ACP_DKP_ITEM_ADD'		=> 'Ajouter objet',
	'ACP_DKP_ITEM_LIST'		=> 'Liste des Objets',
	'ACP_DKP_ITEM_EDIT'		=> 'Editer Objet', 
	'ACP_DKP_ITEM_SEARCH'		=> 'Recherche d’objets',
	'ACP_DKP_ITEM_VIEW'		=> 'Visualisation d’objets',
));
