<?php
require_once 'TranslationScanner.php';

$scanner = new TranslationScanner('com_monitor');

$scanner->scanAll(__DIR__ . '/com_monitor');
?>
<!doctype html>
<html>
<head>
	<title>Scan results for <?php echo $scanner->getExtensionName(); ?></title>
</head>
<body>
<h1>Scan results for <?php echo $scanner->getExtensionName(); ?></h1>
<h2>Language files</h2>
<h3>Administrator</h3>
<table>
	<thead>
	<tr>
		<th>Language</th>
		<th>Administrator</th>
		<th>Site</th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ($scanner->getLanguages() as $language)
	{
		echo '<tr><td>' . $language . '</td><td>';
		echo '<ul>';

		foreach ($scanner->getLanguageAdmin()[$language] as $file => $strings)
		{
			echo '<li>' . $file . ' (' . count($strings) . ' strings)' . '</li>';
		}

		echo '</ul></td>';
		echo '<td><ul>';

		foreach ($scanner->getLanguageSite()[$language] as $file => $strings)
		{
			echo '<li>' . $file . ' (' . count($strings) . ' strings)' . '</li>';
		}

		echo '</ul></td>';
		echo '</tr>';
	}
	?>
	</tbody>
</table>

<h2>Missing strings</h2>
<table>
	<thead>
	<tr>
		<th>Language</th>
		<th>Administrator</th>
		<th>Site</th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ($scanner->getLanguages() as $language)
	{
		echo '<tr>';
		echo '<td>' . $language . '</td>';
		echo '<td>';

		if (empty($scanner->getMissingAdmin()[$language]))
		{
			echo '<p>Congratulations! No strings are missing.</p>';
		}
		else
		{
			echo '<ul>';

			foreach ($scanner->getMissingAdmin()[$language] as $string)
			{
				echo '<li>' . $string . '</li>';
			}

			echo '</ul>';
		}

		echo '</td><td>';

		if (empty($scanner->getMissingSite()[$language]))
		{
			echo '<p>Congratulations! No strings are missing.</p>';
		}
		else
		{
			echo '<ul>';

			foreach ($scanner->getMissingSite()[$language] as $string)
			{
				echo '<li>' . $string . '</li>';
			}

			echo '</ul>';
		}

		echo '</td></tr>';
	}
	?>
	</tbody>
</table>

<h2>Unused strings</h2>
<table>
	<thead>
	<tr>
		<th>Language</th>
		<th>Administrator</th>
		<th>Site</th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ($scanner->getLanguages() as $language)
	{
		echo '<tr>';
		echo '<td>' . $language . '</td>';
		echo '<td>';

		if (empty($scanner->getUnusedAdmin()[$language]))
		{
			echo '<p>Congratulations! No unused strings found.</p>';
		}
		else
		{
			echo '<ul>';

			foreach ($scanner->getUnusedAdmin()[$language] as $file => $strings)
			{
				echo '<li>' . $file . '<ul>';

				foreach ($strings as $string)
				{
					echo '<li>' . $string . '</li>';
				}

				echo '</ul></li>';
			}

			echo '</ul>';
		}

		echo '</td><td>';

		if (empty($scanner->getUnusedSite()[$language]))
		{
			echo '<p>Congratulations! No unused strings found.</p>';
		}
		else
		{
			echo '<ul>';

			foreach ($scanner->getUnusedSite()[$language] as $file => $strings)
			{
				echo '<li>' . $file . '<ul>';

				foreach ($strings as $string)
				{
					echo '<li>' . $string . '</li>';
				}

				echo '</ul></li>';
			}
		}

		echo '</td></tr>';
	}
	?>
	</tbody>
</table>
</body>
</html>
