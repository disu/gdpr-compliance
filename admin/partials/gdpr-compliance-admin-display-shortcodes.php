<?php
/**
 * Provide setting page for Shortcodes
 *
 * @since      1.2.1
 * @package    gdpr-compliance
 * @subpackage gdpr-compliance/admin/partials
 * @author     Scribit <wordpress@scribit.it>
 */
?>
<h3><?= __('User data', 'gdpr-compliance') ?></h3>
<strong><pre>[gdpruserdata text_notlogged="#####"]</pre></strong>
<p><?= __('Insert this shortcode into a public content (Post, Page or other) to show to logged user his personal data.', 'gdpr-compliance') ?></p>
<p>
	<?= __('Shortcode parameters', 'gdpr-compliance') ?>:
	<ul>
		<li><b>text_notlogged</b>: <?= __('Custom message text for not logged users', 'gdpr-compliance') .' - '. __('Default value', 'gdpr-compliance') .': <i>'. __('User must be logged to see his data', 'gdpr-compliance') ?></i></li>
	</ul>
</p>

<h3><?= __('User data download', 'gdpr-compliance') ?></h3>
<strong><pre>[gdpruserdata_download text="#####" text_notlogged="#####"]</pre></strong>
<p><?= __('Insert this shortcode into a public content to show to logged user a button for personal data download (CSV format).', 'gdpr-compliance') ?></p>
<p>
	<?= __('Shortcode parameters', 'gdpr-compliance') ?>:
	<ul>
		<li><b>text</b>: <?= __('Custom text for download button', 'gdpr-compliance') .' - '. __('Default value', 'gdpr-compliance') .': <i>'. __('Download user data', 'gdpr-compliance') ?></i></li>
		<li><b>text_notlogged</b>: <?= __('Custom message text for not logged users', 'gdpr-compliance') .' - '. __('Default value', 'gdpr-compliance') .': <i>'. __('User must be logged to download his data', 'gdpr-compliance') ?></i></li>
	</ul>
</p>

<p class="gdpr-compliance-ads"><span class="logo" style="margin-top:-14px;"><img src="<?= plugins_url('../images/logo_shortcodes_finder.png', __FILE__) ?>"></span><?= 
	sprintf( 
		__( 'Download <a href="%s" target="_blank">Shortcodes Finder</a> to better manage shortcodes in your Wordpress site', 'gdpr-compliance' ), 
		esc_url( 'https://wordpress.org/plugins/shortcodes-finder' ) 
	);?>
</p>