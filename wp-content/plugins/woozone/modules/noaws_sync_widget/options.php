<?php

global $WooZone;

// Fix Sync Issue
function WooZone_fix_issue_sync( $istab = '', $is_subtab='' ) {
	global $WooZone;
   
	$html = array();

	//$options = $WooZone->settings();
	$options = get_option( 'WooZone_noaws_sync_widget', array() );

	$html[] = '<div class="WooZone-bug-fix WooZone-bug-fix panel-body WooZone-panel-body WooZone-form-row fix_issue_sync-wrapp' . ($istab!='' ? ' '.$istab : '') . ($is_subtab!='' ? ' '.$is_subtab : '') . '" style="line-height: 35px;">';

	// products in trash after X tries
	$val_trash = $WooZone->sync_tries_till_trash;
	if ( isset($options['fix_issue_sync'], $options['fix_issue_sync']['trash_tries']) ) {
		$val_trash = $options['fix_issue_sync']['trash_tries'];
	}
	
	$html[] = '<div>';
	$html[] = '<label style="display: inline; float: none;" for="fix_issue_sync-trash_tries">' . __('Put amazon products in trash when syncing after: ', $WooZone->localizationName) . '</label>';

	ob_start();
?>
		<select id="fix_issue_sync-trash_tries" name="fix_issue_sync[trash_tries]" style="width: 120px; margin-left: 18px;">
			<?php
			foreach (array(1 => 'First try', 2 => 'Second try', 3 => 'Third try', 4 => '4th try', 5 => '5th try', -1 => 'Never') as $kk => $vv){
				echo '<option value="' . ( $kk ) . '" ' . ( $val_trash == $kk ? 'selected="selected"' : '' ) . '>' . ( $vv ) . '</option>';
			} 
			?>
		</select>&nbsp;&nbsp;
<?php
	$html[] = ob_get_contents();
	ob_end_clean();

	//$html[] = '<input type="button" class="WooZone-button green" style="width: 160px;" id="fix_issue_sync-save_setting" value="' . ( __('Verify how many', $WooZone->localizationName) ) . '">';
	$html[] = '<span style="margin: 0px; margin-left: 10px; display: block;" class="response_save"></span>';
	$html[] = '</div>';
	
	// restore products with status
	$val_restore = 'publish';
	if ( isset($options['fix_issue_sync'], $options['fix_issue_sync']['restore_status']) ) {
		$val_restore = $options['fix_issue_sync']['restore_status'];
	}

	/*
	$html[] = '<div>';
	$html[] = '<input type="button" class="WooZone-form-button-small WooZone-form-button-primary" style="vertical-align:middle;line-height:12px;" id="fix_issue_sync-fix_now" value="' . ( __('Restore now', $WooZone->localizationName) ) . '">';
	$html[] = '<label style="display: inline; float: none;" for="fix_issue_sync-restore_status">' . __('trashed amazon products (and variations) | their NEW status: ', $WooZone->localizationName) . '</label>';

	ob_start();
?>
		<select id="fix_issue_sync-restore_status" name="fix_issue_sync[restore_status]" style="width: 120px; margin-left: 18px;">
			<?php
			foreach (array('publish' => 'Publish', 'draft' => 'Draft') as $kk => $vv){
				echo '<option value="' . ( $kk ) . '" ' . ( $val_restore == $kk ? 'selected="selected"' : '' ) . '>' . ( $vv ) . '</option>';
			} 
			?>
		</select>&nbsp;&nbsp;
<?php
	$html[] = ob_get_contents();
	ob_end_clean();

	$html[] = '<span style="margin: 0px; margin-left: 10px; display: block;" class="response_fixnow"></span>';
	$html[] = '</div>';
	*/

	$html[] = '</div>';

	// view page button
	ob_start();
	/*
?>
	<script>
	(function($) {
		var ajaxurl = '<?php echo admin_url('admin-ajax.php');?>'

		$("body").on("click", "#fix_issue_sync-save_setting", function(){

			$.post(ajaxurl, {
				'action' 		: 'WooZone_fix_issues',
				'sub_action'	: 'sync_tries_trash'
			}, function(response) {

				var $box = $('.fix_issue_sync-wrapp'), $res = $box.find('.response_save');
				$res.html( response.msg_html );
				if ( response.status == 'valid' )
					return true;
				return false;
			}, 'json');
		});

		// restore status
		$("body").on("click", "#fix_issue_sync-fix_now", function(){

			$.post(ajaxurl, {
				'action' 		: 'WooZone_fix_issues',
				'sub_action'	: 'sync_restore_status',
				'what'			: 'verify'
			}, function(response) {

				var $box = $('.fix_issue_sync-wrapp'), $res = $box.find('.response_fixnow');
				$res.html( response.msg_html );
				if ( response.status == 'valid' )
					return true;
				return false;
			}, 'json');
		});
		
		$("body").on("click", "#fix_issue_sync-fix_now_cancel", function(){
			var $box = $('.fix_issue_sync-wrapp'), $res = $box.find('.response_fixnow');
			$res.html('');
		});

		$("body").on("click", "#fix_issue_sync-fix_now_doit", function(){

			$.post(ajaxurl, {
				'action' 		: 'WooZone_fix_issues',
				'sub_action'	: 'sync_restore_status',
				'what'			: 'doit',
				'post_status'	: $('#fix_issue_sync-restore_status').val(),
			}, function(response) {

				var $box = $('.fix_issue_sync-wrapp'), $res = $box.find('.response_fixnow');
				$res.html( response.msg_html );
				if ( response.status == 'valid' )
					return true;
				return false;
			}, 'json');
		});
	})(jQuery);
	</script>
<?php
	$__js = ob_get_contents();
	ob_end_clean();
	$html[] = $__js;
	*/
  
	return implode( "\n", $html );
}

