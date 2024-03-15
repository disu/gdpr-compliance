<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the gdpr-compliance, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    gdpr-compliance
 * @subpackage gdpr-compliance/admin
 * @author     Scribit <wordpress@scribit.it>
 */
class Gdpr_Compliance_Admin
{

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
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {

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


        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/gdpr-compliance-admin.css', array(), $this->version, 'all');

        if (isset($_GET['page']) && ($_GET['page'] == GDPR_COMPLIANCE_PLUGIN_SLUG)) {
            wp_register_style('font-awesome', 'https://use.fontawesome.com/releases/v5.0.6/css/all.css');
            wp_enqueue_style('font-awesome');
        }
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {

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

        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/gdpr-compliance-admin.js', array( 'jquery' ), $this->version, false);
    }

    /**
     * Define menu items for backend console.
     *
     * @since    1.0.0
     * @access   private
     */
    public function admin_menu()
    {
        require_once plugin_dir_path(__FILE__) . 'partials/gdpr-compliance-admin-display.php';

        add_options_page(
            __("GDPR Compliance", "gdpr-compliance"),
            __("GDPR Compliance", "gdpr-compliance"),
            "manage_options",
            GDPR_COMPLIANCE_PLUGIN_SLUG,
            "gdpr_compliance_admin_page_handler"
        );
    }

    public function admin_notices()
    {
        ?>
		<div class="error notice">
			<p><?php _e('There has been an error. Bummer!', 'my_plugin_textdomain'); ?></p>
		</div>
		<?php
    }

    public function admin_bar_menu($wp_admin_bar)
    {
        $args = array(
            'id'     => 'menu_id',
            'title'	=>	'title',
            'meta'   => array( 'class' => 'first-toolbar-group' ),
        );
        $wp_admin_bar->add_node($args);

        // add child items
        $args = array();
        array_push($args, array(
            'id'		=>	'id_sub',
            'title'		=>	'title_sub',
            'href'		=>	'sub_link',
            'parent'	=>	'menu_id',
        ));

        foreach ($args as $each_arg) {
            $wp_admin_bar->add_node($each_arg);
        }
    }

    public function gdpr_compliance_actions_links($links)
    {
        $settings_link = '<a href="options-general.php?page=' . GDPR_COMPLIANCE_PLUGIN_SLUG . '">' . __('Settings', 'gdpr-compliance') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    public function gdpr_compliance_user_table($column)
    {
        $column['download_gdpr_data'] = 'GDPR Data';
        return $column;
    }

    public function gdpr_compliance_user_table_row($val, $column_name, $user_id)
    {
        if (!current_user_can( 'edit_users' )) return $val;

        switch ($column_name) {
            case 'download_gdpr_data':
                return '<a class="button" href="'.
                    admin_url('admin-post.php?action=gdpr_compliance_userdata_download.csv&user_id='.$user_id) .'">Download</button>';
                break;
            default:
        }
        return $val;
    }

    public function gdpr_compliance_footer_text()
    {

        // Show footer only in plugin pages
        if (strpos(get_current_screen()->id, 'settings_page_gdpr-compliance') !== 0) {
            return;
        }

        $url = 'https://www.scribit.it';
        echo '<span class="scribit_credit">'.sprintf('%s <a href="%s" target="_blank">Scribit</a>', esc_html(__('GDPR Compliance is powered by', 'gdpr-compliance')), esc_url($url)).'</span>';
    }

    public function gdpr_compliance_userdata_download()
    {
        if (!current_user_can( 'edit_users' )) return false;

        if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
            $user_id = $_GET['user_id'];
        } else {
            $current_user = wp_get_current_user();
            if (0 == $current_user->ID) {
                return false;
            }

            $user_id = $current_user->ID;
        }

        header('Pragma: no-cache');
        header('Content-Type: application/xls; charset=utf-8');
        header('Content-Disposition: attachment; filename=Userdata_'. $user_id .'_'. date("Y-m-d") .'.xls');

        global $wpdb;
        $result = '';
        $settings = json_decode(get_option(GDPR_COMPLIANCE_USERDATA_SETTINGNAME), true);	/* Absociative array */


        // USERS

        $user_fields = array();
        if (isset($settings))
            foreach ($settings['others'] as $field => $setting) {
                if ($setting['enabled'] && $this->startsWith($field, $wpdb->prefix .'users')) {		// Include only users table
                    $user_fields[$field] = $setting;
                }
            }

        $usermeta_fields = array();
        if (isset($settings))
            foreach ($settings['usermetas'] as $usermeta => $setting) {
                if ($setting['enabled']) {
                    $usermeta_fields[$usermeta] = $setting['desc'];
                }
            }

        if (count($user_fields) > 0 || count($usermeta_fields) > 0) {
            $result .= strtoupper(__('User data', 'gdpr-compliance')) ."\n";
        }

        if (count($user_fields) > 0) {
            foreach ($user_fields as $field => $setting) {
                $point_index = strpos($field, '.');
                if ($point_index) {
                    $table_name = substr($field, 0, $point_index);
                    $value = $wpdb->get_results($wpdb->prepare("SELECT GROUP_CONCAT({$field}) AS value
						FROM {$table_name}
						WHERE (id = %d)
						LIMIT 1", $user_id), ARRAY_A);

                    $result .= ((strlen($setting['desc']) > 0) ? $setting['desc'] : substr($field, $point_index + 1)) ."\t". htmlentities($value[0]['value']) ."\n";
                }
            }
        }

        // USERMETAS

        if (count($usermeta_fields) > 0) {
            $usermeta_fields_string = implode("', '", array_keys($usermeta_fields));

            if (strlen($usermeta_fields_string) > 0) {
                $usermetas_values = $wpdb->get_results($wpdb->prepare("SELECT meta_key, meta_value
					FROM {$wpdb->prefix}usermeta
					WHERE user_id = %d
						AND meta_key in ('". $usermeta_fields_string ."')", $user_id), ARRAY_A);

                foreach ($usermetas_values as $usermetas_value) {
                    $result .= ((strlen($usermeta_fields[$usermetas_value['meta_key']]) > 0) ? $usermeta_fields[$usermetas_value['meta_key']] : ucwords($usermetas_value['meta_key'])) ."\t". htmlentities($usermetas_value['meta_value']) ."\n";
                }
            }
        }

        if (count($user_fields) > 0 || count($usermeta_fields) > 0) {
            $result .= "\n";
        }



        // COMMENTS

        if (isset($settings['types']['comment']) && ($settings['types']['comment']['enabled'] == 1)) {
            $args = array(
                'author__in' => $user_id,
                'orderby' => 'date',
                'order' => 'DESC'
            );
            $comments = get_comments($args);
            if (count($comments) > 0) :
                $result .= strtoupper((strlen($settings['types']['comment']['desc']) > 0) ? $settings['types']['comment']['desc'] : __('Comments')) ."\t".
                    __('Date', 'gdpr-compliance') ."\t".
                    _x('Status', 'Post format') ."\n";
            foreach ($comments as $comment) {
                $result .= get_permalink($comment->comment_post_ID) ."\t". $comment->comment_date ."\t". ($comment->comment_approved ? _x('Approved', 'comment status') : '') ."\n";
            }

            $result .= "\n";
            endif;
        }



        // POSTS TYPE AND META

        $posts_fields = array();
        $postmeta_fields_desc = array();

        if (isset($settings))
            foreach ($settings['types'] as $postmeta => $setting) {
                if ($setting['enabled']) {
                    $meta_fields = explode('.', $postmeta);	// Every postmeta should be composed as: posttype.metakey

                    if (count($meta_fields) == 2) {
                        $postmeta_fields_desc[$meta_fields[0]]['metas'][$meta_fields[1]] = $setting['desc'];
                    } elseif (count($meta_fields) == 1) {
                        $postmeta_fields_desc[$meta_fields[0]]['desc'] = $setting['desc'];
                    }
                }
            }

        if (count($postmeta_fields_desc) > 0) {
            foreach ($postmeta_fields_desc as $post_type => $post_settings) {
                $postmeta_fields_string = isset($post_settings['metas']) ? implode("', '", array_keys($post_settings['metas'])) : '0';

                $wpdb->query("DROP TABLE IF EXISTS postmeta_fields");
                $wpdb->query("CREATE TEMPORARY TABLE IF NOT EXISTS postmeta_fields (field varchar(128))");
                if (isset($post_settings['metas'])) {
                    $postmeta_fields_db_insert = "('". implode("'), ('", array_keys($post_settings['metas'])) ."')";
                    $wpdb->query("INSERT INTO postmeta_fields (field) VALUES $postmeta_fields_db_insert");
                }

                $posts = $wpdb->get_results($wpdb->prepare("SELECT Posts.id AS post_id, Posts.post_title, Posts.post_status,
						postmeta_fields.field AS meta_key, PostMeta.meta_value
					FROM {$wpdb->prefix}posts Posts
						LEFT OUTER JOIN postmeta_fields ON 1=1
						LEFT OUTER JOIN {$wpdb->prefix}postmeta PostMeta ON Posts.id = PostMeta.post_id
							AND PostMeta.meta_key = postmeta_fields.field
					WHERE Posts.post_author = %d
						AND Posts.post_type = '{$post_type}'
					ORDER BY Posts.id", $user_id), ARRAY_A);

                if (count($posts) > 0) {
                    $i = 0;
                    $last_post = 0;
                    $result .= strtoupper((isset($post_settings['desc']) > 0) ? $post_settings['desc'] : $post_type) ."\n";
                    $result .= __('Title', 'gdpr-compliance') ."\t". __('URL', 'gdpr-compliance') ."\t". _x('Status', 'Post format') ."\t";

                    // Print post fields header
                    if (isset($post_settings['metas'])) {
                        foreach ($post_settings['metas'] as $meta_key => $meta_desc) {
                            $result .= ((strlen($meta_desc) > 0) ? $meta_desc : $meta_key) ."\t";
                        }
                    }
                    $result .= "\n";

                    foreach ($posts as $post) {
                        if ($last_post != $post['post_id']) {
                            if ($last_post != 0) {
                                $result .= "\n";
                            }

                            // Print post link
                            $result .= $post['post_title'] ."\t". get_permalink($post['post_id']) ."\t";

                            // Print status
                            switch ($post['post_status']) {
                                case 'trash': $result .= _x('Trash', 'post status'); break;
                                case 'private': $result .= _x('Private', 'post status'); break;
                                case 'pending': $result .= _x('Pending', 'post status'); break;
                                case 'draft': $result .= _x('Draft', 'post status'); break;
                                case 'future': $result .= _x('Scheduled', 'post status'); break;
                                case 'publish': $result .= _x('Published', 'post status'); break;
                            }
                            $result .= "\t";

                            $last_post = $post['post_id'];
                        }

                        // Print meta fields
                        $result .= $post['meta_value'] ."\t";
                    }
                    $result .= "\n\n";
                }
            }
        }


        // OTHER TABLES

        $other_fields_desc = array();
        if (isset($settings))
            foreach ($settings['others'] as $field => $setting) {
                if ($setting['enabled']) {
                    if ($this->startsWith($field, $wpdb->prefix .'users')) {
                        continue;
                    }	// Esclude users table

                    $table_fields = explode('.', $field);	// Every information should be composed as: tablename.fieldname
                    if (count($table_fields) < 1 || count($table_fields) > 2) {
                        continue;
                    }

                    if (!isset($other_fields_desc[$table_fields[0]])) {
                        $other_fields_desc[$table_fields[0]] = array();
                    }

                    if (count($table_fields) == 2) {
                        $other_fields_desc[$table_fields[0]]['fields'][$table_fields[1]] = $setting['desc'];
                    } else {
                        $other_fields_desc[$table_fields[0]]['desc'] = $setting['desc'];
                    }
                }
            }

        if (count($other_fields_desc) > 0) {
            foreach ($other_fields_desc as $table_name => $table_setting) {
                $other_fields_string = isset($table_setting['fields']) ? implode(", ", array_keys($table_setting['fields'])) : '0';


                if (isset($table_setting['fields'])) {
                    $records = $wpdb->get_results($wpdb->prepare("SELECT $other_fields_string
						FROM {$table_name}
						WHERE (user_id = %d)", $user_id), ARRAY_A);

                    if (count($records) > 0) {
                        $result .= strtoupper(isset($table_setting['desc']) ? $table_setting['desc'] : strtoupper($table_name)) ."\n";

                        // Print post fields header
                        if (isset($table_setting['fields'])) {
                            foreach ($table_setting['fields'] as $field_name => $field_desc) {
                                $result .= ((strlen($field_desc) > 0) ? $field_desc : $field_name) ."\t";
                            }
                        }
                        $result .= "\n";

                        foreach ($records as $record) {
                            foreach ($record as $field_name => $value) {
                                $result .= $value ."\t";
                            }

                            $result .= "\n";
                        }
                    }
                }
                $result .= "\n";	// Next Table
            }

            $result .= "\n";
        }

        echo $result;
        exit;
    }

    public function excelCleanData(&$str)
    {
        // escape tab characters
        $str = preg_replace("/\t/", "\\t", $str);

        // escape new lines
        $str = preg_replace("/\r?\n/", "\\n", $str);

        // convert 't' and 'f' to boolean values
        if ($str == 't') {
            $str = 'TRUE';
        }
        if ($str == 'f') {
            $str = 'FALSE';
        }

        // force certain number/date formats to be imported as strings
        if (preg_match("/^0/", $str) || preg_match("/^\+?\d{8,}$/", $str) || preg_match("/^\d{4}.\d{1,2}.\d{1,2}/", $str)) {
            $str = "'$str";
        }

        // escape fields that include double quotes
        if (strstr($str, '"')) {
            $str = '"' . str_replace('"', '""', $str) . '"';
        }
    }

    private function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }
}
