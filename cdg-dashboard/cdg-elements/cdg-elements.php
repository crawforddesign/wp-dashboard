<?php
/**
 * CDG Elements
 *
 * @package           CDGElements
 * @author            Crawford Design Group
 * @copyright         2024 Crawford Design Group
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       CDG Elements
 * Plugin URI:        https://crawforddesigngp.com/plugins/cdg-elements
 * Description:       Creates floating, static text elements that can be positioned behind or above other elements on the site.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Crawford Design Group
 * Author URI:        https://crawforddesigngp.com
 * Text Domain:       cdg-elements
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

// Plugin version
define('CDG_ELEMENTS_VERSION', '1.0.0');
define('CDG_ELEMENTS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CDG_ELEMENTS_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * The code that runs during plugin activation.
 */
function activate_cdg_elements() {
	require_once CDG_ELEMENTS_PLUGIN_DIR . 'includes/class-cdg-activator.php';
	CDG_Elements_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_cdg_elements() {
	require_once CDG_ELEMENTS_PLUGIN_DIR . 'includes/class-cdg-deactivator.php';
	CDG_Elements_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_cdg_elements');
register_deactivation_hook(__FILE__, 'deactivate_cdg_elements');

/**
 * The core plugin class
 */
require CDG_ELEMENTS_PLUGIN_DIR . 'includes/class-cdg-elements.php';

/**
 * Begins execution of the plugin.
 */
function run_cdg_elements() {
	$plugin = new CDG_Elements();
	$plugin->run();
}
run_cdg_elements();