<?php
/**
 * bbDKP Admin class file
 *
 *   @package bbdkp
 * @link http://www.bbdkp.com
 * @author Sajaki@gmail.com
 * @copyright 2009 bbdkp
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version 1.3.0
 *
 */
namespace bbdkp\admin;
/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

$phpEx = substr(strrchr(__FILE__, '.'), 1);
global $phpbb_root_path;

if (!class_exists('\bbdkp\admin\log'))
{
	require("{$phpbb_root_path}includes/bbdkp/admin/log.$phpEx");
}

/**
 *
 * bbDKP Admin foundation
 *   @package bbdkp
 */
class Admin
{
	/**
	 * bbdkp timestamp
	 * @var integer
	 */
    public $time = 0;
    /**
     * game ragions
     * @var array
     */
    public $regions;

    /**
     * supported languages. The game related texts (class names etc) are not stored in language files but in the database.
     * supported languages are en, fr, de : to add a new language you need to a) make language files b) make db installers in new language c) adapt this array
     *
     * @var array
     */
    public $languagecodes;

    /**
     * games that come pre-installed with bbDKP
     * @var array
     */
    public $preinstalled_games;

    /**
     * bbtips is installed ?
     * @var boolean
     */
    public $bbtips = false;

    /**
     * installed games
     * @var array
     */
    public $games;

    /**
     * Admin class constructor
     */
	public function __construct()
	{
		global $phpbb_root_path, $phpEx, $config, $user;

		$user->add_lang ( array ('mods/dkp_admin' ) );
		$user->add_lang ( array ('mods/dkp_common' ) );

		if(!defined("EMED_BBDKP"))
		{
			trigger_error ( $user->lang['BBDKPDISABLED'] , E_USER_WARNING );
		}

		// Check for required extensions
		if (!function_exists('curl_init'))
		{
			trigger_error($user->lang['CURL_REQUIRED'], E_USER_WARNING);

		}

		if (!function_exists('json_decode'))
		{
			trigger_error($user->lang['JSON_REQUIRED'], E_USER_WARNING);
		}

		$this->regions = array(
				'eu' => $user->lang['REGIONEU'],
				'us' => $user->lang['REGIONUS'],
				'tw' => $user->lang['REGIONTW'],
				'kr' => $user->lang['REGIONKR'],
				'cn' => $user->lang['REGIONCN'],
				'sea' => $user->lang['REGIONSEA'],
				);

		$this->languagecodes = array(
				'de' => $user->lang['LANG_DE'] ,
				'en' => $user->lang['LANG_EN'] ,
				'fr' => $user->lang['LANG_FR']);

		$this->preinstalled_games = array (
				'aion' 	=> $user->lang ['AION'],
				'daoc' 	=> $user->lang ['DAOC'],
				'eq' 	=> $user->lang ['EQ'],
				'eq2' 	=> $user->lang ['EQ2'],
				'FFXI' 	=> $user->lang ['FFXI'],
				'gw2' 	=> $user->lang ['GW2'],
				'lineage2' => $user->lang ['LINEAGE2'],
				'lotro' => $user->lang ['LOTRO'],
				'rift' 	=> $user->lang ['RIFT'],
				'swtor' => $user->lang ['SWTOR'],
				'tera' 	=> $user->lang ['TERA'],
				'vanguard' => $user->lang ['VANGUARD'],
				'warhammer' => $user->lang ['WARHAMMER'],
				'wow' 	=> $user->lang ['WOW'],
				'ffxiv'	=> $user->lang ['FFXIV'],
		);


	    $boardtime = getdate(time() + $user->timezone + $user->dst - date('Z'));
	    $this->time = $boardtime[0];

	    $this->gamesarray();
	    $debug=1;
	}

	/**
	 * constructs the games array
	 */
	private function gamesarray()
	{
		global $db;
		$this->games= array();
		$sql = ' SELECT g.id, g.game_id, g.game_name, status';
		$sql .= ' FROM ' . GAMES_TABLE . '  g';
		$sql .= ' INNER JOIN '. RACE_TABLE . ' r ON r.game_id = g.game_id';
		$sql .= ' INNER JOIN  ' . CLASS_TABLE . ' c ON c.game_id= g.game_id';
		$sql .= ' GROUP BY g.id, g.game_id, g.game_name';
		$sql .= ' ORDER BY g.game_id';
		$result = $db->sql_query ( $sql );
		while($row = $db->sql_fetchrow($result))
		{
			$this->games[$row['game_id']] = $row['game_name'];
		}
		$db->sql_freeresult($result);
	}

    /**
     * creates a unique key, used as adjustments, import, items and raid identifier
     *
     * @param string $part1
     * @param string $part2
     * @param string $part3
     * @return string $group_key
     */
    public final function gen_group_key($part1, $part2, $part3)
    {
        // Get the first 10-11 digits of each md5 hash
        $part1 = substr(md5($part1), 0, 10);
        $part2 = substr(md5($part2), 0, 11);
        $part3 = substr(md5($part3), 0, 11);

        // Group the hashes together and create a new hash based on uniqid()
        $group_key = $part1 . $part2 . $part3;
        $group_key = md5(uniqid($group_key));

        return $group_key;
    }

    /**
	 * connects to remote site and gets xml or html using Curl
	 * @param char $url
	 * @param bool $return_Server_Response_Header default false
	 * @param bool $loud default false
	 * @param bool $json default true
	 * @return array response
     */
  	public final function curl($url, $return_Server_Response_Header = false, $loud= false, $json=true)
	{

        //@todo log all curl calls
		global $user;

		if ( function_exists ( 'curl_init' ))
		{
			 /* Create a CURL handle. */
			if (($curl = curl_init($url)) === false)
			{
				trigger_error('curl_init Failed' , E_USER_WARNING);
			}

			// set options
			curl_setopt_array($curl, array(
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_URL => $url,
				CURLOPT_USERAGENT => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:21.0) Gecko/20100101 Firefox/21.0',
				CURLOPT_SSL_VERIFYHOST => false,
				CURLOPT_SSL_VERIFYPEER => false,
				CURLOPT_TIMEOUT => 60,
				CURLOPT_VERBOSE => false,
				CURLOPT_HEADER => false,
			));

			//@todo : setup authentication keys

			// Execute
			$response = curl_exec($curl);
			$headers = curl_getinfo($curl);
			$error = 0;

			$data = array(
					'response'		    => $json ? json_decode($response, true) : $response,
					'response_headers'  => (array) $headers,
					'error'				=> '',
			);

			//errorhandler
			if (!$response)
			{
				$error = curl_errno ($curl);
				/*
				 CURLE_OK = 0,
				CURLE_UNSUPPORTED_PROTOCOL,     1
				CURLE_FAILED_INIT,              2
				CURLE_URL_MALFORMAT,            3
				CURLE_URL_MALFORMAT_USER,       4 - NOT USED
				CURLE_COULDNT_RESOLVE_PROXY,    5
				CURLE_COULDNT_RESOLVE_HOST,     6
				CURLE_COULDNT_CONNECT,          7
				CURLE_FTP_WEIRD_SERVER_REPLY,   8
				*/
				switch ($error)
				{
					case "28" :
						$data['error'] = 'cURL error :' . $url . ": No response after 30 second timeout : err " . $error . "  ";
						break;
					case "1" :
						$data['error'] = 'cURL error :' . $url . " : error " . $error . " : UNSUPPORTED_PROTOCOL ";
						break;
					case "2" :
						$data['error'] = 'cURL error :' . $url . " : error " . $error . " : FAILED_INIT ";
						break;
					case "3" :
						$data['error'] = 'cURL error :' . $url . " : error " . $error . " : URL_MALFORMAT ";
						break;
					case "5" :
						$data['error'] = 'cURL error :' . $url . " : error " . $error . " : COULDNT_RESOLVE_PROXY ";
						break;
					case "6" :
						$data['error'] = 'cURL error :' . $url . " : error " . $error . " : COULDNT_RESOLVE_HOST ";
						break;
					case "7" :
						$data['error'] = 'cURL error :' . $url . " : error " . $error . " : COULDNT_CONNECT ";
				}
			}



			if (isset($data['response_headers']['http_code']))
			{
				switch ($data['response_headers']['http_code'] )
				{
					case 400:
						$data['error'] .= $user->lang['ERR400'] . ': ' . $data['response']['reason'];
						break;
					case 401:
						$data['error'] .= $user->lang['ERR401'] . ': ' . $data['response']['reason'];
						break;
					case 403:
						$data['error'] .= $user->lang['ERR403'] . ': ' . $data['response']['reason'];
						break;
					case 404:
                        $data['error'] .= $user->lang['ERR404'];
                        if(isset($data['response']['reason']))
                        {
                            $data['error'] .=  ': ' . $data['response']['reason'];
                        }
						break;
					case 500:
						$data['error'] .= $user->lang['ERR500'] . ': ' . $data['response']['reason'];
						break;
					case 501:
						$data['error'] .= $user->lang['ERR501'] . ': ' . $data['response']['reason'];
						break;
					case 502:
						$data['error'] .= $user->lang['ERR502'] . ': ' . $data['response']['reason'];
						break;
					case 503:
						$data['error'] .= $user->lang['ERR503'] . ': ' . $data['response']['reason'];
						break;
					case 504:
						$data['error'] .= $user->lang['ERR504'] . ': ' . $data['response']['reason'];
						break;
				}
			}

			//close conection
			curl_close ($curl);
		}

