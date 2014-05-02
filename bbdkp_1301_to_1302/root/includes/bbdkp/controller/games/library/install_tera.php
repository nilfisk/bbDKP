<?php
/**
 * bbdkp TERA install data
 * 
 *   @package bbdkp
 * @link http://www.bbdkp.com
 * @author Sajaki@gmail.com
 * @copyright 2013 bbdkp
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
 * Tera Installer class
 *   @package bbdkp
 *
 */
class install_tera extends \bbdkp\controller\games\GameInstall
{

    protected $bossbaseurl = 'http://teracodex.com/npc/%s';
    protected $zonebaseurl = 'http://teracodex.com/area/%s';
	
	/**
	 * Installs factions
	 */
	protected function Installfactions()
	{
		global $db;
		// factions
		$db->sql_query('DELETE FROM ' . FACTION_TABLE . " where game_id = 'tera'" );
		$sql_ary = array();
		$sql_ary [] = array ('game_id' => 'tera','faction_id' => 1, 'faction_name' => 'Tera' );
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
		$db->sql_query('DELETE FROM ' . CLASS_TABLE . " where game_id = 'tera'" );
		$sql_ary = array ();
		$sql_ary [] = array ('game_id' => 'tera', 'class_id' => 0, 'class_faction_id' => 1, 'class_armor_type' => 'HEAVY', 'class_min_level' => 1, 'class_max_level' => 60 , 'colorcode' =>  '#999', 'imagename' => 'tera_unknown');
		$sql_ary [] = array ('game_id' => 'tera','class_id' => 1, 'class_faction_id' => 1, 'class_armor_type' => 'LEATHER', 'class_min_level' => 1, 'class_max_level' => 60 , 'colorcode' =>  '#00CC66', 'imagename' => 'tera_archer');
		$sql_ary [] = array ('game_id' => 'tera', 'class_id' => 2, 'class_faction_id' => 1, 'class_armor_type' => 'HEAVY', 'class_min_level' => 1, 'class_max_level' => 60, 'colorcode' =>  '#CC0000',  'imagename' => 'tera_berserker');
		$sql_ary [] = array ('game_id' => 'tera','class_id' => 3, 'class_faction_id' => 1, 'class_armor_type' => 'HEAVY', 'class_min_level' => 1, 'class_max_level' => 60 , 'colorcode' =>  '#C0A172',  'imagename' => 'tera_lancer');
		$sql_ary [] = array ('game_id' => 'tera','class_id' => 4, 'class_faction_id' => 1, 'class_armor_type' => 'ROBE', 'class_min_level' => 1, 'class_max_level' => 60 ,  'colorcode' =>  '#7700AA',  'imagename' => 'tera_mystic');
		$sql_ary [] = array ('game_id' => 'tera','class_id' => 5, 'class_faction_id' => 1, 'class_armor_type' => 'ROBE', 'class_min_level' => 1, 'class_max_level' => 60 , 'colorcode' =>  '#CCCCFF',  'imagename' => 'tera_priest');
		$sql_ary [] = array ('game_id' => 'tera', 'class_id' => 6, 'class_faction_id' => 1, 'class_armor_type' => 'LEATHER', 'class_min_level' => 1, 'class_max_level' => 60, 'colorcode' =>  '#FFFF33',  'imagename' => 'tera_slayer');
		$sql_ary [] = array ('game_id' => 'tera','class_id' => 7, 'class_faction_id' => 1, 'class_armor_type' => 'ROBE', 'class_min_level' => 1, 'class_max_level' => 60 ,  'colorcode' =>  '#0077BB',  'imagename' => 'tera_sorcerer');
		$sql_ary [] = array ('game_id' => 'tera', 'class_id' => 8, 'class_faction_id' => 1, 'class_armor_type' => 'MAIL', 'class_min_level' => 1, 'class_max_level' => 60, 'colorcode' =>  '#FF9900',  'imagename' => 'tera_warrior');
		$db->sql_multi_insert ( CLASS_TABLE, $sql_ary );
		unset ( $sql_ary );
		

		// Dictionary
		$db->sql_query('DELETE FROM ' . BB_LANGUAGE . "  where game_id = 'tera' and attribute = 'class'  ");
		$sql_ary = array ();
		//
		$sql_ary[] = array('game_id' => 'tera', 'attribute_id' => 0, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Unknown' ,  'name_short' =>  'Unknown' );
		$sql_ary[] = array('game_id' => 'tera', 'attribute_id' => 1, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Archer' ,  'name_short' =>  'Archer' );
		$sql_ary[] = array('game_id' => 'tera', 'attribute_id' => 2, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Berserker' ,  'name_short' =>  'Berserker' );
		$sql_ary[] = array('game_id' => 'tera', 'attribute_id' => 3, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Lancer' ,  'name_short' =>  'Lancer' );
		$sql_ary[] = array('game_id' => 'tera', 'attribute_id' => 4, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Mystic' ,  'name_short' =>  'Mystic' );
		$sql_ary[] = array('game_id' => 'tera', 'attribute_id' => 5, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Priest' ,  'name_short' =>  'Priest' );
		$sql_ary[] = array('game_id' => 'tera', 'attribute_id' => 6, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Slayer' ,  'name_short' =>  'Slayer' );
		$sql_ary[] = array('game_id' => 'tera', 'attribute_id' => 7, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Sorcerer' ,  'name_short' =>  'Sorcerer' );
		$sql_ary[] = array('game_id' => 'tera', 'attribute_id' => 8, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Warrior' ,  'name_short' =>  'Warrior' );
		$db->sql_multi_insert ( BB_LANGUAGE , $sql_ary );
		unset ( $sql_ary );
		
	}
	
	/**
	 * Installs races
	*/
    protected function InstallRaces()
	{
		global $db;
		$db->sql_query('DELETE FROM ' .  RACE_TABLE . "  where game_id = 'tera'");
		$sql_ary = array ();
		//Unknown
		$sql_ary [] = array ('game_id' => 'tera','race_id' => 0, 'race_faction_id' => 1, 'image_female' => ' ', 'image_male' => ' '  );
		$sql_ary [] = array ('game_id' => 'tera','race_id' => 1, 'race_faction_id' => 1, 'image_female' => 'tera_aman_female',  'image_male' => 'tera_aman_male' );
		$sql_ary [] = array ('game_id' => 'tera','race_id' => 2, 'race_faction_id' => 1, 'image_female' => 'tera_baraka_female',  'image_male' => 'tera_baraka_male' );
		$sql_ary [] = array ('game_id' => 'tera','race_id' => 3, 'race_faction_id' => 1, 'image_female' => 'tera_castanic_female',  'image_male' => 'tera_castanic_male' );
		$sql_ary [] = array ('game_id' => 'tera','race_id' => 4, 'race_faction_id' => 1, 'image_female' => 'tera_elin_female',  'image_male' => 'tera_elin_female' ) ;
		$sql_ary [] = array ('game_id' => 'tera','race_id' => 5, 'race_faction_id' => 1, 'image_female' => 'tera_highelf_female',  'image_male' => 'tera_highelf_male' );
		$sql_ary [] = array ('game_id' => 'tera','race_id' => 6, 'race_faction_id' => 1, 'image_female' => 'tera_human_female',  'image_male' => 'tera_human_male'  );
		$sql_ary [] = array ('game_id' => 'tera','race_id' => 7, 'race_faction_id' => 1, 'image_female' => 'tera_popori_male',  'image_male' => 'tera_popori_male' );
		$db->sql_multi_insert ( RACE_TABLE , $sql_ary);
		unset ( $sql_ary );
		
		// Dictionary
		$db->sql_query('DELETE FROM ' . BB_LANGUAGE . "  where game_id = 'tera' and attribute = 'race' ");
		$sql_ary = array ();
		
		$sql_ary[] = array('game_id' => 'tera', 'attribute_id' => 0, 'language' => 'en' , 'attribute' =>  'race' , 'name' =>  'Unknown' ,  'name_short' =>  'T7-01' );
		$sql_ary[] = array('game_id' => 'tera', 'attribute_id' => 1, 'language' => 'en' , 'attribute' =>  'race' , 'name' =>  'Aman' ,  'name_short' =>  'Aman' );
		$sql_ary[] = array('game_id' => 'tera', 'attribute_id' => 2, 'language' => 'en' , 'attribute' =>  'race' , 'name' =>  'Baraka' ,  'name_short' =>  'Baraka' );
		$sql_ary[] = array('game_id' => 'tera', 'attribute_id' => 3, 'language' => 'en' , 'attribute' =>  'race' , 'name' =>  'Castanic' ,  'name_short' =>  'Castanic' );
		$sql_ary[] = array('game_id' => 'tera', 'attribute_id' => 4, 'language' => 'en' , 'attribute' =>  'race' , 'name' =>  'Elin' ,  'name_short' =>  'Elin' );
		$sql_ary[] = array('game_id' => 'tera', 'attribute_id' => 5, 'language' => 'en' , 'attribute' =>  'race' , 'name' =>  'High Elf' ,  'name_short' =>  'High Elf' );
		$sql_ary[] = array('game_id' => 'tera', 'attribute_id' => 6, 'language' => 'en' , 'attribute' =>  'race' , 'name' =>  'Human' ,  'name_short' =>  'Human' );
		$sql_ary[] = array('game_id' => 'tera', 'attribute_id' => 7, 'language' => 'en' , 'attribute' =>  'race' , 'name' =>  'Popori' ,  'name_short' =>  'Popori' );
		
		$db->sql_multi_insert ( BB_LANGUAGE , $sql_ary );
		unset ( $sql_ary );
		
	}
	
	/**
	 * Install sample Event Groups
	 * an Event answers the 'what' question
	*/
    protected function InstallEventGroup()
	{
		global $db, $table_prefix;
		
		$sql = 'SELECT dkpsys_id FROM ' .  DKPSYS_TABLE ."  WHERE dkpsys_name = 'Tera Dungeons' ";
		$result = $db->sql_query($sql);
		$this->teradkpid = (int) $db->sql_fetchfield('dkpsys_id');
		$db->sql_freeresult($result);
		
		if ($this->teradkpid == 0)
		{
			// dkp pool
			$sql_ary = array (
					'dkpsys_name' => 'Tera Dungeons',
					'dkpsys_status' => 'Y',
					'dkpsys_addedby' => 'admin',
					'dkpsys_default' => 'N' );
			$sql = 'INSERT INTO ' .  DKPSYS_TABLE  . $db->sql_build_array('INSERT', $sql_ary);
			$db->sql_query($sql);
			$this->teradkpid = $db->sql_nextid();
			$this->InstallEvents(); 
		}
	}
	
	/**
	 * dkp pool created for tera
	 * @var int
	 */
	private $teradkpid = 0; 
	
	/**
	 * Install sample Events and Events
	 * an Event answers the 'what' question
	 */
    private function InstallEvents()
	{
		global $db, $table_prefix;
		
		$sql_ary = array();
		$sql_ary [] = array('event_dkpid' => $this->teradkpid, 'event_name' => 'Bastion of Lok(20)', 'event_color' => '#888888', 'event_value' => 5, 'event_imagename' => ''  ) ;
		$sql_ary [] = array('event_dkpid' => $this->teradkpid, 'event_name' => 'Sinestral Manor (26)', 'event_color' => '#888888', 'event_value' => 5 , 'event_imagename' => '') ;
		$sql_ary [] = array('event_dkpid' => $this->teradkpid, 'event_name' => 'Cultists’ Refuge (35)', 'event_color' => '#00CC66', 'event_value' => 5, 'event_imagename' => '' );
		$sql_ary [] = array('event_dkpid' => $this->teradkpid, 'event_name' => 'Necromancer Tomb (41)', 'event_color' => '#00CC66', 'event_value' => 5, 'event_imagename' => '' );
		$sql_ary [] = array('event_dkpid' => $this->teradkpid, 'event_name' => 'Sigil Adstringo (45)', 'event_color' => '#00CC66', 'event_value' => 5, 'event_imagename' => '' );
		$sql_ary [] = array('event_dkpid' => $this->teradkpid, 'event_name' => 'Golden Labyrinth (48)', 'event_color' => '#00CC66', 'event_value' => 5, 'event_imagename' => '' );
		$sql_ary [] = array('event_dkpid' => $this->teradkpid, 'event_name' => 'Akasha’s Hideout (48)', 'event_color' => '#BBFF66', 'event_value' => 5, 'event_imagename' => '' );
		$sql_ary [] = array('event_dkpid' => $this->teradkpid, 'event_name' => 'Akasha’s Hideout (48* Hard)', 'event_color' => '#BBFF66', 'event_value' => 5, 'event_imagename' => '' );
		$sql_ary [] = array('event_dkpid' => $this->teradkpid, 'event_name' => 'Ascent of Saravash (52)', 'event_color' => '#FFCC55', 'event_value' => 5, 'event_imagename' => '' );
		$sql_ary [] = array('event_dkpid' => $this->teradkpid, 'event_name' => 'Saleron’s Sky Garden (53)', 'event_color' => '#FFCC55', 'event_value' => 5, 'event_imagename' => '' );
		$sql_ary [] = array('event_dkpid' => $this->teradkpid, 'event_name' => 'Suryati’s Peak (56)', 'event_color' => '#FFCC55', 'event_value' => 5, 'event_imagename' => '' );
		$sql_ary [] = array('event_dkpid' => $this->teradkpid, 'event_name' => 'Ebon Tower (58)', 'event_color' => '#FF7777', 'event_value' => 5, 'event_imagename' => '' );
		$sql_ary [] = array('event_dkpid' => $this->teradkpid, 'event_name' => 'Ebon Tower (60* Hard)', 'event_color' => '#AA0099', 'event_value' => 5, 'event_imagename' => '' );
		$sql_ary [] = array('event_dkpid' => $this->teradkpid, 'event_name' => 'Kelsaik’s Nest (58)', 'event_color' => '#FF7777', 'event_value' => 5, 'event_imagename' => '' );
		$sql_ary [] = array('event_dkpid' => $this->teradkpid, 'event_name' => 'Kelsaik’s Nest (60* Hard)', 'event_color' => '#AA0099', 'event_value' => 5, 'event_imagename' => '' );
		$sql_ary [] = array('event_dkpid' => $this->teradkpid, 'event_name' => 'Labyrinth of Terror (58)', 'event_color' => '#FF7777', 'event_value' => 5, 'event_imagename' => '' );
		$sql_ary [] = array('event_dkpid' => $this->teradkpid, 'event_name' => 'Labyrinth of Terror (60* Hard)', 'event_color' => '#AA0099', 'event_value' => 5, 'event_imagename' => '' );
		$sql_ary [] = array('event_dkpid' => $this->teradkpid, 'event_name' => 'Balder’s Temple (60)', 'event_color' => '#DD0066', 'event_value' => 5, 'event_imagename' => '' );
		$sql_ary [] = array('event_dkpid' => $this->teradkpid, 'event_name' => 'Balder’s Temple (60* Hard)', 'event_color' => '#AA0099', 'event_value' => 5, 'event_imagename' => '' );
		$sql_ary [] = array('event_dkpid' => $this->teradkpid, 'event_name' => 'Fane of Kaprima (60)', 'event_color' => '#DD0066', 'event_value' => 5, 'event_imagename' => '' );
		$sql_ary [] = array('event_dkpid' => $this->teradkpid, 'event_name' => 'Fane of Kaprima (60* Hard)', 'event_color' => '#AA0099', 'event_value' => 5, 'event_imagename' => '' );
		$db->sql_multi_insert (  EVENTS_TABLE , $sql_ary );
		
	}

	
}
?>