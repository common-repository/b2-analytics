<?php

/**
 * 
 *
 * @package           B2_Analytics
 * @author			  B2
 * @link              https://www.b2.ai
 * @since             1.0.5
 * 
 * @wordpress-plugin
 * Plugin Name:       B2 Analytics
 * Plugin URI:        https://www.b2.ai/wordpress
 * Description:       B2 Ad Block Analytics provides you with information about users who visit your site that have an ad blocker
 * Version:           1.0.5
 * Author:            B2
 * Author URI:        https://www.b2.ai
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       b2-analytics
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * use SemVer - https://semver.org
 * 
 */
define( 'B2_ANALYTICS_VERSION', '1.0.4' );


function B2_Analytics_add_admin_menu() {
	add_plugins_page(
		'B2 Ad Block Analytics',
		'B2 Analytics',
		'manage_options',
		'b2-analytics',
		'b2_analytics_page',
		'dashicons-chart-area'			
	);
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-b2-analytics-activator.php
 */
function activate_b2_analytics() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-b2-analytics-activator.php';
	B2_Analytics_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-b2-analytics-deactivator.php
 */
function deactivate_b2_analytics() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-b2-analytics-deactivator.php';
	B2_Analytics_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_b2_analytics' );
register_deactivation_hook( __FILE__, 'deactivate_b2_analytics' );





/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-b2-analytics.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_b2_analytics() {

	$plugin = new B2_Analytics();
	$plugin->run();

}
run_b2_analytics();
