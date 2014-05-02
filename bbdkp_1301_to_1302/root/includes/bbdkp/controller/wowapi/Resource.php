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
 * @link 	  http://blizzard.github.com/api-wow-docs
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

if (! defined('EMED_BBDKP'))
{
	$user->add_lang(array('mods/dkp_admin'));
	\trigger_error($user->lang['BBDKPDISABLED'], E_USER_WARNING);
}


/**
 * Resource skeleton
 *
 *   @package bbdkp
 */
abstract class Resource extends \bbdkp\admin\Admin
{

	/**
	 * List of region urls
	 * @var string
	 */
	protected $api_url = array(
		'eu' => 'http://eu.battle.net/api/wow/',
		'us' => 'http://us.battle.net/api/wow/',
		'kr' => 'http://kr.battle.net/api/wow/',
		'tw' => 'http://tw.battle.net/api/wow/',
		'cn' => 'http://www.battlenet.com.cn/api/wow/',
		'sea' => 'http://sea.battle.net/api/wow/'
	);

	/**
	 * Battlenet region
	 *
	 * @var string
	 */
	public $region;

	/**
	 * Methods allowed by this resource (or available).
	 *
	 * @var array
	 */
	protected $methods_allowed;

	/**
	 * realm api constructor
	 * @param string $region Server region
	 */
	public function __construct($region='us')
	{
		global $user;

		if (empty($this->methods_allowed))
		{
			trigger_error($user->lang['NO_METHODS']);
		}
		$this->region = $region;
	}

    /**
     * get the uri property
     *
     * @return string
     */
	public function GetURI()
	{
		return $this->api_url[$this->region];
	}

	/**
	 * Returns the URI for use with the request object
	 *
	 * @param string $method
	 * @return string API URI
	 */
	private function getResourceUri($method)
	{
		$uri = $this->GetURI();
		$classname = get_class($this);
		if (preg_match('@\\\\([\w]+)$@', $classname, $matches))
		{
			$classname = strtolower($matches[1]);
		}
		$uri .= $classname ;
		$uri .= '/'.$method;
		return $uri;
	}

	/**
	 * Consumes the resource by method and returns the results of the request.
	 *
	 * @param string $method Request method
	 * @param array $params Parameters
	 * @return array Request data
	 */
	public function consume($method, $params=array())
	{
		global $user;

		// either a valid method is required or an asterisk
		if (!in_array($method, $this->methods_allowed)  && !in_array('*', $this->methods_allowed) )
		{
			trigger_error($user->lang['WOWAPI_METH_NOTALLOWED']);
		}
		$url = $this->getResourceUri($method);

		//process parameters
		if (isset($params['data']) && !empty($params['data']))
		{
			if (is_array($params['data']))
			{
				$optfields = '';
				foreach($params['data'] as $key => $value)
				{
					$optfields .= $key.'='.$value.'&';
				}
				$optfields = rtrim($data, '&');
				//@debug tis
			}
			else
			{
				$optfields = $params['data'];
			}

			$url .= '?' . $optfields;
		}


		$data = $this->Curl($url, false, false, true);
		return $data;
	}


}
