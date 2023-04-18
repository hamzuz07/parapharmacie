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
		'import_stats' => array(
			'version' => '1.0',
			'menu' => array(
				'order' => 4,
				'show_in_menu' => false,
				'title' => 'Import Stats',
				'icon' => 'auto_import'
			),
			'in_dashboard' => array(
				//admin_url("admin.php?page=WooZone#!/auto_import")
				/*array(
					'title'	=> 'Auto Import Queue',
					'icon' 	=> 'images/32.png',
					'url'	=> 'admin.php?page=WooZone_auto_import_queue'
				),
				array(
					'title'	=> 'Auto Import Search',
					'icon' 	=> 'images/32.png',
					'url'	=> 'admin.php?page=WooZone_auto_import_search'
				)*/
				'icon' 	=> 'auto_import',
				'url'	=> admin_url("admin.php?page=WooZone_import_stats")
			),
			'help' => array(
				'type' => 'remote',
				'url' => 'http://docs.aa-team.com/woocommerce-amazon-affiliates/documentation/amazon-asin-grabber/'
			),
			'description' => "Here you see the logs for your imported products.",
			'module_init' => 'init.php',
			'load_in' => array(
				'backend' => array(
					'admin.php?page=WooZone_import_stats',
					'admin-ajax.php'
				),
				'frontend' => false
			),
			'javascript' => array(
				'admin',
				'hashchange',
				'tipsy',
				'jquery.simplemodal'
			),
			'css' => array(
				'admin',
				'tipsy',
				'jquery.simplemodal'
			),
            'errors' => array()
		)
	)
);