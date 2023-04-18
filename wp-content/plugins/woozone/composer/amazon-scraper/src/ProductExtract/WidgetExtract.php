<?php
//!defined('ABSPATH') and exit;
namespace WooZone\AmazonScraper\ProductExtract;

use Symfony\Component\DomCrawler\Crawler;

if (class_exists(WidgetExtract::class) !== true) { class WidgetExtract {

	const VERSION = '1.0';

	static protected $_instance;

	protected $the_plugin = null;

	protected $content = null;

	protected $variations = array();
	protected $contor = 0;
	protected $notices = array();



	// Required __construct() function
	protected function __construct( $parent=null ) {

		$this->the_plugin = $parent;
	}

	// Singleton pattern
	static public function getInstance( $parent=null ) {
		if (!self::$_instance) {
			self::$_instance = new self( $parent );
		}
		return self::$_instance;
	}



	//====================================================================================
	//== PUBLIC
	//====================================================================================

	public function set_content( $content ) {

		$this->content = $content;
		return $this;
	}

	// notices & errors during parsing
	public function get_notices() {

		return $this->notices;
	}

	public function extract() {

		$this->_filter_content();
		$this->_parse_content();
		return $this->variations;
	}



	//====================================================================================
	//== PROTECTED & PRIVATE
	//====================================================================================

	private function _filter_content() {
		
		if ( empty($this->content) ) {
			return false;
		}

		//var_dump( "<pre>", $this->content  , "</pre>" ) ; 

		$this->content = str_replace( ',]', ']', $this->content );
		//die( var_dump( "<pre>", $this->content  , "<pre>" ) . PHP_EOL .  __FILE__ . ":" . __LINE__  );
		$this->content = json_decode( $this->content, true );

		//die( var_dump( "<pre>", $this->content  , "<pre>" ) . PHP_EOL .  __FILE__ . ":" . __LINE__  ); 

		if ( empty($this->content) || ! is_array($this->content) ) {
			throw new \Exception( 'json decode not working!' );
		}

		return $this->content;
	}

	private function _parse_content() {

		if ( empty($this->content) || ! is_array($this->content) ) {
			return false;
		}

		foreach ( $this->content as $var_content ) {

			$this->_get_variation( $var_content );
			$this->contor++;
		}
	}

	private function _get_variation( $content ) {

		$crawler = new Crawler();
		$crawler->addHtmlContent( $content );
		$node = $crawler->filter( '.p13n-asin' );
		//$html = $node->html();
		//var_dump('<pre>', $node->count(), $html, '</pre>');

		if ( ! $node->count() ) {
			return false;
		}

		$asin = $this->_get_asin( $node );
		if ( is_null($asin) ) {
			$this->notices[ $this->contor ] = 'widget - couldn\'t get variation asin!';
			return false;
		}

		$price = $this->_get_price( $node );

		$link = $this->_get_link( $node );

		$image = $this->_get_image( $node );
		$image_src = is_array($image) && isset($image['src']) ? $image['src'] : null;
		$image_alt = is_array($image) && isset($image['alt']) ? $image['alt'] : null;

		$title = $this->_get_title( $node, $image_alt );

		$this->variations["$asin"] = array(
			'ASIN' 			=> $asin,
			'title' 		=> $title,
			'list_price' 	=> $price,
			'images' 		=> array( $this->_build_variation_image( $image_src ) ),
		);

		$this->_validate_variation_fields( $this->variations["$asin"] );
	}

	private function _validate_variation_fields( $variation ) {

		$notice = array();

		$fields = array( 'ASIN', 'title', 'list_price', 'images' );
		foreach ( $fields as $field ) {

			$asin = $variation['ASIN'];
			if ( is_null($variation["$field"]) || '' === $variation["$field"] ) {
				$notice[] = "[$field] is empty";
			}
		}

		if ( ! empty($notice) ) {
			$this->notices["$asin"] = implode( ' ; ', $notice );
		}
	}

	private function _get_asin( $node ) {

		//{"ref":"homepage_1","asin":"B078H2DWZT"}
		$p13n_asin = $node->attr('data-p13n-asin-metadata');
		if ( empty($p13n_asin) ) {
			return null;
		}

		$p13n_asin = json_decode( $p13n_asin, true );

		if ( empty($p13n_asin) || ! is_array($p13n_asin)
			|| ! isset($p13n_asin['asin']) || empty($p13n_asin['asin'])
		) {
			return null;
		}

		$asin = trim( $p13n_asin['asin'] );
		return '' !== $asin ? $asin : null;
	}

	private function _get_link( $node ) {

		$link = $node->filter( '.p13n-link' );
		if ( ! $link->count() ) {
			return null;
		}
		$link = $link->attr('href');
		$link = trim( $link );
		return $link;
	}

	private function _get_image( $node ) {

		$image = $node->filter( 'img' );
		if ( ! $image->count() ) {
			return null;
		}
		$image_src = $image->attr('src');
		$image_alt = $image->attr('alt');

		$image_src = trim( $image_src );
		$image_alt = trim( $image_alt );
		//var_dump('<pre>', $image_src, $image_alt, '</pre>');
		return array( 'src' => $image_src, 'alt' => $image_alt );
	}

	private function _get_price( $node ) {

		$price = $node->filter( '.price' );
		if ( ! $price->count() ) {
			return null;
		}
		$price = $price->text();
		$price = trim( $price );
		return $price;
	}

	private function _get_title( $node, $alt=null ) {

		$title = $node->filter( '.p13n-link' );
		if ( ! $title->count() ) {
			return $alt;
		}

		$title = $title->text();
		$title = trim( $title );

		$compare = strcmp( $title, $alt );
		if ( 0 === $compare ) {
			return $title;
		}
		else if ( $compare < 0 ) {
			return $alt; // title is less than alt
		}
		//else if ( $compare > 0 ) {
		else {
			return $title; // title is greater than alt
		}
	}



	//====================================================================================
	// MISC

	private function _clean_image_src( $src ) {

		return preg_replace( '/\._(.*)_/imu', '', $src );
	}

	private function _build_variation_image( $src ) {

		if ( ! empty($src) ) {
			$src = $this->_clean_image_src( $src );
		}

		return array(
			'url' 		=> $src,
			'large' 	=> array( 'width' => 500, 'height' => 500 ),
		);
	}

} } // end class
/*
<div class=\" p13n-asin\" data-p13n-asin-metadata=\"{&quot;ref&quot;:&quot;homepage_1&quot;,&quot;asin&quot;:&quot;B07KCGB8G4&quot;}\">

	<a href=\"/NUU-Mobile-R1-Rugged-IP68-Rated/dp/B07KCGB8G4/ref=homepage_1?_encoding=UTF8&psc=1&refRID=X616PCKVETQXGDXFGK8S\" class=\"p13n-link\" >
		<div class=\"p13n-section-spacing-mini\" >
			<img src=\"https://images-na.ssl-images-amazon.com/images/I/51BXP%2B-wtmL._AC_UL100_SR100,100_.jpg\" class=\"p13n-faceout-image\" alt=\"NUU Mobile R1 Rugged IP68-Rated - Waterproof Dual Sim 4G LTE Unlocked Android 8 Smartphone - Black - US Warranty\" width=\"100\" height=\"100\">
		</div>
		<div class=\"p13n-sc-truncate p13n-sc-line-clamp-4\" aria-hidden=\"true\" data-rows=\"4\">
			NUU Mobile R1 Rugged IP68-Rated - Waterproof Dual Sim 4G LTE Unlocked Android 8 Smartphone - Black - US Warranty\n        
		</div>
	</a>

	<div class=\" p13n-icon-row\">
		<a href=\"/NUU-Mobile-R1-Rugged-IP68-Rated/product-reviews/B07KCGB8G4/ref=homepage_cr_1?ie=UTF8&refRID=X616PCKVETQXGDXFGK8S\" class=\"p13n-link\" >
			<span class='p13n-sc-nonAUI-sprite s_star_3_5 '></span>
		</a>
		
		<a href=\"/NUU-Mobile-R1-Rugged-IP68-Rated/product-reviews/B07KCGB8G4/ref=homepage_cr_1?ie=UTF8&refRID=X616PCKVETQXGDXFGK8S\" class=\"p13n-link p13n-text-size-small\" >16</a>
	</div>
	
	<div class=\"p13n-row\">
		<a href=\"/NUU-Mobile-R1-Rugged-IP68-Rated/dp/B07KCGB8G4/ref=homepage_1?_encoding=UTF8&psc=1&refRID=X616PCKVETQXGDXFGK8S\" class=\"p13n-link p13n-text-normal\" >
			<span class='p13n-text-color-price price p13n-text-size-base'>
				<span class='p13n-sc-price'>$169.99</span>
			</span>
		</a>
	</div>

</div>
*/