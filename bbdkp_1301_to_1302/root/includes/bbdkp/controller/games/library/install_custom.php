<?php
/**
 * Custom Game Installer file
 * does in fact nothing...
 *
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
 * Custom Installer Class
 *
 * @author Sajaki
 *
 */
class install_custom extends \bbdkp\controller\games\GameInstall
{
    protected $bossbaseurl = '';
    protected $zonebaseurl = '';
	public $game_id;

	/**
	 * Installs factions
	 */
    protected function Installfactions()
	{


	}

	/**
	 * Installs game classes
	 */
    protected function InstallClasses()
	{

	}

	/**
	 * Installs races
	 */
    protected function InstallRaces()
	{

	}

	/**
	 * Install sample Event Groups
	 * an Event answers the 'what' question
	*/
    protected function InstallEventGroup()
	{

	}


	/**
	 * Install sample Events and Events
	 * an Event answers the 'what' question
	*/
    protected function InstallEvents()
	{

	}


}

?>