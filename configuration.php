<?php
/**
 * @copyright  2015 Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @license    Apache License 2.0; see LICENSE
 */
require_once 'TranslationScanner.php';
require_once 'Configuration.php';

if (!isset($_GET['extension']))
{
	header('location:index.php');
	die();
}

$extension = filter_var($_GET['extension'], FILTER_SANITIZE_STRING);
$scanner = new TranslationScanner($extension);
$config = Configuration::getInstance();

$title = 'Configuration for ' . $extension;
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
	<script>
		var extensionName = '<?php echo $_GET['extension']; ?>';
	</script>
</head>
<body>
<h1><?php echo $title; ?></h1>
<a href="index.php?extension=<?php echo $extension; ?>">Scan Results</a>
<h2>Hidden Language Strings</h2>
<?php
if ($scanner->isComponent())
{
	echo '<h3>Administrator</h3>';

	$hidden = $config->getHiddenStrings($extension, 'admin');

	if (empty($hidden))
	{
		echo '<p>No hidden language strings for this scope.</p>';
	}
	else
	{
		echo '<ul>';

		foreach ($hidden as $string)
		{
			echo '<li>'
				. '<button type="button" class="btn btn-show btn-nostyle" title="Show this entry" data-string="' . $string . '" data-scope="admin">'
				. '<span class="fa fa-eye"></span>'
				. '</button> '
				. $string
				. '</li>';
		}

		echo '</ul>';
	}

	echo '<h3>Site</h3>';
}

$hidden = $config->getHiddenStrings($extension, 'site');

if (empty($hidden))
{
	echo '<p>No hidden language strings for this scope.</p>';
}
else
{
	echo '<ul>';

	foreach ($hidden as $string)
	{
		echo '<li>'
			. '<button type="button" class="btn btn-show btn-nostyle" title="Show this entry" data-string="' . $string . '" data-scope="site">'
			. '<span class="fa fa-eye"></span>'
			. '</button> '
			. $string
			. '</li>';
	}

	echo '</ul>';
}
?>

</body>
</html>
