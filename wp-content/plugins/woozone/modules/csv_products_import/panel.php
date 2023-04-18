<?php 
/*
* Define class bulkImport
* Make sure you skip down to the end of this file, as there are a few
* lines of code that are very important.
*/
! defined( 'ABSPATH' ) and exit;

// load the modules managers class
$module_class_path = $module['folder_path'] . 'csvBulkImport.class.php';
if(is_file($module_class_path)) {
	
	require_once( 'csvBulkImport.class.php' );
	
	$WooZoneCSVBulkImport = new WooZoneCSVBulkImport($this->cfg, $module);
	
	$__module_is_setup_valid = $WooZoneCSVBulkImport->moduleValidation();
	$__module_is_setup_valid = (bool) $__module_is_setup_valid['status'];
	
	// print the lists interface
	$provider_status = WooZone()->provider_action_controller( 'can_import_products', 'amazon', array('msg_type' => 'box_demo') );
	if ( 'invalid' == $provider_status['status'] ) {
		echo $provider_status['msg_html'];
	}
	else {
		echo $WooZoneCSVBulkImport->printListInterface();
	}
}