<?php
//!defined('ABSPATH') and exit;
namespace WooZone\AmazonScraper\ProductExtract\Fields;

use WooZone\AmazonScraper\ProductExtract\ProductExtractException;
use Symfony\Component\DomCrawler\Crawler;

if (class_exists(Images::class) !== true) { class Images extends AbstractField {

	const VERSION = '1.0';

	static protected $_instance;



	// Required __construct() function
	protected function __construct( $parent=null ) {

		parent::__construct( $parent );
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

	// main method
	public function extract() {

		$data_def = array(
			'images' 		=> array(),
		);

		$images = $this->_get_images_default();

		if ( ! empty($images) && is_array($images) ) {
			$data['images'] = $images;
		}

		$this->_validate_fields( $data );

		return $data;
	}



	//====================================================================================
	//== PROTECTED & PRIVATE
	//====================================================================================

	private function _get_images_default() {

		$images = null;
		$images_len = 0;

		if ( ! $images_len ) {
			//#imageBlock #altImages ul li:not(.360IngressTemplate,.videoCountTemplate,.videoThumbnail) .a-button-thumbnail img
			$images = $this->crawler->filter( '#imageBlock #altImages ul li' );

			if ( $images->count() ) {
				$images_ = $images->each( function( $node, $i ) {

					$cssClass = $node->attr('class');
					//var_dump('<pre>', $i, $cssClass, $node->html(), '</pre>'); return true;

					if ( preg_match( '/(360IngressTemplate|videoCountTemplate|videoThumbnail)/iu', $cssClass, $mCssClass )
						&& isset($mCssClass[1])
					) {
						return false;
					}

					//!!! THIS DOESN'T WORK
					//$img = $node->filter('.a-button-thumbnail img');
					//return $img->attr('src');


					$node_html = $node->html();
					// '/<img.+src=[\'"](?P<src>.+?)[\'"].*>/imu'
					if ( preg_match( '/<img.+src=[\'"](.+?)[\'"].*>/imu', $node_html, $mImg )
						&& isset($mImg[1])
					) {
						return $mImg[1];
					}

				});

				$images_ = array_values( array_filter( $images_ ) );
				$images_ = $this->_images_default_filter_url( $images_ );
				//var_dump('<pre>', $images_ , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
				return $images_;
			}
		}

		if ( ! $images_len ) {
			$images = $this->crawler->filter( '#booksImageBlock_feature_div #imageBlockThumbs .imageThumb.thumb img' );
			$images_len = $images->count();
		}

		if ( ! $images_len ) {
			$images = $this->crawler->filter( '#booksImageBlock_feature_div #minimalImageBlock #mainImageContainer img' );
			$images_len = $images->count();
		}

		if ( ! $images_len ) {
			$images = $this->crawler->filter( '#imageBlockNew_feature_div #ebooksImageBlock #ebooks-img-canvas img' );
			$images_len = $images->count();
		}

		if ( ! $images_len ) {
			$images = $this->crawler->filter( '#imageBlock_feature_div .image-wrapper img' );
			$images_len = $images->count();
		}
		//var_dump('<pre>', $images_len, '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		if ( ! $images_len ) {
			return null;
		}

		$images_ = $images->each( function( $node, $i ) {

			$imgSrc = $node->attr('src');
			//var_dump('<pre>', $i, $cssClass, $imgSrc, '</pre>'); return true;
			return $imgSrc;
		});

		$images_ = array_values( array_filter( $images_ ) );
		$images_ = $this->_images_default_filter_url( $images_ );
		//var_dump('<pre>', $images_ , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;
		return $images_;
	}

	private function _images_default_filter_url( $images=array() ) {

		if ( empty($images) ) {
			return $images;
		}

		$images_new = array();

		foreach ( $images as $image ) {

			// https://images-na.ssl-images-amazon.com/images/I/51Clxy0FIgL._AC_SX60_CR,0,0,60,60_.jpg
			$image = preg_replace( '/\._(.*)_/imu', '', $image );
			$images_new[] = array(
				'url' 		=> $image,
				'large' 	=> array( 'width' => 500, 'height' => 500 ),
			);
		}
		return $images_new;
	}

} } // end class