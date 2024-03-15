<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    gdpr-compliance
 * @subpackage gdpr-compliance/includes
 * @author     Scribit <wordpress@scribit.it>
 */
class Gdpr_Compliance_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		
		update_option( GDPR_COMPLIANCE_VERSION_SETTINGNAME , GDPR_COMPLIANCE_VERSION );
		
	}

}