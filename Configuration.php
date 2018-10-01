<?php
/**
 * @copyright  2018 Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @license    Apache License 2.0; see LICENSE
 */

/**
 * Allows configuring the application.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class Configuration
{
	/**
	 * Path to the file where the configuration is stored.
	 *
	 * @var string
	 */
	private static $configPath = 'configuration.json';

	/**
	 * Singleton instance.
	 *
	 * @var Configuration
	 */
	private static $instance;

	/**
	 * Holds the configuration after loaded.
	 *
	 * @var array
	 */
	private $config;

	/**
	 * Creates a singleton instance if necessary and returns the instance.
	 *
	 * @return Configuration
	 */
	public static function getInstance()
	{
		if (!isset(self::$instance))
		{
			self::$instance = new Configuration;
		}

		return self::$instance;
	}

	/**
	 * Configuration constructor.
	 */
	private function __construct()
	{
		$this->load();
	}

	/**
	 * Saves the configuration to a file.
	 *
	 * @return void
	 */
	public function save()
	{
		$json = json_encode($this->config, JSON_PRETTY_PRINT);

		if (!$h = fopen(self::$configPath, 'w'))
		{
			die('Could not open file ' . self::$configPath);
		}

		if (!fwrite($h, $json))
		{
			die('Could not write file ' . self::$configPath);
		}

		fclose($h);
	}

	/**
	 * Loads the configuration from a file.
	 *
	 * @return void
	 */
	public function load()
	{
		if (file_exists(self::$configPath))
		{
			$this->config = json_decode(file_get_contents(self::$configPath), true);
		}
		else
		{
			$this->config = array();
		}
	}

	/**
	 * Hides a given language string from being displayed as unused or missing.
	 *
	 * @param   string  $extension  Extension identifier.
	 * @param   string  $string     The language string to hide.
	 * @param   string  $scope      Either 'site' or 'admin'. Defaults to 'site'.
	 *
	 * @return void
	 */
	public function hideString($extension, $string, $scope = 'site')
	{
		if (!isset($this->config[$extension]))
		{
			$this->config[$extension] = array();
		}

		if (!isset($this->config[$extension]['hidden']))
		{
			$this->config[$extension]['hidden'] = array();
		}

		if (!isset($this->config[$extension]['hidden'][$scope]))
		{
			$this->config[$extension]['hidden'][$scope] = array();
		}

		if (!in_array($string, $this->config[$extension]['hidden'][$scope]))
		{
			array_push($this->config[$extension]['hidden'][$scope], $string);
		}
	}

	/**
	 * Gets all hidden strings for a given extension and scope.
	 *
	 * @param   string  $extension  Extension identifier.
	 * @param   string  $scope      Either 'site' or 'admin'. Defaults to 'site'.
	 *
	 * @return array
	 */
	public function getHiddenStrings($extension, $scope = 'site')
	{
		if (isset($this->config[$extension]) && isset($this->config[$extension]['hidden']) && isset($this->config[$extension]['hidden'][$scope]))
		{
			return $this->config[$extension]['hidden'][$scope];
		}

		return array();
	}
}
