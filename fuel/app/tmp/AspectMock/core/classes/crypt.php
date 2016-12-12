<?php
/**
 * Part of the Fuel framework.
 *
 * @package    Fuel
 * @version    1.8
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2016 Fuel Development Team
 * @link       http://fuelphp.com
 */

namespace Fuel\Core;

use \phpseclib\Crypt\AES;
use \phpseclib\Crypt\Hash;

class Crypt
{
	/**
	 * Crypto default configuration
	 *
	 * @var	array
	 */
	protected static $defaults = array();

	/**
	 * Defined Crypto instances
	 *
	 * @var	array
	 */
	protected static $_instances = array();

	/**
	 * initialisation and auto configuration
	 */
	public static function _init()
	{ if (($__am_res = __amock_before(get_called_class(), __CLASS__, __FUNCTION__, array(), true)) !== __AM_CONTINUE__) return $__am_res; 
		// load the config
		\Config::load('crypt', true);
		static::$defaults = \Config::get('crypt', array());

		// generate random crypto keys if we don't have them or they are incorrect length
		$update = false;
		foreach(array('crypto_key', 'crypto_iv', 'crypto_hmac') as $key)
		{
			if ( empty(static::$defaults[$key]) or (strlen(static::$defaults[$key]) % 4) != 0)
			{
				$crypto = '';
				for ($i = 0; $i < 8; $i++)
				{
					$crypto .= static::safe_b64encode(pack('n', mt_rand(0, 0xFFFF)));
				}
				static::$defaults[$key] = $crypto;
				$update = true;
			}
		}

		// update the config if needed
		if ($update === true)
		{
			try
			{
				\Config::save('crypt', static::$defaults);
			}
			catch (\FileAccessException $e)
			{
				// failed to write the config file, inform the user
				echo \View::forge('errors/crypt_keys', array(
					'keys' => static::$defaults,
				));
				die();
			}
		}
	}

	/**
	 * forge
	 *
	 * create a new named instance
	 *
	 * @param	string	$name	instance name
	 * @param	array	$config	optional runtime configuration
	 * @return  \Crypt
	 */
	public static function forge($name = '__default__', array $config = array())
	{ if (($__am_res = __amock_before(get_called_class(), __CLASS__, __FUNCTION__, array($name, $config), true)) !== __AM_CONTINUE__) return $__am_res; 
		if ( ! array_key_exists($name, static::$_instances))
		{
			static::$_instances[$name] = new static($config);
		}

		return static::$_instances[$name];
	}

	/**
	 * Return a specific named instance
	 *
	 * @param	string  $name	instance name
	 * @return  mixed   Crypt if the instance exists, false if not
	 */
	public static function instance($name = '__default__')
	{ if (($__am_res = __amock_before(get_called_class(), __CLASS__, __FUNCTION__, array($name), true)) !== __AM_CONTINUE__) return $__am_res; 
		if ( ! array_key_exists($name, static::$_instances))
		{
			return static::forge($name);
		}

		return static::$_instances[$name];
	}

	/**
	 * capture static calls to methods
	 *
	 * @param	mixed	$method
	 * @param	array	$args	The arguments will passed to $method.
	 * @return	mixed	return value of $method.
	 */
	public static function __callstatic($method, $args)
	{ if (($__am_res = __amock_before(get_called_class(), __CLASS__, __FUNCTION__, array($method, $args), true)) !== __AM_CONTINUE__) return $__am_res; 
		// static method calls are called on the default instance
		return call_user_func_array(array(static::instance(), $method), $args);
	}

	// --------------------------------------------------------------------

	/**
	 * generate a URI safe base64 encoded string
	 *
	 * @param	string	$value
	 * @return	string
	 */
	protected static function safe_b64encode($value)
	{ if (($__am_res = __amock_before(get_called_class(), __CLASS__, __FUNCTION__, array($value), true)) !== __AM_CONTINUE__) return $__am_res; 
		$data = base64_encode($value);
		$data = str_replace(array('+', '/', '='), array('-', '_', ''), $data);
		return $data;
	}

	/**
	 * decode a URI safe base64 encoded string
	 *
	 * @param	string	$value
	 * @return	string
	 */
	protected static function safe_b64decode($value)
	{ if (($__am_res = __amock_before(get_called_class(), __CLASS__, __FUNCTION__, array($value), true)) !== __AM_CONTINUE__) return $__am_res; 
		$data = str_replace(array('-', '_'), array('+', '/'), $value);
		$mod4 = strlen($data) % 4;
		if ($mod4)
		{
			$data .= substr('====', $mod4);
		}
		return base64_decode($data);
	}

	/**
	 * compare two strings in a timing-insensitive way to prevent time-based attacks
	 *
	 * @param	string	$a
	 * @param	string	$b
	 * @return	bool
	 */
	protected static function secure_compare($a, $b)
	{ if (($__am_res = __amock_before(get_called_class(), __CLASS__, __FUNCTION__, array($a, $b), true)) !== __AM_CONTINUE__) return $__am_res; 
		// make sure we're only comparing equal length strings
		if (strlen($a) !== strlen($b))
		{
			return false;
		}

		// and that all comparisons take equal time
		$result = 0;
		for ($i = 0; $i < strlen($a); $i++)
		{
			$result |= ord($a[$i]) ^ ord($b[$i]);
		}
		return $result === 0;
	}

