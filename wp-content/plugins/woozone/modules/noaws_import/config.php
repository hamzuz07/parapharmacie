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
		'noaws_import' => array(
			'version' => '1.0',
			'menu' => array(
				'order' => 5,
				'show_in_menu' => true,
				'title' => 'NO API Keys Import',
				'icon' => 'import-ext'
			),
			'in_dashboard' => array(
				'icon' 	=> 'import-ext',
				'url'	=> admin_url("admin.php?page=WooZone_no_aws_keys_import") //admin_url("admin.php?page=WooZone#!/noaws_import")
			),
			'help' => array(
				'type' => 'remote',
				'url' => 'http://docs.aa-team.com/woocommerce-amazon-affiliates/documentation/'
			),
			'description' => "This module allows you to Import Products from Amazon without any Product Advertising API keys!",
			'module_init' => 'init.php',
			'load_in' => array(
				'backend' => array(
					'admin.php?page=WooZone_no_aws_keys_import',
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
