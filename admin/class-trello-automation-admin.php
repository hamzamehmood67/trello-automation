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

	// //Function to send slack notification of new Order
	// public function create_slack_notification($order_id)
	// {
	// 	$order = wc_get_order($order_id);
	// 	if (!$order) {
	// 		error_log("Order #{$order_id} not found.");
	// 		return;
	// 	}
	// 	// Prepare and send Slack message
	// 	$slack_message = "";

	// 	foreach ($order->get_items() as $item_id => $item) {
	// 		$slack_message .= $this->prepare_slack_message_for_item($order, $item);
	// 	}
	// 	$slack_message .= $this->prepare_slack_message_for_order($order);

	// 	// Send the consolidated Slack message
	// 	$this->send_to_slack($slack_message, $order->get_order_number());
	// }


	public function create_slack_notification($order_id, $attempt = 1)
	{
		$order = wc_get_order($order_id);
		if (!$order) {
			error_log("Order #{$order_id} not found.");
			return;
		}

		// Prepare message
		$slack_message = "";
		foreach ($order->get_items() as $item_id => $item) {
			$slack_message .= $this->prepare_slack_message_for_item($order, $item);
		}
		$slack_message .= $this->prepare_slack_message_for_order($order);

		// Try to send
		$result = $this->send_to_slack($slack_message, $order->get_order_number());

		// If failed and we have retries left (max 3 attempts)
		if (!$result && $attempt < 3) {
			error_log("Retry attempt {$attempt} for order #{$order_id}");
			sleep(2); // Short delay before retry
			$this->create_slack_notification($order_id, $attempt + 1);
		}
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


	public function getPetNames($order)
	{
		$message = "";

		foreach ($order->get_items() as $item_id => $item) {
			try {
				$item_meta_data = $item->get_meta_data();
				$wsf_submit_id = null;
				$wsf_form_id = null;

				if (!empty($item_meta_data)) {
					foreach ($item_meta_data as $meta) {
						if ($meta->key == '_wsf_submit_id') {
							$wsf_submit_id = $meta->value;
						}
						if ($meta->key == '_wsf_form_id') {
							$wsf_form_id = $meta->value;
						}
					}
				}

				if (empty($wsf_submit_id) || empty($wsf_form_id)) {
					error_log('WS Form submission ID or form ID is missing for order #' . $order->get_id());
					continue;
				}

				$submit_object = wsf_submit_get_object($wsf_submit_id);
				if (!$submit_object) {
					error_log('Failed to fetch WS Form submission object for submit ID ' . $wsf_submit_id);
					continue;
				}

				$form_object = wsf_form_get_object($wsf_form_id);
				if (!$form_object) {
					error_log('Failed to fetch WS Form form object for form ID ' . $wsf_form_id);
					continue;
				}

				$class_name = 'pet_name';
				$field_value = wsf_submit_get_value_by_field_class($form_object, $submit_object, $class_name, 'N/A', true);

				if (empty($field_value)) {
					error_log('Field value not found for class ' . $class_name);
					continue;
				}

				if (is_array($field_value)) {
					$message .= "*Pet Names:* " . implode(", ", $field_value);
				} else {
					$message .= "*Pet Name:* " . (string)$field_value;
				}

				$message .= "\n\n";
			} catch (Exception $e) {
				error_log('Error in getPetNames function: ' . $e->getMessage());
				continue;
			}
		}

		return $message;
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
		$customer_name  = $order->get_formatted_billing_full_name();
		$product_name   = $item->get_name();
		$quantity       = $item->get_quantity();
		$total          = $order->get_total() . ' ' . $order->get_currency();
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
			$description .= 'Additional  Data:' . PHP_EOL;

			// Determine abbreviation mapping based on the service.
			if ($product_name == 'Dog Boarding') {
				$abbreviations = $this->get_dog_boarding_abbreviations();
			} elseif ($product_name == 'Dog Daycare') {
				$abbreviations = $this->get_dog_daycare_abbreviations();
			} elseif ($product_name == 'Dog Bath') {
				$abbreviations = $this->get_dog_bath_abbreviations();
			} elseif ($product_name == 'Dog Walk') {
				$abbreviations = $this->get_dog_walk_abbreviations();
			} elseif ($product_name == 'Express Nail Trim') {
				$abbreviations = $this->get_express_nail_trim_abbreviations();
			} elseif ($product_name == 'At Home Cat Care') {
				$abbreviations = $this->get_cat_care_abbreviations();
			} else {
				$abbreviations = [];
			}

			// Loop through each meta field and output the abbreviated version if available.
			if (!empty($abbreviations)) {
				foreach ($item_meta_data as $meta) {

					$decoded_key   = html_entity_decode($meta->key, ENT_QUOTES, 'UTF-8');
					$decoded_value = html_entity_decode($meta->value, ENT_QUOTES, 'UTF-8');
					if (isset($abbreviations[$decoded_key])) {
						$display_key = $abbreviations[$decoded_key];
						$description .= $display_key  . PHP_EOL . '  ✅ ' . $decoded_value . PHP_EOL;
					}
				}
			} else {
				// Fallback if no abbreviation mapping is set.
				foreach ($item_meta_data as $meta) {
					if (in_array($meta->key, $this->get_excluded_meta_keys())) {
						continue;
					}
					$decoded_key   = html_entity_decode($meta->key, ENT_QUOTES, 'UTF-8');
					$decoded_value = html_entity_decode($meta->value, ENT_QUOTES, 'UTF-8');
					$description .=   $decoded_key .  PHP_EOL . '  ✅ ' . $decoded_value . PHP_EOL;
				}
			}
		}
		$description .= PHP_EOL;
		// Get the customer note from the checkout page
		$customer_note = $order->get_customer_note(); // This retrieves the note entered on the checkout page

		// Add the customer note to the description if it exists
		if (!empty($customer_note)) {
			$description .= "*Customer Note:* " . $customer_note . PHP_EOL;
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
			'Cart Total'
		];
	}

	private function get_excluded_boarding()
	{
		return [
			'Boarding rate per night:',
			'Base price for dog boarding =',
			'Extended discounted base price for single dog:',
			'Additional Price per Dog:',
			'Puppy Price',
			'Morning transportation fee',
			'Evening transportation fee',
			'Discounted base price for single dog:',
			'Cart Total',
			'Please check the box below to confirm that you understand and accept the extended daycare fee.'
		];
	}

	private function get_excluded_daycare()
	{
		return [
			'Additional Dog Price',
			'Price for Morning Transportation',
			'Price for Evening Transportation',
			'Final Price',
			'Base price for dog boarding =',
			'Additional Charge for Extra Day of Dog Daycare:',
			'There is $10 holiday upcharge for this date. Please check the box below to continue:'
		];
	}

	private function getUserRole($order)
	{
		$user_id = $order->get_user_id();

		// Get the user object
		$user = $user_id ? get_userdata($user_id) : null;

		// Get the user's role(s)
		$user_roles = $user ? $user->roles : array('guest');

		// Convert roles array to a string
		$user_role_str = implode(', ', $user_roles);

		// Prepare and send Slack message
		$message = "*Client Role:* {$user_role_str}\n\n";

		return $message;
	}

	/**
	 * Returns the abbreviation mapping for Dog Boarding service.
	 */
	private function get_dog_boarding_abbreviations()
	{
		return [
			'Please select your check in date:' => 'Check In-Date:',
			'How many nights do you need to board your dog(s)?' => 'Number of Nights:',
			'Please click here and select your check out date:' => 'Check Out Date:',
			'Would you like to add any additional dogs to your party?' => 'Additional Dogs:',
			'How many additional dogs do you have?' => 'Number of Additional Dogs:',
			'Do you have any puppies (dogs under one year old) in your party?' => 'Puppies:',
			'Do we need to administer any medications during this stay, including pills, supplements, ointments, sprays, or drops?' => 'Medications:',
			'Transportation: Would you like to use our pet taxi service for pick-up and/or drop-off?' => 'Transportation:',
			'Would you like us to pick up your dog(s)?' => 'Pick-Up:',
			'Arrival Time: What time do you think you will drop off your dog(s) (Normal Business Hours are 7am-9pm)?' => 'Arrival Time:',
			'Would you like us to drop-off your dog(s)?' => 'Drop-Off:',
			'Departure Time: What time do you think you will pick your dog(s) up (Normal Business Hours are 7am-9pm)?' => 'Departure Time:'
		];
	}

	/**
	 * Returns the abbreviation mapping for Dog Daycare service.
	 */
	private function get_dog_daycare_abbreviations()
	{
		return [
			'Please click here and select a date:' => 'Check In-Date:',
			'Would you like to add any additional dogs to your party?' => 'Additional Dogs:',
			'Do you have any puppies (dogs under one year old) in your party?' => 'Puppies:',
			'How many additional dogs do you have?' => 'Number of Additional Dogs:',
			'Morning Transportation: Would you like us to pick up your dog(s)?' => 'Morning Transportation:',
			'Evening Transportation: Would you like us to drop off your dog(s)?' => 'Evening Transportation:',
			'Arrival Time: What time do you think you will drop-off your dog(s) in the morning (Normal Business Hours are 7am-9pm)?' => 'Arrival Time:',
			'Departure Time: What time do you think you will pick-up your dog(s) up in the evening (Normal Business Hours are 7am-9pm)?' => 'Departure Time:',
			'Please click here and select a date for Dog Daycare Appointment #text(#field(1338))' => 'Additional Dates:'
		];
	}
	/**
	 * Returns the abbreviation mapping for Dog Bath service.
	 */
	private function get_dog_bath_abbreviations()
	{
		return [
			"Select a date for your dog's bath:" => 'Dog Bath Date:',
			"What is your dog's size?" => 'Dog size:',
			"What is your dog's hair length?" => 'Hair length:',
			"How thick is your dog's fur?" => 'Dog fur:',
			"Which option best describes your dog's fur coat:" => 'Dog fur coat:',
			'Would you like us to use oatmeal shampoo on your dog?' => 'Oatmeal Shampoo:',
			'Does your dog need their ears cleaned?' => 'Ear Cleaning:',
			'Does your dog need their anal glands expressed?' => 'Anal Glands:',
			"Would you like us to trim your dog's nails?" => 'Trim Nails:',
			'Would you like us to use nail clippers or a nail grinder?' => 'Nail Trim/Nail Grind:',
			'Would you like to add-on a cleansing paw treatment?' => 'Paw Cleanse:',
			"Would you like to include a hydrating butter balm treatment to moisturize your dog's nose and paw pads?" => 'Nose/Paw Pad Balm',
			"Would you like us to give your dog's fur coat a full brush-out?" => 'Brush-out:',
			'Would you like your dog to be sprayed with a leave in conditioner or cologne after their bath?' => 'After bath conditioner/cologne:',
			'Would you like to add another bath for a different dog to your order?' => 'Additional Bath:'
		];
	}

	/**
	 * Returns the abbreviation mapping for Dog Walk service.
	 */
	private function get_dog_walk_abbreviations()
	{
		return [
			'Please select a date for your dog walking appointment:' => 'Appointment Date:',
			'Do you want a 15 or 30 minute dog walk?' => 'Walk duration:',
			'How many additional dogs do you have?' => 'Number of additional dogs:',
			'Do you have any puppies (dogs under one year old) in your party?' => 'Puppies:',
			'How many walks do you need to book for the day?' => 'Number of walks for the day:',
			'Please select the time range you want your dog(s) walked:' => 'Time range:',
			'Please click here and select a date for Dog Walking Appointment #calc(1+#section_row_number)' => 'Another Day:'
		];
	}

	/**
	 * Returns the abbreviation mapping for Express Nail Trim service.
	 */
	private function get_express_nail_trim_abbreviations()
	{
		return [
			"Select a date for your dog's Nail Trim:" => 'Appointment Date:',
			'What is the preferred time of day for your pets nail trim appointment on the date you selected above?' => 'Preferred time:',
			'Would you like us to trim your pets using nail trimmers or the nail grinder?' => 'Nail Trim/Nail Grind:',
			'Are there any notes you would like to add your pets nail trim appointment? This is also a good place to let us know if your pet has had any issues with receiving a nail trim in the past.' => 'Any notes:'
		];
	}

	/**
	 * Returns the abbreviation mapping for At Home Cat Care service.
	 */
	private function get_cat_care_abbreviations()
	{
		return [
			'Please select a date for your cat care appointment:' => 'Appointment Date:',
			'Do you want a 15 or 30 minute cat visit?' => 'Duration:',
			'How many additional cats do you have?' => 'Additional Cats:',
			'How many visits do you need to book for the day?' => 'Number of visits:',
			'Please select the time range you want for your Cat(s) visit:' => 'Time Range:',
			'Please click here and select a date for Cat Care Appointment #calc(1+#section_row_number)' => 'Additional Dates:'
		];
	}


	/**
	 * Prepare the Slack message for an order item.
	 */
	private function prepare_slack_message_for_item($order, $item)
	{
		$product_name   = $item->get_name();
		$order_date     = $order->get_date_created()->date('F j, Y g:i a');
		$item_meta_data = $item->get_meta_data();

		$message  = "*Pet Care Service:* " . $product_name . "\n";
		$message .= "*Order Date:* " . $order_date . "\n\n";
		$message .= $this->getPetNames($order);

		if (!empty($item_meta_data)) {
			$message .= "*Service Detail*:\n";

			// Determine abbreviation mapping based on the service.
			if ($product_name == 'Dog Boarding') {
				$abbreviations = $this->get_dog_boarding_abbreviations();
			} elseif ($product_name == 'Dog Daycare') {
				$abbreviations = $this->get_dog_daycare_abbreviations();
			} elseif ($product_name == 'Dog Bath') {
				$abbreviations = $this->get_dog_bath_abbreviations();
			} elseif ($product_name == 'Dog Walk') {
				$abbreviations = $this->get_dog_walk_abbreviations();
			} elseif ($product_name == 'Express Nail Trim') {
				$abbreviations = $this->get_express_nail_trim_abbreviations();
			} elseif ($product_name == 'At Home Cat Care') {
				$abbreviations = $this->get_cat_care_abbreviations();
			} else {
				$abbreviations = [];
			}

			// Loop through each meta field and output the abbreviated version if available.
			if (!empty($abbreviations)) {
				foreach ($item_meta_data as $meta) {
					$decoded_key = html_entity_decode($meta->key, ENT_QUOTES, 'UTF-8');
					if (isset($abbreviations[$decoded_key])) {
						$display_key = $abbreviations[$decoded_key];
						$decoded_value = html_entity_decode($meta->value, ENT_QUOTES, 'UTF-8');
						$message .= ' * 🔴' . $display_key . '* ' . "\n  ✅ " . $decoded_value . "\n";
					}
				}
			} else {
				// Fallback for services without an abbreviation mapping.
				$excluded_keys = [];
				if ($product_name == 'Dog Boarding') {
					$excluded_keys = $this->get_excluded_boarding();
				} elseif ($product_name == 'Dog Daycare') {
					$excluded_keys = $this->get_excluded_daycare();
				}
				$excluded_keys = array_merge($excluded_keys, $this->get_excluded_meta_keys());
				foreach ($item_meta_data as $meta) {
					if (in_array($meta->key, $excluded_keys)) {
						continue;
					}
					$decoded_value = html_entity_decode($meta->value, ENT_QUOTES, 'UTF-8');
					$message .= ' * 🔴' . $meta->key . '* ' . "\n  ✅ " . $decoded_value . "\n";
				}
			}
		}

		$message .= "\n-----------------------------------\n";
		return $message;
	}




	/**
	 * Prepare the Slack message for the order.
	 */
	private function prepare_slack_message_for_order($order)
	{
		// Get the order edit link
		$order_link = admin_url('post.php?post=' . $order->get_id() . '&action=edit');
		$customer_name  = $order->get_formatted_billing_full_name();
		$customer_link  = "https://thatssofetch.co/profile/" . str_replace(' ', '-', $customer_name);
		$order_id = $order->get_order_number();

		// Start building the Slack message
		$message = "*Order Status:* " . $order->get_status() . "\n";
		$message .= "*Link to Order:* <" . $order_link . "|{$order_id}>\n";
		$message .= "*Client:* <" . $customer_link . "|{$customer_name}>\n";
		$message .= $this->getUserRole($order);

		// Get the customer note from the checkout page
		$customer_note = $order->get_customer_note(); // This retrieves the note entered on the checkout page

		// Add the customer note to the Slack message if it exists
		if (!empty($customer_note)) {
			$customer_note = html_entity_decode($customer_note, ENT_QUOTES, 'UTF-8');
			$message .= "*Customer Note:* " . $customer_note . "\n";
		}

		return $message;
	}

	private function send_to_slack($message, $order_id)
	{
		$slack_channel_id = get_option('slack_channel_id', '');
		$slack_api_token = get_option('slack_api_token', '');

		if (empty($slack_channel_id) || empty($slack_api_token)) {
			error_log("Slack config missing for order #{$order_id}");
			return false;
		}

		$response = wp_remote_post('https://slack.com/api/chat.postMessage', [
			'body' => json_encode([
				'channel' => $slack_channel_id,
				'text' => $message,
				'blocks' => [
					[
						'type' => 'section',
						'text' => ['type' => 'mrkdwn', 'text' => $message]
					],
					[
						'type' => 'actions',
						'elements' => [
							[
								'type' => 'button',
								'text' => ['type' => 'plain_text', 'text' => 'Approve'],
								'style' => 'primary',
								'action_id' => 'approve_order',
								'value' => $order_id
							],
							[
								'type' => 'button',
								'text' => ['type' => 'plain_text', 'text' => 'Reject'],
								'style' => 'danger',
								'action_id' => 'reject_order',
								'value' => $order_id
							]
						]
					]
				]
			]),
			'headers' => [
				'Content-Type' => 'application/json',
				'Authorization' => 'Bearer ' . $slack_api_token
			],
			'timeout' => 5
		]);

		if (is_wp_error($response)) {
			error_log("Slack API error for order #{$order_id}: " . $response->get_error_message());
			$this->write_message_to_file("Slack API error for order #{$order_id}: " . $response->get_error_message(), $order_id);
			return false;
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);
		if (empty($body['ok'])) {
			error_log("Slack API failed for order #{$order_id}: " . ($body['error'] ?? 'Unknown error'));
			$this->write_message_to_file("Slack API failed for order #{$order_id}: " . ($body['error'] ?? 'Unknown error'), $order_id);
			return false;
		}


		if (is_wp_error($response)) {
			$error_message = "Slack API transport error for order #{$order_id}: " . $response->get_error_message();
			error_log($error_message);
			$this->write_message_to_file($error_message, $order_id);
			return false;
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);
		$response_code = wp_remote_retrieve_response_code($response);

		if (empty($body['ok'])) {
			// Prepare detailed error message
			$error_details = [
				'order_id' => $order_id,
				'timestamp' => current_time('mysql'),
				'response_code' => $response_code,
				'slack_error' => $body['error'] ?? 'Unknown error',
				'full_response' => $body
			];

			$error_message = "Slack API failed for order #{$order_id}:\n" .
				print_r($error_details, true);

			error_log($error_message);
			$this->write_message_to_file($error_message, $order_id);
			return false;
		}
		return true;
	}



	public function register_trello_api_routes()
	{
		register_rest_route('slack/v1', '/interactive-endpoint', [
			'methods' => 'POST',
			'callback' => [$this, 'handle_slack_interactive_payload'],
			'permission_callback' => '__return_true',
		]);

		register_rest_route('slack/v1', '/process-order-action', [
			'methods' => 'POST',
			'callback' => [$this, 'process_slack_action'],
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
		$response_url = $payload['response_url'];

		// ✅ Respond to Slack immediately
		$response = new WP_REST_Response(['text' => 'Processing your request... ✅'], 200);
		$response->set_headers(['Content-Type' => 'application/json']);

		// ✅ Trigger background processing via HTTP request
		$this->trigger_background_processing($order_id, $action_id, $response_url);

		return $response;
	}

	function trigger_background_processing($order_id, $action_id, $response_url)
	{
		$url = rest_url('slack/v1/process-order-action');
		$args = [
			'body' => [
				'order_id' => $order_id,
				'action_id' => $action_id,
				'response_url' => $response_url,
			],
			'timeout' => 0.01, // Non-blocking request
			'blocking'  => false,
		];

		wp_remote_post($url, $args);
	}



	function process_slack_action(WP_REST_Request $request)
	{
		$order_id = $request->get_param('order_id');
		$action_id = $request->get_param('action_id');
		$response_url = $request->get_param('response_url');

		$order = wc_get_order($order_id);
		if (!$order) {
			error_log("Slack Order Processing Error: Order #{$order_id} not found.");
			$this->send_slack_update($response_url, "❌ Error: Order not found.");
			return;
		}
		$order_status = $order->get_status();


		$message = "";
		foreach ($order->get_items() as $item_id => $item) {
			$message .= $this->prepare_slack_message_for_item($order, $item);
		}

		if ($order_status == "pending" || $order_status == "approval-waiting") {

			if ($action_id === 'approve_order') {
				$order->update_status('approved', 'Order approved via Slack.');
				$message .= $this->prepare_slack_message_for_order($order);
				$message .= "✅✅ Order *#{$order_id}* has been *approved!* 🎉🎉" . "\n";
			} elseif ($action_id === 'reject_order') {
				$order->update_status('rejected', 'Order rejected via Slack.');
				$message .= $this->prepare_slack_message_for_order($order);
				$message .= "❌❌ Order *#{$order_id}* has been *rejected!*" . "\n";
			}
		} else {
			$message .= $this->prepare_slack_message_for_order($order);
			$message .= " :ballot_box_with_check::ballot_box_with_check:Order *#{$order_id}* status has already been changed to *#{$order_status}* 🎉" . "\n";
			// ✅ Update the Slack message
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

	// Pet profile AUtomation

	private function create_trello_card1($data)
	{


		$api_key = get_option('trello_api_key');
		$token   = get_option('trello_token');
		$list_id = '68028b842bac3fc3bb3149b9';

		$url = "https://api.trello.com/1/cards?key={$api_key}&token={$token}";

		$args = [
			'name'   => $data['title'],
			'desc'   => $data['desc'],
			'idList' => $list_id,
			'pos'    => 'top'
		];

		$response = wp_remote_post($url, [
			'method' => 'POST',
			'body'   => $args,
		]);

		if (is_wp_error($response)) {
			error_log('Trello card creation failed: ' . $response->get_error_message());
			return false;
		}

		$body = json_decode(wp_remote_retrieve_body($response), true);

		if (!isset($body['id'])) {
			error_log('Trello card creation failed: No ID in response');
			return false;
		}

		$card_id = $body['id'];

		$custom_fields = [
			'pet_type' => ['id' => '68150516b530c51c38833565', 'type' => 'list', 'options' => [
				'Dog' => '68150516b530c51c38833566',
				'Cat' => '68150516b530c51c38833567',
				'Bird' => '68150516b530c51c38833568',
				'Herptile' => '68150516b530c51c38833569',
				'Fish' => '68150516b530c51c3883356a',
				'Small mammal' => '68150516b530c51c3883356b',
				'Other' => '68150516b530c51c3883356c'
			]],
			'male-female' => [
				'id' => '681505595832afda863e32f2',
				'type' => 'list',
				'options' => [
					'Male' => '681505595832afda863e32f3',
					'Female' => '681505595832afda863e32f4'
				]
			],
			'birth_date' => [
				'id' => '681506c3ff093609d2e40ee4',
				'type' => 'date'
			],

			'boarding_daycare_services' => ['id' => '681505d9667d24b95e80a70c', 'type' => 'list', 'options' => [
				'Yes.' => '681505d9667d24b95e80a70d',
				'No' => '681505d9667d24b95e80a70e'
			]],
			'group_settings' => ['id' => '6815062fa7e5db813d83cecd', 'type' => 'list', 'options' => [
				'Yes.' => '6815062fa7e5db813d83cece',
				'No, they would need a private room & yard time' => '6815062fa7e5db813d83cecf',
				"I'm not sure " => '6815062fa7e5db813d83ced0'
			]],
			'neutered_and_spayed' => ['id' => '6815069259e69457a085c199', 'type' => 'list', 'options' => [
				'Yes.' => '6815069259e69457a085c19a',
				'No.' => '6815069259e69457a085c19b',
				'Not yet, but they will be prior to attending daycare/boarding.' => '6815069259e69457a085c19c'
			]],
			'pet_weight' => ['id' => '6815074daaace50c01d5c71b', 'type' => 'list', 'options' => [
				'Extra Small - Under 5 LBS' => '6815074daaace50c01d5c71c',
				'Small - Between 6 - 20 LBS' => '6815074daaace50c01d5c71d',
				'Medium - Between 21 - 50 LBS' => '6815074daaace50c01d5c71e',
				'Large - Between 51 LBS - 80 LBS' => '6815074daaace50c01d5c71f',
				'Extra Large - Over 81 LBS' => '6815074daaace50c01d5c720'
			]],
			'sleeping_arrangements' => ['id' => '681507ddd8881f3afbb6cfa0', 'type' => 'list', 'options' => [
				'Crate' => '681507ddd8881f3afbb6cfa1',
				'Roams freely' => '681507ddd8881f3afbb6cfa2',
				'A gated/closed off area or room' => '681507ddd8881f3afbb6cfa3'
			]],
			'medicine' => ['id' => '68150861dcecaecde58591d2', 'type' => 'list', 'options' => [
				'Yes' => '68150861dcecaecde58591d3',
				'No' => '68150861dcecaecde58591d4'
			]],
			'species_breed' => ['id' => '681506e330058fb760feda87', 'type' => 'text'],
			'pet_color_markings' => ['id' => '681506fd44b0b1fa00ea428d', 'type' => 'text'],
			'pet_personality' => ['id' => '6815080323c99bd1acecdf99', 'type' => 'text'],
			'eating_arrangements' => ['id' => '68150797d4ccbd94ae0d8492', 'type' => 'text'],
			'medicine_details' => ['id' => '681508a94a92eebb125f889a', 'type' => 'text'],
			'additional_information' => ['id' => '681508d525afc91c26278012', 'type' => 'text'],
		];

		foreach ($data['fields'] as $key => $value) {
			if (empty($value) || !isset($custom_fields[$key])) {
				error_log("Skipping empty or unmapped field: $key");
				continue;
			}

			$field_data = $custom_fields[$key];
			$field_id   = $field_data['id'];

			if ($field_data['type'] === 'list' && isset($field_data['options'][$value])) {
				$field_value = ['idValue' => $field_data['options'][$value]];
			} elseif ($field_data['type'] === 'text') {
				$field_value = ['value' => ['text' => $value]];
			} elseif ($field_data['type'] === 'date') {
				// Convert to ISO 8601 format
				$date = date('c', strtotime($value)); // e.g., "2025-06-30T00:00:00+00:00"
				$field_value = ['value' => ['date' => $date]];
			} else {
				error_log("Unsupported field or value not mapped for $key");
				continue;
			}

			$custom_url = "https://api.trello.com/1/card/{$card_id}/customField/{$field_id}/item?key={$api_key}&token={$token}";

			$resp = wp_remote_request($custom_url, [
				'method' => 'PUT',
				'headers' => ['Content-Type' => 'application/json'],
				'body'    => json_encode($field_value)
			]);

			if (is_wp_error($resp)) {
				error_log("Failed to set custom field [$key]: " . $resp->get_error_message());
			}
		}

		return true;
	}

	public function handle_post_submission($post_ID, $post, $update)
	{


		if (get_post_type($post_ID) !== 'post') {
			error_log("Not a post. Exiting.");
			return;
		}

		if (get_post_meta($post_ID, '_trello_card_created', true)) {
			error_log("Trello card already created. Skipping.");
			return;
		}

		add_action('shutdown', function () use ($post_ID) {


			$title = get_the_title($post_ID);
			$content = get_post_field('post_content', $post_ID);
			$author = get_the_author_meta('display_name', get_post_field('post_author', $post_ID));

			$fields = [
				'pet_type',
				'male-female',
				'boarding_daycare_services',
				'group_settings',
				'neutered_and_spayed',
				'pet_weight',
				'sleeping_arrangements',
				'medicine',
				'species_breed',
				'pet_color_markings',
				'pet_personality',
				'eating_arrangements',
				'medicine_details',
				'additional_information',
				'birth_date'
			];

			$meta_fields = [];
			foreach ($fields as $key) {
				$meta_fields[$key] = get_post_meta($post_ID, $key, true);
			}

			// $this->log_post_meta_and_custom_fields($post_ID, $meta_fields);

			$card_data = [
				'title' => $title,
				'desc' => "Author: $author\n\nContent:\n$content",
				'fields' => $meta_fields
			];

			$success = $this->create_trello_card1($card_data);

			if ($success) {
				update_post_meta($post_ID, '_trello_card_created', true);
				error_log("Trello card created and flagged on post.");
			} else {
				error_log("Failed to create Trello card (delayed).");
			}
		}, 100);
	}


	private function log_post_meta_and_custom_fields($post_id, $fields = [])
	{
		$output = "==== Debug for Post ID: $post_id ====\n\n";

		$output .= "🎯 Custom Field Values Passed:\n";
		foreach ($fields as $key => $value) {
			$output .= "- $key => " . print_r($value, true) . "\n";
		}

		$output .= "\n🧠 Raw Post Meta:\n";
		$all_meta = get_post_meta($post_id);
		foreach ($all_meta as $key => $value) {
			$output .= "$key => " . print_r($value, true) . "\n";
		}

		$this->write_message_to_file($output, $post_id);
	}
}
