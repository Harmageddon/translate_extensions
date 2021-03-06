<?php
/**
 * @copyright  2015 Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @license    Apache License 2.0; see LICENSE
 */
require_once 'TranslationScanner.php';

if (isset($_GET['extension']))
{
	$extension = filter_var($_GET['extension'], FILTER_SANITIZE_STRING);
	$scanner = new TranslationScanner($extension);
	$scanner->scanAll();

	$title = 'Scan Results for ' . $extension;

	$thead = '<thead><tr><th width="10%">Language</th>';

	if ($scanner->isComponent())
	{
		$thead .= '<th width="45%">Administrator</th><th width="45%">Site</th>';
	}
	else
	{
		$thead .= '<th width="90%">Site</th>';
	}

	$thead .= '</tr></thead>';
}
else
{
	$title = 'Translation Scanner';
}
?>
<!doctype html>
<html>
<head>
	<title><?php echo $title; ?></title>
	<script src="js/jquery-2.2.3.min.js"></script>
	<script src="js/script.js"></script>
	<link href="css/main.css" rel="stylesheet" type="text/css" />
	<link href="css/fontawesome.min.css" rel="stylesheet" type="text/css" />
	<link href="css/solid.min.css" rel="stylesheet" type="text/css" />
	<?php if (isset($extension)) : ?>
	<script>
		var extensionName = '<?php echo $extension; ?>';
	</script>
	<?php endif; ?>
