<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @since      1.0.0
 * @package    gdpr-compliance
 * @subpackage gdpr-compliance/admin/partials
 * @author     Scribit <wordpress@scribit.it>
 */
function gdpr_compliance_admin_page_handler() {
 
	if (isset($_GET['subpage']) && ($_GET['subpage'] == 'shortcodes' || $_GET['subpage'] == 'about'))
		$current_page = $_GET['subpage'];
	else
		$current_page = 'users-data';
?>
	<div class="wrap gdpr-compliance gdpr-compliance-<?= $current_page ?>">
		<span class="logo"><img src="<?= plugins_url('../images/logo.png', __FILE__) ?>"></span>
		<h1><?= __('GDPR Compliance', 'gdpr-compliance') . ' - ' . __('Settings','gdpr-compliance') ?></h1>
		<div class="clearfix" />
		
		<h2 class="nav-tab-wrapper"> 
			<a href="options-general.php?page=<?= GDPR_COMPLIANCE_PLUGIN_SLUG ?>" class="nav-tab <?= ($current_page == 'users-data') ? 'nav-tab-active' : '' ?>">
				<?= __('Users Data','gdpr-compliance') ?>
			</a>
			<a href="options-general.php?page=<?= GDPR_COMPLIANCE_PLUGIN_SLUG ?>&subpage=shortcodes" class="nav-tab <?= ($current_page == 'shortcodes') ? 'nav-tab-active' : '' ?>">
				<?= __('Shortcodes','gdpr-compliance') ?>
			</a>
			<a href="options-general.php?page=<?= GDPR_COMPLIANCE_PLUGIN_SLUG ?>&subpage=about" class="nav-tab <?= ($current_page == 'about') ? 'nav-tab-active' : '' ?>">
				<?= __('About','gdpr-compliance') ?>
			</a>
		</h2>
		<div class="gdpr-compliance-tab-content">
			<?php include_once 'gdpr-compliance-admin-display-'. $current_page .'.php'; ?>
		</div>
	</div><?php
}