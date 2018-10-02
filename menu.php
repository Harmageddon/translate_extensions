<?php
/**
 * @copyright  2018 Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @license    Apache License 2.0; see LICENSE
 */

if (!isset($extension))
{
	return;
}

$active = substr($_SERVER['PHP_SELF'], 1);
?>
<div class="menu">
	<a href="index.php?extension=<?php echo $extension; ?>" class="scan<?php if ($active === 'index.php') { echo ' active'; } ?>">
		Scan Results
	</a>
	<a href="configuration.php?extension=<?php echo $extension; ?>" class="configuration<?php if ($active === 'configuration.php') { echo ' active'; } ?>">
		Configuration
	</a>
</div>
