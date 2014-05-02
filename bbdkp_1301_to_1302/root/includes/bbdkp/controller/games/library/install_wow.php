<?php
/**
 * Wow Installer File
 *
 * @package bbdkp
 * @link http://www.bbdkp.com
 * @author Sajaki@gmail.com
 * @copyright 2013 bbdkp
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.3.0
 *
 */
namespace bbdkp\controller\games;
use bbdkp\controller\games;

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
 * Wow installer Class
 * @author Sajaki
 * @package bbdkp
 *
 */
class install_wow extends GameInstall
{

    protected $bossbaseurl = 'http://www.wowhead.com/?npc=%s';
    protected $zonebaseurl = 'http://www.wowhead.com/?zone=%s';

    /**
	 * Installs factions
	 */
    protected function Installfactions()
	{
		global $db;

		// factions
		$db->sql_query('DELETE FROM ' . FACTION_TABLE . " WHERE game_id = 'wow'" );
		$sql_ary = array();
		$sql_ary [] = array ('game_id' => 'wow','faction_id' => 1, 'faction_name' => 'Alliance' );
		$sql_ary [] = array ('game_id' => 'wow','faction_id' => 2, 'faction_name' => 'Horde' );
		$db->sql_multi_insert ( FACTION_TABLE, $sql_ary );
		unset ( $sql_ary );


	}

	/**
	 * Installs game classes
	*/
    protected function InstallClasses()
	{
		global $db;

		// classes
		// note class 10 does not exist
		$db->sql_query('DELETE FROM ' . CLASS_TABLE . " WHERE game_id = 'wow'" );
		$sql_ary = array ();
		$sql_ary [] = array ('game_id' => 'wow', 'class_id' => 0, 'class_armor_type' => 'PLATE', 'class_min_level' => 1, 'class_max_level' => 90 , 'colorcode' =>  '#999', 'imagename' => 'wow_unknown');
		$sql_ary [] = array ('game_id' => 'wow','class_id' => 1, 'class_armor_type' => 'PLATE', 'class_min_level' => 1, 'class_max_level' => 90 , 'colorcode' =>  '#C79C6E', 'imagename' => 'wow_warrior');
		$sql_ary [] = array ('game_id' => 'wow', 'class_id' => 4, 'class_armor_type' => 'LEATHER', 'class_min_level' => 1, 'class_max_level' => 90, 'colorcode' =>  '#FFF569',  'imagename' => 'wow_rogue');
		$sql_ary [] = array ('game_id' => 'wow','class_id' => 3, 'class_armor_type' => 'MAIL', 'class_min_level' => 1, 'class_max_level' => 90 , 'colorcode' =>  '#ABD473',  'imagename' => 'wow_hunter');
		$sql_ary [] = array ('game_id' => 'wow','class_id' => 2, 'class_armor_type' => 'PLATE', 'class_min_level' => 1, 'class_max_level' => 90 ,  'colorcode' =>  '#F58CBA',  'imagename' => 'wow_paladin');
		$sql_ary [] = array ('game_id' => 'wow','class_id' => 7, 'class_armor_type' => 'MAIL', 'class_min_level' => 1, 'class_max_level' => 90 , 'colorcode' =>  '#0070DE',  'imagename' => 'wow_shaman');
		$sql_ary [] = array ('game_id' => 'wow','class_id' => 11, 'class_armor_type' => 'LEATHER', 'class_min_level' => 1, 'class_max_level' => 90 , 'colorcode' =>  '#FF7D0A',  'imagename' => 'wow_druid');
		$sql_ary [] = array ('game_id' => 'wow','class_id' => 9, 'class_armor_type' => 'CLOTH', 'class_min_level' => 1, 'class_max_level' => 90 , 'colorcode' =>  '#9482C9',  'imagename' => 'wow_warlock');
		$sql_ary [] = array ('game_id' => 'wow','class_id' => 8, 'class_armor_type' => 'CLOTH', 'class_min_level' => 1, 'class_max_level' => 90 , 'colorcode' =>  '#69CCF0',  'imagename' => 'wow_mage');
		$sql_ary [] = array ('game_id' => 'wow','class_id' => 5, 'class_armor_type' => 'CLOTH', 'class_min_level' => 1, 'class_max_level' => 90 ,  'colorcode' =>  '#FFFFFF', 'imagename' => 'wow_priest');
		$sql_ary [] = array ('game_id' => 'wow','class_id' => 6, 'class_armor_type' => 'PLATE', 'class_min_level' => 55, 'class_max_level' => 90 , 'colorcode' =>  '#C41F3B',  'imagename' => 'wow_death_knight');
		$sql_ary [] = array ('game_id' => 'wow','class_id' => 10, 'class_armor_type' => 'LEATHER', 'class_min_level' => 1, 'class_max_level' => 90 ,  'colorcode' =>  '#008467', 'imagename' => 'wow_monk');
		$db->sql_multi_insert ( CLASS_TABLE, $sql_ary );
		unset ( $sql_ary );

		// languages
		$db->sql_query('DELETE FROM ' . BB_LANGUAGE . " WHERE game_id = 'wow' AND attribute='class' ");

		$sql_ary = array ();
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 0, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Unknown' ,  'name_short' =>  'Unknown' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 1, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Warrior' ,  'name_short' =>  'Warrior' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 4, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Rogue' ,  'name_short' =>  'Rogue' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 3, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Hunter' ,  'name_short' =>  'Hunter' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 2, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Paladin' ,  'name_short' =>  'Paladin' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 7, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Shaman' ,  'name_short' =>  'Shaman' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 11, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Druid' ,  'name_short' =>  'Druid' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 9, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Warlock' ,  'name_short' =>  'Warlock' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 8, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Mage' ,  'name_short' =>  'Mage' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 5, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Priest' ,  'name_short' =>  'Priest' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 6, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Death Knight' ,  'name_short' =>  'Death Knight' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 10, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Monk' ,  'name_short' =>  'Monk' );

