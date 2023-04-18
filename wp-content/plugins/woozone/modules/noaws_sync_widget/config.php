<?php
/**
 * Config file, return as json_encode
 * http://www.aa-team.com
 * =======================
 *
 * @author		Andrei Dinca, AA-Team
 * @version		1.0
 */
echo json_encode(
	array(
		'noaws_sync_widget' => array(
			'version' => '1.0',
			'menu' => array(
				'order' => 5,
				'show_in_menu' => true,
				'title' => 'No AWS Keys Synchronization',
				'icon' => 'auto_importpr'
			),
			'in_dashboard' => array(
				'icon' 	=> 'auto_importpr',
				'url'	=> admin_url("admin.php?page=WooZone_no_aws_keys_sync_widget")
				//admin_url("admin.php?page=WooZone#!/noaws_sync_widget")
			),
			'help' => array(
				'type' => 'remote',
				'url' => 'http://docs.aa-team.com/woocommerce-amazon-affiliates/documentation/'
			),
			'description' => "The No AWS Keys Synchronization Widget allows you to Sync Amazon Products (title & price fields!) without the need of Amazon PA API Keys!",
			'module_init' => 'init.php',
			'load_in' => array(
				'backend' => array(
					'admin.php?page=WooZone_no_aws_keys_sync_widget',
					'admin-ajax.php'
				),
				'frontend' => false
			),
			'javascript' => array(
				'admin',
				'hashchange',
				'tipsy'
			),
			'css' => array(
				'admin',
				'tipsy'
			)
		)
	)
);
