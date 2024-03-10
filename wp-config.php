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
define( 'DB_NAME', 'alpha' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

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
define( 'AUTH_KEY',         'p2I*?>SFOK.v<zvFovQu7,f9iWHhztlhhP}7/TWBf/Ky0QQ.&pySxD=Y:6x^|^hU' );
define( 'SECURE_AUTH_KEY',  '4&#5tf3]7.G$*`eabhd<lum[jslfdF3]2@<e:7s4QwgTr$Eso4O0c|}mT/d3XM/a' );
define( 'LOGGED_IN_KEY',    '&:cws0^D*7*P&Py.S  ]MO<=r(O_@C1k+X /*{re/F|h(oO/4>>4d&Gj+UdC@h{{' );
define( 'NONCE_KEY',        'RyOn2Y2)6#;wPp$(v.v7V]du(`dJ=MU~.sTfrWgMuGs;PNXo[?+k9>I8A]K(9)<W' );
define( 'AUTH_SALT',        'llKGw*cQvQJCZh)_lYKx &e8UB.Vq$$lncT`=4v}Mm-/ q;PnZdm;Hu__JD:teOi' );
define( 'SECURE_AUTH_SALT', 'v}GcVK;g9 v|,mQ$HPnE7Pz%Y{1x]E,N}L^K6<oH*?PEx0[=Is 7kMr@N,K=a$cB' );
define( 'LOGGED_IN_SALT',   'Gm>dNh~PeXg<2wNbYQKN?lCA7Ppq$ym0&EW.ns<H+ZDo*[I5,lMa.GPH<=u{5mb7' );
define( 'NONCE_SALT',       '5y_ylC5[bO9.dy.h[2O~mu8C($R`m*>>[.+XRa]kVMe$L48#Y*W6I@)ff+=I0IA8' );

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
define( 'WP_DEBUG', true );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