</head>
<body>
<h1><?php echo $title; ?></h1>
<?php include 'menu.php'; ?>
<div class="container">
<?php if (isset($scanner)) :?>
	<?php if ($scanner->getError()) : ?>
		<h2>Error</h2>
		<p>
			<?php echo $scanner->getError(); ?>
		</p>
	<?php else : ?>

	<h2>Language Files</h2>
	<table>
		<?php echo $thead; ?>
		<tbody>
		<?php
		foreach ($scanner->getLanguages() as $language)
		{
			echo '<tr><td class="first">' . $language . '</td><td>';

			if ($scanner->isComponent())
			{
				if (is_array($scanner->getLanguageAdmin()[$language]))
				{
					$translated = 0;
					$missing = count($scanner->getMissingAdmin()[$language]);
					$unused = array_reduce(
						$scanner->getUnusedAdmin()[$language],
						function ($carry, $item)
						{
								return $carry + count($item);
						},
						0
					);

					echo '<ul>';

					foreach ($scanner->getLanguageAdmin()[$language] as $file => $strings)
					{
						$n = count($strings);
						$translated += $n;
						echo '<li>' . $file . ' (' . $n . ' strings)</li>';
					}

					echo '</ul>';

					$max = $translated + $missing;
					$translated -= $unused;
					$good = round($translated / $max * 100, 3);
					$bad = round($missing / $max * 100, 3);
					$warn = 100 - $good - $bad;

					echo '<div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="' . $max . '" aria-valuenow="' . $translated . '">'
						. '<div class="progress-bar success" style="width: ' . $good . '%" title="' . $translated . ' translated language strings"></div>'
						. '<div class="progress-bar error" style="width: ' . $bad . '%" title="' . $missing . ' missing language strings"></div>'
						. '<div class="progress-bar warning" style="width: ' . $warn . '%" title="' . $unused . ' unused language strings"></div>'
						. '</div>';
				}
				else
				{
					echo '<p>No language files found.</p>';
				}

				echo '</td><td>';
			}

			if (is_array($scanner->getLanguageSite()[$language]))
			{
				$translated = 0;
				$missing = count($scanner->getMissingSite()[$language]);
				$unused = array_reduce(
					$scanner->getUnusedSite()[$language],
					function ($carry, $item)
					{
						return $carry + count($item);
					},
					0
				);

				echo '<ul>';

				foreach ($scanner->getLanguageSite()[$language] as $file => $strings)
				{
					$n = count($strings);
					$translated += $n;
					echo '<li>' . $file . ' (' . $n . ' strings)</li>';
				}

				echo '</ul>';

				$max = $translated + $missing;
				$translated -= $unused;
				$good = round($translated / $max * 100, 3);
				$bad = round($missing / $max * 100, 3);
				$warn = 100 - $good - $bad;

				echo '<div class="progress" role="progressbar" aria-valuemin="0" aria-valuemax="' . $max . '" aria-valuenow="' . $translated . '">'
					. '<div class="progress-bar success" style="width: ' . $good . '%" title="' . $translated . ' translated language strings"></div>'
					. '<div class="progress-bar error" style="width: ' . $bad . '%" title="' . $missing . ' missing language strings"></div>'
					. '<div class="progress-bar warning" style="width: ' . $warn . '%" title="' . $unused . ' unused language strings"></div>'
					. '</div>';
			}
			else
			{
				echo '<p>No language files found.</p>';
			}


			echo '</td>';
			echo '</tr>';
		}
		?>
		</tbody>
	</table>

	<h2>Missing Strings</h2>
	<table>
		<?php echo $thead; ?>
		<tbody>
		<?php
		foreach ($scanner->getLanguages() as $language)
		{
			echo '<tr>';
			echo '<td class="first">' . $language . '</td>';
			echo '<td>';

			if ($scanner->isComponent())
			{
				if (!isset($scanner->getMissingAdmin()[$language]))
				{
					echo '<p>No language files found.</p>';
				}
				elseif (empty($scanner->getMissingAdmin()[$language]))
				{
					echo '<p>Congratulations! No strings are missing.</p>';
				}
				else
				{
					echo '<ul>';

					foreach ($scanner->getMissingAdmin()[$language] as $string)
					{
						echo '<li>'
							. '<button type="button" class="btn btn-hide btn-nostyle" title="Hide this entry" data-string="' . $string . '" data-scope="admin">'
							. '<span class="fa fa-eye-slash"></span>'
							. '</button> '
							. $string
							. '</li>';
					}

					echo '</ul>';

					$className = 'missing-admin-' . $language;

					echo '<button class="toggle btn" data-toggle="#' . $className . '" type="button">Show as language file</button>';
					echo '<textarea id="' . $className . '" class="hide" rows="8" cols="50">';

					foreach ($scanner->getMissingAdmin()[$language] as $string)
					{
						echo $string . "=\"\"\n";
					}

					echo '</textarea>';
				}

				echo '</td><td>';
			}

			if (!isset($scanner->getMissingSite()[$language]))
			{
				echo '<p>No language files found.</p>';
			}
			elseif (empty($scanner->getMissingSite()[$language]))
			{
				echo '<p>Congratulations! No strings are missing.</p>';
			}
			else
			{
				echo '<ul>';

				foreach ($scanner->getMissingSite()[$language] as $string)
				{
					echo '<li>'
						. '<button type="button" class="btn btn-hide btn-nostyle" title="Hide this entry" data-string="' . $string . '" data-scope="site">'
						. '<span class="fa fa-eye-slash"></span>'
						. '</button> '
						. $string
						. '</li>';
				}

				echo '</ul>';

				$className = 'missing-site-' . $language;

				echo '<button class="toggle btn" data-toggle="#' . $className . '" type="button">Show as language file</button>';
				echo '<textarea id="' . $className . '" class="hide" rows="8" cols="50">';

				foreach ($scanner->getMissingSite()[$language] as $string)
				{
					echo $string . "=\"\"\n";
				}

				echo '</textarea>';
			}

			echo '</td></tr>';
		}
		?>
		</tbody>
	</table>

	<h2>Unused Strings</h2>
	<table>
		<?php echo $thead; ?>
		<tbody>
		<?php
		foreach ($scanner->getLanguages() as $language)
		{
			echo '<tr>';
			echo '<td class="first">' . $language . '</td>';
			echo '<td>';

			if ($scanner->isComponent())
			{
				if (isset($scanner->getUnusedAdmin()[$language]))
				{
					echo '<ul>';

					foreach ($scanner->getUnusedAdmin()[$language] as $file => $strings)
					{
						echo '<li>' . $file . '<ul>';

						if (empty($strings))
						{
							echo '<li>Congratulations! No unused strings found.</li>';
						}

						foreach ($strings as $string)
						{
							echo '<li>'
								. '<button type="button" class="btn btn-hide btn-nostyle" title="Hide this entry" data-string="' . $string . '" data-scope="admin">'
								. '<span class="fa fa-eye-slash"></span>'
								. '</button> '
								. $string
								. '</li>';
						}

						echo '</ul></li>';
					}

					echo '</ul>';
				}

				echo '</td><td>';
			}
			else
			{
				echo '<p>No language files found.</p>';
			}

			if (isset($scanner->getUnusedSite()[$language]))
			{
				echo '<ul>';

				foreach ($scanner->getUnusedSite()[$language] as $file => $strings)
				{
					echo '<li>' . $file . '<ul>';

					if (empty($strings))
					{
						echo '<li>Congratulations! No unused strings found.</li>';
					}

					foreach ($strings as $string)
					{
						echo '<li>'
							. '<button type="button" class="btn btn-hide btn-nostyle" title="Hide this entry" data-string="' . $string . '" data-scope="site">'
							. '<span class="fa fa-eye-slash"></span>'
							. '</button> '
							. $string
							. '</li>';
					}

					echo '</ul></li>';
				}
			}
			else
			{
				echo '<p>No language files found.</p>';
			}

			echo '</td></tr>';
		}
		?>
		</tbody>
	</table>
	<?php endif; ?>
</div>
<div class="spacer"></div>
<div class="container">
<h2>Scan Another Extension</h2>
<?php endif; ?>
<form action="" method="get">
	<label for="extension">Extension Name:</label>
	<input type="text" name="extension" id="extension" list="extensions" />
	<datalist id="extensions">
<?php
$dir = __DIR__ . '/extensions/';
$files = scandir($dir);

foreach ($files as $file)
{
	if ($file != '.' && $file != '..' && is_dir($dir . $file))
	{
		echo '<option>' . $file . '</option>';
	}
}
?>
	</datalist>
	<button type="submit" class="btn primary">Load Translation Information</button>
</form>
</div>
</body>
</html>
