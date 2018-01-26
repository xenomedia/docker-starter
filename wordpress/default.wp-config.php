<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress' );

/** MySQL database username */
define( 'DB_USER', 'wordpress' );

/** MySQL database password */
define( 'DB_PASSWORD', 'wordpress' );

/** MySQL hostname */
define( 'DB_HOST', 'mariadb' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '17)hGtl,5|5x`_6Xj(RRdspo|>`DU&mHg383B7#-MGdXpZm[fM!}ICb4+o:nFu2B');
define('SECURE_AUTH_KEY',  'A+O)csUxQ8q$4lE-~;^skTLq DiS[wM<vK-<dOt%{?z8s:X+!vA+Lb>|7|%+(1!g');
define('LOGGED_IN_KEY',    'P6SChJXJ.v?+8e cmF0Z:c 8Wet2R/]>OE^qs VgbW5{?$S0}V;`-w/)QCtw+7U8');
define('NONCE_KEY',        'J|jK2iTFPQj7+,;Q@J<89u4;C ZX|#i*8#vK]S4y1-QM<iC|=#8n+.q+Um:o]6Ro');
define('AUTH_SALT',        '=&C0bcl>.r$SFYq@7M>De>1YV9H5%uZ+za|!cJ))sA^WHZIl=$A.@N#{5X;Nn<tB');
define('SECURE_AUTH_SALT', 'ptQ56>I*8qHdSwux!o>R~ *6yigird{mU;Eo,cEHaG~!lV/QIhB#zD7|fqq`?zH8');
define('LOGGED_IN_SALT',   '9i^TT;#kf$Bvp-Qaiuq6+N1Z]m6_}e+(i|:1N:^_:8@w8QV/.z204%qt@l@KP|bE');
define('NONCE_SALT',       't&Q:iWVxjG<W0})WZ+&c(-!+=M.&.bs8z:mWK4W 5/N&p}neEVqAsBX~6W|brvW#');

/**#@-*/

/**
 * WordPress Database Table prefix.
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
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );