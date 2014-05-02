<?php
/**
*
* @package phpbb
* @version $Id$
* @copyright (c) 2009 phpBB Group
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
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

/**
* Replacement for a superglobal (like $_GET or $_POST) which calls
* trigger_error on any operation, overloads the [] operator using SPL.
*
* @package core
* @author naderman
*/

class deactivated_super_global implements \ArrayAccess, \Countable, \IteratorAggregate
{
	/**
	* @var string Holds the error message
	*/
	private $message;

	/**
	* Constructor generates an error message fitting the super global to be used within the other functions.
	*
	* @param string	$name	Name of the super global this is a replacement for - e.g. '_GET'
	*/
	public function __construct($name)
	{
		$this->message = 'Illegal use of $' . $name . '. You must use the request class or request_var() to
		 access input data. Found in %s on line %d. This error message was generated xxxxxxxxxxxxxx';
	}

	/**
	* Calls trigger_error with the file and line number the super global was used in
	*/
	private function error()
	{
		$file = '';
		$line = 0;

		$backtrace = debug_backtrace();
		if (isset($backtrace[1]))
		{
			$file = $backtrace[1]['file'];
			$line = $backtrace[1]['line'];
		}
		trigger_error(sprintf($this->message, $file, $line), E_USER_ERROR);
	}

	/**#@+
	* Part of the ArrayAccess implementation, will always result in a FATAL error
	*/
	public function offsetExists($offset)
	{
		$this->error();
	}

	public function offsetGet($offset)
	{
		$this->error();
	}

	public function offsetSet($offset, $value)
	{
		$this->error();
	}

	public function offsetUnset($offset)
	{
		$this->error();
	}
	/**#@-*/

	/**
	* Part of the Countable implementation, will always result in a FATAL error
	*/
	public function count()
	{
		$this->error();
	}

	/**
	* Part of the Traversable/IteratorAggregate implementation, will always result in a FATAL error
	*/
	public function getIterator()
	{
		$this->error();
	}
}

/**
* All application input is accessed through this class.
*
* It provides a method to disable access to input data through super globals.
* This should force MOD authors to read about data validation.
*
* @package core
* @author naderman
*/
class krequest
{
	/**#@+
	* Constant identifying the super global
	*/
	const POST = 0;
	const GET = 1;
	const REQUEST = 2;
	const COOKIE = 3;
	/**#@-*/

	/**
	* @var krequest Global instance of this class
	*/
	protected static $instance = null;

	/**
	* @var array The names of super global variables that this class should protect if super globals are disabled.
	*/
	protected static $super_globals = array(krequest::POST => '_POST', krequest::GET => '_GET', krequest::REQUEST => '_REQUEST', krequest::COOKIE => '_COOKIE');

	/**
	* @var array Stores original contents of $_REQUEST array.
	*/
	protected $original_request = null;

	/**
	* @var
	*/
	protected $super_globals_disabled = false;

	/**
	* @var array An associative array that has the value of super global constants as keys and holds their data as values.
	*/
	protected $input;

	/**
	* @var string Whether slashes need to be stripped from input
	*/
	protected $strip;

	/**
	* Either returns an existing instance of this class or creates a new one.
	*
	* @return	krequest	An instance of this class
	*/
	public static function get_instance()
	{
		if (!self::$instance)
		{
			self::$instance = new krequest();
		}

		return self::$instance;
	}

	/**
	* Deletes the internal instance of this class and forces the creation
	* of a new one on the next {@link get_instance get_instance} call.
	*/
	public static function reset()
	{
		self::$instance = null;
	}

	/**
	* Initialises the request class, that means it stores all input data in {@link $input input}
	* and then calls {@link deactivated_super_global deactivated_super_global}
	*/
	public function __construct()
	{
		if (version_compare(PHP_VERSION, '6.0.0-dev', '>='))
		{
			$this->strip = false;
		}
		else
		{
			$this->strip = (@get_magic_quotes_gpc()) ? true : false;
		}

		foreach (self::$super_globals as $const => $super_global)
		{
			$this->input[$const] = isset($GLOBALS[$super_global]) ? $GLOBALS[$super_global] : array();
		}

		// simulate request_order = GP
		$this->original_request = $this->input[krequest::REQUEST];
		$this->input[krequest::REQUEST] = $this->input[krequest::POST] + $this->input[krequest::GET];

		$this->disable_super_globals();
	}

	/**
	* Getter for $super_globals_disabled
	*
	* @return bool	Whether super globals are disabled or not.
	*/
	public function super_globals_disabled()
	{
		return $this->super_globals_disabled;
	}

	/**
	* Disables access of super globals specified in $super_globals.
	* This is achieved by overwriting the super globals with instances of {@link deactivated_super_global deactivated_super_global}
	*/
	public function disable_super_globals()
	{
		if (!$this->super_globals_disabled)
		{
			foreach (self::$super_globals as $const => $super_global)
			{
				unset($GLOBALS[$super_global]);
				$GLOBALS[$super_global] = new deactivated_super_global($super_global);
			}

			$this->super_globals_disabled = true;
		}
	}

	/**
	* Enables access of super globals specified in $super_globals if they were disabled by {@link disable_super_globals disable_super_globals}.
	* This is achieved by making the super globals point to the data stored within this class in {@link $input input}.
	*/
	public function enable_super_globals()
	{
		if ($this->super_globals_disabled)
		{
			foreach (self::$super_globals as $const => $super_global)
			{
				$GLOBALS[$super_global] = $this->input[$const];
			}

			$GLOBALS['_REQUEST'] = $this->original_request;

			$this->super_globals_disabled = false;
		}
	}

	/**
	* Recursively applies addslashes to a variable.
	*
	* @param mixed	&$var	Variable passed by reference to which slashes will be added.
	*/
	public static function addslashes_recursively(&$var)
	{
		if (is_string($var))
		{
			$var = addslashes($var);
		}
		else if (is_array($var))
		{
			$var_copy = $var;
			$var = array();
			foreach ($var_copy as $key => $value)
			{
				if (is_string($key))
				{
					$key = addslashes($key);
				}
				$var[$key] = $value;

				self::addslashes_recursively($var[$key]);
			}
		}
	}

    /**
     * This function allows overwriting or setting a value in one of the super global arrays.
     *
     * Changes which are performed on the super globals directly will not have any effect on the results of
     * other methods this class provides. Using this function should be avoided if possible! It will
     * consume twice the the amount of memory of the value
     *
     * @param string $var_name The name of the variable that shall be overwritten
     * @param mixed $value The value which the variable shall contain.
     *                            If this is null the variable will be unset.
     * @param int $super_global
     */
	public function overwrite($var_name, $value, $super_global = krequest::REQUEST)
	{
		if (!isset(self::$super_globals[$super_global]))
		{
			return;
		}

		if ($this->strip)
		{
			self::addslashes_recursively($value);
		}

		// setting to null means unsetting
		if ($value === null)
		{
			unset($this->input[$super_global][$var_name]);
			if (!$this->super_globals_disabled())
			{
				unset($GLOBALS[self::$super_globals[$super_global]][$var_name]);
			}
		}
		else
		{
			$this->input[$super_global][$var_name] = $value;
			if (!self::super_globals_disabled())
			{
				$GLOBALS[self::$super_globals[$super_global]][$var_name] = $value;
			}
		}

		if (!self::super_globals_disabled())
		{
			unset($GLOBALS[self::$super_globals[$super_global]][$var_name]);
			$GLOBALS[self::$super_globals[$super_global]][$var_name] = $value;
		}
	}

	/**
	* Set variable $result to a particular type.
	*
	* @param mixed	&$result	The variable to fill
	* @param mixed	$var		The contents to fill with
	* @param mixed	$type		The variable type. Will be used with {@link settype()}
	* @param bool	$multibyte	Indicates whether string values may contain UTF-8 characters.
	* 							Default is false, causing all bytes outside the ASCII range (0-127) to be replaced with question marks.
	*/
	public function set_var(&$result, $var, $type, $multibyte = false)
	{
		settype($var, $type);
		$result = $var;

		if ($type == 'string')
		{
			$result = trim(htmlspecialchars(str_replace(array("\r\n", "\r", "\0"), array("\n", "\n", ''), $result), ENT_COMPAT, 'UTF-8'));

			if (!empty($result))
			{
				// Make sure multibyte characters are wellformed
				if ($multibyte)
				{
					if (!preg_match('/^./u', $result))
					{
						$result = '';
					}
				}
				else
				{
					// no multibyte, allow only ASCII (0-127)
					$result = preg_replace('/[\x80-\xFF]/', '?', $result);
				}
			}

			$result = ($this->strip) ? stripslashes($result) : $result;
		}
	}

	/**
	* Recursively sets a variable to a given type using {@link set_var set_var}
	* This function is only used from within {@link krequest::variable krequest::variable}.
	*
	* @param string	$var		The value which shall be sanitised (passed by reference).
	* @param mixed	$default	Specifies the type $var shall have.
	* 							If it is an array and $var is not one, then an empty array is returned.
	* 							Otherwise var is cast to the same type, and if $default is an array all keys and values are cast recursively using this function too.
	* @param bool	$multibyte	Indicates whether string values may contain UTF-8 characters.
	* 							Default is false, causing all bytes outside the ASCII range (0-127) to be replaced with question marks.
	*/
	protected function recursive_set_var(&$var, $default, $multibyte)
	{
		if (is_array($var) !== is_array($default))
		{
			$var = (is_array($default)) ? array() : $default;
			return;
		}

		if (!is_array($default))
		{
			$type = gettype($default);
			$this->set_var($var, $var, $type, $multibyte);
		}
		else
		{
			// make sure there is at least one key/value pair to use get the
			// types from
			if (!sizeof($default))
			{
				$var = array();
				return;
			}

			list($default_key, $default_value) = each($default);
			$value_type = gettype($default_value);
			$key_type = gettype($default_key);

			$_var = $var;
			$var = array();

			foreach ($_var as $k => $v)
			{
				$this->set_var($k, $k, $key_type, $multibyte);

				$this->recursive_set_var($v, $default_value, $multibyte);
				$this->set_var($var[$k], $v, $value_type, $multibyte);
			}
		}
	}

    /**
     * Central type safe input handling function.
     * All variables in GET or POST requests should be retrieved through this function to maximise security.
     *
     * @param string|array $var_name The form variable's name from which data shall be retrieved.
     *                                    If the value is an array this may be an array of indizes which will give
     *                                    direct access to a value at any depth. E.g. if the value of "var" is array(1 => "a")
     *                                    then specifying array("var", 1) as the name will return "a".
     * @param mixed $default A default value that is returned if the variable was not set.
     *                                    This function will always return a value of the same type as the default.
     * @param bool $multibyte If $default is a string this paramater has to be true if the variable may contain any UTF-8 characters
     *                                    Default is false, causing all bytes outside the ASCII range (0-127) to be replaced with question marks
     * @param int $super_global    Specifies which super global should be used
     *
     * @return mixed    The value of $_REQUEST[$var_name] run through {@link set_var set_var} to ensure that the type is the
     *                the same as that of $default. If the variable is not set $default is returned.
     */
	public function variable($var_name, $default, $multibyte = false, $super_global = krequest::REQUEST)
	{
		$path = false;

		// deep direct access to multi dimensional arrays
		if (is_array($var_name))
		{
			$path = $var_name;
			// make sure at least the variable name is specified
			if (!sizeof($path))
			{
				return (is_array($default)) ? array() : $default;
			}
			// the variable name is the first element on the path
			$var_name = array_shift($path);
		}

		if (!isset($this->input[$super_global][$var_name]))
		{
			return (is_array($default)) ? array() : $default;
		}
		$var = $this->input[$super_global][$var_name];

		if ($path)
		{
			// walk through the array structure and find the element we are looking for
			foreach ($path as $key)
			{
				if (is_array($var) && isset($var[$key]))
				{
					$var = $var[$key];
				}
				else
				{
					return (is_array($default)) ? array() : $default;
				}
			}
		}

		self::recursive_set_var($var, $default, $multibyte);

		return $var;
	}

	/**
	* Checks whether a certain variable was sent via POST.
	* To make sure that a request was sent using POST you should call this function
	* on at least one variable.
	*
	* @param string	$name	The name of the form variable which should have a
	*						_p suffix to indicate the check in the code that creates the form too.
	*
	* @return bool	True if the variable was set in a POST request, false otherwise.
	*/
	public function is_set_post($name)
	{
		return $this->is_set($name, krequest::POST);
	}

    /**
     * Checks whether a certain variable is set in one of the super global
     * arrays.
     *
     * @param string $var Name of the variable
     * @param int   $super_global  Specifies the super global which shall be checked
     *
     * @return bool    True if the variable was sent as input
     */
	public function is_set($var, $super_global = krequest::REQUEST)
	{
		return isset($this->input[$super_global][$var]);
	}

    /**
     * Returns all variable names for a given super global
     *
     * @param int  $super_global   The super global from which names shall be taken
     *
     * @return array    All variable names that are set for the super global.
     *                Pay attention when using these, they are unsanitised!
     */
	public function variable_names($super_global = krequest::REQUEST)
	{
		if (!isset($this->input[$super_global]))
		{
			return array();
		}

		return array_keys($this->input[$super_global]);
	}
}
