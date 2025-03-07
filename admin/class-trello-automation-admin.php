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

	//Function to send slack notification of new Order
	public function create_slack_notification($order_id)
	{
		$order = wc_get_order($order_id);
		if (!$order) {
			error_log("Order #{$order_id} not found.");
			return;
		}
		$this->tempTrelloNotification($order, $order_id);
		// Prepare and send Slack message
		$slack_message = "";
		foreach ($order->get_items() as $item_id => $item) {
			$slack_message .= $this->prepare_slack_message_for_item($order, $item);
		}
		$slack_message .= $this->prepare_slack_message_for_order($order);

		// Send the consolidated Slack message
		$this->send_to_slack($slack_message, $order->get_order_number());
	}
	private function write_message_to_file($message, $order_id)
	{
		// Define the file path
		$file_path = plugin_dir_path(__FILE__) . 'logs/order_' . $order_id . '_message.txt';

		// Create the logs directory if it doesn't exist
		$logs_dir = plugin_dir_path(__FILE__) . 'logs';
		if (!file_exists($logs_dir)) {
			if (!mkdir($logs_dir, 0755, true)) {
				error_log('Failed to create logs directory: ' . $logs_dir);
				return false;
			}
		}

		// Write the message to the file
		if (file_put_contents($file_path, $message) === false) {
			error_log('Failed to write message to file: ' . $file_path);
			return false;
		}

		return true;
	}

	public function tempTrelloNotification($order, $order_id)
	{
		$message = "";
		foreach ($order->get_items() as $item_id => $item) {
			$item_meta_data = $item->get_meta_data();

			if (!empty($item_meta_data)) {
				foreach ($item_meta_data as $meta) {
					if ($meta->key == '_wsf_submit_id') {
						$wsf_submit_id = $meta->value;
					}

					if ($meta->key == '_wsf_form_id') {
						$wsf_form_id = $meta->value;
					}
				}

				// Check if the meta keys exist
				if (empty($wsf_submit_id) || empty($wsf_form_id)) {
					error_log('WS Form submission ID or form ID is missing for order #' . $order_id);
					return;
				}

				// Fetch WS Form submission object
				$submit_object = wsf_submit_get_object($wsf_submit_id);
				if (!$submit_object) {
					error_log('Failed to fetch WS Form submission object for submit ID ' . $wsf_submit_id);
					return;
				}

				// Fetch WS Form form object
				$form_object = wsf_form_get_object($wsf_form_id);
				if (!$form_object) {
					error_log('Failed to fetch WS Form form object for form ID ' . $wsf_form_id);
					return;
				}

				$class_name = 'test';

				// Get the field value from the submission data
				$field_value = wsf_submit_get_value_by_field_class($form_object, $submit_object, $class_name, 'N/A', true);

				if (empty($field_value)) {
					error_log('Field value not found for class ' . $class_name);
					return;
				}

				$message .= "Pet Name: " . $field_value . "\n";
			}
		}

		// Log the message for debugging
		error_log($message);

		// Write the message to a file
		if (!$this->write_message_to_file($message, $order_id)) {
			error_log('Failed to write message to file for order #' . $order_id);
		}

		// Uncomment to send Slack notification
		// $this->send_to_slack($message, $order_id);
	}

	/**
	 * Retrieve a specific field value from WS Form submission data using the field object.
	 */
	private function get_ws_form_field_value($submit_object, $field_object)
	{
		// Check if the submission object has data
		if (empty($submit_object->data)) {
			return null;
		}

		// Decode the submission data
		$data = json_decode($submit_object->data, true);
		if (empty($data)) {
			return null;
		}

		// Find the field value by field ID
		foreach ($data as $field) {
			if (isset($field['id']) && $field['id'] == $field_object->id) {
				return $field['value'];
			}
		}

		return null;
	}

	public function create_trello_card_on_approval($order_id, $old_status, $new_status)
	{
		// Check if the new status is "approved"
		if ($new_status !== 'approved') {
			return;
		}

		$order = wc_get_order($order_id);
		if (!$order) {
			error_log("Order #{$order_id} not found.");
			return;
		}

		// Loop through each order item and create Trello cards
		foreach ($order->get_items() as $item_id => $item) {
			$product_name = $item->get_name();

			// Check if the product has a mapped Trello list
			if ($this->is_product_mapped_to_trello_list($product_name)) {
				$list_id = $this->get_trello_list_id_for_product($product_name);

				// Create Trello card
				$this->create_trello_card($order, $item, $list_id);
			} else {
				error_log('No Trello list mapping found for product: ' . $product_name);
			}
		}
	}


	public function create_trello_card_from_order_old($order_id)
	{
		$order = wc_get_order($order_id);
		if (!$order) {
			error_log("Order #{$order_id} not found.");
			return;
		}

		// Prepare Slack message
		$slack_message = "";

		// Loop through each order item
		foreach ($order->get_items() as $item_id => $item) {
			$product_name = $item->get_name();

			// Check if the product has a mapped Trello list
			if ($this->is_product_mapped_to_trello_list($product_name)) {
				$list_id = $this->get_trello_list_id_for_product($product_name);

				// Create Trello card
				$this->create_trello_card($order, $item, $list_id);

				// Add item details to Slack message
				$slack_message .= $this->prepare_slack_message_for_item($order, $item);
			} else {
				error_log('No Trello list mapping found for product: ' . $product_name);
			}
		}

		// Add order details to Slack message
		$slack_message .= $this->prepare_slack_message_for_order($order);

		// Send the consolidated Slack message
		$this->send_to_slack($slack_message, $order->get_order_number());
	}

	/**
	 * Check if a product is mapped to a Trello list.
	 */
	private function is_product_mapped_to_trello_list($product_name)
	{
		$product_list_mapping = $this->get_product_list_mapping();
		return array_key_exists($product_name, $product_list_mapping);
	}

	/**
	 * Get the Trello list ID for a product.
	 */
	private function get_trello_list_id_for_product($product_name)
	{
		$product_list_mapping = $this->get_product_list_mapping();
		return $product_list_mapping[$product_name];
	}

	/**
	 * Get the product-to-Trello list mapping.
	 */
	private function get_product_list_mapping()
	{
		return [
			'Dog Daycare' => '649303238df9c13a72d46e77',
			'Dog Bath' => '65d4b5bb060254cea7e7171a',
			'Dog Boarding' => '64bef4229123ac1610f21482',
			'Dog Walk' => '65d4b59ec661c3b0fb9c20d7',
			'Express Nail Trim' => '65d4b5e26d216beadd8fd137',
			'At Home Cat Care' => '65d4b5d625b5da1cade3bd5f',
			'Meet and Greet for Boarding and/or Daycare Services' => '6601b365b9d5f576f11109d4',
			'Meet & Greet for Dog Walking, Cat Care & Home Visit Services' => '6601b365b9d5f576f11109d4',
		];
	}

	/**
	 * Create a Trello card for an order item.
	 */
	private function create_trello_card($order, $item, $list_id)
	{
		$api_key = get_option('trello_api_key', '');
		$token = get_option('trello_token', '');

		$card_name = $this->prepare_trello_card_name($order, $item);
		$card_description = $this->prepare_trello_card_description($order, $item);

		$url = "https://api.trello.com/1/cards";
		$body = [
			'key' => $api_key,
			'token' => $token,
			'idList' => $list_id,
			'pos' => 'top',
			'name' => $card_name,
			'desc' => $card_description,
		];

		$response = wp_remote_post($url, [
			'body' => $body,
		]);

		if (is_wp_error($response)) {
			error_log('Trello API Error for product ' . $item->get_name() . ': ' . $response->get_error_message());
		} else {
			error_log('Trello card created for product ' . $item->get_name() . ' in order: ' . $order->get_id());
		}
	}

	/**
	 * Prepare the Trello card name.
	 */
	private function prepare_trello_card_name($order, $item)
	{
		$order_number = $order->get_order_number();
		$customer_name = $order->get_formatted_billing_full_name();
		$order_date = $order->get_date_created()->date('F j, Y g:i a');
		$product_name = $item->get_name();

		return sprintf(
			'Order %s - %s placed by %s on %s',
			$order_number,
			$product_name,
			$customer_name,
			$order_date
		);
	}

	/**
	 * Prepare the Trello card description.
	 */
	private function prepare_trello_card_description($order, $item)
	{
		$customer_name = $order->get_formatted_billing_full_name();
		$product_name = $item->get_name();
		$quantity = $item->get_quantity();
		$total = $order->get_total() . ' ' . $order->get_currency();
		$payment_method = $order->get_payment_method_title();

		$description = 'Order Details:' . PHP_EOL;
		$description .= 'Customer: ' . $customer_name . PHP_EOL;
		$description .= 'Product: ' . $product_name . PHP_EOL;
		$description .= 'Quantity: ' . $quantity . PHP_EOL;
		$description .= 'Total: ' . $total . PHP_EOL;
		$description .= 'Payment Method: ' . $payment_method . PHP_EOL;

		// Add additional meta data (if any)
		$item_meta_data = $item->get_meta_data();
		if (!empty($item_meta_data)) {
			$description .= 'Additional Meta Data:' . PHP_EOL;
			foreach ($item_meta_data as $meta) {
				if (in_array($meta->key, $this->get_excluded_meta_keys())) {
					continue;
				}
				$description .= ' - ' . $meta->key . ': ' . $meta->value . PHP_EOL;
			}
		}

		return $description;
	}

	/**
	 * Get the list of excluded meta keys.
	 */
	private function get_excluded_meta_keys()
	{
		return [
			'_advanced_woo_discount_item_total_discount',
			'_wdr_discounts',
			'_ywpar_total_points',
			'_wsf_submit_id',
			'_wsf_form_id',
			'Total Order Amount:',
			'Additional Charge for Extra Day of Dog Daycare:'
		];
	}

	/**
	 * Prepare the Slack message for an order item.
	 */
	private function prepare_slack_message_for_item($order, $item)
	{
		$product_name = $item->get_name();
		$order_date = $order->get_date_created()->date('F j, Y g:i a');
		$customer_name = $order->get_formatted_billing_full_name();
		$item_meta_data = $item->get_meta_data();

		$message = "Pet Care Service: " . $product_name . "\n";
		$message .= "Order Dates: " . $order_date . "\n";
		$message .= "Client Name: " . $customer_name . "\n";
		$message .= "Client Profile: https://thatssofetch.co/profile/" . str_replace(' ', '-', $customer_name) . "/\n\n";

		if (!empty($item_meta_data)) {
			$message .= "*Service Detail*:\n";
			foreach ($item_meta_data as $meta) {
				if (in_array($meta->key, $this->get_excluded_meta_keys())) {
					continue;
				}
				$message .= ' * 🔴' . $meta->key . '* ' . "\n  ✅ " . $meta->value . "\n\n";
			}
		}

		$message .= "\n"; // Add a newline between services
		return $message;
	}

	/**
	 * Prepare the Slack message for the order.
	 */
	private function prepare_slack_message_for_order($order)
	{
		$order_link = admin_url('post.php?post=' . $order->get_id() . '&action=edit');

		$message = "Order ID: " . $order->get_order_number() . "\n";
		$message .= "Order Status: " . $order->get_status() . "\n";
		$message .= "Link to Order: <" . $order_link . "|View Order>\n";

		return $message;
	}

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
						"action_id" => "approve_order",
						"value" => $order_id, // Store the order ID here
					],
					[
						"type" => "button",
						"text" => [
							"type" => "plain_text",
							"text" => "Reject",
						],
						"style" => "danger",
						"action_id" => "reject_order",
						"value" => $order_id, // Store the order ID here
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



	public function register_trello_api_routes()
	{
		register_rest_route('slack/v1', '/interactive-endpoint', [
			'methods' => 'POST',
			'callback' => [$this, 'handle_slack_interactive_payload'],
			'permission_callback' => '__return_true',
		]);
	}


	function handle_slack_interactive_payload(WP_REST_Request $request)
	{

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




	public function process_slack_action($order_id, $action_id, $response_url)
	{
		$order = wc_get_order($order_id);
		if (!$order) {
			error_log("Slack Order Processing Error: Order #{$order_id} not found.");
			$this->send_slack_update($response_url, "❌ Error: Order not found.");
			return;
		}

		$message = "";
		foreach ($order->get_items() as $item_id => $item) {
			// Add item details to Slack message
			$message .= $this->prepare_slack_message_for_item($order, $item);
		}
		$message .= $this->prepare_slack_message_for_order($order);
		if ($action_id === 'approve_order') {
			$order->update_status('approved', 'Order approved via Slack.');
			$message .= "✅ Order *#{$order_id}* has been *approved!* 🎉" . "\n";
		} elseif ($action_id === 'reject_order') {
			$order->update_status('cancelled', 'Order rejected via Slack.');
			$message .= "❌ Order *#{$order_id}* has been *rejected!*" . "\n";
		}
		// ✅ Update the Slack message
		$this->send_slack_update($response_url, $message);
	}


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
}
