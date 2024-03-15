<?php
/**
 * Provide useful functions
 *
 * @since      1.0.0
 * @package    gdpr-compliance
 * @subpackage gdpr-compliance/admin/partials
 * @author     Scribit <wordpress@scribit.it>
 */

function gdpr_compliance_get_post_types_objects($show_ui = true, $built_in = null){
	
	$args = array(
		'public'   => true,
		'show_ui' => $show_ui
	);
	if ($built_in != null) $args['_builtin'] = $built_in;

	$types = get_post_types($args);
	foreach ($types as &$type){
		$type_object = get_post_type_object($type);
		$type = array('name' => $type);
		$type['label'] = $type_object->label;
	}
	
	return $types;
	
}

function gdpr_compliance_sanitize_userdata_setting($array_from, &$array_to){

	if (isset($array_from))
		foreach ($array_from as $name => $value){
			$item = array('enabled' => (isset($value['enabled']) && $value['enabled'] == 'on'), 'desc' => sanitize_text_field($value['desc']));
			$array_to[$name] = $item;
		}
	
}