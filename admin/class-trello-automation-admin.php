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



	// Hook into WooCommerce new order creation
	// public function create_trello_card_from_order($order_id)
	// {


	// 	// // Get order details
	// 	$order = wc_get_order($order_id);
	// 	if (!$order) return;


	// 	// Prepare card name
	// 	$order_number = $order->get_order_number(); // Order number
	// 	$customer_name = $order->get_formatted_billing_full_name(); // Customer name
	// 	$order_date = $order->get_date_created()->date('F j, Y g:i a'); // Order date in desired format

	// 	$card_name = sprintf(
	// 		'Order %s placed by %s on %s',
	// 		$order_number,
	// 		$customer_name,
	// 		$order_date
	// 	);

	// 	$card_description = 'Order Details:' . PHP_EOL;
	// 	$card_description .= 'Customer: ' . $customer_name . PHP_EOL;
	// 	$card_description .= 'Total: ' . $order->get_total() . ' ' . $order->get_currency() . PHP_EOL;
	// 	$card_description .= 'Payment Method: ' . $order->get_payment_method_title() . PHP_EOL;
	// 	$card_description .= 'Items:' . PHP_EOL;

	// 	// Loop through each order item
	// 	foreach ($order->get_items() as $item_id => $item) {
	// 		$card_description .= '----------------------------------------' . PHP_EOL;

	// 		// Get common item data
	// 		$item_data = $item->get_data();
	// 		$card_description .= 'Product Name: ' . $item->get_name() . PHP_EOL;
	// 		$card_description .= 'Quantity: ' . $item->get_quantity() . PHP_EOL;


	// 		// Get additional meta data (raw)
	// 		$item_meta_data = $item->get_meta_data();
	// 		if (!empty($item_meta_data)) {
	// 			$card_description .= 'Additional Meta Data:' . PHP_EOL;
	// 			foreach ($item_meta_data as $meta) {
	// 				$card_description .= ' - ' . $meta->key . ': ' . $meta->value . PHP_EOL;
	// 			}
	// 		}
	// 	}


	// 	// Trello Card creatiion logic

	// 	$url = "https://api.trello.com/1/cards";

	// 	$body = [
	// 		'key' => $api_key,
	// 		'token' => $token,
	// 		'idList' => $list_id,
	// 		'name' => $card_name,
	// 		'desc' => $card_description,
	// 	];

	// 	// Send request to Trello
	// 	$response = wp_remote_post($url, [
	// 		'body' => $body,
	// 	]);

	// 	// Log result
	// 	if (is_wp_error($response)) {
	// 		error_log('Trello API Error: ' . $response->get_error_message());
	// 	} else {
	// 		error_log('Trello card created for order: ' . $order_id);
	// 	}
	// }



	public function create_trello_card_from_order($order_id)
	{



		// Define product-to-list mapping


		// Get order details
		$order = wc_get_order($order_id);
		if (!$order) return;

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
			} else {
				error_log('No Trello list mapping found for product: ' . $product_name);
			}
		}
	}
}
