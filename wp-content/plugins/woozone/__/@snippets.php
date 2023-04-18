<?php
die();

$plugin_path = plugin_dir_path( (__FILE__)  );
$plugin_path = str_replace('lib/scripts/amazon/', '', $plugin_path);
$plugin_path = str_replace('lib\scripts\amazon/', '', $plugin_path); // for Windows servers
require_once( $plugin_path . 'composer/amazon-paapi/paapi5-php-sdk/vendor/autoload.php' );





		private function check_amazon_newlocations( $pms=array() ) {
			$pms = array_replace_recursive( array(
			), $pms);
			extract( $pms );

			$need_check = $this->plugin_integrity_need_verification('check_amazon_newlocations');

			if ( ! $need_check['status'] && ! $force ) {
				return true; // don't need verification yet!
			}

			$table_ = $this->db->prefix . 'amz_locale_reference';

			// SELECT COUNT(a.ID) AS nb, a.country FROM $table_ AS a WHERE 1=1 GROUP BY a.country ORDER BY nb desc;
			$query = "SELECT a.country FROM $table_ AS a WHERE 1=1 GROUP BY a.country;";
			$res = $this->db->get_results( $query, OBJECT_K );
			if ( ! is_array($res) || empty($res) ) {

				$this->plugin_integrity_update_time('check_amazon_newlocations', array(
					'status'	=> 'invalid',
					'html'		=> __('Check amazon new locations: amz_locale_reference doesn\'t have the needed rows', 'woozone'),
				));
				return false; //something was wrong!
			}
			$res = array_keys($res);

			// all fine
			$this->plugin_integrity_update_time('check_amazon_newlocations', array(
				'timeout'	=> time(),
				'status'	=> 'valid',
				'html'		=> sprintf( __('Check plugin table %s: installed ok.', 'woozone'), $table_ ),
			));
			return true; // all is fine!
		}