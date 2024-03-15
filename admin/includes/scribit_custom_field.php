<?php
/**
 * Backend content edit page customization.
 *
 * Provide functionalities for add custom field to edit contents page, such as textbox, radio buttons, checkboxes and so on.
 *
 * @since      1.0.0
 * @package    gdpr-compliance
 * @subpackage gdpr-compliance/admin/includes
 * @author     Scribit <wordpress@scribit.it>
 */
class ScribitCustomField {
 
    public $parameters;
    public $fields;
	
	/*
	* POSSIBLE FIELDS
	* 	name:			name of HTML element
	*	class:			class of HTML element
	*	style:			CSS style to add to HTML element
	*	label:			label text for main element
	*	style_label:	CSS style to add to label element
	*
	*	options:		array of elements for Select type element. Every element must be in this format: array('label' => element_label', 'value' => 'element_value')
	*	allow_empty:	allow empty value for Select type element.
	*/
 
    public function __construct( $params = array(), $fields = array() ) {
		
        $this->parameters = $params; // parametri metabox
        $this->fields = $fields; // campi
        add_action( 'add_meta_boxes', array( &$this, 'register' ) ); // crea la metabox
        //add_action( 'save_post', array( &$this, 'save' ) ); // salva i campi
         
        // aggiungo uno script per i campi immagine
        wp_register_script( 'cf', plugins_url() . '/'. GDPR_COMPLIANCE_PLUGIN_SLUG .'/admin/js/scribit-custom-fields.js', array( 'jquery' ), GDPR_COMPLIANCE_VERSION, true );
        wp_enqueue_script( 'cf' );
		
    }
 
    // crea la metabox
    public function register() {
		
        $config = $this->parameters;
        add_meta_box( $config['id'], $config['title'], array( &$this, 'output' ), $config['screen'], $config['context'], $config['priority'] );
		
    }
     
    // crea il codice HTML dei campi
    public function output( $post ) {
		
        $form_fields = $this->fields;
        $post_id = $post->ID;
        $html = '';
        $output = array();
		
        foreach( $form_fields as $form_field )
            $output[] = $this->_display( $form_field, $post_id );

        $html = implode( "\n", $output );
        echo $html;
		
    }
     
    // salva il campo
    public function save( $post_id ) {
		
        $form_fields = $this->fields;
        $config = $this->parameters;
		
        if (!isset($_POST['post_type']) || $config['screen'] != $_POST['post_type'] ) {
            return $post_id;
        }
        if ( !current_user_can( 'edit_post', $post_id ) ) {
            return $post_id;
        }
        foreach( $form_fields as $form_field ) {
            $name = $form_field['name'];
            update_post_meta( $post_id, $name, sanitize_text_field( $_POST[$name] ) );
        }   
		
    }
     
    // crea una stringa HTML a seconda del tipo di campo
    private function _display( $field, $id ) {
		
        $field_html = '';
        if( !is_array( $field ) ) {
            return '';
        }
		
		if (isset( $field['name'] )){
			$value = get_post_meta( $id, $field['name'], true );
			if ( strlen($value)==0 && isset( $field['default'] ) )
				$value = $field['default'];
		}
		else $field['name'] = '';
		
		if (!isset($field['class'])) $field['class'] = '';
		if (!isset($field['style'])) $field['style'] = '';
		
		$label_tag = (isset($field['label'])) ? '<label for="' . $field['name'] . '">' . $field['label'] . '</label>' : '';
		
		switch( $field['type'] ) {
			
            case 'text':
                $field_html = '<span style="' . $field['style'] . '" class="' . $field['class'] . '">' . $label_tag . '<input type="' . $field['type'] . '" name="' . $field['name'] . '" placeholder="' . $field['label'] . '" value="' . $value . '" /></span>';
                break;
				
            case 'checkbox':
            case 'radio':
                $checked = ( $value == $field['value'] ) ? ' checked="checked"' : '';
                $field_html = '<span style="' . $field['style'] . '" class="' . $field['class'] . '"><input type="' . $field['type'] . '" name="' . $field['name'] . '" id="' . $field['name'] . '" value="' . $value . '"' . $checked . ' />' . $label_tag . '</span>';
                break;
				
            case 'textarea':
                $field_html = '<span style="' . $field['style'] . '" class="' . $field['class'] . '">' . $label_tag . '
					<textarea class="' . $field['class'] . '" cols="20" rows="' . ( ( isset( $field['rows'] ) && is_numeric( $field['rows'] ) ) ? $field['rows'] : 3 ) . '" style="display: block; width: 100%;' . $field['style'] . '" id="' . $field['name'] . '" name="' . $field['name'] . '" placeholder="' . $field['label'] . '">' . esc_html( $value ). '</textarea></span>';
                break;
				
            case 'select':
                $field_html = '<span style="' . $field['style'] . '" class="' . $field['class'] . '">' . $label_tag;
                $field_html .= '<select name="' . $field['name'] . '" id="' . $field['name'] . '">';
				
				if ( !isset($field['allow_empty']) || $field['allow_empty'] )
					$field_html .= '<option value=""></option>';
 
				if (isset($field['options']))
					foreach( $field['options'] as $opt ) {
						$selected = ( $field['value'] == $opt['value'] ) ? ' selected="selected"' : '';
						$field_html .= '<option value="' . $opt['value'] . '"' . $selected . '>' . $opt['label'] . '</option>';
					}
                $field_html .= '</select></span>';
                break;
				
            case 'image':
                $field_html = '<span style="' . $field['style'] . '" class="' . $field['class'] . '">' . $label_tag;
                $image = ( isset ( $value ) && is_numeric( $value ) ) ? wp_get_attachment_image_src( $value, 'large' )[0] :
					( isset($field['url']) ? $field['url'] : '' ); // thumbnail, medium, large, full
                if( $image !== '' ) {
                    $field_html .= '<img src="' . $image . '" style="cursor:pointer; max-width: 100%; height: auto; width: auto" />';
                }
                $field_html .= '</span>';
                break;
				
			case 'submit':
                $field_html = '<span style="' . $field['style'] . '" class="' . $field['class'] . '"><span class="spinner"></span>
					<input id="' . $field['name'] . '" name="' . $field['name'] . '" type="submit" class="button button-primary button-large" value="' . $field['label'] . '"></span>';
                break;
				
			case 'button':
                $field_html = '<span style="' . $field['style'] . '" class="' . $field['class'] . '"><span class="spinner"></span>
					<input id="' . $field['name'] . '" name="' . $field['name'] . '" type="button" class="button button-primary button-large" value="' . $field['label'] . '"></span>';
                break;
				
			case 'div':
                $field_html = '<div id="' . $field['id'] . '" style="' . $field['style'] . '" class="' . $field['class'] . '">' . $field['label'] . '</div>';
                break;
 
            default:
                break;
        }
 
        return $field_html;
    }
 
}

?>