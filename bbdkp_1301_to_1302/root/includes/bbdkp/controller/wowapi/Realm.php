<?php
/**
 * Battle.net WoW API PHP SDK
 *
 * This software is not affiliated with Battle.net, and all references
 * to Battle.net and World of Warcraft are copyrighted by Blizzard Entertainment.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 *   @package bbdkp
 * @author	  Chris Saylor
 * @author	  Daniel Cannon <daniel@danielcannon.co.uk>
 * @author	  Andreas Vandenberghe <sajaki9@gmail.com> 
 * @copyright Copyright (c) 2011, Chris Saylor, Daniel Cannon,  Andreas Vandenberghe
 * @license   http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link	  https://github.com/bbDKP/WoWAPI
 * @link 	  http://blizzard.github.com/api-wow-docs/#realm-status-api
 * @version   1.0.4 
 */
namespace bbdkp\controller\wowapi;

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

if (!class_exists('\bbdkp\controller\wowapi\Resource')) 
{
	require($phpbb_root_path . "includes/bbdkp/controller/wowapi/Resource.$phpEx");
}


/**
 * Realm resource.
 *   @package bbdkp
 */
class Realm extends \bbdkp\controller\wowapi\Resource 
{
	/**
	 * allowed realm api methods
	 * @var array
	 */
	protected $methods_allowed = array(
		'status'
	);

	/**
	 * Get status results for all realms.
	 *
	 * @return array
	 */
	public function getAllRealmStatus() 
	{
		return $this->consume('status');
	}

	/**
	 * Get status results for specified realm(s).
	 *
	 * @param mixed $realms String or array of realm(s)
	 * @return mixed
	 */
	
	public function getRealmStatus($realms = array()) 
	{
		global $user;

		if (empty($realms)) 
		{
			trigger_error($user->lang['WOWAPI_NO_REALMS']);
		}
		
		elseif (!is_array($realms)) 
		{
			$data = $this->consume('status', array(
				'data' => 'realm='.$realms
			));
		} 
		else 
		{
			$realm_str = 'realms=';
			foreach($realms as $key => $realm) 
			{
				$realm_str .= ($key == 0 ? '' : ',') . rawurlencode($realm);
			}
			$data = $this->consume('status', array(
				'data' => $realm_str
			));
		}
		return $data;
	}
}
