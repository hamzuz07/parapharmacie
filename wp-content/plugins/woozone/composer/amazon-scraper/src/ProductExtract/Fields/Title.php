<?php
//!defined('ABSPATH') and exit;
namespace WooZone\AmazonScraper\ProductExtract\Fields;

use WooZone\AmazonScraper\ProductExtract\ProductExtractException;
use Symfony\Component\DomCrawler\Crawler;

if (class_exists(Title::class) !== true) { class Title extends AbstractField {

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
			'title' 	=> null,
		);

		$fields = array();
		$fields[] = array(
			'name' 		=> 'title',
			'selector' 	=> '#productTitle, #ebooksProductTitle, #mas-title',
			'attribute' => 'text',
			'callback' 	=> function ($value) {
				return $this->clean_text( $value );
			},
		);

		$data = $this->_extract( $fields );
		$data = array_replace_recursive( $data_def, $data );

		//:: ebook case
		if ( $this->is_ebook() ) {
			$data['title'] = $this->_get_ebook_title( $data['title'] );
		}
		//$data['title'] = ''; //DEBUG

		//$this->_validate_fields( $data );
		if ( empty($data['title']) ) {
			$msg = "product field [title] is empty!";
			throw new ProductExtractException( $msg );
		}

		return $data;
	}



	//====================================================================================
	//== PROTECTED & PRIVATE
	//====================================================================================

	private function _get_ebook_title( $title_def=null ) {

		$title = $title_def;

		$node_title = $this->crawler->filter( '#productTitle, #ebooksProductTitle, #mas-title' );

		if ( $node_title->count() ) {
			$node_title = $node_title->parents()->filter( '#title' );

			if ( $node_title->count() ) {

				$title = $node_title->text();
				$title = $this->clean_text( $title );
			}
		}
		return $title;
	}

} } // end class