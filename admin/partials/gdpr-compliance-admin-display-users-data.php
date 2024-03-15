<?php
/**
 * Provide setting page for User data
 *
 * @since      1.2.1
 * @package    gdpr-compliance
 * @subpackage gdpr-compliance/admin/partials
 * @author     Scribit <wordpress@scribit.it>
 */

include_once 'gdpr-compliance-admin-utils.php';
 
$settings = array();

if (isset($_POST['submit'])) {
	$res = true;
	$nonce = sanitize_text_field(wp_unslash($_POST['nonce']));
	
	if ( wp_verify_nonce( $nonce, 'gdpr-compliance-admin-menu-save' ) ){
		// Sanitizing
		$settings['types'] = array();
		gdpr_compliance_sanitize_userdata_setting($_POST['types'], $settings['types']);
		
		$settings['usermetas'] = array();
		gdpr_compliance_sanitize_userdata_setting($_POST['usermetas'], $settings['usermetas']);
		
		$settings['others'] = array();
		gdpr_compliance_sanitize_userdata_setting($_POST['others'], $settings['others']);
		
		//$settings['posts'] = array();
		//gdpr_compliance_sanitize_userdata_setting($_POST['posts'], $settings['posts']);
		
		update_option( GDPR_COMPLIANCE_USERDATA_SETTINGNAME, json_encode($settings) );
	}
	else $res = false;
	
	?>
	<div id="setting-error-settings_updated" class="<?= $res ? '' : 'error' ?> updated settings-error notice is-dismissible"> 
		<p><strong><?= $res ? __('Settings saved.','gdpr-compliance') : __('Saving Error.','gdpr-compliance') ?></strong></p>
		<button type="button" class="notice-dismiss">
			<span class="screen-reader-text"><?= __('Dismiss this notice.','gdpr-compliance') ?></span>
		</button>
	</div>
<?php }
else
{
	$nonce = wp_create_nonce( 'gdpr-compliance-admin-menu-save' );
	
	$settings = json_decode( get_option( GDPR_COMPLIANCE_USERDATA_SETTINGNAME ) , true);	/* Absociative array */
	
} 
?>
<?php
	global $wpdb;
	$current_user = wp_get_current_user();
	$current_user_id = $current_user->data->ID;
?>

