<?php
require_once 'TranslationScanner.php';

if (isset($_GET['extension']))
{
	$scanner = new TranslationScanner($_GET['extension']);
	$scanner->scanAll();

	$title = 'Scan results for ' . $_GET['extension'];
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
</head>
<body>
<h1><?php echo $title; ?></h1>
<?php if (isset($scanner)) :?>
<?php if ($scanner->getError()) : ?>
<h2>Error</h2>
<p>
	<?php echo $scanner->getError(); ?>
</p>
<?php else : ?>
<h2>Language files</h2>
<table>
	<thead>
	<tr>
		<th>Language</th>
		<?php if ($scanner->isComponent()) : ?>
			<th>Administrator</th>
		<?php endif; ?>
		<th>Site</th>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ($scanner->getLanguages() as $language)
	{
		echo '<tr><td>' . $language . '</td><td>';
		echo '<ul>';

		if ($scanner->isComponent())
		{
			foreach ($scanner->getLanguageAdmin()[$language] as $file => $strings)
			{
				echo '<li>' . $file . ' (' . count($strings) . ' strings)' . '</li>';
			}

			echo '</ul></td>';
			echo '<td><ul>';
		}

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
		<?php if ($scanner->isComponent()) : ?>
			<th>Administrator</th>
		<?php endif; ?>
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

		if ($scanner->isComponent())
		{
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
		}

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
		<?php if ($scanner->isComponent()) : ?>
			<th>Administrator</th>
		<?php endif; ?>
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

		if ($scanner->isComponent())
		{
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
		}

		else
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
<?php endif; ?>
<?php endif; ?>
<form action="" method="get">
	<label for="extension">Extension name:</label>
	<input type="text" name="extension" id="extension" />
	<button type="submit">Load translation information</button>
</form>
</body>
</html>