		//report errors?
		if ($data['error'] != 0)
		{
			if($loud == true)
			{
				trigger_error($data['error'], E_USER_WARNING);
			}
	        return false;
		}
		else
		{
			return $data['response'];
		}

	}

	/**
	 * sends POST request to bbdkp.com for registration
	 * @param array $regdata
	 */
	public final function post_register_request($regdata)
	{

		$regcode = hash("sha256", serialize(array($regdata['domainname'],$regdata['phpbbversion'], $regdata['bbdkpversion'])));

		// bbdkp registration url
		$url = "https://www.bbdkp.com/services/registerbbdkp.php";
		// Create URL parameter string
		$fields_string = '';
		foreach( $regdata as $key => $value )
		{
			$fields_string .= $key.'='.$value.'&';
		}
		$fields_string .= 'regcode='.$regcode;

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url);
		curl_setopt( $ch, CURLOPT_POST, 4 );
		curl_setopt( $ch, CURLOPT_HEADER , false);
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $fields_string );
		$result = curl_exec($ch);
		curl_close( $ch );

		$this->get_register_request($regdata, $url, $regcode);
	}


	/**
	 * GET reauests for registration ID
	 * @param array $regdata
	 * @param string $url
	 * @param string $regcode
	 */
	private final function get_register_request($regdata, $url, $regcode)
	{
		global $cache, $config;

		$fields_string = '';
		foreach( $regdata as $key => $value )
		{
			$fields_string .= $key.'='.$value.'&';
		}
		$fields_string .= 'regcode='.$regcode;

		$url .= '?' . $fields_string;

		$data = $this->Curl($url, 'GET');
		$regID = isset($data['registration']) ? $data['registration'] : '';
		set_config('bbdkp_regid', $regID, true);
		$cache->destroy('config');
		trigger_error('Registration Successful : ' . $config['bbdkp_regid'], E_USER_NOTICE );
	}


	/**
	 * retrieve latest bbdkp productversion
	 * @param string $product productname
	 * @param bool $force_update Ignores cached data. Defaults to false.
	 * @param bool $warn_fail Trigger a warning if obtaining the latest version information fails. Defaults to false.
	 * @param int $ttl Cache version information for $ttl seconds. Defaults to 86400 (24 hours).
	 * @return string | false Version info on success, false on failure.
 	 */
	public final function get_productversion($product, $force_update = false, $warn_fail = false, $ttl = 86400)
	{
		global $cache;
		//get latest productversion from cache
		$info = $cache->get('version_' . $product);

		//if update is forced or cache expired then make the call to refresh latest productversion
		if ($info === false || $force_update)
		{
			$errstr = '';

			$info = $this->curl(BBDKP_VERSIONURL . 'version_' . $product .'.txt' , false, false, false);

			if (empty($info))
			{
				$cache->destroy($product. '_version');
				if ($warn_fail)
				{
					trigger_error($errstr, E_USER_WARNING);
				}
				return false;
			}
			//put this info in the cache
			$cache->put('version_' . $product , $info, $ttl);
		}
		return $info;
	}

    /**
	 * get plugin info
     * @param bool $force_update
     * @param int $ttl
     * @return array|bool
     */
    public final function get_plugin_info($force_update = false, $ttl = 86400)
	{
		global $cache, $db;
		//get latest productversion from cache
		$plugins = $cache->get('bbdkpplugins');

		//if update is forced or cache expired then make the query to refresh latest productversion
		if ($plugins === false || $force_update)
		{
			$plugins = array();
			$sql = 'SELECT name, value, version, installdate FROM ' . BBDKPPLUGINS_TABLE . ' ORDER BY installdate DESC ';
			$result = $db->sql_query ($sql);
			while($row = $db->sql_fetchrow($result))
			{
				$info = $this->curl(BBDKP_VERSIONURL . 'version_' . $row['name'] .'.txt' , false, false, false);

				//get latest
				$plugins[$row['name']] = array(
					'name' => $row['name'],
					'value' => $row['value'],
					'version' => $row['version'],
					'latest' =>  empty($info) ? '?' : $info,
					'installdate' => $row['installdate']
				);
			}
			$db->sql_freeresult($result);

			$cache->destroy('bbdkpplugins');
			$cache->put( 'bbdkpplugins', $plugins, $ttl);
		}
		return $plugins;

	}

	/**
	 * Pagination function altered from functions.php used in viewmember.php because we need two linked paginations
	 *
	 * @param string $base_url
	 * @param integer $num_items
	 * @param integer $per_page
	 * @param integer $start_item
	 * @param boolean $add_prevnext_text
	 * @param string $tpl_prefix  using different pagination blocks at one page
	 * @return boolean|string
	 */
	public final function generate_pagination2($base_url, $num_items, $per_page, $start_item, $add_prevnext_text = true, $tpl_prefix = '')
	{
		global $template, $user;

		// Make sure $per_page is a valid value
		$per_page = ($per_page <= 0) ? 1 : $per_page;
		$total_pages = ceil($num_items / $per_page);

		$seperator = '<span class="page-sep">' . $user->lang['COMMA_SEPARATOR'] . '</span>';

		if ($total_pages == 1 || !$num_items)
		{
			return false;
		}

		$on_page = floor($start_item / $per_page) + 1;
		$url_delim = (strpos($base_url, '?') === false) ? '?' : '&amp;';

		$page_string = ($on_page == 1) ? '<strong>1</strong>' : '<a href="' . $base_url . '">1</a>';

		if ($total_pages > 5)
		{
			$start_cnt = min(max(1, $on_page - 4), $total_pages - 5);
			$end_cnt = max(min($total_pages, $on_page + 4), 6);

			$page_string .= ($start_cnt > 1) ? ' ... ' : $seperator;

			for ($i = $start_cnt + 1; $i < $end_cnt; $i++)
			{
				$page_string .= ($i == $on_page) ? '<strong>' . $i . '</strong>' : '<a href="' . $base_url . "{$url_delim}" . $tpl_prefix  . "=" . (($i - 1) * $per_page) . '">' . $i . '</a>';
				if ($i < $end_cnt - 1)
				{
					$page_string .= $seperator;
				}
			}

			$page_string .= ($end_cnt < $total_pages) ? ' ... ' : $seperator;
		}
		else
		{
			$page_string .= $seperator;

			for ($i = 2; $i < $total_pages; $i++)
			{
				$page_string .= ($i == $on_page) ? '<strong>' . $i . '</strong>' : '<a href="' . $base_url . "{$url_delim}" . $tpl_prefix  . "=" . (($i - 1) * $per_page) . '">' . $i . '</a>';
				if ($i < $total_pages)
				{
					$page_string .= $seperator;
				}
			}
		}

		$page_string .= ($on_page == $total_pages) ? '<strong>' . $total_pages . '</strong>' : '<a href="' . $base_url . "{$url_delim}" . $tpl_prefix  . "=" . (($total_pages - 1) * $per_page) . '">' . $total_pages . '</a>';
		if ($add_prevnext_text)
		{
			if ($on_page != 1)
			{
				$page_string = '<a href="' . $base_url . "{$url_delim}" . $tpl_prefix  . "=" . (($on_page - 2) * $per_page) . '">' . $user->lang['PREVIOUS'] . '</a>&nbsp;&nbsp;' . $page_string;
			}

			if ($on_page != $total_pages)
			{
				$page_string .= '&nbsp;&nbsp;<a href="' . $base_url . "{$url_delim}" . $tpl_prefix  . "=" . ($on_page * $per_page) . '">' . $user->lang['NEXT'] . '</a>';
			}
		}

		$template->assign_vars(array(
				$tpl_prefix . 'BASE_URL'		=> $base_url,
				'A_' . $tpl_prefix . 'BASE_URL'	=> addslashes($base_url),
				$tpl_prefix . 'PER_PAGE'		=> $per_page,

				$tpl_prefix . 'PREVIOUS_PAGE'	=> ($on_page == 1) ? '' : $base_url . "{$url_delim}" . $tpl_prefix  . "=" . (($on_page - 2) * $per_page),
				$tpl_prefix . 'NEXT_PAGE'		=> ($on_page == $total_pages) ? '' : $base_url . "{$url_delim}" . $tpl_prefix  . "=" . ($on_page * $per_page),
				$tpl_prefix . 'TOTAL_PAGES'		=> $total_pages,
		));

		return $page_string;
	}

    /**
     * Switch array order
     * Switches the sorting order of a supplied array, prerserving key values
     * The array is in the format [number][0/1] (0 = the default, 1 = the opposite)
     * Returns an array containing the code to use in an SQL query and the code to
     * use to pass the sort value through the URI.  URI is in the format
     * checks that the 2nd element is either 0 or 1
     *
     * @param array $sort_order
     * @param string $arg
     * @param int $forcedorder
     * @return mixed
     */
    public final function switch_order($sort_order, $arg = URI_ORDER, $forcedorder=0)
	{
		$uri_order = ( isset($_GET[$arg]) ) ? request_var($arg, 0.0) : '0.0';

		$uri_order = explode('.', $uri_order);

		$element1 = ( isset($uri_order[0]) ) ? $uri_order[0] : 0;
		$element2 = ( isset($uri_order[1]) ) ? $uri_order[1] : 0;
		// check if correct input
		if ( $element2 != 1 )
		{
			$element2 = 0;
		}

		foreach((array) $sort_order as $key => $value )
		{
			if ( $element1 == $key )
			{
				$uri_element2 = ( $element2 == 0 ) ? 1 : 0;
			}
			else
			{
				$uri_element2 = 0;
			}
			$current_order['uri'][$key] = $key . '.' . $uri_element2;
		}

		$current_order['uri']['current'] = $element1.'.'.$element2;
		$current_order['sql'] = $sort_order[$element1][$element2];

		return $current_order;
	}


	/**
	 * remove end of a string
	 * @param string $string
	 * @param string $stringToRemove
	 * @return string
	 */
	public function removeFromEnd($string, $stringToRemove)
	{
		$stringToRemoveLen = strlen($stringToRemove);
		$stringLen = strlen($string);
		$pos = $stringLen - $stringToRemoveLen;
		$out = substr($string, 0, $pos);
		return $out;
	}

	/**
	 * makes an entry in the bbdkp log table
	 * log_action is an xml containing the log
	 *
	 * log_id	int(11)		UNSIGNED	No		auto_increment
	 * log_date	int(11)			No	0
	 * log_type	varchar(255)	utf8_bin		No
	 * log_action	text	utf8_bin		No
	 * log_ipaddress	varchar(15)	utf8_bin		No
	 * log_sid	varchar(32)	utf8_bin		No
	 * log_result	varchar(255)	utf8_bin		No
	 * log_userid	mediumint(8)	UNSIGNED	No	0
	 *
	 * @param array $values
	 */
	public final function log_insert($values = array())
	{
		// log
		$logs = \bbdkp\admin\log::Instance();
		return $logs->log_insert($values);

	}

}

?>
