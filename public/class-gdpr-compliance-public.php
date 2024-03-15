<?php
/**
 * The public-facing functionality of the plugin.
 *
 * Defines the gdpr-compliance, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @since      1.0.0
 * @package    gdpr-compliance
 * @subpackage gdpr-compliance/public
 * @author     Scribit <wordpress@scribit.it>
 */
class Gdpr_Compliance_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Gdpr_Compliance_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Gdpr_Compliance_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/gdpr-compliance-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Gdpr_Compliance_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Gdpr_Compliance_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/gdpr-compliance-public.js', array( 'jquery' ), $this->version, false );

	}
	
	/**
	 * Define handle for gdpruserdata shortcode.
	 * 
	 * @since    1.0.0
	 */
	public function shortcode_gdpruserdata_handle( $atts ) {
		
		$atts = shortcode_atts(
			array(
				'text_notlogged' => ''
			),
			$atts,
			'gdpruserdata'
		);
		
		$current_user = wp_get_current_user ();
		if ( 0 == $current_user->ID )
			return '<p class="gdpr-compliance-userdata-show">'. (isset( $atts['text_notlogged'] ) ? $atts['text_notlogged'] : __('User must be logged to see his data', 'gdpr-compliance')) .'</p>';
		
		global $wpdb;
		$result = '';
		$settings = json_decode( get_option( GDPR_COMPLIANCE_USERDATA_SETTINGNAME ) , true);	/* Absociative array */
		
		
		
		// USERS
		
		// Note: I must mantain users table settings into "others" setting array for retrocompatibility (<=1.1.0)
		$user_fields = array();
		foreach($settings['others'] as $field => $setting){
			// Include only users table fields, excluding user_pass
			if ( $setting['enabled'] && $this->startsWith($field, $wpdb->prefix .'users') && ($field != $wpdb->prefix .'users.user_pass'))
				$user_fields[$field] = $setting;
		}
				
		$usermeta_fields = array();
		foreach($settings['usermetas'] as $usermeta => $setting)
			if ($setting['enabled'])
				$usermeta_fields[$usermeta] = $setting['desc'];
		
		if ( count($user_fields) > 0 || count($usermeta_fields) > 0 )
			$result .= '<table class="gdpr-compliance-user wp-list-table gdpr-compliance-table gdpr-compliance-userdata">
				<thead><tr><td colspan="2">'. __('User data', 'gdpr-compliance') .'</td></tr></thead>
				<tbody>';
		
		if ( count($user_fields) > 0 ){	
			foreach ($user_fields as $field => $setting){
				
				$point_index = strpos($field, '.');
				if ( $point_index ){
					
					$table_name = substr($field, 0, $point_index);
					$value = $wpdb->get_results( $wpdb->prepare("SELECT GROUP_CONCAT({$field}) AS value
						FROM {$table_name}
						WHERE (id = %d)
						LIMIT 1", $current_user->ID )
						, ARRAY_A );
					
					$result .= '<tr>
						<td>'. ((strlen($setting['desc']) > 0) ? $setting['desc'] : substr($field, $point_index + 1)) .'</td>
						<td>'. htmlentities($value[0]['value']) .'</td>
					</tr>';
					
				}
			}
		}
		
		// USERMETAS
		
		if ( count($usermeta_fields) > 0 ){
			$usermeta_fields_string = implode ("', '", array_keys($usermeta_fields));
			
			if ( strlen($usermeta_fields_string) > 0 ){
				
				$usermetas_values = $wpdb->get_results( $wpdb->prepare("SELECT meta_key, meta_value
					FROM {$wpdb->prefix}usermeta
					WHERE user_id = %d
						AND meta_key IN ('$usermeta_fields_string')
						AND meta_key NOT LIKE '{$wpdb->prefix}%_capabilities' AND meta_key != 'session_tokens'",
						$current_user->ID)
					, ARRAY_A );
					
				foreach ($usermetas_values as $usermetas_value){
		
					$result .= '<tr>
						<td>'. ((strlen($usermeta_fields[$usermetas_value['meta_key']]) > 0) ? $usermeta_fields[$usermetas_value['meta_key']] : ucwords($usermetas_value['meta_key'])) .'</td>
						<td>'. htmlentities($usermetas_value['meta_value']) .'</td>
					</tr>';
					
				}
			}
		}
		
		if ( count($user_fields) > 0 || count($usermeta_fields) > 0 )
			$result .= '</tbody></table>';
		
		
		
		// COMMENTS
		
		if( isset($settings['types']['comment']) && ($settings['types']['comment']['enabled'] == 1) ){
			
			$args = array(
				'author__in' => $current_user->ID,
				'orderby' => 'date',
				'order' => 'DESC'
			);
			$comments = get_comments( $args );
			if ( count($comments) > 0 ) :
				$result .= '<table class="gdpr-compliance-comment wp-list-table gdpr-compliance-table gdpr-compliance-type"><thead><tr>
					<td>'. ((strlen($settings['types']['comment']['desc']) > 0) ? $settings['types']['comment']['desc'] : __('Comments')) .'</td>
					<td>'. _x('Status', 'Post format') .'</td>
				</tr></thead><tbody>';
				foreach ($comments as $comment) :
					$result .= '<tr>
						<td><a href="'. get_permalink($comment->comment_post_ID) .'">'. $comment->comment_date .'</a></td>
						<td>'. ($comment->comment_approved ? _x('Approved', 'comment status') : '') .'</td>
					</tr>';
				endforeach;
				$result .= '</tbody></table>';
			endif;
			
		}
		
		
		
		// POSTS TYPE AND META
		
		$posts_fields = array();
		$postmeta_fields_desc = array();
		
		foreach($settings['types'] as $postmeta => $setting)
			if ($setting['enabled']){
				$meta_fields = explode('.', $postmeta);	// Every postmeta should be composed as: posttype.metakey
				
				if (count($meta_fields) == 2)
					$postmeta_fields_desc[$meta_fields[0]]['metas'][$meta_fields[1]] = $setting['desc'];
				elseif (count($meta_fields) == 1)
					$postmeta_fields_desc[$meta_fields[0]]['desc'] = $setting['desc'];
			}
		
		if ( count($postmeta_fields_desc) > 0 ){
			
			foreach ($postmeta_fields_desc as $post_type => $post_settings){
			
				$postmeta_fields_string = isset($post_settings['metas']) ? implode ("', '", array_keys($post_settings['metas'])) : '0';
								
				$posts = $wpdb->get_results( $wpdb->prepare("SELECT Posts.id AS post_id, Posts.post_title, Posts.post_status,
						PostMeta.meta_key, PostMeta.meta_value
					FROM {$wpdb->prefix}posts Posts
						LEFT OUTER JOIN {$wpdb->prefix}postmeta PostMeta ON Posts.id = PostMeta.post_id
							AND PostMeta.meta_key in ('". $postmeta_fields_string ."')
					WHERE Posts.post_author = %d
						AND Posts.post_type = '{$post_type}'", $current_user->ID )
					, ARRAY_A );
				
				if (count($posts) > 0){
						
					$i = 0;
					$last_post = 0;
					$result .= '<table class="gdpr-compliance-'. $post_type .' wp-list-table gdpr-compliance-table gdpr-compliance-userdata">
						<thead><tr><td colspan="2">'. 
							( (isset($post_settings['desc']) > 0) ? $post_settings['desc'] : ucwords($post_type) )
						.'</td></thead>
						<tbody>';
					foreach($posts as $post){
						
						if ($last_post != $post['post_id']){
							if ($last_post != 0)
								$result .= '</td></tr>';
							
							// Print post link
							$result .= '<tr><td><a href="'. get_permalink($post['post_id']) .'">'. $post['post_title'] .'</a></td><td>';
							
							// Print status
							$result .= _x('Status', 'Post format') .': ';
							switch($post['post_status']){
								case 'trash': $result .= _x('Trash', 'post status'); break;
								case 'private': $result .= _x('Private', 'post status'); break;
								case 'pending': $result .= _x('Pending', 'post status'); break;
								case 'draft': $result .= _x('Draft', 'post status'); break;
								case 'future': $result .= _x('Scheduled', 'post status'); break;
								case 'publish': $result .= _x('Published', 'post status'); break;
							}
							$result .= '<br/>';
							
							$last_post = $post['post_id'];
						}
						
						// Print meta fields
						if (strlen($post['meta_key']) > 0)
							$result .= ( ( (isset($post_settings['metas']) && (strlen($post_settings['metas'][$post['meta_key']]) > 0)) ) ?
								$post_settings['metas'][$post['meta_key']] :
								$post['meta_key']) .': '. $post['meta_value'] .'<br/>';
					}
					$result .= '</tbody>
					</table>';
				
				}
			}
		}
		
		
		// OTHER TABLES
		
		$other_fields_desc = array();
		foreach($settings['others'] as $field => $setting)
			if ($setting['enabled']){
				if ($this->startsWith($field, $wpdb->prefix .'users'))	continue;	// Esclude users table
				
				$table_fields = explode('.', $field);	// Every information should be composed as: tablename.fieldname
				if (count($table_fields) < 1 || count($table_fields) > 2) continue;
				
				if (!isset($other_fields_desc[$table_fields[0]]))
					$other_fields_desc[$table_fields[0]] = array();
				
				if (count($table_fields) == 2)
					$other_fields_desc[$table_fields[0]]['fields'][$table_fields[1]] = $setting['desc'];
				else
					$other_fields_desc[$table_fields[0]]['desc'] = $setting['desc'];
			}
		
		if ( count($other_fields_desc) > 0 ){
			
			foreach ( $other_fields_desc as $table_name => $table_setting ){

				$other_fields_string = isset($table_setting['fields']) ? implode (", ", array_keys($table_setting['fields'])) : '0';
				
				if ( isset($table_setting['fields']) ){
					
					$records = $wpdb->get_results( $wpdb->prepare("SELECT $other_fields_string
						FROM {$table_name}
						WHERE (user_id = %d)", $current_user->ID )
						, ARRAY_A );
					
					if (count($records) > 0){
								
						$result .= '<table class="gdpr-compliance-'. $table_name .' wp-list-table gdpr-compliance-table gdpr-compliance-userdata">
							<thead><tr><td>'. ( isset($table_setting['desc']) ? $table_setting['desc'] : strtoupper($table_name) ) .'</td></thead>
							<tbody>';
					
						foreach( $records as $record ){
							$result .= '<tr><td>';
							
							foreach ($record as $field_name => $value)
								$result .= ((strlen($table_setting['fields'][$field_name]) > 0) ? $table_setting['fields'][$field_name] : $field_name) .': '. htmlentities($value) .'<br/>';
							
							$result .= '</td></tr>';
						}
						
					}
				}
			}
			$result .= '</tbody></table>';
		}
		
		return $result;
	}
	
	
	/**
	 * Define handle for gdpruserdata shortcode.
	 * 
	 * @since    1.0.0
	 */
	public function shortcode_gdpruserdata_download_handle( $atts ) {
		
		$atts = shortcode_atts(
			array(
				'text_notlogged' => ''
			),
			$atts,
			'gdpruserdata_download'
		);
		
		$result = '<p class="gdpr-compliance-userdata-download">';
		
		if ( is_user_logged_in() )
			$result .= '<a href="' . admin_url( 'admin-post.php?action=gdpr_compliance_userdata_download.csv' ) . '">'. (isset( $atts['text'] ) ? $atts['text'] : __('Download user data', 'gdpr-compliance')) .'</a>';
		else
			$result .= (isset( $atts['text_notlogged'] ) ? $atts['text_notlogged'] : __('User must be logged to download his data', 'gdpr-compliance'));
		
		$result .= '</p>';
		
		return $result;
		
	}
	
	
	private function startsWith($haystack, $needle) {
		 $length = strlen($needle);
		 return (substr($haystack, 0, $length) === $needle);
	}

}