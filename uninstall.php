<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @since      1.0.0
 * @package    gdpr-compliance
 * @author     Scribit <wordpress@scribit.it>
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'gdpr-compliance-consts.php';

delete_option( GDPR_COMPLIANCE_USERDATA_SETTINGNAME );
delete_option( GDPR_COMPLIANCE_VERSION_SETTINGNAME );