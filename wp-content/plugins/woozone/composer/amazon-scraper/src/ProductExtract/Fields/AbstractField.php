<?php
//!defined('ABSPATH') and exit;
namespace WooZone\AmazonScraper\ProductExtract\Fields;

use WooZone\AmazonScraper\ProductExtract\ProductExtractException;
use Symfony\Component\DomCrawler\Crawler;

if (class_exists(AbstractField::class) !== true) { abstract class AbstractField {

	const VERSION = '1.0';

	protected $the_plugin = null;

	protected $content_orig = null; // original raw content
	protected $content = null; // content minified (no more than one consecutive spaces)
	protected $content_clean = null; // content striped of (style|script) tags

	protected $crawler = null;

	protected $product_main = array(
		'ASIN' => null, // MANDATORY
		'ParentASIN' => null,
		'country' => null, // MANDATORY
	);

	protected $notices = array();
	protected $errors = array();



	// Required __construct() function
	protected function __construct( $parent=null ) {

		$this->the_plugin = $parent;
	}

	// main method to be implemented in child classes
	abstract public function extract();



	//====================================================================================
	//== PUBLIC
	//====================================================================================

	public function set_content( $content_orig, $content, $content_clean ) {

		$this->content_orig = $content_orig;
		$this->content = $content;
		$this->content_clean = $content_clean;

		//die( var_dump( "<pre>", $this->content_orig , "<pre>" ) . PHP_EOL .  __FILE__ . ":" . __LINE__  ); 
		//die( var_dump( "<pre>", $this->content , "<pre>" ) . PHP_EOL .  __FILE__ . ":" . __LINE__  );
		//die( var_dump( "<pre>", $this->content_clean , "<pre>" ) . PHP_EOL .  __FILE__ . ":" . __LINE__  );
		return $this;
	}

	// set product main data like asin, parent asin, country...
	public function set_product_main( $product_main=array() ) {

		$this->product_main = array_replace_recursive( $this->product_main, $product_main );
		return $this;
	}

	public function set_crawler( $content=null, $pms=array() ) {

		$pms = array_replace_recursive(array(
			'use_content' => 'clean', // orig | standard | clean
		), $pms);
		extract($pms);

		if ( ! is_null($content) ) {
			$this->set_content( $content );
		}

		$theContent = $this->content; // standard
		if ( 'orig' === $use_content ) {
			$theContent = $this->content_orig;
		}
		else if ( 'clean' === $use_content ) {
			$theContent = $this->content_clean;
		}
		//var_dump('<pre>', $theContent , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		$crawler = new Crawler();
		$crawler->addHtmlContent( $theContent );
		$this->crawler = $crawler;
		return $this;
	}

	// notices & errors during parsing
	public function get_notices() {

		return $this->notices;
	}
	public function get_errors() {

		return $this->errors;
	}

	// product is an ebook?
	public function is_ebook() {

		$authors = $this->_get_authors();
		return ! empty($authors) ? 1 : 0;
	}

	// product is a variable parent product? (returns the number of dimensions)
	public function is_variable() {

		// how many dimensions the variable product parent has (!!! not the number of variations)
		// dimensions = size, color, style...
		$nodes = $this->crawler->filter( 'div[id^="variation_"]' );
		return $nodes->count();
	}



	//====================================================================================
	//== PROTECTED & PRIVATE
	//====================================================================================

	protected function _get_authors() {

		$authors = $this->crawler->filter( '#bylineInfo > span.author' );

		$authors_ = array();

		if ( $authors->count() ) {

			$authors_ = $authors->each( function( $node, $i ) {

				//var_dump('<pre>',$node->html() ,'</pre>'); //return true;
				//$node = new Crawler( $node->html() );
				//$td1 = $node_->filterXPath( 'a[1] | /span[1]' )->text();

				$td1 = $node->filterXPath( "//a[contains(concat(' ', normalize-space(@class), ' '), ' contributorNameID ')]" );
				if ( $td1->count() ) {
					$td1 = $td1->text();
				}
				else {
					$td1 = $node->filterXPath( '//span/*[1]' );
					if ( $td1->count() ) {
						$td1 = $td1->text();
					}
				}
				$td1 = trim( $td1 );

				$td2 = ( $__ = $node->filter( 'span.contribution' ) ) && $__->count() ? $__->text() : 'author';
				$td2 = trim( trim( $td2 ), '(),' );
				//var_dump('<pre>',$td1, $td2 ,'</pre>'); return true;

				if ( '' === $td2 || '' === $td1 ) {
					return false;
				}

				return array( $td1, $td2 );
			});
		}

		$authors_ = array_values( array_filter( $authors_ ) );
		//var_dump('<pre>', $authors_ , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		//$authors_sel = $this->_array_to_selected( $authors_ );
		//var_dump('<pre>', $authors_sel , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		$authors_sel = array();
		foreach ( $authors_ as $author ) {

			$author_name = $author[0];
			$author_type = $author[1];

			if ( ! isset($authors_sel["$author_type"]) ) {
				$authors_sel["$author_type"] = array();
			}
			$authors_sel["$author_type"][] = $author_name;
		}
		//var_dump('<pre>', $authors_sel , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		$authors_sel2 = array();
		foreach ( $authors_sel as $author_type => $author_list ) {

			$cc = 0;
			foreach ( $author_list as $author_name ) {

				$author_type_ = $author_type . ( $cc ? $cc + 1 : '' );
				$authors_sel2["$author_type_"] = $author_name;
				$cc++;
			}
		}
		//var_dump('<pre>', $authors_sel2 , '</pre>'); echo __FILE__ . ":" . __LINE__;die . PHP_EOL;

		return $authors_sel2;
	}

	protected function _extract( $fields ) {

		$data = array();

		foreach ( $fields as $field ) {

			$value = $this->_get_node_value(
				$field['selector'],
				isset($field['attribute']) ? $field['attribute'] : 'text',
				isset($field['default']) ? $field['default'] : null
			);

			if ( isset($field['callback']) && is_callable($field['callback']) ) {
				$value = $field['callback']( $value, $data );
			}

			$data["{$field['name']}"] = $value;
		}
		// end foreach

		return $data;
	}

	protected function _validate_fields( $data ) {

		foreach ( $data as $field_key => $field_val ) {
			if ( empty($field_val) ) {
				$this->notices["$field_key"] = "field \"$field_key\" is empty!";
			}
		}
	}

	protected function _get_node_value( $selector, $attribute='html', $default=null ) {

		$node = $this->crawler->filter( $selector );

		$ret = $default;
		if ( $node->count() ) {

			switch($attribute) {
				case "html":
					$ret = $node->html();
					break;

				case "text":
					$ret = $node->text();
					break;

				default:
					$ret = $node->attr($attribute);
					break;
			}
		}
		return $ret;
	}

	protected function _array_to_selected( $itemattr ) {

		$itemattr_sel = array();
		if ( ! empty($itemattr) ) {
			foreach ( $itemattr as $value ) {

				$attr_title = $value[0];
				$attr_value = $value[1];

				if ( ! isset($itemattr_sel["$attr_value"]) ) {
					$itemattr_sel[ "$attr_title" ] = $attr_value;
				}
			}
		}
		return $itemattr_sel;
	}


	//====================================================================================
	// MISC

	protected function clean_text( $text ) {

		$text = preg_replace('/\s+/imu', ' ', $text);
		$text = trim( $text );
		return $text;
	}

	protected function clean_html( $html ) {

		//$html = $this->the_plugin->clean_html( $html, false );
		$html = wp_strip_all_tags( $html );
		return $html;
	}
	
	protected function strip_tags_content( $text, $tags='', $invert=false ) {

		preg_match_all( '/<(.+?)[\s]*\/?[\s]*>/si', trim($tags), $tags );
		if ( ! is_array($tags) && ! isset($tags[1]) ) {
			return $text;
		}
		$tags = array_unique( $tags[1] );

		if ( ! empty($tags) && is_array($tags) ) {
			if ( false === $invert ) {
				return preg_replace( '@<(?!(?:'. implode('|', $tags) .')\b)(\w+)\b.*?>.*?</\1>@si', '', $text );
			}
			else { 
				return preg_replace( '@<('. implode('|', $tags) .')\b.*?>.*?</\1>@si', '', $text );
			}
		}
		else if ( false === $invert ) {
			return preg_replace( '@<(\w+)\b.*?>.*?</\1>@si', '', $text );
		}
		return $text;
	}

} } // end class