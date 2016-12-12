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

/**
 * Event Class
 *
 * @package		Fuel
 * @category	Core
 * @author		Eric Barnes
 * @author		Harro "WanWizard" Verton
 */
abstract class Event
{
	/**
	 * @var  array  $instances  Event_Instance container
	 */
	protected static $instances = array();

	/**
	 * Event instance forge.
	 *
	 * @param   array   $events  events array
	 * @return  object  new Event_Instance instance
	 */
	public static function forge(array $events = array())
	{ if (($__am_res = __amock_before(get_called_class(), __CLASS__, __FUNCTION__, array($events), true)) !== __AM_CONTINUE__) return $__am_res; 
		return new \Event_Instance($events);
	}

	/**
	 * Multiton Event instance.
	 *
	 * @param   string  $name    instance name
	 * @param   array   $events  events array
	 * @return  object  Event_Instance object
	 */
	public static function instance($name = 'fuelphp', array $events = array())
	{ if (($__am_res = __amock_before(get_called_class(), __CLASS__, __FUNCTION__, array($name, $events), true)) !== __AM_CONTINUE__) return $__am_res; 
		if ( ! array_key_exists($name, static::$instances))
		{
			$events = array_merge(\Config::get('event.'.$name, array()), $events);
			$instance = static::forge($events);
			static::$instances[$name] = &$instance;
		}

		return static::$instances[$name];
	}

	/**
	 * Static call forwarder
	 *
	 * @param   string  $func  method name
	 * @param   array   $args  passed arguments
	 * @return  mixed
	 * @throws  \BadMethodCallException
	 */
	public static function __callStatic($func, $args)
	{ if (($__am_res = __amock_before(get_called_class(), __CLASS__, __FUNCTION__, array($func, $args), true)) !== __AM_CONTINUE__) return $__am_res; 
		$instance = static::instance();

		if (method_exists($instance, $func))
		{
			return call_fuel_func_array(array($instance, $func), $args);
		}

		throw new \BadMethodCallException('Call to undefined method: '.get_called_class().'::'.$func);
	}

	/**
	 * Load events config
	 */
	public static function _init()
	{ if (($__am_res = __amock_before(get_called_class(), __CLASS__, __FUNCTION__, array(), true)) !== __AM_CONTINUE__) return $__am_res; 
		\Config::load('event', true);
	}
}
