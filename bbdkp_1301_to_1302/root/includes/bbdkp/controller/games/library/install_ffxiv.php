<?php
/**
 * bbdkp ffxiv install data
 *
 * @package bbdkp
 * @link http://www.bbdkp.com
 * @author Brytor (Legion of Altana)
 * @author Sajaki
 * @copyright 2014 bbdkp
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.3.0
 *
 */
namespace bbdkp\controller\games;
/**
 * @ignore
 */
if (! defined ( 'IN_PHPBB' ))
{
	exit ();
}

$phpEx = substr(strrchr(__FILE__, '.'), 1);
global $phpbb_root_path;

if (!class_exists('\bbdkp\controller\games\GameInstall'))
{
	require("{$phpbb_root_path}includes/bbdkp/controller/games/library/GameInstall.$phpEx");
}

/**
 * ffxiv Installer Class
 * @package bbdkp
 *
 */
class install_ffxiv extends \bbdkp\controller\games\GameInstall
{
    protected $bossbaseurl = 'http://na.finalfantasyxiv.com/lodestone/playguide/db/npc/enemy/%s/';
    protected $zonebaseurl = 'http://na.finalfantasyxiv.com/lodestone/playguide/db/npc/?category2=enemy&area=%s';

	/**
	 * Installs factions
	 */
    protected function Installfactions()
	{
		global  $db;

		// factions
		$db->sql_query('DELETE FROM ' . FACTION_TABLE . " where game_id = 'ffxiv'" );
		$sql_ary = array();
	    $sql_ary[] = array('game_id' => 'ffxiv','faction_id' => 1, 'faction_name' => 'Limsa Lominsa: The Maelstrom' );
	    $sql_ary[] = array('game_id' => 'ffxiv','faction_id' => 2, 'faction_name' => 'Gridania: The Order of the Twin Adder' );
	    $sql_ary[] = array('game_id' => 'ffxiv','faction_id' => 3, 'faction_name' => 'Ul’dah: The Immortal Flames' );
		$db->sql_multi_insert( FACTION_TABLE, $sql_ary);
		unset ($sql_ary);

	}