// reset products stats
function WooZone_reset_sync_stats( $istab = '', $is_subtab='' ) {
	global $WooZone;
   
	$html = array();

	//WooZone-bug-fix
	$html[] = '<div class="panel-body WooZone-panel-body WooZone-form-row reset_sync_stats2' . ($istab!='' ? ' '.$istab : '') . ($is_subtab!='' ? ' '.$is_subtab : '') . '">';

	$html[] = '<label class="WooZone-form-label" for="reset_sync_stats">' . __('Reset products SYNC stats:', $WooZone->localizationName) . '</label>';

	//$options = $WooZone->settings();
	$options = get_option( 'WooZone_noaws_sync_widget', array() );
	$val = 'yes';
	if ( isset($options['reset_sync_stats']) ) {
		$val = $options['reset_sync_stats'];
	}
		
	ob_start();
?>
		<select id="reset_sync_stats" name="reset_sync_stats" style="width: 240px; margin-left: 18px;">
			<?php
			$optionsList = array(
				'yes_all' 	=> 'YES - complete sync reset',
				'yes' 		=> 'YES - only reset last sync date',
				'no' 		=> 'NO'
			);
			foreach ($optionsList as $kk => $vv){
				echo '<option value="' . ( $kk ) . '" ' . ( $val == $kk ? 'selected="true"' : '' ) . '>' . ( $vv ) . '</option>';
			} 
			?>
		</select>&nbsp;&nbsp;
<?php
	$html[] = ob_get_contents();
	ob_end_clean();

	$html[] = '<input type="button" class="' . ( WooZone()->alias ) . '-form-button-small ' . ( WooZone()->alias ) . '-form-button-primary" id="WooZone-reset_sync_stats" value="' . ( __('reset Now ', $WooZone->localizationName) ) . '">';
	$html[] = '<span class="WooZone-form-note WooZone-reset-sync-help" style="display: block;"><ul><li><span>YES - complete sync reset</span> : reset all sync meta info for your amazon products</li><li><span>YES - only reset last sync date</span> : reset only the last sync date meta info for your products</li><li><span>NO</span> : don\'t reset sync for products</li></ul></span>';
	$html[] = '<div style="width: 100%; display: none; margin-top: 10px; " class="WooZone-response-options  WooZone-callout WooZone-callout-info"></div>';

	$html[] = '<p>If you encounter issues with products on sale after sync, please use the <b>Regenerate Product lookup tables</b> option available <a class="WooZone-form-button-small WooZone-form-button-primary" href="' . admin_url('admin.php?page=wc-status&tab=tools') . '" target="_blank">here</a></p>';
	
	$html[] = '</div>';
	// view page button
	ob_start();
?>
	<script>
	(function($) {
		var ajaxurl = '<?php echo admin_url('admin-ajax.php');?>'

		$("body").on("click", "#WooZone-reset_sync_stats", function(){

			$.post(ajaxurl, {
				'action' 		: 'WooZoneNoAWS_SyncWidget',
				'sub_action'	: 'reset_sync_stats',
				'what'			: $('#reset_sync_stats').val()
			}, function(response) {

				var $box = $('.reset_sync_stats2'), $res = $box.find('.WooZone-response-options');
				$res.html( response.msg ).show();
				if ( response.status == 'valid' )
					return true;
				return false;
			}, 'json');
		});
	})(jQuery);
	</script>
<?php
	$__js = ob_get_contents();
	ob_end_clean();
	$html[] = $__js;
  
	return implode( "\n", $html );
}

