<?php

/**
 * Register all actions and filters for the plugin.
 *
 * Maintain a list of all hooks that are registered throughout
 * the plugin, and register them with the WordPress API. Call the
 * run function to execute the list of actions and filters.
 *
 * @package    Trello_Automation
 * @subpackage Trello_Automation/includes
 * @author     Hamza Mehmood <thehamzamehmood@gmail.com>
 */
class Trello_Automation_Loader
{

	protected $actions;
	protected $filters;

	public function __construct()
	{

		$this->actions = array();
		$this->filters = array();
	}

	function custom_log($message)
	{
		$log_file = plugin_dir_path(__FILE__) . 'custom_log1.txt';
		file_put_contents($log_file, $message . PHP_EOL, FILE_APPEND);
	}


	public function add_action1($hook, $component, $callback, $priority = 10, $accepted_args = 1)
	{
		$this->custom_log('add_action1 method called with hook: ' . $hook);
		$this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $accepted_args);
	}


	public function add_filter1($hook, $component, $callback, $priority = 10, $accepted_args = 1)
	{
		$this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $accepted_args);
	}


	private function add($hooks, $hook, $component, $callback, $priority, $accepted_args)
	{

		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args
		);

		return $hooks;
	}


	public function run()
	{

		foreach ($this->filters as $hook) {
			add_filter($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
		}

		foreach ($this->actions as $hook) {
			add_action($hook['hook'], array($hook['component'], $hook['callback']), $hook['priority'], $hook['accepted_args']);
		}
	}
}
