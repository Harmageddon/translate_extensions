<?php
/**
 * @copyright  2015 Constantin Romankiewicz
 * @license    Apache License 2.0; see LICENSE
 */

/**
 * Scans file system for translation files.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class TranslationScanner
{
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

	/**
	 * TranslationScanner constructor.
	 *
	 * @param $extensionName
	 */
	public function __construct($extensionName)
	{
		$this->extensionName = $extensionName;
	}

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

	private function scanCodeFile($file)
	{
		$strings = array();

		$content = file_get_contents($file);

		if (!$content)
		{
			return array();
		}

		$pattern = "/" . strtoupper($this->extensionName) . "_[A-Z_]+/";

		if (preg_match_all($pattern, $content, $matches))
		{
			$strings = $matches[0];
		}

		return $strings;
	}

	public function scanAll($basePath)
	{
		$this->usedAdmin = $this->sortUnique(
			array_merge(
				$this->scanDirectory($basePath . '/admin', '.php'),
				$this->scanDirectory($basePath . '/admin', '.xml'),
				$this->scanDirectory($basePath . '/site', '.xml')
			)
		);
		$this->usedSite = $this->sortUnique(
			array_merge(
				$this->scanDirectory($basePath . '/site', '.php'),
				$this->scanDirectory($basePath . '/admin/model/forms', '.xml')
			)
		);

		if (is_dir($basePath . '/admin/language'))
		{
			$this->languageAdmin = $this->scanLanguages($basePath . '/admin/language');

			// TODO .sys.ini
		}
		else
		{
			// TODO
		}

		if (is_dir($basePath . '/site/language'))
		{
			$this->languageSite = $this->scanLanguages($basePath . '/site/language');
		}
		else
		{
			// TODO
		}

		$this->compareStrings($this->languageAdmin, $this->usedAdmin, $this->missingAdmin, $this->unusedAdmin);
		$this->compareStrings($this->languageSite, $this->usedSite, $this->missingSite, $this->unusedSite);
	}

	private function sortUnique($array)
	{
		sort($array);

		return array_unique($array);
	}

	private function scanLangFile($file)
	{
		$strings = array();

		$handle = @fopen($file, "r");

		if (!$handle)
		{
			return array();
		}

		$pattern = "/^(" . strtoupper($this->extensionName) . "_[A-Z_]+)=/";

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
	 * @param $languageFiles
	 */
	private function compareStrings($languageFiles, $used, &$missing, &$unused)
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
					else
					{
						array_push($missing[$language], $string);
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
}
