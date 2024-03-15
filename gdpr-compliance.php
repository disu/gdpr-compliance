<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.scribit.it/
 * @since             1.1.1
 * @package           gdpr-compliance
 *
 * @wordpress-plugin
 * Plugin Name:       GDPR Compliance
 * Plugin URI:        https://www.scribit.it/en/wordpress-open-source-plugins/
 * Description:       This plugin helps webmasters to accomplish the european GDPR (data protection regulation) allowing users to manage their personal data.
 * Version:           1.3.0
 * Author:            Scribit
 * Author URI:        https://www.scribit.it/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       gdpr-compliance
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once plugin_dir_path( __FILE__ ) . 'gdpr-compliance-consts.php';

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-gdpr-compliance-activator.php
 */
function activate_gdpr_compliance() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-gdpr-compliance-activator.php';
	Gdpr_Compliance_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-gdpr-compliance-deactivator.php
 */
function deactivate_gdpr_compliance() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-gdpr-compliance-deactivator.php';
	Gdpr_Compliance_Deactivator::deactivate();
}

/**
 * The code that runs during plugin uninstall.
 * This action is documented in includes/class-gdpr-compliance-uninstaller.php
 */
function uninstall_gdpr_compliance() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-gdpr-compliance-uninstaller.php';
	Gdpr_Compliance_Uninstaller::uninstall();
}

register_activation_hook( __FILE__, 'activate_gdpr_compliance' );
register_deactivation_hook( __FILE__, 'deactivate_gdpr_compliance' );
register_uninstall_hook( __FILE__, 'uninstall_gdpr_compliance' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-gdpr-compliance.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_gdpr_compliance() {

	$plugin = new Gdpr_Compliance();
	$plugin->run();

}
run_gdpr_compliance();