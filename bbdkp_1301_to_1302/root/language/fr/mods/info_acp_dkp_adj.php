<?php
/**
 * bbdkp acp language file for Ajdustments (FR)
 * 
 * 
 * @copyright 2009 bbdkp <http://code.google.com/p/bbdkp/>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.3.0
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
  	'ACP_DKP_MDKP'	        => 'Gestion Comptes DKP', 
	'ACP_DKP_ADDADJ'	=> 'Ajouter ajustement',
	'ACP_DKP_LISTADJ'	=> 'Liste des Ajustements',
));
