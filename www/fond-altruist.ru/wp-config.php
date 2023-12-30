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
define( 'DB_NAME', 'u2120826_wp398' );

/** Database username */
define( 'DB_USER', 'u2120826_wp398' );

/** Database password */
define( 'DB_PASSWORD', 'Yp2.S8-A70' );

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
define( 'AUTH_KEY',         'bwr9q7uctwvopetz7n8atkju3kn9skrmzxjkbjnd2vaj8spv0sp6ewz7ghfgcyp3' );
define( 'SECURE_AUTH_KEY',  'mwhql9xfk4rdkewqyhleabbckyh0hg2p1vwhidfagfctybfl7mpkqhpceflbueer' );
define( 'LOGGED_IN_KEY',    'sxzyy2urnp9goc8lpjbw40pp4ejx1wlkpcxqledulantmjvrlul520dfynmehaoe' );
define( 'NONCE_KEY',        'rkyqwmr58uuollqnb8i9q750i1wiqlutrpt7hl89dyipgbgd2wakpzrnqnnkh8fh' );
define( 'AUTH_SALT',        'abgdz7dmfpx8mbg0dsqh8ae9erjmtuygt8zceibtr1uhd0fdvhsmq1uhmrpqmuea' );
define( 'SECURE_AUTH_SALT', 'poqakmylmdu2czdt74cyen3w4tbfufor0nmzz5wfhy6me1qdrctsgzvzuhrwgdxm' );
define( 'LOGGED_IN_SALT',   'fljwpmroq2ljyicpjmmpzf0uagtaxyvsmx8m759spfohl6dfzamdygctqxsf0poa' );
define( 'NONCE_SALT',       'ohmj9zuaop1oqtn7efuxonwm9yw9ukc6kac7ufpnl3t2kycqsufbsmlqdlizsvcv' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wpnh_';

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
