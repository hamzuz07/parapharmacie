<?php
!defined('ABSPATH') and exit;

require_once( WOOZONE_ABSPATH . 'composer_prefixed/guzzlehttp/vendor/scoper-autoload.php' );
require_once( WOOZONE_ABSPATH . 'composer_prefixed/random-user-agent/vendor/scoper-autoload.php' );

require_once( WOOZONE_ABSPATH . 'lib/scripts/mobiledetect/MobileDetect.php' );

// functionalities
require_once( WOOZONE_ABSPATH . 'melib/Utils.php' );
require_once( WOOZONE_ABSPATH . 'melib/WooMisc.php' );

require_once( WOOZONE_ABSPATH . 'melib/GeoLocation.php' );
require_once( WOOZONE_ABSPATH . 'melib/GeoLocationStats.php' );

require_once( WOOZONE_ABSPATH . 'melib/CountryAvailability.php' );
require_once( WOOZONE_ABSPATH . 'melib/CountryAvailabilityStats.php' );