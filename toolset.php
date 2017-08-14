<?php

/*
 * Plugin Name: Advanced Custom Toolset
 * Plugin URI: https://thenextgen.io
 * Description: Advanced toolset for managing revisions and orphans acf values
 * Version: 0.1
 * Author: Rowdy Klijnsmit
 * Author URI: https://www.rowydklijnsmit.nl
 * Copyright: Next Generation Online
 * Text Domain: at
 */

// Require some files
require_once(__DIR__ . '/table.php');
require_once(__DIR__ . '/orphans.php');
require_once(__DIR__ . '/revisions.php');

class Toolset
{
	private $templateDir;

	/**
	 * Toolset constructor
	 */
	public function __construct()
	{
		// Set template directory
		$this->templateDir = __DIR__ . '/views/';

		// Register menu
		add_action('admin_menu', [$this, 'registerMenu']);
	}

	/**
	 * Register in the admin menu
	 */
	public function registerMenu()
	{
		add_menu_page('Toolset', 'Toolset', 'manage_options', 'toolset.php', function() { $this->showMenuPage('main'); });
		add_submenu_page('toolset.php', 'Revisions', 'Revisions', 'manage_options', 'revisions.php', function() { $this->showMenuPage('main'); });
		add_submenu_page('toolset.php', 'Orphaned ACF fields', 'Orphaned ACF fields', 'manage_options', 'orphans.php', function() { $this->showMenuPage('orphans'); });
		remove_submenu_page('toolset.php', 'toolset.php');
	}

	/**
	 * Show menu page
	 *
	 * @param string $page
	 */
	public function showMenuPage($page)
	{
		$file = $this->templateDir . $page . '.php';

		// Check if we got a template
		if (file_exists($file)) {
			require_once($file);
		}
	}
}

new Toolset();