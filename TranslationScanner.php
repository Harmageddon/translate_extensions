<?php
/**
 * @copyright  2015 Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @license    Apache License 2.0; see LICENSE
 */

require_once 'Configuration.php';

/**
 * Scans file system for translation files.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class TranslationScanner
{
	/**
	 * @var   string  The default base directory containing the extensions.
	 */
	public static $basePath = __DIR__ . '/extensions/';

	private $component;
	private $path;

	protected $usedAdmin = array();
	protected $usedSite  = array();

	protected $unusedAdmin = array();
	protected $unusedSite  = array();

	protected $missingAdmin = array();
	protected $missingSite  = array();

	protected $extensionName;

	private $languageAdmin;

	private $languageSite;

	private $languages = array();

	private $error;

	/**
	 * TranslationScanner constructor.
	 *
	 * @param   string  $extensionName  Identifier of the extension to be scanned.
	 *
	 * @param   string  $path           Location of the extension folder (optional).
	 *                                  If left empty, the extension should be in a sub-folder of the $baseFolder.
	 */
	public function __construct($extensionName, $path = '')
	{
		$this->extensionName = $extensionName;

		if (empty($path))
		{
			$path = __DIR__ . '/extensions/' . $extensionName;
		}

		$this->path = $path;

		if (!is_dir($path))
		{
			return;
		}

		if ($installFile = $this->findInstallFile())
		{
			$xml = simplexml_load_file($path . '/' . $installFile);

			$this->component = ($xml['type'] == 'component');
		}
		else
		{
			$this->error = 'No XML installation file found.';
		}
	}

	/**
	 * Scans all code and language files inside the extension directory and analyzes the language strings.
	 *
	 * @return void
	 */
	public function scanAll()
	{
		if (!is_dir($this->path))
		{
			$this->error = 'Invalid path supplied: ' . $this->path . ' is not a directory.';
		}

		$config = Configuration::getInstance();

		if ($this->component)
		{
			$this->usedAdmin = $this->sortUnique(
				array_merge(
					$this->scanDirectory($this->path . '/admin', '.php'),
					$this->scanDirectory($this->path . '/admin', '.xml'),
					$this->scanDirectory($this->path . '/site', '.xml')
				)
			);
			$this->usedSite  = $this->sortUnique(
				array_merge(
					$this->scanDirectory($this->path . '/site', '.php'),
					$this->scanDirectory($this->path . '/admin/model/field', '.xml'),
					$this->scanDirectory($this->path . '/admin/model/form', '.xml'),
					$this->scanDirectory($this->path . '/admin/models/fields', '.xml'),
					$this->scanDirectory($this->path . '/admin/models/forms', '.xml')
				)
			);

			if (is_dir($this->path . '/admin/language'))
			{
				$this->languageAdmin = $this->scanLanguages($this->path . '/admin/language');
				$hidden = $config->getHiddenStrings($this->extensionName, 'admin');
				$this->compareStrings($this->languageAdmin, $this->usedAdmin, $this->missingAdmin, $this->unusedAdmin, $hidden);

				// TODO .sys.ini
			}
			else
			{
				// TODO
			}

			if (is_dir($this->path . '/site/language'))
			{
				$this->languageSite = $this->scanLanguages($this->path . '/site/language');
				$hidden = $config->getHiddenStrings($this->extensionName, 'site');
				$this->compareStrings($this->languageSite, $this->usedSite, $this->missingSite, $this->unusedSite, $hidden);
			}
			else
			{
				// TODO
			}
		}
		else
		{
			$this->usedSite  = $this->sortUnique(
				array_merge(
					$this->scanDirectory($this->path, '.php'),
					$this->scanDirectory($this->path, '.xml')
				)
			);

			$this->languageSite = $this->scanLanguages($this->path . '/language');
			$hidden = $config->getHiddenStrings($this->extensionName, 'site');
			$this->compareStrings($this->languageSite, $this->usedSite, $this->missingSite, $this->unusedSite, $hidden);
		}
	}

	/**
	 * Recursively searches for code files in a directory.
	 *
	 * @param   string  $path    Directory to be searched.
	 * @param   string  $ending  File ending of the files to be returned.
	 *
	 * @return   array  One-dimensional array containing the paths to all files that have been found.
	 */
	public function scanDirectory($path, $ending)
	{
		$strings = array();

		if (is_dir($path))
		{
			if ($dh = opendir($path))
			{
				while (($file = readdir($dh)) !== false)
				{
					if ($file === '.' || $file === '..')
					{
						continue;
					}

					$filePath = $path . '/' . $file;

					if (is_dir($filePath))
					{
						$strings = array_merge($strings, $this->scanDirectory($filePath, $ending));
					}
					elseif (substr($file, -strlen($ending)) === $ending)
					{
						$strings = array_merge($strings, $this->scanCodeFile($filePath));
					}
				}

				closedir($dh);
			}
		}

		return $strings;
	}

	/**
	 * Scans all language files inside a given path.
	 *
	 * @param   string   $path  Path to the directory containing the language files.
	 * @param   boolean  $sys   Whether to include .sys.ini files or not (default: false).
	 *
	 * @return  array  Associative array with the key being the subfolder of the language files (typically the language key)
	 *                 and the value being the language strings, grouped by file.
	 */
	public function scanLanguages($path, $sys = false)
	{
		$strings = array();

		if (!is_dir($path))
		{
			return array();
		}

		if ($dh = opendir($path))
		{
			while (($folder = readdir($dh)) !== false)
			{
				if ($folder === '.' || $folder === '..')
				{
					continue;
				}

				$folderPath = $path . '/' . $folder;

				if (is_dir($folderPath))
				{
					if (!in_array($folder, $this->languages))
					{
						array_push($this->languages, $folder);
					}

					$strings[$folder] = $this->scanLanguage($folderPath, $folder, $sys);
				}
			}

			closedir($dh);
		}

		return $strings;
	}

	/**
	 * Scans the language files of a single language.
	 *
	 * @param   string   $path      Path to the directory containing the language files.
	 * @param   string   $language  Language key.
	 * @param   boolean  $sys       Whether to include .sys.ini files or not (default: false).
	 *
	 * @return  array  Associative array with the key being the language file and the value an array of language strings.
	 */
	public function scanLanguage($path, $language, $sys = false)
	{
		$fileName = $language . '.' . $this->extensionName . ($sys ? '.sys' : '') . '.ini';

		$strings = array();

		if (!is_dir($path))
		{
			return array();
		}

		if ($dh = opendir($path))
		{
			while (($file = readdir($dh)) !== false)
			{
				if ($file === '.' || $file === '..')
				{
					continue;
				}

				$filePath = $path . '/' . $file;

				if (is_file($filePath) && $file === $fileName)
				{
					$strings[$file] = $this->scanLangFile($filePath);
				}
			}

			closedir($dh);
		}

		return $strings;
	}

	/**
	 * Searches for an installation XML manifest in the given directory.
	 *
	 * @param   string  $dir  Directory containing the XML manifest, relative to the base path of the extension (optional).
	 *
	 * @return null|string  Path to the installation manifest file, relative to the base path of the extension.
	 *                      Null if no installation manifest could be found.
	 */
	private function findInstallFile($dir = '')
	{
		$files = scandir($this->path . '/' . $dir);
		$split = explode('_', $this->extensionName);
		$pattern = $split[count($split) - 1] . '.xml';

		foreach ($files as $file)
		{
			if (substr($file, -strlen($pattern)) === $pattern)
			{
				if ($dir != '')
				{
					$file = $dir . '/' . $file;
				}

				return $file;
			}
		}

		if ($dir == '')
		{
			$file = $this->findInstallFile('admin');

			if ($file)
			{
				return $file;
			}

			$file = $this->findInstallFile('site');

			if ($file)
			{
				return $file;
			}
		}

		return null;
	}


	/**
	 * Scans a given code file for language strings.
	 * The language strings have to be in the format EXTENSION_IDENTIFIER_SOMETHING, e.g. COM_MYCOMPONENT_ERROR_NO_FILE.
	 *
	 * @param   string  $file  Path to the file.
	 *
	 * @return   array  All found language strings.
	 */
	private function scanCodeFile($file)
	{
		$strings = array();

		$content = file_get_contents($file);

		if (!$content)
		{
			return array();
		}

		$pattern = "/" . strtoupper($this->extensionName) . "[A-Z_0-9]*/";

		if (preg_match_all($pattern, $content, $matches))
		{
			$strings = $matches[0];
		}

		return $strings;
	}

	/**
	 * Sorts an array and removes duplicated values.
	 *
	 * @param   array  $array  The array to operate on.
	 *
	 * @return  array  A sorted and unique version of the array.
	 */
	private function sortUnique($array)
	{
		sort($array);

		return array_unique($array);
	}

	/**
	 * Scans a single language file for translated language strings.
	 *
	 * @param   string  $file  Path to the language file.
	 *
	 * @return  array  All translated language strings contained in the file.
	 */
	private function scanLangFile($file)
	{
		$strings = array();

		$handle = @fopen($file, "r");

		if (!$handle)
		{
			return array();
		}

		$pattern = "/^(" . strtoupper($this->extensionName) . "[A-Z_0-9]*)=/";

		while (($line = fgets($handle)) !== false)
		{
			if (preg_match($pattern, $line, $matches))
			{
				array_push($strings, $matches[1]);
			}
		}

		if (!feof($handle))
		{
			return array();
		}

		fclose($handle);

		return $strings;
	}

	/**
	 * Compares the language strings found in the code base with those translated in the language files.
	 *
	 * @param   array  $languageFiles  Associative array in the form:
	 *                                 array(
	 *                                      'en-GB' => array(
	 *                                         'en-GB.com_something.ini => array(language strings),
	 *                                          ...
	 *                                      ),
	 *                                      ...
	 *                                 )
	 * @param   array  $used           Array containing all strings used in the code base.
	 * @param   array  $missing        Reference to an array where the function inserts all untranslated strings.
	 * @param   array  $unused         Reference to an array where the function inserts all translated strings not used in the code base.
	 * @param   array  $hidden         Language strings that should be ignored.
	 *
	 * @return   void  Values are returned by reference in the parameters.
	 */
	private function compareStrings($languageFiles, $used, &$missing, &$unused, $hidden)
	{
		foreach ($languageFiles as $language => $files)
		{
			$missing[$language] = array();

			foreach ($files as $file => $defined)
			{
				foreach ($used as $string)
				{
					if (($index = array_search($string, $defined)) !== false)
					{
						unset($languageFiles[$language][$file][$index]);
					}
					elseif (!in_array($string, $hidden))
					{
						array_push($missing[$language], $string);
					}
				}

				// Remove hidden strings from defined strings.
				foreach ($hidden as $string)
				{
					if (($index = array_search($string, $defined)) !== false)
					{
						unset($languageFiles[$language][$file][$index]);
					}
				}
			}
		}

		$unused = $languageFiles;
	}

	/**
	 * @return array
	 */
	public function getUsedAdmin()
	{
		return $this->usedAdmin;
	}

	/**
	 * @return array
	 */
	public function getUsedSite()
	{
		return $this->usedSite;
	}

	/**
	 * @return array
	 */
	public function getUnusedAdmin()
	{
		return $this->unusedAdmin;
	}

	/**
	 * @return array
	 */
	public function getUnusedSite()
	{
		return $this->unusedSite;
	}

	/**
	 * @return array
	 */
	public function getMissingAdmin()
	{
		return $this->missingAdmin;
	}

	/**
	 * @return array
	 */
	public function getMissingSite()
	{
		return $this->missingSite;
	}

	/**
	 * @return mixed
	 */
	public function getExtensionName()
	{
		return $this->extensionName;
	}

	/**
	 * @return mixed
	 */
	public function getLanguageAdmin()
	{
		return $this->languageAdmin;
	}

	/**
	 * @return mixed
	 */
	public function getLanguageSite()
	{
		return $this->languageSite;
	}

	/**
	 * @return array
	 */
	public function getLanguages()
	{
		return $this->languages;
	}

	/**
	 * @return string
	 */
	public function getError()
	{
		return $this->error;
	}

	/**
	 * @return boolean
	 */
	public function isComponent()
	{
		return $this->component;
	}
}
