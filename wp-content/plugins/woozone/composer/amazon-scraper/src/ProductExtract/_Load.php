<?php
//!defined('ABSPATH') and exit;
namespace WooZone\AmazonScraper\ProductExtract;

if (class_exists(Load::class) !== true) { class Load {

	const VERSION = '1.0';

	static protected $_instance;

	public $the_plugin = null;

	protected $u; // utils function object!

	protected $paths;

	protected $errors = array();



	// Required __construct() function
	protected function __construct( $parent=null ) {
		
		$this->the_plugin = $parent;

		$this->u = $this->the_plugin->u;

		// paths
		$this->paths = array(
			'composer_dir_url' => $this->the_plugin->cfg['paths']['composer_dir_url'],
			'composer_dir_path' => $this->the_plugin->cfg['paths']['composer_dir_path'],
		);
		$this->paths = array_merge( array(
			'product_extract_url' 	=> $this->paths['composer_dir_url'] . 'amazon-scraper/src/ProductExtract/', 
			'product_extract_path' 	=> $this->paths['composer_dir_path'] . 'amazon-scraper/src/ProductExtract/',
		));

		$this->load();
	}

	// Singleton pattern
	static public function getInstance( $parent=null ) {
		if (!self::$_instance) {
			self::$_instance = new self( $parent );
		}
		return self::$_instance;
	}

	private function load() {

		require_once( 'ProductExtract.php' );
		require_once( 'ProductExtractException.php' );
		require_once( "Fields/AbstractField.php" );

		$classFields = glob( $this->paths['product_extract_path'] . 'Fields/*.php' );

		foreach ( $classFields as $classField ) {

			$class_name = preg_replace('~(.*/)([\w\-]*)(\.php)$~iu', '$2', $classField);
			$class_file = "Fields/{$class_name}.php";

			if ( in_array($class_name, array('AbstractField')) ) {
				continue 1;
			}

			if ( ! $this->u->verifyFileExists( $classField ) ) {
				$this->errors[] = "php class file $class_file not found or unreadable.";
				continue 1;
			}

			//var_dump('<pre>',$classField, $class_name, $class_file ,'</pre>');
			require_once( $classField );
		}
		// end foreach

		if ( ! empty($this->errors) ) {
			$msg = implode( PHP_EOL, array_merge( array('Errors occured'), $this->errors) );
			throw new Exception( $msg );
		}
	}

} } // end class