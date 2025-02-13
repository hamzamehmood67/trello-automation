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
	private $slack_api_token = '';

	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
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

	public function enqueue_scripts()
	{

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/trello-automation-admin.js', array('jquery'), $this->version, false);
	}



	public function create_trello_card_from_order($order_id)
	{
		$api_key = '';
		$token = '';
		$order_link = admin_url('post.php?post=' . $order_id . '&action=edit');

		// Define product-to-list mapping
		$product_list_mapping = [
			'Dog Daycare' => '',
			'Dog Bath' => '',
			'Dog Boarding' => '',
			'Dog Walk' => '',
			'Express Nail Trim' => '',
			'At Home Cat Care' => '',
			'Meet and Greet for Boarding and/or Daycare Services' => '',
			'Meet & Greet for Dog Walking, Cat Care & Home Visit Services' => '',
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
					$slack_message .= "Service Detail:\n";
					foreach ($item_meta_data as $meta) {
						if (in_array($meta->key, $excluded_meta_keys)) {
							continue;
						}
						$slack_message .= ' - ' . $meta->key . "  " . $meta->value . "\n";
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
		$this->send_to_slack('C087JCCAAAG', $slack_message); // Replace with your Slack channel
	}

	/**
	 * Send order details to Slack using the Web API.
	 *
	 * @param string $channel The Slack channel ID or name (e.g., #general).
	 * @param string $message The message to send.
	 */
	private function send_to_slack($channel, $message)
	{
		$url = 'https://slack.com/api/chat.postMessage';


		$body = [
			'channel' => $channel,
			'text' => $message,
		];

		// Send the request to Slack
		$response = wp_remote_post($url, [
			'body' => json_encode($body),
			'headers' => [
				'Content-Type' => 'application/json; charset=utf-8',
				'Authorization' => 'Bearer ' . $this->slack_api_token,
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
