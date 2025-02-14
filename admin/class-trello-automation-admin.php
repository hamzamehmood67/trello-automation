<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://https://www.linkedin.com/in/thehamzamehmood/
 * @since      1.0.0
 *
 * @package    Trello_Automation
 * @subpackage Trello_Automation/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Trello_Automation
 * @subpackage Trello_Automation/admin
 * @author     Hamza Mehmood <thehamzamehmood@gmail.com>
 */
class Trello_Automation_Admin
{


	private $plugin_name;


	private $version;


	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}




	public function add_trello_admin_menu()
	{
		add_menu_page(
			'Trello Automation',       // Page title
			'Trello Automation',       // Menu title
			'manage_options',          // Capability
			'trello-automation',       // Menu slug
			array($this, 'display_trello_admin_page'), // Callback function
			'dashicons-trello',        // Icon (Trello logo style)
			25                         // Position in menu
		);
	}

	public function display_trello_admin_page()
	{
		$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'trello';
?>
		<div class="wrap">
			<h1>Trello Automation Settings</h1>
			<h2 class="nav-tab-wrapper">
				<a href="?page=trello-automation&tab=trello" class="nav-tab <?php echo ($active_tab == 'trello') ? 'nav-tab-active' : ''; ?>">Trello</a>
				<a href="?page=trello-automation&tab=slack" class="nav-tab <?php echo ($active_tab == 'slack') ? 'nav-tab-active' : ''; ?>">Slack</a>
			</h2>

			<?php if ($active_tab == 'trello') : ?>
				<form method="post" action="options.php">
					<?php
					settings_fields('trello_options');
					do_settings_sections('trello');
					submit_button('Save Trello Settings');
					?>
				</form>

			<?php elseif ($active_tab == 'slack') : ?>
				<form method="post" action="options.php">
					<?php
					settings_fields('slack_options');
					do_settings_sections('slack');
					submit_button('Save Slack Settings');
					?>
				</form>
			<?php endif; ?>
		</div>
<?php
	}

	public function register_plugin_settings()
	{
		// Trello Settings
		register_setting('trello_options', 'trello_api_key');
		register_setting('trello_options', 'trello_token');

		add_settings_section('trello_section', 'Trello API Settings', '__return_false', 'trello');
		add_settings_field('trello_api_key', 'Trello API Key', array($this, 'trello_api_key_callback'), 'trello', 'trello_section');
		add_settings_field('trello_token', 'Trello Token', array($this, 'trello_token_callback'), 'trello', 'trello_section');

		// Slack Settings
		register_setting('slack_options', 'slack_api_token');
		register_setting('slack_options', 'slack_channel_id');
		register_setting('slack_options', 'slack_signing_secret'); // Register Slack Signing Secret

		add_settings_section('slack_section', 'Slack API Settings', '__return_false', 'slack');
		add_settings_field('slack_api_token', 'Slack API Token', array($this, 'slack_api_token_callback'), 'slack', 'slack_section');
		add_settings_field('slack_channel_id', 'Slack Channel ID', array($this, 'slack_channel_id_callback'), 'slack', 'slack_section');
		add_settings_field('slack_signing_secret', 'Slack Signing Secret', array($this, 'slack_signing_secret_callback'), 'slack', 'slack_section');
	}

	public function trello_api_key_callback()
	{
		$api_key = get_option('trello_api_key', '');
		echo "<input type='text' name='trello_api_key' value='$api_key' class='regular-text' />";
	}

	public function trello_token_callback()
	{
		$token = get_option('trello_token', '');
		echo "<input type='text' name='trello_token' value='$token' class='regular-text' />";
	}

	public function slack_api_token_callback()
	{
		$slack_token = get_option('slack_api_token', '');
		echo "<input type='text' name='slack_api_token' value='$slack_token' class='regular-text' />";
	}

	public function slack_channel_id_callback()
	{
		$slack_channel_id = get_option('slack_channel_id', '');
		echo "<input type='text' name='slack_channel_id' value='$slack_channel_id' class='regular-text' />";
	}

	public function slack_signing_secret_callback()
	{
		$signing_secret = get_option('slack_signing_secret', '');
		echo "<input type='text' name='slack_signing_secret' value='$signing_secret' class='regular-text' />";
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{


		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/trello-automation-admin.css', array(), $this->version, 'all');
	}

	public function enqueue_scripts($hook)
	{

		// Load the script only on the plugin's settings page
		if ($hook === 'toplevel_page_trello-automation-settings') {
			$script_url = plugin_dir_url(__FILE__) . 'js/trello-automation-admin.js';
			error_log('Script URL: ' . $script_url); // Log the script URL
			wp_enqueue_script(
				$this->plugin_name,
				plugin_dir_url(__FILE__) . 'js/trello-automation-admin.js',
				array('jquery'), // Ensure jQuery is a dependency
				$this->version,
				true // Load in the footer
			);
		}
	}



	public function create_trello_card_from_order($order_id)
	{

		$api_key = get_option('trello_api_key', '');
		$token = get_option('trello_token', '');

		$order_link = admin_url('post.php?post=' . $order_id . '&action=edit');

		// Define product-to-list mapping
		$product_list_mapping = [
			'Dog Daycare' => '649303238df9c13a72d46e77',
			'Dog Bath' => '65d4b5bb060254cea7e7171a',
			'Dog Boarding' => '64bef4229123ac1610f21482',
			'Dog Walk' => '65d4b59ec661c3b0fb9c20d7',
			'Express Nail Trim' => '65d4b5e26d216beadd8fd137',
			'At Home Cat Care' => '65d4b5d625b5da1cade3bd5f',
			'Meet and Greet for Boarding and/or Daycare Services' => '6601b365b9d5f576f11109d4',
			'Meet & Greet for Dog Walking, Cat Care & Home Visit Services' => '6601b365b9d5f576f11109d4',
		];
		// Define meta keys to exclude
		$excluded_meta_keys = [
			'_advanced_woo_discount_item_total_discount',
			'_wdr_discounts',
			'_ywpar_total_points',
			'_wsf_submit_id',
			'_wsf_form_id',
			'Total Order Amount:'
		];

		// Get order details
		$order = wc_get_order($order_id);
		if (!$order) return;

		// Prepare Slack message
		$slack_message = "";

		// Loop through each order item
		foreach ($order->get_items() as $item_id => $item) {
			$product_name = $item->get_name(); // Get product name
			$product_id = $item->get_product_id(); // Get product ID

			// Check if the product has a mapped Trello list
			if (array_key_exists($product_name, $product_list_mapping)) {
				$list_id = $product_list_mapping[$product_name]; // Get the Trello list ID

				// Prepare card name
				$order_number = $order->get_order_number(); // Order number
				$customer_name = $order->get_formatted_billing_full_name(); // Customer name
				$order_date = $order->get_date_created()->date('F j, Y g:i a'); // Order date

				$card_name = sprintf(
					'Order %s - %s placed by %s on %s',
					$order_number,
					$product_name,
					$customer_name,
					$order_date
				);

				// Prepare card description
				$card_description = 'Order Details:' . PHP_EOL;
				$card_description .= 'Customer: ' . $customer_name . PHP_EOL;
				$card_description .= 'Product: ' . $product_name . PHP_EOL;
				$card_description .= 'Quantity: ' . $item->get_quantity() . PHP_EOL;
				$card_description .= 'Total: ' . $order->get_total() . ' ' . $order->get_currency() . PHP_EOL;
				$card_description .= 'Payment Method: ' . $order->get_payment_method_title() . PHP_EOL;
				// Add additional meta data (if any)
				$item_meta_data = $item->get_meta_data();
				if (!empty($item_meta_data)) {
					$card_description .= 'Additional Meta Data:' . PHP_EOL;
					foreach ($item_meta_data as $meta) {
						if (in_array($meta->key, $excluded_meta_keys)) {
							continue;
						}
						$card_description .= ' - ' . $meta->key . ': ' . $meta->value . PHP_EOL;
					}
				}
				// Trello API request
				$url = "https://api.trello.com/1/cards";
				$body = [
					'key' => $api_key, // Replace with your Trello API key
					'token' => $token, // Replace with your Trello token
					'idList' => $list_id,
					'pos' => 'top',
					'name' => $card_name,
					'desc' => $card_description,
				];

				// Send request to Trello
				$response = wp_remote_post($url, [
					'body' => $body,
				]);

				// Log result
				if (is_wp_error($response)) {
					error_log('Trello API Error for product ' . $product_name . ': ' . $response->get_error_message());
				} else {
					error_log('Trello card created for product ' . $product_name . ' in order: ' . $order_id);
				}

				// Add item details to Slack message
				$slack_message .= "Pet Care Service: " . $product_name . "\n";
				$slack_message .= "Order Dates: " . $order_date . "\n";
				$slack_message .= "Client Name: " . $customer_name . "\n";
				$slack_message .= "Client Profile: https://thatssofetch.co/profile/" . str_replace(' ', '-', $customer_name) . "/\n\n";

				// Add additional meta data (if any)
				if (!empty($item_meta_data)) {
					$slack_message .= "*Service Detail*:\n";
					foreach ($item_meta_data as $meta) {
						if (in_array($meta->key, $excluded_meta_keys)) {
							continue;
						}
						$slack_message .= ' *-> ' . $meta->key . '* ' . "\n" . $meta->value . "\n";
					}
				}
				$slack_message .= "\n"; // Add a newline between services
			} else {
				error_log('No Trello list mapping found for product: ' . $product_name);
			}
		}

		// Add order ID, status, and link at the bottom
		$slack_message .= "Order ID: " . $order->get_order_number() . "\n";
		$slack_message .= "Order Status: " . $order->get_status() . "\n";
		$slack_message .= "Link to Order: <" . $order_link . "|View Order>\n";

		// Send the consolidated Slack message
		$this->send_to_slack($slack_message, $order_number); // Replace with your Slack channel
	}

	/**
	 * Send order details to Slack using the Web API.
	 *
	 * @param string $channel The Slack channel ID or name (e.g., #general).
	 * @param string $message The message to send.
	 */
	// private function send_to_slack($message)
	// {
	// 	$slack_channel_id = get_option('slack_channel_id', '');
	// 	$slack_api_token = get_option('slack_api_token', '');
	// 	$url = 'https://slack.com/api/chat.postMessage';


	// 	$body = [
	// 		'channel' => $slack_channel_id,
	// 		'text' => $message,
	// 	];

	// 	// Send the request to Slack
	// 	$response = wp_remote_post($url, [
	// 		'body' => json_encode($body),
	// 		'headers' => [
	// 			'Content-Type' => 'application/json; charset=utf-8',
	// 			'Authorization' => 'Bearer ' . $slack_api_token,
	// 		],
	// 	]);

	// 	// Log errors or success
	// 	if (is_wp_error($response)) {
	// 		error_log('Slack API Error: ' . $response->get_error_message());
	// 	} else {
	// 		$response_body = json_decode(wp_remote_retrieve_body($response), true);
	// 		if ($response_body['ok']) {
	// 			error_log('Slack notification sent successfully.');
	// 		} else {
	// 			error_log('Slack API Error: ' . $response_body['error']);
	// 		}
	// 	}
	// }



	private function send_to_slack($message, $order_id)
	{
		$slack_channel_id = get_option('slack_channel_id', '');
		$slack_api_token = get_option('slack_api_token', '');
		$url = 'https://slack.com/api/chat.postMessage';

		$blocks = [
			[
				"type" => "section",
				"text" => [
					"type" => "mrkdwn",
					"text" => $message,
				],
				"block_id" => $order_id, // Store the order ID in the block
			],
			[
				"type" => "actions",
				"elements" => [
					[
						"type" => "button",
						"text" => [
							"type" => "plain_text",
							"text" => "Approve",
						],
						"style" => "primary",
						"action_id" => "approve_order", // Match this in your handler
					],
					[
						"type" => "button",
						"text" => [
							"type" => "plain_text",
							"text" => "Reject",
						],
						"style" => "danger",
						"action_id" => "reject_order", // Match this in your handler
					],
				],
			],
		];

		$response = wp_remote_post($url, [
			'body' => json_encode([
				'channel' => $slack_channel_id,
				'text' => $message,
				'blocks' => $blocks,
			]),
			'headers' => [
				'Content-Type' => 'application/json; charset=utf-8',
				'Authorization' => 'Bearer ' . $slack_api_token,
			],
		]);

		// Log errors or success
		if (is_wp_error($response)) {
			error_log('Slack API Error: ' . $response->get_error_message());
		} else {
			$response_body = json_decode(wp_remote_retrieve_body($response), true);
			if ($response_body['ok']) {
				error_log('Slack notification sent successfully.');
			} else {
				error_log('Slack API Error: ' . $response_body['error']);
			}
		}
	}
}
