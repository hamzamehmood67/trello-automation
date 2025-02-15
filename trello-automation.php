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


add_action('rest_api_init', function () {
	register_rest_route('slack/v1', '/interactive-endpoint', [
		'methods' => 'POST',
		'callback' => 'handle_slack_interactive_payload',
		'permission_callback' => '__return_true',
	]);
});

// function handle_slack_interactive_payload(WP_REST_Request $request)
// {
// 	// Slack sends the payload as form data
// 	$raw_payload = $request->get_body();
// 	parse_str($raw_payload, $parsed_data);

// 	// Extract payload JSON
// 	if (!isset($parsed_data['payload'])) {
// 		return new WP_REST_Response('Invalid payload.', 400);
// 	}

// 	$payload = json_decode($parsed_data['payload'], true);

// 	// Verify the request is from Slack
// 	// if (!verify_slack_request($request)) {
// 	// 	return new WP_REST_Response('Unauthorized', 401);
// 	// }

// 	if (isset($payload['type']) && $payload['type'] === 'block_actions') {
// 		$action = $payload['actions'][0]; // Get the first action
// 		$order_id = $action['value']; // Extract order ID from button value
// 		$action_id = $action['action_id']; // e.g., 'approve_order' or 'reject_order'

// 		// Process the action
// 		if ($order_id) {
// 			$order = wc_get_order($order_id);
// 			if ($order) {
// 				if ($action_id === 'approve_order') {
// 					$order->update_status('completed', 'Order approved via Slack.');
// 					return new WP_REST_Response(['text' => '✅ Order Approved!'], 200);
// 				} elseif ($action_id === 'reject_order') {
// 					$order->update_status('cancelled', 'Order rejected via Slack.');
// 					return new WP_REST_Response(['text' => '❌ Order Rejected!'], 200);
// 				}
// 			}
// 		}
// 		return new WP_REST_Response('Order not found.', 404);
// 	}

// 	return new WP_REST_Response('Invalid action.', 400);
// }



function handle_slack_interactive_payload(WP_REST_Request $request)
{
	// ✅ 1. Verify Slack request
	// if (!verify_slack_request($request)) {
	//     return new WP_REST_Response('Unauthorized', 401);
	// }

	// ✅ 2. Parse the Slack payload
	$raw_payload = $request->get_body();
	parse_str($raw_payload, $parsed_data);

	if (!isset($parsed_data['payload'])) {
		return new WP_REST_Response('Invalid payload.', 400);
	}

	$payload = json_decode($parsed_data['payload'], true);
	$action = $payload['actions'][0];
	$order_id = $action['value'];
	$action_id = $action['action_id'];
	$response_url = $payload['response_url']; // Slack’s response URL

	// ✅ 3. Respond to Slack immediately (Prevents timeout)
	$response = new WP_REST_Response(['text' => 'Processing your request... ✅'], 200);
	$response->set_headers(['Content-Type' => 'application/json']);

	// ✅ 4. Schedule background processing
	wp_schedule_single_event(time() + 1, 'process_slack_order_action', [$order_id, $action_id, $response_url]);

	return $response;
}

add_action('process_slack_order_action', function ($order_id, $action_id, $response_url) {
	$order = wc_get_order($order_id);
	if (!$order) {
		error_log("Slack Order Processing Error: Order #{$order_id} not found.");
		send_slack_update($response_url, "❌ Error: Order not found.");
		return;
	}

	$message = "";
	if ($action_id === 'approve_order') {
		$order->update_status('approved', 'Order approved via Slack.');
		$message = "✅ Order *#{$order_id}* has been *approved!* 🎉";
	} elseif ($action_id === 'reject_order') {
		$order->update_status('cancelled', 'Order rejected via Slack.');
		$message = "❌ Order *#{$order_id}* has been *rejected!*";
	}

	// ✅ Update the Slack message
	send_slack_update($response_url, $message);
}, 10, 3);



function send_slack_update($response_url, $message)
{
	wp_remote_post($response_url, [
		'body'    => json_encode([
			'text' => $message,
			'replace_original' => true, // Update the original message
		]),
		'headers' => [
			'Content-Type' => 'application/json',
		],
	]);
}
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
