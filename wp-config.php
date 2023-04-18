<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'para' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost:3306' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'kc]T*8#u8HdUri98h;`A|#)35RDtV]*=vhU ,/9WpX3`N:(:E1f@bh89FiXoW50`' );
define( 'SECURE_AUTH_KEY',  'AxPz?+~DgGjf-w?u8^TIR>9c xC{Vqi_V4Si-;IkJ]&a~&wt44sL 1%6cMJ!CK<C' );
define( 'LOGGED_IN_KEY',    'v6}R2asser@z3F9s$9xo?~a=*2J5B>+:,,2Ow8GYqux]1Fmwh$Ls>k?v)N+|Y W|' );
define( 'NONCE_KEY',        'A WFug7SdZc;&$;06%7&Xn1;7bb(%^oa`/<@Nxf;A7WB6yTWMs,,$~>Z%RA_|&(7' );
define( 'AUTH_SALT',        '[}1(nYHaO}s&aJ`;^xJ14{3ebQ^U;y8DDDNfjy{xPiw5@m.TK+>cIXz<Ax7O)Kk1' );
define( 'SECURE_AUTH_SALT', 'm]]?Il)bjiU7=/4#Cd(g%_t!j_rtj:{xco> 84iq&GwKrW,fGd}+#Xa:u^<e{UsZ' );
define( 'LOGGED_IN_SALT',   '4U7,oOJSk*0_>,#e<fKwRWFgY;^Yh.Kv4<mx-@^vNow4w#iR6d9q<7n;V:^&L!t ' );
define( 'NONCE_SALT',       'iS$=E<sPt)fb~h$my,]{6}udt<5Z~}vSIRDw$Or Z ^35Rx-2>~dh=<zBN]6Bi]i' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
