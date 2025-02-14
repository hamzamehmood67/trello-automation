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
 * Description:       Automatically create Trello cards from Order items, streamlining task management and workflow.
 * Version:           1.2.1
 * Author:            Hamza Mehmood
 * Author URI:        https://https://www.linkedin.com/in/thehamzamehmood/
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


// Handle Slack interactive payloads
add_action('rest_api_init', function () {
	register_rest_route('slack/v1', '/interactive-endpoint', [
		'methods' => 'POST',
		'callback' => 'handle_slack_interactive_payload',
		'permission_callback' => '__return_true',
	]);
});

function handle_slack_interactive_payload(WP_REST_Request $request)
{
	// Parse the payload
	$payload = json_decode($request->get_body(), true);

	// Verify the request is from Slack (optional but recommended)
	if (!verify_slack_request($request)) {
		return new WP_REST_Response('Unauthorized', 401);
	}

	// Handle the interaction
	if (isset($payload['type']) && $payload['type'] === 'block_actions') {
		$action = $payload['actions'][0]; // Get the first action
		$order_id = $payload['message']['blocks'][0]['block_id']; // Extract order ID from the message
		$action_id = $action['action_id']; // e.g., 'approve_order' or 'reject_order'

		// Process the action
		switch ($action_id) {
			case 'approve_order':
				$order = wc_get_order($order_id);
				if ($order) {
					$order->update_status('completed', 'Order approved via Slack.');
					return new WP_REST_Response('Order approved.', 200);
				}
				break;

			case 'reject_order':
				$order = wc_get_order($order_id);
				if ($order) {
					$order->update_status('cancelled', 'Order rejected via Slack.');
					return new WP_REST_Response('Order rejected.', 200);
				}
				break;

			default:
				return new WP_REST_Response('Unknown action.', 400);
		}
	}

	return new WP_REST_Response('Invalid payload.', 400);
}

// Verify the request is from Slack (optional but recommended)
function verify_slack_request(WP_REST_Request $request)
{
	$signature = $request->get_header('X-Slack-Signature');
	$timestamp = $request->get_header('X-Slack-Request-Timestamp');
	$body = $request->get_body();

	$signing_secret = get_option('slack_signing_secret'); // Store this in your plugin settings
	$sig_basestring = "v0:$timestamp:$body";
	$my_signature = 'v0=' . hash_hmac('sha256', $sig_basestring, $signing_secret);

	return hash_equals($my_signature, $signature);
}

function run_trello_automation()
{

	$plugin = new Trello_Automation();
	$plugin->run();
}
run_trello_automation();
