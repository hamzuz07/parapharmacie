<?php
/*
* Define class Modules Manager List
* Make sure you skip down to the end of this file, as there are a few
* lines of code that are very important.
*/
! defined( 'ABSPATH' ) and exit;

if(class_exists('amazonDebug') != true) {

	class amazonDebug {
		/*
		* Some required plugin information
		*/
		const VERSION = '1.0';

		/*
		* Store some helpers config
		*
		*/
		public $cfg	= array();
		public $module	= array();
		public $networks	= array();
		public $the_plugin = null;
		
		/*
		http://docs.aws.amazon.com/AWSECommerceService/latest/DG/CHAP_ResponseGroupsList.html
		(function($) {
		  var $wrap = $('.informaltable');
		  
		  $wrap.find('li.listitem').each(function(i, el) {
		    var $this = $(el),
		        $alink = $this.find('a.link'),
		        val = $alink.text(),
		        href = $alink.attr('href');
		    
		    //href = 'http://docs.aws.amazon.com/AWSECommerceService/latest/DG/' + href;
		    
		    console.log( '\''+val+'\' => \''+href+'\',' );
		    
		  });
		}(jQuery));
		*/
		private static $ResponseGroups_baseurl = 'http://docs.aws.amazon.com/AWSECommerceService/latest/DG/';
		private static $ResponseGroups_parenturl = 'http://docs.aws.amazon.com/AWSECommerceService/latest/DG/CHAP_ResponseGroupsList.html';
		private static $ResponseGroups_default = array('Large', 'ItemAttributes', 'OfferFull', 'Variations', 'PromotionSummary');
		private static $ResponseGroups = array(
			'Accessories',
			'AlternateVersions',
			'BrowseNodeInfo',
			'BrowseNodes',
			'Cart',
			'CartNewReleases',
			'CartTopSellers',
			'CartSimilarities',
			'EditorialReview',
			'Images',
			'ItemAttributes',
			'ItemIds',
			'Large',
			'Medium',
			'MostGifted ',
			'MostWishedFor',
			'NewReleases',
			'OfferFull',
			'OfferListings',
			'Offers',
			'OfferSummary',
			'PromotionSummary',
			'RelatedItems',
			'Request',
			'Reviews',
			'SalesRank',
			'SearchBins',
			'Similarities',
			'Small',
			'TopSellers',
			'Tracks',
			'Variations',
			'VariationImages',
			'VariationMatrix',
			'VariationOffers',
			'VariationSummary',
		);
		private static $ResponseGroups_deprecated = array(
			"BrowseNodeInfo",
			"Cart",
			"CartNewReleases",
			"CartTopSellers",
			"CartSimilarities",
			"MostGifted ",
			"MostWishedFor ",
			"NewReleases",
			"OfferFull",
			"SearchBins",
			"TopSellers",
		);
		private static $ResponseGroups_new = array(
			"PromotionDetails",
			"VariationMinimum",
			"TagsSummary",
			"Tags",
			"MerchantItemAttributes",
			"Accessories",
			"Subjects",
			"ListmaniaLists",
			"SearchInside",
			"PromotionalTag",
			"Collections",
			"ShippingCharges",
			"ShippingOptions",
		);
		

		/*
		* Required __construct() function that initalizes the AA-Team Framework
		*/
		public function __construct($cfg, $module)
		{
			global $WooZone;
			
			$this->the_plugin = $WooZone;
			$this->cfg = $cfg;
			$this->module = $module;
		}
		
		public function moduleValidation() {
			$ret = array(
				'status'			=> false,
				'html'				=> ''
			);
  
			// AccessKeyID, SecretAccessKey, AffiliateId, main_aff_id
			
			// find if user makes the setup
			$module_settings = $this->the_plugin->settings();

			$module_mandatoryFields = array(
				'AccessKeyID'			=> false,
				'SecretAccessKey'		=> false,
				'main_aff_id'			=> false
			);
			if ( isset($module_settings['AccessKeyID']) && !empty($module_settings['AccessKeyID']) ) {
				$module_mandatoryFields['AccessKeyID'] = true;
			}
			if ( isset($module_settings['SecretAccessKey']) && !empty($module_settings['SecretAccessKey']) ) {
				$module_mandatoryFields['SecretAccessKey'] = true;
			}
			if ( isset($module_settings['main_aff_id']) && !empty($module_settings['main_aff_id']) ) {
				$module_mandatoryFields['main_aff_id'] = true;
			}
			$mandatoryValid = true;
			foreach ($module_mandatoryFields as $k=>$v) {
				if ( !$v ) {
					$mandatoryValid = false;
					break;
				}
			}
			if ( !$mandatoryValid ) {
				$error_number = 1; // from config.php / errors key
				
				$ret['html'] = $this->the_plugin->print_module_error( $this->module, $error_number, 'Error: Unable to use CSV Bulk Import module, yet!' );
				return $ret;
			}
			
			if( !$this->the_plugin->is_woocommerce_installed() ) {  
				$error_number = 2; // from config.php / errors key
				
				$ret['html'] = $this->the_plugin->print_module_error( $this->module, $error_number, 'Error: Unable to use Advanced Search module, yet!' );
				return $ret;
			}
			
			if( !extension_loaded('soap') ) {  
				$error_number = 3; // from config.php / errors key
				
				$ret['html'] = $this->the_plugin->print_module_error( $this->module, $error_number, 'Error: Unable to use Advanced Search module, yet!' );
				return $ret;
			}

			if( !(extension_loaded("curl") && function_exists('curl_init')) ) {  
				$error_number = 4; // from config.php / errors key
				
				$ret['html'] = $this->the_plugin->print_module_error( $this->module, $error_number, 'Error: Unable to use Advanced Search module, yet!' );
				return $ret;
			}
			
			$ret['status'] = true;
			return $ret;
		}

		public function printListInterface ()
		{
			global $WooZone;

			// find if user makes the setup
			$moduleValidateStat = $this->moduleValidation();
			if ( !$moduleValidateStat['status'] || !is_object($this->the_plugin->get_ws_object( $this->the_plugin->cur_provider )) || is_null($this->the_plugin->get_ws_object( $this->the_plugin->cur_provider )) )
				echo $moduleValidateStat['html'];
			else {

			$module_folder = $this->the_plugin->cfg['paths']['plugin_dir_url'] . 'modules/insane_import/';

			$amazon_settings = $WooZone->settings();
        		
			$html = array();
			$html[] = '<style type="text/css">#WooZone-amazonDebug { display: block } </style>';
			
			// hightlight.js
 			//$html[] = WooZone_asset_path( 'css', $this->module['folder_uri'] . 'lib/school_book.css', false );
 			//$html[] = WooZone_asset_path( 'js', $this->module['folder_uri'] . 'lib/highlight.pack.js', false );
			
			// collapsible
 			$html[] = WooZone_asset_path( 'css', $this->module['folder_uri'] . 'lib.collapsible/json.format.css', false );
 			$html[] = WooZone_asset_path( 'js', $this->module['folder_uri'] . 'lib.collapsible/json.format.js', false );
 			$html[] = WooZone_asset_path( 'js', $this->module['folder_uri'] . 'app.amazon_debug.js', false );

			ob_start();
		?>
			<div id="WooZone-amazonDebug">
				<div>



<!-- Parents TABS -->
<div class="WooZone-insane-container WooZone-big-buttons WooZone-insane-tabs" id="WooZone-wrap-loadproducts">

							<div class="WooZone-insane-panel-headline WooZone-top-headline">

								<?php /*<a href="#WooZone-content-amazon" data-provider="amazon" class="on"><?php _e('AMAZON', $this->the_plugin->localizationName);?></a>
								<a href="#WooZone-content-ebay" data-provider='ebay'><?php _e('EBAY', $this->the_plugin->localizationName);?></a>*/ ?>

								<?php if ($this->the_plugin->providers_is_enabled('amazon')) { ?>
								<a href="#WooZone-content-amazon" data-provider="amazon" class="on"><img src="<?php echo $module_folder;?>images/amz-logo.png" /></a>
								<?php } ?>
								<?php if ($this->the_plugin->providers_is_enabled('ebay')) { ?>
								<a href="#WooZone-content-ebay" data-provider='ebay'><img src="<?php echo $module_folder;?>images/ebay-logo.png" /></a>
								<?php } ?>
							</div>
							<div class="WooZone-insane-tabs-content">
								<div class="WooZone-content-scroll">

<!-- AMAZON PROVIDER IS ENABLED -->
<?php if ($this->the_plugin->providers_is_enabled('amazon')) { ?>
<div id="WooZone-content-amazon" class="WooZone-insane-tab-content">

	<!-- AMAZON Content Area -->
	<?php
	$provider_status = $this->the_plugin->provider_action_controller( 'is_valid', 'amazon', array() );

	if ( 'invalid' == $provider_status['status'] ) {

		$provider_status_addon = $this->the_plugin->provider_action_controller( 'has_addon_activated', 'amazon', array() );
		if ( 'invalid' == $provider_status_addon['status'] ) {
			echo $provider_status_addon['msg_html'];
		}
		else {
			echo $provider_status['msg_html'];
		}
	} else {
	?>

					<div class="WooZone-amzdbg-ResponseGroups-Head">
						<span style="display: none;" id="WooZone-amzdbg-datael" alt="<?php echo $this->module['folder_uri'] . 'lib.collapsible/'; ?>"></span>
						<input type="hidden" id="WooZone-amzdbg-default" name="WooZone-amzdbg-default" value="<?php echo implode(',', self::$ResponseGroups_default); ?>" />
						<input id="WooZone-amzdbg-rg[all]" type="checkbox" name="WooZone-amzdbg-rg[all]" value="all" <?php echo count(self::$ResponseGroups) == count(self::$ResponseGroups_default) ? 'checked="checked" ' : ''; ?>/>
						<label for="WooZone-amzdbg-rg[all]">Check / Uncheck All</label>
						
						<a href="#" class="WooZone-form-button WooZone-form-button-success" id="WooZone-amzdbg-rg-godefault">Restore to default reponse groups</a>

						<a href="<?php echo self::$ResponseGroups_parenturl; ?>" class="WooZone-form-button WooZone-form-button-info WooZone-amz-docs-details" target="_blank">Amazon Available Response Groups</a>
						
					</div>
					<?php /*<div style="clear: both;"></div>*/ ?>
					<ul class="WooZone-amzdbg-ResponseGroups">
						<?php
							$ResponseGroups = self::$ResponseGroups;
							//$ResponseGroups = array_diff($ResponseGroups, self::$ResponseGroups_deprecated);
							//$ResponseGroups = array_merge($ResponseGroups, self::$ResponseGroups_new);

							foreach($ResponseGroups as $key) {
								$checked = in_array($key, self::$ResponseGroups_default);
						?>
							<li>
								<input id="WooZone-amzdbg-rg[<?php echo $key; ?>]" type="checkbox" name="WooZone-amzdbg-rg[]" value="<?php echo $key; ?>" <?php echo $checked ? 'checked="checked" ' : ''; ?>/>
								<label for="WooZone-amzdbg-rg[<?php echo $key; ?>]">
									<a href="<?php echo self::$ResponseGroups_baseurl . "RG_$key.html"; ?>" target="_blank" class="<?php echo $checked ? 'on' : ''; ?>"><?php echo $key; ?></a>
								</label>
							</li>
						<?php
							}
						?>
					</ul>
					<div class="WooZone-amzdbg-exec">

						<label for="country_amazon">Amazon location:</label>
						<?php
							$opt_countries = $this->WooZone_amazon_countries();
							$def_country = $this->the_plugin->amz_settings['country'];
						?>
						<select id="country_amazon" name="country_amazon" class="small">
							<?php
							foreach ( $opt_countries as $key => $val ) {
								$is_selected = $key == $def_country ? ' selected="true"' : '';
								echo '<option value="' . $key . '"' . $is_selected . '>' . $val . '</option>';
							}
							?>
						</select>

						<label for="WooZone-amzdbg-asin-amazon">ASIN code:</label>
						<input id="WooZone-amzdbg-asin-amazon" type="text" class="" name="WooZone-amzdbg-asin-amazon" value="amz-B00KDRPW76" />
						
						<a href="#" class="WooZone-form-button WooZone-form-button-success WooZone-amzdbg-getAmzResponse" data-provider="amazon">Get Amazon Response</a>

						<?php
						if ( $this->the_plugin->is_aateam_devserver() ) {

							//$url = admin_url( 'admin-ajax.php?action=WooZoneAmazonDebugGetResponseDev' );
							$url = admin_url( 'admin-ajax.php' );

							$url_xml = $url;
							$url_json = $url;

							echo '<a href="' . $url_xml . '" class="WooZone-form-button WooZone-form-button-warning WooZone-amzdbg-getAmzResponse-dev" target="_blank" data-req_type="xml" data-provider="amazon">DEV: Get Amazon Response</a>';
							//echo '<a href="' . $url_json . '" class="WooZone-form-button WooZone-form-button-warning WooZone-amzdbg-getAmzResponse-dev" target="_blank" data-req_type="json">DEV: Get Amazon Response (json)</a>';
						}
						?>
					</div>

	<?php } ?>
	<!-- end Amazon Content Area -->
						
</div>
<?php } ?>
<!-- end AMAZON PROVIDER IS ENABLED -->



<!-- EBAY PROVIDER IS ENABLED -->
<?php if ($this->the_plugin->providers_is_enabled('ebay')) { ?>
<div id="WooZone-content-ebay" class="WooZone-insane-tab-content">

	<!-- Ebay Content Area -->
	<?php
	$provider_status = $this->the_plugin->provider_action_controller( 'is_valid', 'ebay', array() );

	if ( 'invalid' == $provider_status['status'] ) {

		$provider_status_addon = $this->the_plugin->provider_action_controller( 'has_addon_activated', 'ebay', array() );
		if ( 'invalid' == $provider_status_addon['status'] ) {
			echo $provider_status_addon['msg_html'];
		}
		else {
			echo $provider_status['msg_html'];
		}
	} else {
	?>

					<div class="WooZone-amzdbg-exec">

						<label for="country_ebay">Ebay location:</label>
						<?php
							$opt_countries = $this->WooZone_ebay_countries();
							$def_country = $this->the_plugin->amz_settings['ebay_country'];
						?>
						<select id="country_ebay" name="country_ebay" class="small">
							<?php
							foreach ( $opt_countries as $key => $val ) {
								$is_selected = $key == $def_country ? ' selected="true"' : '';
								echo '<option value="' . $key . '"' . $is_selected . '>' . $val . '</option>';
							}
							?>
						</select>

						<label for="WooZone-amzdbg-asin-ebay">ASIN code:</label>
						<input id="WooZone-amzdbg-asin-ebay" type="text" class="" name="WooZone-amzdbg-asin-ebay" value="eby-132652258132" />
						
						<a href="#" class="WooZone-form-button WooZone-form-button-success WooZone-amzdbg-getAmzResponse" data-provider="ebay">Get Ebay Response</a>

						<?php
						if ( $this->the_plugin->is_aateam_devserver() ) {

							//$url = admin_url( 'admin-ajax.php?action=WooZoneAmazonDebugGetResponseDev' );
							$url = admin_url( 'admin-ajax.php' );

							$url_xml = $url;
							$url_json = $url;

							echo '<a href="' . $url_xml . '" class="WooZone-form-button WooZone-form-button-warning WooZone-amzdbg-getAmzResponse-dev" target="_blank" data-req_type="xml" data-provider="ebay">DEV: Get Ebay Response</a>';
							//echo '<a href="' . $url_json . '" class="WooZone-form-button WooZone-form-button-warning WooZone-amzdbg-getAmzResponse-dev" target="_blank" data-req_type="json">DEV: Get Amazon Response (json)</a>';
						}
						?>
					</div>

	<?php } ?>
	<!-- end Ebay Content Area -->

</div>
<?php } ?>
<!-- end EBAY PROVIDER IS ENABLED -->

					<div id="WooZone-amzdbg-amazonResponse">
						<?php
							require('lib.collapsible/json.format.html');
						?>
					</div>

		</div>
	</div>
</div>
<!-- end Parents TABS -->




				</div>
			</div>
		<?php
			$html[] = ob_get_clean();

			return implode("\n", $html);
			}
		}


		/**
		 * Others
		 */
		public function WooZone_amazon_countries( $what='array' ) {
			global $WooZone;
			
			$html         = array();
			$img_base_url = $WooZone->cfg['paths']["plugin_dir_url"] . 'modules/amazon/images/flags/';
			
			$config = $WooZone->settings();

			$theHelper = $WooZone->get_ws_object_new( 'amazon', 'new_helper', array(
				'the_plugin' => $WooZone,
			));

			$list = is_object($theHelper) ? $theHelper->get_countries( 'country' ) : array();
			
			if ( 'array' == $what ) {
				return $list;
			}
			return implode(', ', array_values($list));
		}

		public function WooZone_ebay_countries( $what='array' ) {
		    global $WooZone;
		    
		    $html         = array();
		    $img_base_url = $WooZone->cfg['paths']["plugin_dir_url"] . 'modules/amazon/images/flags/';
		    
		    //$config = @unserialize(get_option($WooZone->alias . '_amazon'));
		    $config = $WooZone->settings();
			
			$theHelper = $WooZone->get_ws_object( 'ebay', 'helper' );

			$list = is_object($theHelper) ? $theHelper->get_countries() : array();
			
			if ( 'array' == $what ) {
				return $list;
			}
			return implode(', ', array_values($list));
		}
	}
}

// Initalize the your amazonDebug
$amazonDebug = new amazonDebug($this->cfg, $module);