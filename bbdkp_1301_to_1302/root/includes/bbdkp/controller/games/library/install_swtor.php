<?php
/**
 * bbdkp SWTOR install file
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
 * SwTor Installer class
 *   @package bbdkp
 *
 */
class install_swtor extends \bbdkp\controller\games\GameInstall
{
    protected $bossbaseurl = 'http://www.swtor-spy.com/codex/%s';
    protected $zonebaseurl = 'http://www.swtor-spy.com/codex/%s';

    /**
	 * Installs factions
	 */
    protected function Installfactions()
	{
		global $db;
		// factions
		$db->sql_query('DELETE FROM ' . FACTION_TABLE . " where game_id = 'swtor'" );
		$sql_ary = array();
		$sql_ary [] = array ('game_id' => 'swtor','faction_id' => 1, 'faction_name' => 'Galactic Republic' );
		$sql_ary [] = array ('game_id' => 'swtor','faction_id' => 2, 'faction_name' => 'Jedi Order' );
		$sql_ary [] = array ('game_id' => 'swtor','faction_id' => 3, 'faction_name' => 'Sith Empire' );
		$sql_ary [] = array ('game_id' => 'swtor','faction_id' => 4, 'faction_name' => 'Sith Lords' );
		$db->sql_multi_insert ( FACTION_TABLE, $sql_ary );
		unset ( $sql_ary );
	}
	
