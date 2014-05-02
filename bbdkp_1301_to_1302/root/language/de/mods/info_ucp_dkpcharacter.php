<?php
/**
 * bbdkp ucp language file - german
 * 
 * 
 * @copyright 2010 bbdkp <https://www.github.com/bbDKP>
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
    'UCP_DKP_CHARACTERS'	=> 'Charaktere',
	'UCP_DKP'			=> 'bbDKP', 
	'UCP_DKP_CHARACTER_LIST'	=> 'Meine Charaktere',
	'UCP_DKP_CHARACTER_ADD'		=> 'Charakter hinzufügen',

));