	// --------------------------------------------------------------------

	/**
	 * Crypto object used to encrypt/decrypt
	 *
	 * @var	object
	 */
	protected $crypter = null;

	/**
	 * Hash object used to generate hashes
	 *
	 * @var	object
	 */
	protected $hasher = null;

	/**
	 * Crypto configuration
	 *
	 * @var	array
	 */
	protected $config = array();

	/**
	 * Class constructor
	 *
	 * @param	array    $config
	 */
	public function __construct(array $config = array())
	{ if (($__am_res = __amock_before($this, __CLASS__, __FUNCTION__, array($config), false)) !== __AM_CONTINUE__) return $__am_res; 
		$this->config = array_merge(static::$defaults, $config);

		$this->crypter = new AES();
		$this->hasher = new Hash('sha256');

		$this->crypter->enableContinuousBuffer();
		$this->hasher->setKey(static::safe_b64decode($this->config['crypto_hmac']));
	}

	/**
	 * encrypt a string value, optionally with a custom key
	 *
	 * @param	string		$value		value to encrypt
	 * @param	string|bool	$key		optional custom key to be used for this encryption
	 * @param	int|bool	$keylength	optional key length
	 * @return	string	encrypted value
	 */
	protected function encode($value, $key = false, $keylength = false)
	{ if (($__am_res = __amock_before($this, __CLASS__, __FUNCTION__, array($value, $key, $keylength), false)) !== __AM_CONTINUE__) return $__am_res; 
		if ( ! $key)
		{
			$key = static::safe_b64decode($this->config['crypto_key']);
			// Used for backwards compatibility with encrypted data prior
			// to FuelPHP 1.7.2, when phpseclib was updated, and became a
			// bit smarter about figuring out key lengths.
			$keylength = 128;
		}

		if ($keylength)
		{
			$this->crypter->setKeyLength($keylength);
		}

		$this->crypter->setKey($key);
		$this->crypter->setIV(static::safe_b64decode($this->config['crypto_iv']));

		$value = $this->crypter->encrypt($value);
		return static::safe_b64encode($this->add_hmac($value));

	}

	/**
	 * capture calls to normal methods
	 *
	 * @param	mixed	$method
	 * @param	array	$args	The arguments will passed to $method.
	 * @return	mixed	return value of $method.
	 * @throws	\ErrorException
	 */
	public function __call($method, $args)
	{ if (($__am_res = __amock_before($this, __CLASS__, __FUNCTION__, array($method, $args), false)) !== __AM_CONTINUE__) return $__am_res; 
		// validate the method called
		if ( ! in_array($method, array('encode', 'decode')))
		{
			throw new \ErrorException('Call to undefined method '.__CLASS__.'::'.$method.'()', E_ERROR, 0, '/Users/masahiro/program/sampler/fuel/core/classes/crypt.php', __LINE__);
		}

		// static method calls are called on the default instance
		return call_user_func_array(array($this, $method), $args);
	}

	/**
	 * decrypt a string value, optionally with a custom key
	 *
	 * @param	string		$value		value to decrypt
	 * @param	string|bool	$key		optional custom key to be used for this encryption
	 * @param	int|bool	$keylength	optional key length
	 * @access	public
	 * @return	string	encrypted value
	 */
	protected function decode($value, $key = false, $keylength = false)
	{ if (($__am_res = __amock_before($this, __CLASS__, __FUNCTION__, array($value, $key, $keylength), false)) !== __AM_CONTINUE__) return $__am_res; 
		if ( ! $key)
		{
			$key = static::safe_b64decode($this->config['crypto_key']);
			// Used for backwards compatibility with encrypted data prior
			// to FuelPHP 1.7.2, when phpseclib was updated, and became a
			// bit smarter about figuring out key lengths.
			$keylength = 128;
		}

		if ($keylength)
		{
			$this->crypter->setKeyLength($keylength);
		}

		$this->crypter->setKey($key);
		$this->crypter->setIV(static::safe_b64decode($this->config['crypto_iv']));

		$value = static::safe_b64decode($value);
		if ($value = $this->validate_hmac($value))
		{
			return $this->crypter->decrypt($value);
		}
		else
		{
			return false;
		}
	}

	protected function add_hmac($value)
	{ if (($__am_res = __amock_before($this, __CLASS__, __FUNCTION__, array($value), false)) !== __AM_CONTINUE__) return $__am_res; 
		// calculate the hmac-sha256 hash of this value
		$hmac = static::safe_b64encode($this->hasher->hash($value));

		// append it and return the hmac protected string
		return $value.$hmac;
	}

	protected function validate_hmac($value)
	{ if (($__am_res = __amock_before($this, __CLASS__, __FUNCTION__, array($value), false)) !== __AM_CONTINUE__) return $__am_res; 
		// strip the hmac-sha256 hash from the value
		$hmac = substr($value, strlen($value)-43);

		// and remove it from the value
		$value = substr($value, 0, strlen($value)-43);

		// only return the value if it wasn't tampered with
		return (static::secure_compare(static::safe_b64encode($this->hasher->hash($value)), $hmac)) ? $value : false;
	}

}