	/**
	 * Installs game classes
	*/
    protected function InstallClasses()
	{
		global  $db;


		// class :
	    $db->sql_query('DELETE FROM ' . CLASS_TABLE . " where game_id = 'ffxiv'" );
	    $sql_ary = array();
	    $sql_ary[] = array('game_id' => 'ffxiv','class_id' => 0, 'class_armor_type' => 'CLOTH' , 'class_min_level' => 1 , 'class_max_level'  => 50, 'imagename' => 'ffxiv_unknown' );
	    $sql_ary[] = array('game_id' => 'ffxiv','class_id' => 1, 'class_armor_type' => 'LEATHER' , 'class_min_level' => 1 , 'class_max_level'  => 50, 'imagename' => 'ffxiv_archer' );
	    $sql_ary[] = array('game_id' => 'ffxiv','class_id' => 2, 'class_armor_type' => 'LEATHER' , 'class_min_level' => 1 , 'class_max_level'  => 50, 'imagename' => 'ffxiv_bard' );
	    $sql_ary[] = array('game_id' => 'ffxiv','class_id' => 3, 'class_armor_type' => 'PLATE' , 'class_min_level' => 1 , 'class_max_level'  => 50, 'imagename' => 'ffxiv_gladiator' );
	    $sql_ary[] = array('game_id' => 'ffxiv','class_id' => 4, 'class_armor_type' => 'PLATE' , 'class_min_level' => 1 , 'class_max_level'  => 50, 'imagename' => 'ffxiv_paladin' );
	    $sql_ary[] = array('game_id' => 'ffxiv','class_id' => 5, 'class_armor_type' => 'PLATE' , 'class_min_level' => 1 , 'class_max_level'  => 50, 'imagename' => 'ffxiv_lancer' );
	    $sql_ary[] = array('game_id' => 'ffxiv','class_id' => 6, 'class_armor_type' => 'PLATE' , 'class_min_level' => 1 , 'class_max_level'  => 50, 'imagename' => 'ffxiv_dragoon' );
	    $sql_ary[] = array('game_id' => 'ffxiv','class_id' => 7, 'class_armor_type' => 'PLATE' , 'class_min_level' => 1 , 'class_max_level'  => 50, 'imagename' => 'ffxiv_marauder' );
	    $sql_ary[] = array('game_id' => 'ffxiv','class_id' => 8, 'class_armor_type' => 'PLATE' , 'class_min_level' => 1 , 'class_max_level'  => 50, 'imagename' => 'ffxiv_warrior' );
	    $sql_ary[] = array('game_id' => 'ffxiv','class_id' => 9, 'class_armor_type' => 'LEATHER' , 'class_min_level' => 1 , 'class_max_level'  => 50, 'imagename' => 'ffxiv_pugilist' );
	    $sql_ary[] = array('game_id' => 'ffxiv','class_id' => 10, 'class_armor_type' => 'LEATHER' , 'class_min_level' => 1 , 'class_max_level'  => 50, 'imagename' => 'ffxiv_conjurer' );
	    $sql_ary[] = array('game_id' => 'ffxiv','class_id' => 11, 'class_armor_type' => 'CLOTH' , 'class_min_level' => 1 , 'class_max_level'  => 50, 'imagename' => 'ffxiv_white_mage' );
	    $sql_ary[] = array('game_id' => 'ffxiv','class_id' => 12, 'class_armor_type' => 'CLOTH' , 'class_min_level' => 1 , 'class_max_level'  => 50, 'imagename' => 'ffxiv_thaumaturge' );
	    $sql_ary[] = array('game_id' => 'ffxiv','class_id' => 13, 'class_armor_type' => 'CLOTH' , 'class_min_level' => 1 , 'class_max_level'  => 50, 'imagename' => 'ffxiv_black_mage' );
	    $sql_ary[] = array('game_id' => 'ffxiv','class_id' => 14, 'class_armor_type' => 'CLOTH' , 'class_min_level' => 1 , 'class_max_level'  => 50, 'imagename' => 'ffxiv_arcanist' );
	    $sql_ary[] = array('game_id' => 'ffxiv','class_id' => 15, 'class_armor_type' => 'CLOTH' , 'class_min_level' => 1 , 'class_max_level'  => 50, 'imagename' => 'ffxiv_summoner' );
	    $sql_ary[] = array('game_id' => 'ffxiv','class_id' => 16, 'class_armor_type' => 'CLOTH' , 'class_min_level' => 1 , 'class_max_level'  => 50, 'imagename' => 'ffxiv_scholar' );
		$db->sql_multi_insert( CLASS_TABLE, $sql_ary);
		unset ($sql_ary);

		// Language table
		$db->sql_query('DELETE FROM ' . BB_LANGUAGE . "  where game_id = 'ffxiv' and attribute='class' ");
		$sql_ary = array();
		$sql_ary[] = array('game_id' => 'ffxiv','attribute_id' => 0, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Unknown' ,  'name_short' =>  'Unknown' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 1, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Archer' ,  'name_short' =>  'Archer' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 2, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Bard' ,  'name_short' =>  'Bard' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 3, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Gladiator' ,  'name_short' =>  'Gladiator' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 4, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Paladin' ,  'name_short' =>  'Paladin' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 5, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Lancer' ,  'name_short' =>  'Dragoon' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 6, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Marauder' ,  'name_short' =>  'Marauder' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 7, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Warrior' ,  'name_short' =>  'Warrior' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 8, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Pugilist' ,  'name_short' =>  'Pugilist' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 9, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Monk' ,  'name_short' =>  'Monk' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 10, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Conjurer' ,  'name_short' =>  'Conjurer' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 11, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'White Mage' ,  'name_short' =>  'White Mage' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 12, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Thaumaturge' ,  'name_short' =>  'Thaumaturge' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 13, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Black Mage' ,  'name_short' =>  'Black Mage' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 14, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Arcanist' ,  'name_short' =>  'Arcanist' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 15, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Summoner' ,  'name_short' =>  'Summoner' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 16, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Scholar' ,  'name_short' =>  'Scholar' );

		$sql_ary[] = array('game_id' => 'ffxiv','attribute_id' => 0, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'Unknown' ,  'name_short' =>  'Unknown' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 1, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'Archer' ,  'name_short' =>  'Archer' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 2, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'Bard' ,  'name_short' =>  'Bard' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 3, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'Gladiator' ,  'name_short' =>  'Gladiator' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 4, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'Paladin' ,  'name_short' =>  'Paladin' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 5, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'Lancer' ,  'name_short' =>  'Dragoon' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 6, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'Marauder' ,  'name_short' =>  'Marauder' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 7, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'Warrior' ,  'name_short' =>  'Warrior' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 8, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'Pugilist' ,  'name_short' =>  'Pugilist' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 9, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'Monk' ,  'name_short' =>  'Monk' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 10, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'Conjurer' ,  'name_short' =>  'Conjurer' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 11, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'White Mage' ,  'name_short' =>  'White Mage' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 12, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'Thaumaturge' ,  'name_short' =>  'Thaumaturge' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 13, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'Black Mage' ,  'name_short' =>  'Black Mage' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 14, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'Arcanist' ,  'name_short' =>  'Arcanist' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 15, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'Summoner' ,  'name_short' =>  'Summoner' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 16, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'Scholar' ,  'name_short' =>  'Scholar' );

		$sql_ary[] = array('game_id' => 'ffxiv','attribute_id' => 0, 'language' =>   'fr' , 'attribute' =>  'class' , 'name' =>  'Unknown' ,  'name_short' =>  'Unknown' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 1, 'language' =>  'fr' , 'attribute' =>  'class' , 'name' =>  'Archer' ,  'name_short' =>  'Archer' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 2, 'language' =>  'fr' , 'attribute' =>  'class' , 'name' =>  'Bard' ,  'name_short' =>  'Bard' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 3, 'language' =>  'fr' , 'attribute' =>  'class' , 'name' =>  'Gladiator' ,  'name_short' =>  'Gladiator' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 4, 'language' =>  'fr' , 'attribute' =>  'class' , 'name' =>  'Paladin' ,  'name_short' =>  'Paladin' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 5, 'language' =>  'fr' , 'attribute' =>  'class' , 'name' =>  'Lancer' ,  'name_short' =>  'Dragoon' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 6, 'language' =>  'fr' , 'attribute' =>  'class' , 'name' =>  'Marauder' ,  'name_short' =>  'Marauder' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 7, 'language' =>  'fr' , 'attribute' =>  'class' , 'name' =>  'Warrior' ,  'name_short' =>  'Warrior' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 8, 'language' =>  'fr' , 'attribute' =>  'class' , 'name' =>  'Pugilist' ,  'name_short' =>  'Pugilist' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 9, 'language' =>  'fr' , 'attribute' =>  'class' , 'name' =>  'Monk' ,  'name_short' =>  'Monk' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 10, 'language' =>  'fr' , 'attribute' =>  'class' , 'name' =>  'Conjurer' ,  'name_short' =>  'Conjurer' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 11, 'language' =>  'fr' , 'attribute' =>  'class' , 'name' =>  'White Mage' ,  'name_short' =>  'White Mage' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 12, 'language' =>  'fr' , 'attribute' =>  'class' , 'name' =>  'Thaumaturge' ,  'name_short' =>  'Thaumaturge' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 13, 'language' =>  'fr' , 'attribute' =>  'class' , 'name' =>  'Black Mage' ,  'name_short' =>  'Black Mage' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 14, 'language' =>  'fr' , 'attribute' =>  'class' , 'name' =>  'Arcanist' ,  'name_short' =>  'Arcanist' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 15, 'language' =>  'fr' , 'attribute' =>  'class' , 'name' =>  'Summoner' ,  'name_short' =>  'Summoner' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 16, 'language' =>  'fr' , 'attribute' =>  'class' , 'name' =>  'Scholar' ,  'name_short' =>  'Scholar' );
		$db->sql_multi_insert (  BB_LANGUAGE  , $sql_ary );
		unset ( $sql_ary );

	}