echo json_encode(array(
	$tryed_module['db_alias'] => array(
		
		/* define the form_sizes  box */
		'noaws_sync_widget' => array(
			'title' => 'No AWS Keys Sync Widget Settings',
			'icon' => '{plugin_folder_uri}images/16.png',
			'size' => 'grid_4', // grid_1|grid_2|grid_3|grid_4
			'header' => true, // true|false
			'toggler' => false, // true|false
			'buttons' => true, // true|false
			'style' => 'panel', // panel|panel-widget
			
			// create the box elements array
			'elements' => array(

				'sync_fields' 	=> array(
					'type' 		=> 'multiselect_left2right',
					'std' 		=> array(
						'title',
						'price',
					),
					'size' 		=> 'large',
					'rows_visible'	=> 2,
					'force_width'=> '150',
					'title' 	=> __('Sync product fields?', $WooZone->localizationName),
					'desc' 		=> __('Choose what product fields you want synced? <span style="color: red;">For sync to work, please make sure you select at least one product field to be updated (Price or Title)!</span>', $WooZone->localizationName),
					'info'		=> array(
						'left' => 'All items list',
						'right' => 'Your chosen items from list'
					),
					'options' 	=> array(
						'title' => 'Product Title',
						'price' => 'Product Price',
					)
				),

				'keep_old_price_if_new_empty' => array(
					'type' => 'select',
					'std' => 'yes',
					'size' => 'large',
					'force_width' => '100',
					'title' => 'Keep old price if new price is empty?',
					'desc' => 'Choose YES if you want to keep product old price if new product price (from widget) is empty (zero)! If NO, then your product price will become empty (zero).',
					'options' => array(
						'yes' => 'YES',
						'no' => 'NO'
					)
				),

				'delete_variations_if_parent_notfound' => array(
					'type' => 'select',
					'std' => 'yes',
					'size' => 'large',
					'force_width' => '100',
					'title' => 'Delete variations if parent not found?',
					'desc' => 'Choose YES if you want to delete all variations (childrens) of parent product if the parent product is not found in widget!',
					'options' => array(
						'yes' => 'YES',
						'no' => 'NO'
					)
				),

				'sync_choose_country' => array(
					'type' => 'select',
					'std' => 'import_country',
					'size' => 'large',
					'force_width' => '350',
					'title' => 'Amazon location for sync',
					'desc' => 'With this setting you can choose which "amazon location" to use to sync products. Available values: <br /><span style="color: red;">Use current "amazon location" setting</span> : is the current setting from amazon config module<br /><span style="color: red;">Use product import country</span> : is the "amazon location" setting which was active when you\'ve imported the product.',
					'options' => array(
						'default' => 'Use current "amazon location" setting (DEFAULT)',
						'import_country' => 'Use product import country (RECOMMENDED)'
					)
				),

				'fix_issue_sync' => array(
					'type' => 'html',
					'std' => '',
					'size' => 'large',
					'title' => 'Sync Issue',
					'html' => WooZone_fix_issue_sync( '__tab4', '__subtab_amazon' )
				),

				'reset_sync_stats_now' => array(
					'type' => 'html',
					'std' => '',
					'size' => 'large',
					'title' => 'Reset SYNC stats',
					'html' => WooZone_reset_sync_stats( '__tab4', '__subtab_amazon' )
				),
			)
		)
	)
));