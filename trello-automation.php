<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://https://www.linkedin.com/in/thehamzamehmood/
 * @since             1.0.0
 * @package           Trello_Automation
 *
 * @wordpress-plugin
 * Plugin Name:       Trello Automation
 * Plugin URI:        https://#
 * Description:       Automatically create Trello cards, send slack notification streamlining workflow.
 * Version:           1.3.4
 * Author:            That's So Fetch
 * Author URI:        https://thatssofetch.co/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       trello-automation
 * Domain Path:       /languages
 */


if (!defined('ABSPATH')) exit;


define('TRELLO_AUTOMATION_VERSION', '1.0.4');


function custom_log($message)
{
	$log_file = plugin_dir_path(__FILE__) . 'custom_log.txt';
	file_put_contents($log_file, $message . PHP_EOL, FILE_APPEND);
}

function activate_trello_automation()
{
	custom_log('Plugin is activated ');
	require_once plugin_dir_path(__FILE__) . 'includes/class-trello-automation-activator.php';
	Trello_Automation_Activator::activate();
}


function deactivate_trello_automation()
{
	require_once plugin_dir_path(__FILE__) . 'includes/class-trello-automation-deactivator.php';
	Trello_Automation_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_trello_automation');
register_deactivation_hook(__FILE__, 'deactivate_trello_automation');


require plugin_dir_path(__FILE__) . 'includes/class-trello-automation.php';


function run_trello_automation()
{

	$plugin = new Trello_Automation();
	$plugin->run();
}
run_trello_automation();
