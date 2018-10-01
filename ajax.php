<?php
/**
 * @copyright  2018 Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @license    Apache License 2.0; see LICENSE
 */
require_once 'TranslationScanner.php';
require_once 'Configuration.php';

$action = filter_var($_GET['action'], FILTER_SANITIZE_STRING);
$config = Configuration::getInstance();

switch ($action)
{
	case 'hide':
		$extension = filter_var($_GET['extension'], FILTER_SANITIZE_STRING);
		$scope = filter_var($_GET['scope'], FILTER_SANITIZE_STRING);
		$value = filter_var($_GET['value'], FILTER_SANITIZE_STRING);
		$config->hideString($extension, $value, $scope);
		$config->save();
		break;
	case 'show':
		$extension = filter_var($_GET['extension'], FILTER_SANITIZE_STRING);
		$scope = filter_var($_GET['scope'], FILTER_SANITIZE_STRING);
		$value = filter_var($_GET['value'], FILTER_SANITIZE_STRING);
		$config->showString($extension, $value, $scope);
		$config->save();
		break;

	default:
		echo 'Invalid action.';
}