		//classes in fr
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 0, 'language' =>  'fr' , 'attribute' =>  'class' , 'name' =>  'Unknown' ,  'name_short' =>  'Unknown' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 1, 'language' =>  'fr' , 'attribute' =>  'class' , 'name' =>  'Warrior' ,  'name_short' =>  'Warrior' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 4, 'language' =>  'fr' , 'attribute' =>  'class' , 'name' =>  'Voleur' ,  'name_short' =>  'Voleur' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 3, 'language' =>  'fr' , 'attribute' =>  'class' , 'name' =>  'Chasseur' ,  'name_short' =>  'Chasseur' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 2, 'language' =>  'fr' , 'attribute' =>  'class' , 'name' =>  'Paladin' ,  'name_short' =>  'Paladin' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 7, 'language' =>  'fr' , 'attribute' =>  'class' , 'name' =>  'Chaman' ,  'name_short' =>  'Chaman' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 11, 'language' =>  'fr' , 'attribute' =>  'class' , 'name' =>  'Druide' ,  'name_short' =>  'Druide' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 9, 'language' =>  'fr' , 'attribute' =>  'class' , 'name' =>  'Démoniste' ,  'name_short' =>  'Démoniste' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 8, 'language' =>  'fr' , 'attribute' =>  'class' , 'name' =>  'Mage' ,  'name_short' =>  'Mage' );
		$sql_ary[] = array('game_id' => 'wow','attribute_id' => 5, 'language' =>  'fr' , 'attribute' =>  'class' , 'name' =>  'Prêtre' ,  'name_short' =>  'Prêtre' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 6, 'language' =>  'fr' , 'attribute' =>  'class' , 'name' =>  'Chevalier de la Mort' ,  'name_short' =>  'Chevalier de la Mort' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 10, 'language' =>  'fr' , 'attribute' =>  'class' , 'name' =>  'Moine' ,  'name_short' =>  'Moine' );

		//classes in de
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 0, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'Unbekannt' ,  'name_short' =>  'Unbekannt' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 1, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'Krieger' ,  'name_short' =>  'Krieger' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 4, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'Schurke' ,  'name_short' =>  'Schurke' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 3, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'Jäger' ,  'name_short' =>  'Jäger' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 2, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'Paladin' ,  'name_short' =>  'Paladin' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 7, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'Schamane' ,  'name_short' =>  'Schamane' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 11, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'Druide' ,  'name_short' =>  'Druide' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 9, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'Hexenmeister' ,  'name_short' =>  'Hexenmeister' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 8, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'Magier' ,  'name_short' =>  'Magier' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 5, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'Priester' ,  'name_short' =>  'Priester' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 6, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'Todesritter' ,  'name_short' =>  'Todesritter' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 10, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'Mönch' ,  'name_short' =>  'Mönch' );

		$db->sql_multi_insert ( BB_LANGUAGE , $sql_ary );
		unset ( $sql_ary );

	}

	/**
	 * Installs races
	*/
    protected function InstallRaces()
	{
		global $db;

		// races
		$db->sql_query('DELETE FROM ' .  RACE_TABLE . " WHERE game_id = 'wow'");
		$sql_ary = array ();
		$sql_ary [] = array ('game_id' => 'wow','race_id' => 0, 'race_faction_id' => 0, 'image_female' => ' ',  'image_male' => ' '  ); //Unknown
		$sql_ary [] = array ('game_id' => 'wow','race_id' => 1, 'race_faction_id' => 1, 'image_female' => 'wow_human_female',  'image_male' => 'wow_human_male'  ); //Human
		$sql_ary [] = array ('game_id' => 'wow','race_id' => 2, 'race_faction_id' => 2 , 'image_female' => 'wow_orc_female',  'image_male' => 'wow_orc_male' ); //Orc
		$sql_ary [] = array ('game_id' => 'wow','race_id' => 3, 'race_faction_id' => 1 , 'image_female' => 'wow_dwarf_female',  'image_male' => 'wow_dwarf_male' ); //Dwarf
		$sql_ary [] = array ('game_id' => 'wow','race_id' => 4, 'race_faction_id' => 1 , 'image_female' => 'wow_nightelf_female',  'image_male' => 'wow_nightelf_male' ) ; //Night Elf
		$sql_ary [] = array ('game_id' => 'wow','race_id' => 5, 'race_faction_id' => 2 , 'image_female' => 'wow_scourge_female',  'image_male' => 'wow_scourge_male' ); //Undead
		$sql_ary [] = array ('game_id' => 'wow','race_id' => 6, 'race_faction_id' => 2 , 'image_female' => 'wow_tauren_female',  'image_male' => 'wow_tauren_male' ); //Tauren
		$sql_ary [] = array ('game_id' => 'wow','race_id' => 7, 'race_faction_id' => 1 , 'image_female' => 'wow_gnome_female',  'image_male' => 'wow_gnome_male' ); //Gnome
		$sql_ary [] = array ('game_id' => 'wow','race_id' => 8, 'race_faction_id' => 2 , 'image_female' => 'wow_troll_female',  'image_male' => 'wow_troll_male' ); //Troll
		$sql_ary [] = array ('game_id' => 'wow','race_id' => 9, 'race_faction_id' => 2 , 'image_female' => 'wow_goblin_female',  'image_male' => 'wow_goblin_male' ); //Goblin
		$sql_ary [] = array ('game_id' => 'wow','race_id' => 10, 'race_faction_id' => 2 , 'image_female' => 'wow_bloodelf_female',  'image_male' => 'wow_bloodelf_male' ); //Blood Elf
		$sql_ary [] = array ('game_id' => 'wow','race_id' => 11, 'race_faction_id' => 1 , 'image_female' => 'wow_draenei_female',  'image_male' => 'wow_draenei_male' ); //Draenei
		$sql_ary [] = array ('game_id' => 'wow','race_id' => 22, 'race_faction_id' => 1 , 'image_female' => 'wow_worgen_female',  'image_male' => 'wow_worgen_male' ); //Worgen
		$sql_ary [] = array ('game_id' => 'wow','race_id' => 25, 'race_faction_id' => 1 , 'image_female' => 'wow_pandaren_female',  'image_male' => 'wow_pandaren_male' ); //Pandaren alliance
		$sql_ary [] = array ('game_id' => 'wow','race_id' => 26, 'race_faction_id' => 2 , 'image_female' => 'wow_pandaren_female',  'image_male' => 'wow_pandaren_male' ); //Pandaren Horde
		$db->sql_multi_insert (  RACE_TABLE , $sql_ary );
		unset ( $sql_ary );

		$db->sql_query('DELETE FROM ' . BB_LANGUAGE . " WHERE game_id = 'wow' AND attribute = 'race' ");

		//races in en
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 0, 'language' => 'en' , 'attribute' =>  'race' , 'name' =>  'Unknown' ,  'name_short' =>  'Unknown' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 1, 'language' => 'en' , 'attribute' =>  'race' , 'name' =>  'Human' ,  'name_short' =>  'Human' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 2, 'language' => 'en' , 'attribute' =>  'race' , 'name' =>  'Orc' ,  'name_short' =>  'Orc' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 3, 'language' => 'en' , 'attribute' =>  'race' , 'name' =>  'Dwarf' ,  'name_short' =>  'Dwarf' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 4, 'language' => 'en' , 'attribute' =>  'race' , 'name' =>  'Night Elf' ,  'name_short' =>  'Night Elf' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 5, 'language' => 'en' , 'attribute' =>  'race' , 'name' =>  'Undead' ,  'name_short' =>  'Undead' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 6, 'language' => 'en' , 'attribute' =>  'race' , 'name' =>  'Tauren' ,  'name_short' =>  'Tauren' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 7, 'language' => 'en' , 'attribute' =>  'race' , 'name' =>  'Gnome' ,  'name_short' =>  'Gnome' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 8, 'language' => 'en' , 'attribute' =>  'race' , 'name' =>  'Troll' ,  'name_short' =>  'Troll' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 10, 'language' => 'en' , 'attribute' =>  'race' , 'name' =>  'Blood Elf' ,  'name_short' =>  'Blood Elf' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 11, 'language' => 'en' , 'attribute' =>  'race' , 'name' =>  'Draenei' ,  'name_short' =>  'Draenei' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 9, 'language' => 'en' , 'attribute' =>  'race' , 'name' =>  'Goblin' ,  'name_short' =>  'Goblin' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 22, 'language' => 'en' , 'attribute' =>  'race' , 'name' =>  'Worgen' ,  'name_short' =>  'Worgen' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 25, 'language' => 'en' , 'attribute' =>  'race' , 'name' =>  'Pandaren Alliance' ,  'name_short' =>  'Pandaren Alliance' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 26, 'language' => 'en' , 'attribute' =>  'race' , 'name' =>  'Pandaren Horde' ,  'name_short' =>  'Pandaren Horde' );

		// races in fr
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 0, 'language' => 'fr' , 'attribute' =>  'race' , 'name' =>  'Unknown' ,  'name_short' =>  'Unknown' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 1, 'language' => 'fr' , 'attribute' =>  'race' , 'name' =>  'Humain' ,  'name_short' =>  'Humain' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 2, 'language' => 'fr' , 'attribute' =>  'race' , 'name' =>  'Orc' ,  'name_short' =>  'Orc' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 3, 'language' => 'fr' , 'attribute' =>  'race' , 'name' =>  'Nain' ,  'name_short' =>  'Nain' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 4, 'language' => 'fr' , 'attribute' =>  'race' , 'name' =>  'Elfe de la Nuit' ,  'name_short' =>  'Elfe de la Nuit' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 5, 'language' => 'fr' , 'attribute' =>  'race' , 'name' =>  'Mort-Vivant' ,  'name_short' =>  'Mort-Vivant' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 6, 'language' => 'fr' , 'attribute' =>  'race' , 'name' =>  'Tauren' ,  'name_short' =>  'Tauren' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 7, 'language' => 'fr' , 'attribute' =>  'race' , 'name' =>  'Gnome' ,  'name_short' =>  'Gnome' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 8, 'language' => 'fr' , 'attribute' =>  'race' , 'name' =>  'Troll' ,  'name_short' =>  'Troll' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 10, 'language' => 'fr' , 'attribute' =>  'race' , 'name' =>  'Elfe de Sang' ,  'name_short' =>  'Elfe de Sang' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 11, 'language' => 'fr' , 'attribute' =>  'race' , 'name' =>  'Draeneï' ,  'name_short' =>  'Draeneï' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 9, 'language' => 'fr' , 'attribute' =>  'race' , 'name' =>  'Goblin' ,  'name_short' =>  'Goblin' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 22, 'language' => 'fr' , 'attribute' =>  'race' , 'name' =>  'Worgen' ,  'name_short' =>  'Worgen' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 25, 'language' => 'fr' , 'attribute' =>  'race' , 'name' =>  'Pandaren Alliance' ,  'name_short' =>  'Pandaren Alliance' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 26, 'language' => 'fr' , 'attribute' =>  'race' , 'name' =>  'Pandaren Horde' ,  'name_short' =>  'Pandaren Horde' );

		//races in de
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 0, 'language' => 'de' , 'attribute' =>  'race' , 'name' =>  'Unknown' ,  'name_short' =>  'Unknown' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 1, 'language' => 'de' , 'attribute' =>  'race' , 'name' =>  'Mensch' ,  'name_short' =>  'Mensch' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 2, 'language' => 'de' , 'attribute' =>  'race' , 'name' =>  'Orc' ,  'name_short' =>  'Orc' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 3, 'language' => 'de' , 'attribute' =>  'race' , 'name' =>  'Zwerg' ,  'name_short' =>  'Zwerg' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 4, 'language' => 'de' , 'attribute' =>  'race' , 'name' =>  'Nachtelf' ,  'name_short' =>  'Nachtelf' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 5, 'language' => 'de' , 'attribute' =>  'race' , 'name' =>  'Untoter' ,  'name_short' =>  'Untoter' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 6, 'language' => 'de' , 'attribute' =>  'race' , 'name' =>  'Tauren' ,  'name_short' =>  'Tauren' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 7, 'language' => 'de' , 'attribute' =>  'race' , 'name' =>  'Gnome' ,  'name_short' =>  'Gnome' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 8, 'language' => 'de' , 'attribute' =>  'race' , 'name' =>  'Troll' ,  'name_short' =>  'Troll' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 10, 'language' => 'de' , 'attribute' =>  'race' , 'name' =>  'Blutelf' ,  'name_short' =>  'Blutelf' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 11, 'language' => 'de' , 'attribute' =>  'race' , 'name' =>  'Draenei' ,  'name_short' =>  'Draenei' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 9, 'language' =>  'de' , 'attribute' =>  'race' , 'name' =>  'Goblin' ,  'name_short' =>  'Goblin' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 22, 'language' => 'de' , 'attribute' =>  'race' , 'name' =>  'Worgen' ,  'name_short' =>  'Worgen' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 25, 'language' => 'de' , 'attribute' =>  'race' , 'name' =>  'Pandaren Alliance' ,  'name_short' =>  'Pandaren Alliance' );
		$sql_ary[] = array('game_id' => 'wow', 'attribute_id' => 26, 'language' => 'de' , 'attribute' =>  'race' , 'name' =>  'Pandaren Horde' ,  'name_short' =>  'Pandaren Horde' );

		$db->sql_multi_insert ( BB_LANGUAGE , $sql_ary );
		unset ( $sql_ary );

	}

	/**
	 * Install event groups - Events answer the 'what' question
	 */
    protected function  InstallEventGroup()
	{
		global $db;

		$sql = 'SELECT count(*) as num_dkp FROM ' .  DKPSYS_TABLE ."  WHERE dkpsys_name = 'Wotlk' ";
		$result = $db->sql_query($sql);
		$dkpid = (int) $db->sql_fetchfield('num_dkp');
		$db->sql_freeresult($result);
		if($dkpid == 0)
		{
			$sql_ary = array ();
			$sql_ary [] = array ('dkpsys_name' => 'Wotlk', 'dkpsys_status' => 'Y', 'dkpsys_addedby' => 'admin', 'dkpsys_default' => 'N' );
			$db->sql_multi_insert (  DKPSYS_TABLE  , $sql_ary );
			unset ( $sql_ary );
		}

		$sql = 'SELECT count(*) as num_dkp FROM ' .  DKPSYS_TABLE ."  WHERE dkpsys_name = 'Cataclysm' ";
		$result = $db->sql_query($sql);
		$dkpid = (int) $db->sql_fetchfield('num_dkp');
		$db->sql_freeresult($result);
		if($dkpid == 0)
		{
			$sql_ary = array ();
			$sql_ary [] = array ('dkpsys_name' => 'Cataclysm', 'dkpsys_status' => 'Y', 'dkpsys_addedby' => 'admin', 'dkpsys_default' => 'N' );
			$db->sql_multi_insert (  DKPSYS_TABLE  , $sql_ary );
			$result = $db->sql_query('select dkpsys_id FROM ' .  DKPSYS_TABLE ."  WHERE dkpsys_name='Cataclysm'" );
			$dkpid = (int) $db->sql_fetchfield('dkpsys_id');
			$this->InstallEventsCataclysm($dkpid);
		}

		$sql = 'SELECT count(*) as num_dkp FROM ' .  DKPSYS_TABLE ."  WHERE dkpsys_name = 'Pandaria' ";
		$result = $db->sql_query($sql);
		$dkpid = (int) $db->sql_fetchfield('num_dkp');
		$db->sql_freeresult($result);
		if($dkpid == 0)
		{
			$sql_ary = array ();
			$sql_ary [] = array ('dkpsys_name' => 'Pandaria', 'dkpsys_status' => 'Y', 'dkpsys_addedby' => 'admin', 'dkpsys_default' => 'N' );
			$db->sql_multi_insert (  DKPSYS_TABLE  , $sql_ary );
			$result = $db->sql_query('select dkpsys_id FROM ' .  DKPSYS_TABLE ."  WHERE dkpsys_name='Pandaria'" );
			$dkpid = (int) $db->sql_fetchfield('dkpsys_id');
			$this->InstallEventsPandaria($dkpid);
		}

	}


	/**
	 * Install Events
	 * an Event answers the 'what' question
	 * @param integer $dkpid
	 */
	private function InstallEventsCataclysm($dkpid)
	{
		global $db;

	    $sql_ary = array();
		$sql_ary [] = array('event_dkpid' => $dkpid , 'event_name' => 'T11 Blackwing Descent 10', 'event_color' => '#77FF11', 'event_value' => 5, 'event_imagename' => 'wow_bd10'  ) ;
	    $sql_ary [] = array('event_dkpid' => $dkpid , 'event_name' => 'T11 Bastion of Twilight 10', 'event_color' => '#99FF11', 'event_value' => 5, 'event_imagename' => 'wow_bot10'  ) ;
	    $sql_ary [] = array('event_dkpid' => $dkpid , 'event_name' => 'T11 Throne of the Four Winds 10', 'event_color' => '#BBFF11', 'event_value' => 5, 'event_imagename' => 'wow_tfw10');
		$sql_ary [] = array('event_dkpid' => $dkpid , 'event_name' => 'T12 Firelands 10', 'event_color' => '#CCFF11', 'event_value' => 5 , 'event_imagename' => 'wow_fl10');
		$sql_ary [] = array('event_dkpid' => $dkpid , 'event_name' => 'T12 Firelands 10hm', 'event_color' => '#FF2266', 'event_value' => 5 , 'event_imagename' => 'wow_fl10hm');
		$sql_ary [] = array('event_dkpid' => $dkpid , 'event_name' => 'T12 Firelands 25', 'event_color' => '#FFBB22', 'event_value' => 5 , 'event_imagename' => 'wow_fl25');
		$sql_ary [] = array('event_dkpid' => $dkpid , 'event_name' => 'T12 Firelands 25hm', 'event_color' => '#FF2266', 'event_value' => 5 , 'event_imagename' => 'wow_fl25hm');
		$sql_ary [] = array('event_dkpid' => $dkpid , 'event_name' => 'T13 Dragon Soul 10', 'event_color' => '#CCFF22', 'event_value' => 5, 'event_imagename' => 'wow_ds10' );
		$sql_ary [] = array('event_dkpid' => $dkpid , 'event_name' => 'T13 Dragon Soul 10hm', 'event_color' => '#FF2266', 'event_value' => 5, 'event_imagename' => 'wow_ds10hm' );
		$sql_ary [] = array('event_dkpid' => $dkpid , 'event_name' => 'T13 Dragon Soul 25', 'event_color' => '#FFBB33', 'event_value' => 5, 'event_imagename' => 'wow_ds25' );
		$sql_ary [] = array('event_dkpid' => $dkpid , 'event_name' => 'T13 Dragon Soul 25hm', 'event_color' => '#FF2266', 'event_value' => 5, 'event_imagename' => 'wow_ds25hm' );

		$db->sql_multi_insert (  EVENTS_TABLE , $sql_ary );
		unset ( $sql_ary );

	}

	/**
	 * Install Events
	 * an Event answers the 'what' question
	 * @param integer $dkpid
	 */
	private function InstallEventsPandaria($dkpid)
	{
		global $db;

		$sql_ary = array();

		$sql_ary [] = array('event_dkpid' => $dkpid , 'event_name' => 'T14 Mogu’Shan Vaults (10)', 'event_color' => '#FF9999', 'event_value' => 5, 'event_imagename' => 'mogushanvaults10'  ) ;
		$sql_ary [] = array('event_dkpid' => $dkpid , 'event_name' => 'T14 Mogu’Shan Vaults (10HM)', 'event_color' => '#FF9999', 'event_value' => 5, 'event_imagename' => 'mogushanvaultsh10'  ) ;
		$sql_ary [] = array('event_dkpid' => $dkpid , 'event_name' => 'T14 Mogu’Shan Vaults (25)', 'event_color' => '#FF9999', 'event_value' => 5, 'event_imagename' => 'mogushanvaults25'  ) ;
		$sql_ary [] = array('event_dkpid' => $dkpid , 'event_name' => 'T14 Mogu’Shan Vaults (25HM)', 'event_color' => '#FF9999', 'event_value' => 5, 'event_imagename' => 'mogushanvaultsh25'  ) ;

		$sql_ary [] = array('event_dkpid' => $dkpid , 'event_name' => 'T14 Terrace of Endless Spring  (10)', 'event_color' => '#FF88AA', 'event_value' => 5 , 'event_imagename' => 'wow_tes10') ;
		$sql_ary [] = array('event_dkpid' => $dkpid , 'event_name' => 'T14 Terrace of Endless Spring  (10HM)', 'event_color' => '#FF88AA', 'event_value' => 5 , 'event_imagename' => 'wow_tes10') ;
		$sql_ary [] = array('event_dkpid' => $dkpid , 'event_name' => 'T14 Terrace of Endless Spring  (25)', 'event_color' => '#FF88AA', 'event_value' => 5 , 'event_imagename' => 'wow_tes25') ;
		$sql_ary [] = array('event_dkpid' => $dkpid , 'event_name' => 'T14 Terrace of Endless Spring  (25HM)', 'event_color' => '#FF88AA', 'event_value' => 5 , 'event_imagename' => 'wow_tes25') ;

		$sql_ary [] = array('event_dkpid' => $dkpid , 'event_name' => 'T14 Heart of Fear (10)', 'event_color' => '#DD00AA', 'event_value' => 10, 'event_imagename' => 'wow_hof10' );
		$sql_ary [] = array('event_dkpid' => $dkpid , 'event_name' => 'T14 Heart of Fear (10HM)', 'event_color' => '#DD00AA', 'event_value' => 10, 'event_imagename' => 'wow_hof10' );
		$sql_ary [] = array('event_dkpid' => $dkpid , 'event_name' => 'T14 Heart of Fear (25)', 'event_color' => '#DD00AA', 'event_value' => 10, 'event_imagename' => 'wow_hof25' );
		$sql_ary [] = array('event_dkpid' => $dkpid , 'event_name' => 'T14 Heart of Fear (25HM)', 'event_color' => '#DD00AA', 'event_value' => 10, 'event_imagename' => 'wow_hof25' );

		$sql_ary [] = array('event_dkpid' => $dkpid , 'event_name' => 'T15 Throne of Thunder (10)', 'event_color' => '#DD00AA', 'event_value' => 10, 'event_imagename' => 'wow_tot10' );
		$sql_ary [] = array('event_dkpid' => $dkpid , 'event_name' => 'T15 Throne of Thunder (10HM)', 'event_color' => '#DD00AA', 'event_value' => 10, 'event_imagename' => 'wow_tot10' );
		$sql_ary [] = array('event_dkpid' => $dkpid , 'event_name' => 'T15 Throne of Thunder (25)', 'event_color' => '#DD00AA', 'event_value' => 10, 'event_imagename' => 'wow_tot25' );
		$sql_ary [] = array('event_dkpid' => $dkpid , 'event_name' => 'T15 Throne of Thunder (25HM)', 'event_color' => '#DD00AA', 'event_value' => 10, 'event_imagename' => 'wow_tot25' );

		$sql_ary [] = array('event_dkpid' => $dkpid , 'event_name' => 'T16 Siege of Orgrimmar (10)', 'event_color' =>  '#3F5347'  , 'event_value' => 20, 'event_imagename' => 'wow_sog10' );
		$sql_ary [] = array('event_dkpid' => $dkpid , 'event_name' => 'T16 Siege of Orgrimmar (10HM)', 'event_color' => '#3F5347', 'event_value' => 20, 'event_imagename' => 'wow_sog10' );
		$sql_ary [] = array('event_dkpid' => $dkpid , 'event_name' => 'T16 Siege of Orgrimmar (25)', 'event_color' => '#3F5347', 'event_value' => 20, 'event_imagename' => 'wow_sog25' );
		$sql_ary [] = array('event_dkpid' => $dkpid , 'event_name' => 'T16 Siege of Orgrimmar (25HM)', 'event_color' => '#3F5347', 'event_value' => 20, 'event_imagename' => 'wow_sog25' );

		$db->sql_multi_insert (  EVENTS_TABLE , $sql_ary );
		unset ( $sql_ary );

	}


	/**
	* inserts demo data 3 dummy members, 3 dkp accounts, 3 dummy items, and 1 dummy raid
     * unused!
	*/
	private function populate_wow()
	{

		global $user, $db;

		$sql_ary = array();
		$sql_ary[] = array(
				'game_id'  		   => 'wow',
				'member_name'      => 'wow1',
				'member_status'    => 1 ,
				'member_level'     => 90,
				'member_race_id'   => 1,
				'member_class_id'  => 1,
				'member_rank_id'   => 0,
				'member_comment'   => 'Inserted by installer',
				'member_joindate'  => time(),
				'member_outdate'   => '1893456000',
				'member_guild_id'  => 1,
				'member_gender_id' => 0,
				'member_achiev'    => 0,
				'phpbb_user_id' 	=> $user->data['user_id'],
				'member_title' 	=> '',
		) ;

		$sql_ary[] = array(
				'game_id'  		   => 'wow',
				'member_name'      => 'wow2',
				'member_status'    => 1 ,
				'member_level'     => 90,
				'member_race_id'   => 2,
				'member_class_id'  => 2,
				'member_rank_id'   => 0,
				'member_comment'   => 'Inserted by installer',
				'member_joindate'  => time(),
				'member_outdate'   => '1893456000',
				'member_guild_id'  => 1,
				'member_gender_id' => 0,
				'member_achiev'    => 0,
				'phpbb_user_id' 	=> $user->data['user_id'],
				'member_title' 	=> '',
		) ;

		$sql_ary[] = array(
				'game_id'  		   => 'wow',
				'member_name'      => 'wow3',
				'member_status'    => 1 ,
				'member_level'     => 90,
				'member_race_id'   => 3,
				'member_class_id'  => 3,
				'member_rank_id'   => 0,
				'member_comment'   => 'Inserted by installer',
				'member_joindate'  => time(),
				'member_outdate'   => '1893456000',
				'member_guild_id'  => 1,
				'member_gender_id' => 0,
				'member_achiev'    => 0,
				'phpbb_user_id' 	=> $user->data['user_id'],
				'member_title' 	=> '',
		) ;
		$db->sql_multi_insert ( MEMBER_LIST_TABLE , $sql_ary );


	}



}



?>