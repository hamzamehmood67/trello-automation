<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://https://www.linkedin.com/in/thehamzamehmood/
 * @since      1.0.0
 *
 * @package    Trello_Automation
 * @subpackage Trello_Automation/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Trello_Automation
 * @subpackage Trello_Automation/includes
 * @author     Hamza Mehmood <thehamzamehmood@gmail.com>
 */
class Trello_Automation
{


	protected $loader;


	protected $plugin_name;


	protected $version;

	public function __construct()
	{
		if (defined('TRELLO_AUTOMATION_VERSION')) {
			$this->version = TRELLO_AUTOMATION_VERSION;
		} else {
			$this->version = '1.0.2';
		}
		$this->plugin_name = 'trello-automation';

		$this->load_dependencies();
		$this->define_admin_hooks();
	}


	private function load_dependencies()
	{

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-trello-automation-loader.php';



		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-trello-automation-admin.php';


		$this->loader = new Trello_Automation_Loader();
	}


	private function define_admin_hooks()
	{

		$plugin_admin = new Trello_Automation_Admin($this->get_plugin_name(), $this->get_version());

		$this->loader->add_action1('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
		$this->loader->add_action1('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
		// $this->loader->add_action1('wsf_submit_post_complete', $plugin_admin, 'create_trello_card_from_wsform', 10, 2);
		$this->loader->add_action1('woocommerce_checkout_order_processed', $plugin_admin, 'create_trello_card_from_order', 10, 1);
	}



	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		$this->loader->run();
	}


	public function get_plugin_name()
	{
		return $this->plugin_name;
	}


	public function get_loader()
	{
		return $this->loader;
	}


	public function get_version()
	{
		return $this->version;
	}
}
