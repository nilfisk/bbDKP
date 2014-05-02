<?php
/**
 * Battle.net WoW API PHP SDK
 *
 * This software is not affiliated with Battle.net, and all references
 * to Battle.net and World of Warcraft are copyrighted by Blizzard Entertainment.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 *   @package bbdkp
 * @author	  Andreas Vandenberghe <sajaki9@gmail.com> 
 * @copyright Copyright (c) 2011, Chris Saylor, Daniel Cannon,  Andreas Vandenberghe
 * @license   http://opensource.org/licenses/gpl-license.php GNU Public License
 * @link	  https://github.com/bbDKP/WoWAPI
 * @link 	  http://blizzard.github.com/api-wow-docs/#character-profile-api
 * @version   1.0.4
 * 
 * 

The Character Profile API is the primary way to access character information. 
This Character Profile API can be used to fetch a single character at a time through an 
HTTP GET request to a URL describing the character profile resource. 
By default, a basic dataset will be returned and with each request and zero or more additional
 fields can be retrieved. 
To access this API, craft a resource URL pointing to the character whos information is to be retrieved.

URL = Host + "/api/wow/character/" + Realm + "/" + CharacterName

Realm = <proper realm name> | <normalized realm name>

There are no required query string parameters when accessing this resource, although the "fields" query string parameter can optionally be passed to indicate that one or more of the optional datasets is to be retrieved. Those additional fields are listed in the subsection titled "Optional Fields".
Example 2.1. An example Character Profile API request and response.
GET /api/wow/character/Medivh/Uther?fields=guild
Host: us.battle.net
HTTP/1.1 200 OK
<http headers>
{"realm": "Medivh", "name": "Uther", "level": 85, "lastModified": 1307596000000, "thumbnail": "medivh/1/1-avatar.jpg",
"race": 1, "achievementPoints": 9745, "gender": 0, "class": 2, "guild": { ... } }

The core dataset returned includes the character's realm, name, level, last modified timestamp, thumbnail, 
race id, achievement points value, gender id and class id.

**/
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
 * Character resource.
 *   @package bbdkp
 */
class Character extends \bbdkp\controller\wowapi\Resource
{
   
	/**
	 * accepted methods : none in this resource (asterisk) 
	 *
	 * @var array
	 */
	protected $methods_allowed = array('*');

	/**
	 * available extra Fields from guild
	 * standard fields are name, level, faction and achievement points.
	 *
	+ * @var array
	 */
	private $extrafields = array(
		'achievements',
		'appearance',
		'feed', 
		'guild',
		'hunterPets',
		'items',
		'mounts',
		'pets',
		'petSlots', 
		'professions',
		'progression',
		'pvp',
		/*'quests',*/
		'reputation',
		'stats',
		'talents',
		'titles',
	  );
	  
	/**
	  * return the private fields
	  *
	  * @return array
	  */
	 public function getFields()
	 {
	 	return $this->extrafields;
	 }

    /**
     * fetch character results
     * example : http://eu.battle.net/api/wow/character/Lightbringer/Sajaki
     * example : http://eu.battle.net/api/wow/character/Lightbringer/Sajaki?fields=progression,professions
     *
     * @param string $name
     * @param string $realm
     * @param array $fields
     * @return mixed
     */
	public function getCharacter($name = '', $realm = '', $fields=array()) 
	{
		global $user;
	
		if(empty($name))
		{
			trigger_error($user->lang['WOWAPI_NO_CHARACTER']);
		}
		
		/* caution input has to be utf8 */
		/* RFC 3986 as per http://us.battle.net/wow/en/forum/topic/3050125211 */
		$name = rawurlencode($name);
		if (empty($realm)) 
		{
			trigger_error($user->lang['WOWAPI_NO_REALMS']);
		}
		
		$realm = rawurlencode($realm);
		
		// URL = Host + "/api/wow/character/" + Realm + "/" + Name
		$field_str = '';
		if (is_array($fields) && count($fields) > 0) 
		{
			$field_str = 'fields=' . implode(',', $fields);
			//check if correct keys were requested
			$keys = $this->getFields();
			if (count( array_intersect($fields, $keys)) == 0 )
			{
				trigger_error(sprintf($user->lang['WOWAPI_INVALID_FIELD'], $field_str));
			}
			
			$data = $this->consume( $realm. '/'. $name , array(
				'data' => $field_str
			));
			
		}
		else
		{
			$data = $this->consume( $realm. '/'. $name);
		}
			
		return $data;
	}
}