<form method="post" novalidate="novalidate">
	<input type="hidden" name="nonce" value="<?= $nonce ?>" />
	<p><?= __('GDPR law provides that user data must be accessible from himself and downloadable in electronic format.', 'gdpr-compliance') ?></p>
	<p><?= __('GDPR Compliance plugin allows you to select the sensitive data to show to the user.', 'gdpr-compliance') ?><br/>
	<?= sprintf( _x('Choose from the following lists the data fields and define the relative readable descriptions. Then use the <b>%s</b> shortcode to show the selected datas for logged in user.', '%s = shortcode_name', 'gdpr-compliance'), '[gdpruserdata]') ?></p>

	<ul class="wp-tab-bar">
		<li class="wp-tab-active"><a href="#tabs-user"><h3><?= __('User data', 'gdpr-compliance') ?></h3></a></li>
		<li><a href="#tabs-content"><h3><?= __('User content', 'gdpr-compliance') ?></h3></a></li>
		<li><a href="#tabs-other"><h3><?= __('Other data', 'gdpr-compliance') ?></h3></a></li>
		
		<?php if ( class_exists( 'WooCommerce' ) ) : ?>
			<li><a href="#tabs-woocommerce"><h3><?= __('WooCommerce', 'gdpr-compliance') ?></h3></a></li>
		<?php endif ?>
	</ul>
	
	<div class="wp-tab-panel" id="tabs-user">
	
		<?php $table_with_userid_columns = $wpdb->get_results("SELECT table_name AS 'table', column_name AS 'column'
				FROM information_schema.columns
				WHERE table_schema = '{$wpdb->dbname}' AND table_name = '{$wpdb->prefix}users' And column_name != 'user_pass'");
			$table_with_userid_example = $wpdb->get_results("SELECT *
				FROM {$wpdb->prefix}users
				LIMIT 1");
			?>
		<p class="note"><?= __('Data contained into [users] table.', 'gdpr-compliance') ?></p>
		<table class="wp-list-table widefat fixed striped table-with-check-column">
			<thead>
				<tr>
					<td scope="col" class="manage-column column-cb check-column"><input id="cb-select-all-users" type="checkbox"></td>
					<td scope="col" class="manage-column"><label for="cb-select-all-users"><b><?= __('Data', 'gdpr-compliance') ?></b></label></td>
					<td scope="col" class="manage-column"><b><?= __('Data description', 'gdpr-compliance') ?></b></td>
					<td class="manage-column"><?= __('Example', 'gdpr-compliance') ?></td>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($table_with_userid_columns as $column) : $column_key = $column->table .'.'. $column->column; ?>
				<tr>
					<th scope="row" class="check-column">
						<label class="screen-reader-text" for="others[<?= $column_key ?>][enabled]"><?= __('Select') ?></label>
						<input type="checkbox" name="others[<?= $column_key ?>][enabled]" id="others[<?= $column_key ?>][enabled]"
							<?= (isset($settings['others'][$column_key]) && ($settings['others'][$column_key]['enabled'] == 1)) ? 'checked="checked"' : '' ?>
						>
					</th>
					<td><label for="others[<?= $column_key ?>][enabled]"><?= $column->column ?></td></label>
					<td><input type="text" name="others[<?= $column_key ?>][desc]" id="others[<?= $column_key ?>][desc]"
						<?= (isset($settings['others'][$column_key])) ? 'value="'. esc_js($settings['others'][$column_key]['desc']) .'"' : '' ?>
					/></td>
					<td class="note long-column"><?= (count($table_with_userid_example) > 0) ? htmlentities($table_with_userid_example[0]->{$column->column}) : '' ?></td>
				</tr>
				<?php endforeach ?>
			</tbody>
		</table>
		
		
		<?php $usermetas = $wpdb->get_results("SELECT meta_key, meta_value AS meta_example
				FROM {$wpdb->prefix}usermeta u1
				WHERE CONCAT('',meta_value * 1) <> meta_value
					AND meta_value NOT IN ('true', 'false', '', 'a:0:{}')
					AND meta_key NOT LIKE '{$wpdb->prefix}%_capabilities' AND meta_key != 'session_tokens'
				GROUP BY meta_key", ARRAY_A);
		?>
		<p class="note"><?= __('Data contained into [usermeta] table.', 'gdpr-compliance') ?></p>
		<table class="wp-list-table widefat fixed striped table-with-check-column">
			<thead>
				<tr>
					<td scope="col" class="manage-column column-cb check-column"><input id="cb-select-all-usermeta" type="checkbox"></td>
					<td scope="col" class="manage-column"><label for="cb-select-all-usermeta"><b><?= __('Data', 'gdpr-compliance') ?></b></label></td>
					<td scope="col" class="manage-column"><b><?= __('Data description', 'gdpr-compliance') ?></b></td>
					<td class="manage-column"><?= __('Example', 'gdpr-compliance') ?></td>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($usermetas as $usermeta) : ?>
				<tr>
					<th scope="row" class="check-column">
						<label class="screen-reader-text" for="usermetas[<?= $usermeta['meta_key'] ?>][enabled]"><?= __('Select') ?></label>
						<input type="checkbox" name="usermetas[<?= $usermeta['meta_key'] ?>][enabled]" id="usermetas[<?= $usermeta['meta_key'] ?>][enabled]"
							<?= (isset($settings['usermetas'][$usermeta['meta_key']]) && ($settings['usermetas'][$usermeta['meta_key']]['enabled'] == 1)) ? 'checked="checked"' : '' ?>
						>
					</th>
					<td><label for="usermetas[<?= $usermeta['meta_key'] ?>][enabled]"><?= $usermeta['meta_key'] ?></label></td>
					<td><input type="text" name="usermetas[<?= $usermeta['meta_key'] ?>][desc]" id="usermetas[<?= $usermeta['meta_key'] ?>[desc]]"
						<?= (isset($settings['usermetas'][$usermeta['meta_key']])) ? 'value="'. esc_js($settings['usermetas'][$usermeta['meta_key']]['desc']) .'"' : '' ?>
					/></td>
					<td class="note long-column"><?= htmlentities($usermeta['meta_example']) ?></td>
				</tr>
				<?php endforeach ?>
			</tbody>
		</table>
	
	</div>
	
	
	
	<div class="wp-tab-panel" id="tabs-content" style="display: none;">
		
		<?php
		// Read posts WP database tables and relative metas
		$posts_types = $wpdb->get_results("SELECT DISTINCT(post_type) AS post_type, id
			FROM {$wpdb->prefix}posts
			GROUP BY post_type");
		
		if (count($posts_types) > 0) : ?>
			<h2><?= __('Posts table informations', 'gdpr-compliance') ?></h2>
			<p class="note"><?= __('Data contained into posts tables.', 'gdpr-compliance') ?></p>
			
			<table class="wp-list-table widefat fixed striped table-with-check-column">
				<thead>
					<tr>
						<th scope="col" class="manage-column"></th>
						<th scope="col" class="manage-column"><b><?= __('Content type', 'gdpr-compliance') ?></b></th>
						<th scope="col" class="manage-column"><b><?= __('Content public description', 'gdpr-compliance') ?></b></th>
						<th scope="col" class="manage-column"><b><?= __('Data example', 'gdpr-compliance') ?></b></th>
					</tr>
				</thead>
			</table>
			
			<table class="wp-list-table widefat fixed striped table-with-check-column">
				<thead>
					<tr>
						<td scope="col" class="manage-column column-cb">
							<input type="checkbox" name="types[comment][enabled]" id="types[comment][enabled]"
								<?= (isset($settings['types']['comment']) && ($settings['types']['comment']['enabled'] == 1)) ? 'checked="checked"' : '' ?>
							>
						</td>
						<td scope="col" class="manage-column"><label for="types[comment][enabled]"><b><?= strtoupper(__('Comments')) ?></b></label></td>
						<td scope="col" class="manage-column" colspan="2">
							<input type="text" name="types[comment][desc]" id="types[comment][desc]" 
								<?= (isset($settings['types']['comment'])) ? 'value="'. esc_js($settings['types']['comment']['desc']) .'"' : '' ?>
							/>
						</td>
					</tr>
				</thead>
			</table>
			
			<?php
			foreach($posts_types as $posts_type) :
					
				$posts_type_metas = $wpdb->get_results("SELECT DISTINCT(meta_key) AS meta_key, meta_value AS value
					FROM {$wpdb->prefix}postmeta
					WHERE post_id IN (SELECT id FROM {$wpdb->prefix}posts WHERE post_type = '{$posts_type->post_type}')
					GROUP BY meta_key");
				?>
				<table class="wp-list-table widefat fixed striped table-with-check-column">
					<thead>
						<tr>
							<td scope="col" class="manage-column column-cb">
								<input type="checkbox" name="types[<?= $posts_type->post_type ?>][enabled]" id="types[<?= $posts_type->post_type ?>][enabled]"
									<?= (isset($settings['types'][$posts_type->post_type]) && ($settings['types'][$posts_type->post_type]['enabled'] == 1)) ? 'checked="checked"' : '' ?>
								>
							</td>
							<td scope="col" class="manage-column"><label for="types[<?= $posts_type->post_type ?>][enabled]"><b><?= strtoupper(__(ucwords($posts_type->post_type))) ?></b></label></td>
							<td scope="col" class="manage-column" colspan="2">
								<input type="text" name="types[<?= $posts_type->post_type ?>][desc]" id="types[<?= $posts_type->post_type ?>][desc]" 
									<?= (isset($settings['types'][$posts_type->post_type])) ? 'value="'. esc_js($settings['types'][$posts_type->post_type]['desc']) .'"' : '' ?>
								/>
							</td>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($posts_type_metas as $posts_type_meta) :
							$column_key = $posts_type->post_type .'.'. $posts_type_meta->meta_key; ?>
						<tr>
							<th scope="row" class="check-column">
								<label class="screen-reader-text" for="types[<?= $column_key ?>][enabled]"><?= __('Select') ?></label>
								<input type="checkbox" name="types[<?= $column_key ?>][enabled]" id="types[<?= $column_key ?>][enabled]"
									<?= (isset($settings['types'][$column_key]) && ($settings['types'][$column_key]['enabled'] == 1)) ? 'checked="checked"' : '' ?>
								>
							</th>
							<td><label for="types[<?= $column_key ?>][enabled]"><?= $posts_type_meta->meta_key ?></td></label>
							<td><input type="text" name="types[<?= $column_key ?>][desc]" id="types[<?= $column_key ?>][desc]"
								<?= (isset($settings['types'][$column_key])) ? 'value="'. esc_js($settings['types'][$column_key]['desc']) .'"' : '' ?>
							/></td>
							<td class="note long-column"><?= htmlentities($posts_type_meta->value) ?></td>
						</tr>
						<?php endforeach ?>
					</tbody>
				</table>
				
			<?php endforeach ?>
		<?php endif ?>
	</div>
	
	
	
	<div class="wp-tab-panel" id="tabs-other" style="display: none;">
		
		<?php
		// Read all WP database tables
		$tables_with_userid = $wpdb->get_results("SELECT table_name
			FROM information_schema.columns
			WHERE table_schema = '{$wpdb->dbname}'
				AND table_name NOT IN ('{$wpdb->prefix}users', '{$wpdb->prefix}usermeta', '{$wpdb->prefix}comments')
				AND LOWER(column_name) = 'user_id'
		");
		
		if (count($tables_with_userid) > 0) : ?>
			<h2><?= __('Other table informations', 'gdpr-compliance') ?></h2>
			<p class="note"><?= __('Data contained into other tables.', 'gdpr-compliance') ?></p>
			
			<table class="wp-list-table widefat fixed striped table-with-check-column">
				<thead>
					<tr>
						<th scope="col" class="manage-column"></th>
						<th scope="col" class="manage-column"><b><?= __('Database table', 'gdpr-compliance') ?></b></th>
						<th scope="col" class="manage-column"><b><?= __('Content public description', 'gdpr-compliance') ?></b></th>
						<th scope="col" class="manage-column"><b><?= __('Data example', 'gdpr-compliance') ?></b></th>
					</tr>
				</thead>
			</table>
			
			<?php
		
			foreach($tables_with_userid as $t) :
				$table_with_userid = $t->table_name;
				
				$table_with_userid_columns = $wpdb->get_results("SELECT table_name AS 'table', column_name AS 'column'
					FROM information_schema.columns
					WHERE table_schema = '{$wpdb->dbname}' AND table_name = '{$table_with_userid}' AND LOWER(column_name) <> 'user_id'");
				$table_with_userid_example = $wpdb->get_results("SELECT *
					FROM {$table_with_userid}
					LIMIT 1");
				?>
				<table class="wp-list-table widefat fixed striped table-with-check-column">
					<thead>
						<tr>
							<td scope="col" class="manage-column column-cb">
								<input type="checkbox" name="others[<?= $table_with_userid ?>][enabled]" id="others[<?= $table_with_userid ?>][enabled]"
									<?= (isset($settings['others'][$table_with_userid]) && ($settings['others'][$table_with_userid]['enabled'] == 1)) ? 'checked="checked"' : '' ?>
								>
							</td>
							<td scope="col" class="manage-column"><label for="others[<?= $table_with_userid ?>][enabled]"><b><?= strtoupper($table_with_userid) ?></b></label></td>
							<td scope="col" class="manage-column" colspan="2">
								<input type="text" name="others[<?= $table_with_userid ?>][desc]" id="others[<?= $table_with_userid ?>][desc]" 
									<?= (isset($settings['others'][$table_with_userid])) ? 'value="'. esc_js($settings['others'][$table_with_userid]['desc']) .'"' : '' ?>
								/>
							</td>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($table_with_userid_columns as $column) : $column_key = $column->table .'.'. $column->column; ?>
						<tr>
							<th scope="row" class="check-column">
								<label class="screen-reader-text" for="others[<?= $column_key ?>][enabled]"><?= __('Select') ?></label>
								<input type="checkbox" name="others[<?= $column_key ?>][enabled]" id="others[<?= $column_key ?>][enabled]"
									<?= (isset($settings['others'][$column_key]) && ($settings['others'][$column_key]['enabled'] == 1)) ? 'checked="checked"' : '' ?>
								>
							</th>
							<td><label for="others[<?= $column_key ?>][enabled]"><?= $column->column ?></td></label>
							<td><input type="text" name="others[<?= $column_key ?>][desc]" id="others[<?= $column_key ?>][desc]"
								<?= (isset($settings['others'][$column_key])) ? 'value="'. esc_js($settings['others'][$column_key]['desc']) .'"' : '' ?>
							/></td>
							<td class="note long-column"><?= (count($table_with_userid_example) > 0) ? htmlentities($table_with_userid_example[0]->{$column->column}) : '' ?></td>
						</tr>
						<?php endforeach ?>
					</tbody>
				</table>
				
			<?php endforeach ?>
		<?php endif ?>
	
	</div>
	
	
	<div class="wp-tab-panel" id="tabs-woocommerce" style="display: none;">
		<p><?= __('Coming soon', 'gdpr-compliance') ?></p>
	</div>
	
	
	<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?= __('Save settings', 'gdpr-compliance') ?>"></p>
</form>