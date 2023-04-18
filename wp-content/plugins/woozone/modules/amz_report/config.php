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
		'amz_report' => array(
			'version' => '1.0',
			'menu' => array(
				'order' => 5,
				'show_in_menu' => true,
				'title' => 'Associates Reports <i style="font-size: 11px;position: relative; top: -5px;">*BETA</i>',
				'icon' => 'stats'
			),
			'in_dashboard' => array(
				'icon' 	=> 'stats',
				'url'	=> admin_url("admin.php?page=WooZone_amz_report") //admin_url("admin.php?page=WooZone#!/noaws_import")
			),
			'help' => array(
				'type' => 'remote',
				'url' => 'http://docs.aa-team.com/woocommerce-amazon-affiliates/documentation/'
			),
			'description' => "This module allows you to display an earnings report chart from your Amazon Associates Account. Because it's in beta testing, it only works for Amazon United States.",
			'module_init' => 'init.php',
			'load_in' => array(
				'backend' => array(
					'admin.php?page=WooZone_amz_report',
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