	/**
	 * Installs races
	*/
    protected function InstallRaces()
	{
		global  $db;
		$db->sql_query('DELETE FROM ' .  RACE_TABLE . "  where game_id = 'ffxiv'");
		$sql_ary = array();
		$sql_ary[] = array('game_id' => 'ffxiv','race_id' => 1, 'race_faction_id' => 3, 'image_female' => '',  'image_male' => '' ); //Unknown
		$sql_ary[] = array('game_id' => 'ffxiv','race_id' => 2, 'race_faction_id' => 1, 'image_female' => 'ffxiv_roegadyn_female',  'image_male' => 'ffxiv_roegadyn_male' ); //Roegadyn
		$sql_ary[] = array('game_id' => 'ffxiv','race_id' => 3, 'race_faction_id' => 3, 'image_female' => 'ffxiv_hyur_female',  'image_male' => 'ffxiv_hyur_female' ); //Hyur
		$sql_ary[] = array('game_id' => 'ffxiv','race_id' => 4, 'race_faction_id' => 2, 'image_female' => 'ffxiv_elezen_female',  'image_male' => 'ffxiv_elezen_male' ); ///Elezen
		$sql_ary[] = array('game_id' => 'ffxiv','race_id' => 5, 'race_faction_id' => 2, 'image_female' => 'ffxiv_lalafell_female',  'image_male' => 'ffxiv_lalafell_male' ); //Lalafell
		$sql_ary[] = array('game_id' => 'ffxiv','race_id' => 6, 'race_faction_id' => 3, 'image_female' => 'ffxiv_miqote_female',  'image_male' => 'ffxiv_miqote_male' ); //Miqo'te
		$db->sql_multi_insert(  RACE_TABLE , $sql_ary);


		// Language table
		$db->sql_query('DELETE FROM ' . BB_LANGUAGE . "  where game_id = 'ffxiv' and attribute='race' ");
		$sql_ary = array();

		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 1, 'language' =>  'en' , 'attribute' =>  'race' , 'name' =>  'Unknown' ,  'name_short' =>  'Unknown' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 2, 'language' =>  'en' , 'attribute' =>  'race' , 'name' =>  'Roegadyn' ,  'name_short' =>  'Roegadyn' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 3, 'language' =>  'en' , 'attribute' =>  'race' , 'name' =>  'Hyur' ,  'name_short' =>  'Hyur' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 4, 'language' =>  'en' , 'attribute' =>  'race' , 'name' =>  'Elezen' ,  'name_short' =>  'Elezen' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 5, 'language' =>  'en' , 'attribute' =>  'race' , 'name' =>  'Lalafell' ,  'name_short' =>  'Lalafell' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 6, 'language' =>  'en' , 'attribute' =>  'race' , 'name' =>  'Miqo’te' ,  'name_short' =>  'Miqo’te' );

		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 1, 'language' =>  'fr' , 'attribute' =>  'race' , 'name' =>  'Unknown' ,  'name_short' =>  'Unknown' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 2, 'language' =>  'fr' , 'attribute' =>  'race' , 'name' =>  'Roegadyn' ,  'name_short' =>  'Roegadyn' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 3, 'language' =>  'fr' , 'attribute' =>  'race' , 'name' =>  'Hyur' ,  'name_short' =>  'Hyur' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 4, 'language' =>  'fr' , 'attribute' =>  'race' , 'name' =>  'Elezen' ,  'name_short' =>  'Elezen' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 5, 'language' =>  'fr' , 'attribute' =>  'race' , 'name' =>  'Lalafell' ,  'name_short' =>  'Lalafell' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 6, 'language' =>  'fr' , 'attribute' =>  'race' , 'name' =>  'Miqo’te' ,  'name_short' =>  'Miqo’te' );

		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 1, 'language' =>  'de' , 'attribute' =>  'race' , 'name' =>  'Unknown' ,  'name_short' =>  'Unknown' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 2, 'language' =>  'de' , 'attribute' =>  'race' , 'name' =>  'Roegadyn' ,  'name_short' =>  'Roegadyn' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 3, 'language' =>  'de' , 'attribute' =>  'race' , 'name' =>  'Hyur' ,  'name_short' =>  'Hyur' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 4, 'language' =>  'de' , 'attribute' =>  'race' , 'name' =>  'Elezen' ,  'name_short' =>  'Elezen' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 5, 'language' =>  'de' , 'attribute' =>  'race' , 'name' =>  'Lalafell' ,  'name_short' =>  'Lalafell' );
		$sql_ary[] = array('game_id' => 'ffxiv', 'attribute_id' => 6, 'language' =>  'de' , 'attribute' =>  'race' , 'name' =>  'Miqo’te' ,  'name_short' =>  'Miqo’te' );
		$db->sql_multi_insert (  BB_LANGUAGE  , $sql_ary );
		unset ( $sql_ary );

	}



	/**
	 * Install event groups - Events answer the 'what' question
	 */
    protected function  InstallEventGroup()
	{
		global $db;

		$sql = 'SELECT count(*) as num_dkp FROM ' .  DKPSYS_TABLE ."  WHERE dkpsys_name = 'ffxiv: A Realm Reborn' ";
		$result = $db->sql_query($sql);
		$dkpid = (int) $db->sql_fetchfield('num_dkp');
		$db->sql_freeresult($result);
		if($dkpid == 0)
		{
			// add new dkp pool
			$sql_ary = array (
					'dkpsys_name' => 'ffxiv: A Realm Reborn',
					'dkpsys_status' => 'Y',
					'dkpsys_addedby' => 'admin',
					'dkpsys_default' => 'N' );
			$sql = 'INSERT INTO ' .  DKPSYS_TABLE  . $db->sql_build_array('INSERT', $sql_ary);
			$db->sql_query($sql);
			$ffxivpdkpid = $db->sql_nextid();
			$this->InstallEvents($ffxivpdkpid);
		}

	}


    /**
     * Install Events
     * an Event answers the 'what' question
     * @author Brytor
     * @param $ffxivpdkpid
     */
	private function InstallEvents($ffxivpdkpid)
	{
		global $db;

		$sql_ary = array();
		// Dungeons:
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'Sastasha', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_dungeon'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'The Tam-Tara Deepcroft', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_dungeon'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'Copperbell Mines', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_dungeon'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'Copperbell Mines (Hard)', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_dungeon'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'Halatali', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_dungeon'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'The Thousand Maws of Toto-Rak', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_dungeon'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'Haukke Manor', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_dungeon'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'Haukke Manor (Hard)', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_dungeon'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'Brayflox’s Longstop', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_dungeon'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'The Sunken Temple of Qarn', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_dungeon'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'Cutter’s Cry', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_dungeon'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'Stone Vigil', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_dungeon'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'The Dzemael Darkhold', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_dungeon'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'The Aurum Vale', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_dungeon'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'The Wanderer’s Palace', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_dungeon'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'Pharos Sirius', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_dungeon'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'Amdapor Keep', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_dungeon'  ) ;

		// Main Scenario:
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'The Praetorium', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_main_scenario'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'Castrum Meridianum', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_main_scenario'  ) ;

		// Trials:
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'The Bowl of Embers (Easy)', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_trial'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'The Bowl of Embers (Hard)', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_trial'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'The Bowl of Embers (Extreme)', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_trial'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'The Navel (Easy)', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_trial'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'The Navel (Hard)', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_trial'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'The Navel (Extreme)', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_trial'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'The Howling Eye (Easy)', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_trial'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'The Howling Eye (Hard)', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_trial'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'The Howling Eye (Extreme)', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_trial'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => '1Cape Westwind', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_trial'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'Thornmarch', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_trial'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'The Minstrel’s Ballad: Ultima’s Bane', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_trial'  ) ;

		// Raids:
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'Crystal Tower: Labyrinth of the Ancients', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_raid'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'The Binding Coil of Bahamut: Turn 1', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_raid'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'The Binding Coil of Bahamut: Turn 2', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_raid'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'The Binding Coil of Bahamut: Turn 3', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_raid'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'The Binding Coil of Bahamut: Turn 4', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_raid'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'The Binding Coil of Bahamut: Turn 5', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_raid'  ) ;

		// Guildhest:
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'Guildhest: Level 10', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_guildhest'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'Guildhest: Level 15', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_guildhest'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'Guildhest: Level 20', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_guildhest'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'Guildhest: Level 25', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_guildhest'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'Guildhest: Level 30', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_guildhest'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'Guildhest: Level 35', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_guildhest'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'Guildhest: Level 40', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_guildhest'  ) ;

		// PVP:
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'Wolfs’ Den: Level 30', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_pvp'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'Wolfs’ Den: Level 40', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_pvp'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'Wolfs’ Den: Level 50', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_pvp'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'Wolfs’ Den: Level 50 (Solo)', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_pvp'  ) ;

		// Fate:
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'Fate Level 01-10', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_fate'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'Fate Level 11-20', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_fate'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'Fate Level 21-30', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_fate'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'Fate Level 31-40', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_fate'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'Fate Level 31-49', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_fate'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'Fate Level 50: Steel Reign', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_fate'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'Fate Level 50: Behold Now Behemoth', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_fate'  ) ;
		$sql_ary [] = array('event_dkpid' => $ffxivpdkpid , 'event_name' => 'Fate Level 50: He Taketh It with His Eyes', 'event_color' => '#000000', 'event_value' => 1, 'event_imagename' => 'ffxiv_fate'  ) ;

		//check if events already exist
		$sql_ary2 = array();
		foreach($sql_ary as $evt => $event)
		{
			$sql = 'SELECT event_id FROM ' .  EVENTS_TABLE . ' where event_name ' . $db->sql_like_expression($db->any_char . $event['event_name'] . $db->any_char);
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow ($result);
			if(!$row)
			{
				//ok new one, add it
				$sql_ary2[] = $event;
			}
			$db->sql_freeresult ($result);
		}

		if (count($sql_ary2) > 0)
		{
			$db->sql_multi_insert (  EVENTS_TABLE , $sql_ary2 );
		}

    }








}



?>