	/**
	 * Installs game classes
	*/
    protected function InstallClasses()
	{
		global $db;
		// note subclasses not done
		$db->sql_query('DELETE FROM ' . CLASS_TABLE . " where game_id = 'swtor'" );
		$sql_ary = array ();
		$sql_ary [] = array ('game_id' => 'swtor', 'class_id' => 0, 'class_faction_id' => 1, 'class_armor_type' => 'HEAVY', 'class_min_level' => 1, 'class_max_level' => 50 , 'colorcode' =>  '#999', 'imagename' => 'swtor_unknown');
		$sql_ary [] = array ('game_id' => 'swtor','class_id' => 1, 'class_faction_id' => 1, 'class_armor_type' => 'HEAVY', 'class_min_level' => 1, 'class_max_level' => 50 , 'colorcode' =>  '#66CCFF', 'imagename' => 'swtor_trooper');
		$sql_ary [] = array ('game_id' => 'swtor', 'class_id' => 2, 'class_faction_id' => 1, 'class_armor_type' => 'LEATHER', 'class_min_level' => 1, 'class_max_level' => 50, 'colorcode' =>  '#AFDCEC',  'imagename' => 'swtor_smuggler');
		$sql_ary [] = array ('game_id' => 'swtor','class_id' => 3, 'class_faction_id' => 2, 'class_armor_type' => 'AUGMENTED', 'class_min_level' => 1, 'class_max_level' => 50 , 'colorcode' =>  '#437C17',  'imagename' => 'swtor_jedi');
		$sql_ary [] = array ('game_id' => 'swtor','class_id' => 4, 'class_faction_id' => 2, 'class_armor_type' => 'ROBE', 'class_min_level' => 1, 'class_max_level' => 50 ,  'colorcode' =>  '#663333',  'imagename' => 'swtor_consul');
		$sql_ary [] = array ('game_id' => 'swtor','class_id' => 5, 'class_faction_id' => 3, 'class_armor_type' => 'HEAVY', 'class_min_level' => 1, 'class_max_level' => 50 , 'colorcode' =>  '#CC0033',  'imagename' => 'swtor_hunter');
		$sql_ary [] = array ('game_id' => 'swtor','class_id' => 6, 'class_faction_id' => 4, 'class_armor_type' => 'LEATHER', 'class_min_level' => 1, 'class_max_level' => 50 , 'colorcode' =>  '#FF6600',  'imagename' => 'swtor_warrior');
		$sql_ary [] = array ('game_id' => 'swtor','class_id' => 7, 'class_faction_id' => 3, 'class_armor_type' => 'AUGMENTED', 'class_min_level' => 1, 'class_max_level' => 50 , 'colorcode' =>  '#996699',  'imagename' => 'swtor_agent');
		$sql_ary [] = array ('game_id' => 'swtor','class_id' => 8, 'class_faction_id' => 4, 'class_armor_type' => 'ROBE', 'class_min_level' => 1, 'class_max_level' => 50 , 'colorcode' =>  '#660033',  'imagename' => 'swtor_inquisitor');
		$db->sql_multi_insert ( CLASS_TABLE, $sql_ary );
		unset ( $sql_ary );
		
		// Dictionary
		$db->sql_query('DELETE FROM ' . BB_LANGUAGE . "  where game_id = 'swtor' and attribute  = 'class' ");
		$sql_ary = array ();
		//
		$sql_ary[] = array('game_id' => 'swtor', 'attribute_id' => 0, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Unknown' ,  'name_short' =>  'T7-01' );
		$sql_ary[] = array('game_id' => 'swtor', 'attribute_id' => 1, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Trooper' ,  'name_short' =>  'Trooper' );
		$sql_ary[] = array('game_id' => 'swtor', 'attribute_id' => 2, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Smuggler' ,  'name_short' =>  'Smuggler' );
		$sql_ary[] = array('game_id' => 'swtor', 'attribute_id' => 3, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Jedi Knight' ,  'name_short' =>  'Jedi' );
		$sql_ary[] = array('game_id' => 'swtor', 'attribute_id' => 4, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Jedi Consular' ,  'name_short' =>  'Consul' );
		$sql_ary[] = array('game_id' => 'swtor', 'attribute_id' => 5, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Sith Inquisitor' ,  'name_short' =>  'Inquisitor' );
		$sql_ary[] = array('game_id' => 'swtor', 'attribute_id' => 6, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Sith Warrior' ,  'name_short' =>  'Warrior' );
		$sql_ary[] = array('game_id' => 'swtor', 'attribute_id' => 7, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Bounty Hunter' ,  'name_short' =>  'Hunter' );
		$sql_ary[] = array('game_id' => 'swtor', 'attribute_id' => 8, 'language' =>  'en' , 'attribute' =>  'class' , 'name' =>  'Imperial Agent' ,  'name_short' =>  'Agent' );

		$sql_ary[] = array('game_id' => 'swtor', 'attribute_id' => 0, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'unbkannt' ,  'name_short' =>  'unbekannt' );
		$sql_ary[] = array('game_id' => 'swtor', 'attribute_id' => 1, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'Soldat' ,  'name_short' =>  'Soldat' );
		$sql_ary[] = array('game_id' => 'swtor', 'attribute_id' => 2, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'Schmuggler' ,  'name_short' =>  'Schmuggler' );
		$sql_ary[] = array('game_id' => 'swtor', 'attribute_id' => 3, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'Jedi-Ritter' ,  'name_short' =>  'Ritter' );
		$sql_ary[] = array('game_id' => 'swtor', 'attribute_id' => 4, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'Jedi Botschafter' ,  'name_short' =>  'Botschafter' );
		$sql_ary[] = array('game_id' => 'swtor', 'attribute_id' => 5, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'Sith Inquisitor' ,  'name_short' =>  'Inquisitor' );
		$sql_ary[] = array('game_id' => 'swtor', 'attribute_id' => 6, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'Sith Krieger' ,  'name_short' =>  'Krieger' );
		$sql_ary[] = array('game_id' => 'swtor', 'attribute_id' => 7, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'Kopfgeldjäger' ,  'name_short' =>  'Kopfgeldjäger' );
		$sql_ary[] = array('game_id' => 'swtor', 'attribute_id' => 8, 'language' =>  'de' , 'attribute' =>  'class' , 'name' =>  'Imperialer Agent' ,  'name_short' =>  'Imperialer Agent' );
		$db->sql_multi_insert ( BB_LANGUAGE , $sql_ary );
		unset ( $sql_ary );
	}
	
	/**
	 * Installs races
	*/
    protected function InstallRaces()
	{
		global $db;
		
		// species source : http://starwars.wikia.com/wiki/Star_Wars:_The_Old_Republic
		$db->sql_query('DELETE FROM ' .  RACE_TABLE . "  where game_id = 'swtor'");
		$sql_ary = array ();
		//Unknown
		$sql_ary [] = array ('game_id' => 'swtor','race_id' => 0, 'race_faction_id' => 1, 'image_female' => ' ',  'image_male' => ' '  );
		$sql_ary [] = array ('game_id' => 'swtor','race_id' => 1, 'race_faction_id' => 2 , 'image_female' => 'swtor_miraluka_female',  'image_male' => 'swtor_miraluka_male' );
		$sql_ary [] = array ('game_id' => 'swtor','race_id' => 2, 'race_faction_id' => 1 , 'image_female' => 'swtor_twilek_female',  'image_male' => 'swtor_twilek_male' );
		$sql_ary [] = array ('game_id' => 'swtor','race_id' => 3, 'race_faction_id' => 2  , 'image_female' => 'swtor_zabrak_female',  'image_male' => 'swtor_zabrak_male' );
		$sql_ary [] = array ('game_id' => 'swtor','race_id' => 4, 'race_faction_id' => 1 , 'image_female' => 'swtor_miralian_female',  'image_male' => 'swtor_miralian_male' ) ;
		$sql_ary [] = array ('game_id' => 'swtor','race_id' => 5, 'race_faction_id' => 1 , 'image_female' => 'swtor_human_female',  'image_male' => 'swtor_human_male' );
		$sql_ary [] = array ('game_id' => 'swtor','race_id' => 6, 'race_faction_id' => 3, 'image_female' => 'swtor_chiss_female',  'image_male' => 'swtor_chiss_male'  );
		$sql_ary [] = array ('game_id' => 'swtor','race_id' => 7, 'race_faction_id' => 3, 'image_female' => 'swtor_rattataki_female',  'image_male' => 'swtor_rattataki_male' );
		$sql_ary [] = array ('game_id' => 'swtor','race_id' => 8, 'race_faction_id' => 3, 'image_female' => 'swtor_redsith_female',  'image_male' => 'swtor_redsith_male' );
		$db->sql_multi_insert ( RACE_TABLE , $sql_ary);
		unset ( $sql_ary );
		
		// Dictionary
		$db->sql_query('DELETE FROM ' . BB_LANGUAGE . "  where game_id = 'swtor' and attribute in('race') ");
		$sql_ary = array ();
		
		// species
		$sql_ary[] = array('game_id' => 'swtor', 'attribute_id' => 0, 'language' => 'en' , 'attribute' =>  'race' , 'name' =>  'Unknown' ,  'name_short' =>  'T7-01' );
		$sql_ary[] = array('game_id' => 'swtor', 'attribute_id' => 1, 'language' => 'en' , 'attribute' =>  'race' , 'name' =>  'Miraluka' ,  'name_short' =>  'Miraluka' );
		$sql_ary[] = array('game_id' => 'swtor', 'attribute_id' => 2, 'language' => 'en' , 'attribute' =>  'race' , 'name' =>  'Twilek' ,  'name_short' =>  'Twilek' );
		$sql_ary[] = array('game_id' => 'swtor', 'attribute_id' => 3, 'language' => 'en' , 'attribute' =>  'race' , 'name' =>  'Zabrak' ,  'name_short' =>  'Zabrak' );
		$sql_ary[] = array('game_id' => 'swtor', 'attribute_id' => 4, 'language' => 'en' , 'attribute' =>  'race' , 'name' =>  'Miralian' ,  'name_short' =>  'Miralian' );
		$sql_ary[] = array('game_id' => 'swtor', 'attribute_id' => 5, 'language' => 'en' , 'attribute' =>  'race' , 'name' =>  'Human' ,  'name_short' =>  'Human' );
		$sql_ary[] = array('game_id' => 'swtor', 'attribute_id' => 6, 'language' => 'en' , 'attribute' =>  'race' , 'name' =>  'Chiss' ,  'name_short' =>  'Chiss' );
		$sql_ary[] = array('game_id' => 'swtor', 'attribute_id' => 7, 'language' => 'en' , 'attribute' =>  'race' , 'name' =>  'Rattataki' ,  'name_short' =>  'Rattataki' );
		$sql_ary[] = array('game_id' => 'swtor', 'attribute_id' => 8, 'language' => 'en' , 'attribute' =>  'race' , 'name' =>  'Red Siths' ,  'name_short' =>  'Red Siths' );
		
		$sql_ary[] = array('game_id' => 'swtor', 'attribute_id' => 0, 'language' => 'de' , 'attribute' =>  'race' , 'name' =>  'unbekannt' ,  'name_short' =>  'unbekannt' );
		$sql_ary[] = array('game_id' => 'swtor', 'attribute_id' => 1, 'language' => 'de' , 'attribute' =>  'race' , 'name' =>  'Miraluka' ,  'name_short' =>  'Miraluka' );
		$sql_ary[] = array('game_id' => 'swtor', 'attribute_id' => 2, 'language' => 'de' , 'attribute' =>  'race' , 'name' =>  'Twilek' ,  'name_short' =>  'Twilek' );
		$sql_ary[] = array('game_id' => 'swtor', 'attribute_id' => 3, 'language' => 'de' , 'attribute' =>  'race' , 'name' =>  'Zabrak' ,  'name_short' =>  'Zabrak' );
		$sql_ary[] = array('game_id' => 'swtor', 'attribute_id' => 4, 'language' => 'de' , 'attribute' =>  'race' , 'name' =>  'Miralian' ,  'name_short' =>  'Miralian' );
		$sql_ary[] = array('game_id' => 'swtor', 'attribute_id' => 5, 'language' => 'de' , 'attribute' =>  'race' , 'name' =>  'Mensch' ,  'name_short' =>  'Mensch' );
		$sql_ary[] = array('game_id' => 'swtor', 'attribute_id' => 6, 'language' => 'de' , 'attribute' =>  'race' , 'name' =>  'Chiss' ,  'name_short' =>  'Chiss' );
		$sql_ary[] = array('game_id' => 'swtor', 'attribute_id' => 7, 'language' => 'de' , 'attribute' =>  'race' , 'name' =>  'Rattataki' ,  'name_short' =>  'Rattataki' );
		$sql_ary[] = array('game_id' => 'swtor', 'attribute_id' => 8, 'language' => 'de' , 'attribute' =>  'race' , 'name' =>  'Reinblütige Sith' ,  'name_short' =>  'Reinblütige Sith' );
		
		$db->sql_multi_insert ( BB_LANGUAGE , $sql_ary );
		unset ( $sql_ary );
		
		
	}
	
	/**
	 * dkp pool created for SWTOR
	 * @var int
	 */
	private $swtordkpid = 0; 
	
	/**
	 * Install sample Event Groups
	 * an Event answers the 'what' question
	*/
    protected function InstallEventGroup()
	{
		global $db;
		
		$sql = 'SELECT dkpsys_id FROM ' .  DKPSYS_TABLE ."  WHERE dkpsys_name = 'SWTOR Flashpoints' ";
		$result = $db->sql_query($sql);
		$this->swtordkpid = (int) $db->sql_fetchfield('dkpsys_id');
		$db->sql_freeresult($result);
		if ($this->swtordkpid == 0)
		{
			$db->sql_query('DELETE FROM ' .  EVENTS_TABLE . ' WHERE event_dkpid = ' . $this->swtordkpid );
			
			$sql_ary = array (
					'dkpsys_name' => 'SWTOR Flashpoints',
					'dkpsys_status' => 'Y',
					'dkpsys_addedby' => 'admin',
					'dkpsys_default' => 'N' );
			$sql = 'INSERT INTO ' .  DKPSYS_TABLE  . $db->sql_build_array('INSERT', $sql_ary);
			$db->sql_query($sql);
			$this->swtordkpid = intval($db->sql_nextid());
			
			$this->InstallEvents();
		}
		
	}
	
	/**
	 * Install sample Events and Events
	 * an Event answers the 'what' question
	 */
    protected function InstallEvents()
	{
		global $db;
		$sql_ary = array();
		$sql_ary [] = array('event_dkpid' => $this->swtordkpid , 'event_name' => 'The Esseles', 'event_color' => '#C6DEFF', 'event_value' => 5, 'event_imagename' => ''  ) ;
		$sql_ary [] = array('event_dkpid' => $this->swtordkpid , 'event_name' => 'Black Talon', 'event_color' => '#C6DEFF', 'event_value' => 5 , 'event_imagename' => '') ;
		$sql_ary [] = array('event_dkpid' => $this->swtordkpid , 'event_name' => 'Hammer Station', 'event_color' => '#6D7B8D', 'event_value' => 10, 'event_imagename' => '' );
		$sql_ary [] = array('event_dkpid' => $this->swtordkpid , 'event_name' => 'Taral V', 'event_color' => '#6D7B8D', 'event_value' => 10, 'event_imagename' => '' );
		$sql_ary [] = array('event_dkpid' => $this->swtordkpid , 'event_name' => 'Boarding party', 'event_color' => '#6D7B8D', 'event_value' => 20, 'event_imagename' => '' );
		$sql_ary [] = array('event_dkpid' => $this->swtordkpid , 'event_name' => 'Directive 7', 'event_color' => '#842DCE', 'event_value' => 20, 'event_imagename' => '' );
		$sql_ary [] = array('event_dkpid' => $this->swtordkpid , 'event_name' => 'Voidstar', 'event_color' => '#842DCE', 'event_value' => 20, 'event_imagename' => '' );
		$sql_ary [] = array('event_dkpid' => $this->swtordkpid , 'event_name' => 'Huttball', 'event_color' => '#842DCE', 'event_value' => 20, 'event_imagename' => '' );
		$sql_ary [] = array('event_dkpid' => $this->swtordkpid , 'event_name' => 'Alderaan', 'event_color' => '#842DCE', 'event_value' => 20, 'event_imagename' => '' );
		$db->sql_multi_insert (  EVENTS_TABLE , $sql_ary );
		
	}

				
	
	
	
	
	
}
?>