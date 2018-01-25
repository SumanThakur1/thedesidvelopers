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
define('DB_NAME', 'thedesidvelopers');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'hestabit');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'uWejmFcX3=N`]7M}?Bs![PA]xuxx_V;b+6=[a71e#;Ua|9fM7+GTtC(T^^8w&O~(');
define('SECURE_AUTH_KEY',  'Yf1]HUS3]LXj/dv{zdp#r0[9.MndNHbSB=RjcgMCRWmO6)A6;bXI{e/|,prIKHP;');
define('LOGGED_IN_KEY',    'NMdkU}$+6]rkRRFbQs+QLq#?Xe#{b!zudTDTa(5GskfuZLARd$COQOC(y#?[au3p');
define('NONCE_KEY',        'h@b6PKQsl`$ych5&|8FEs:-F]Khy7NfLiFGX(*Idn,bo6aTcKYyY.+KWW|UA.;o5');
define('AUTH_SALT',        'B[c`_r&2ikF>,euf[;+fKP5R[qE/$nn5twT^t=q4sWqWg&mnlEgHd2y=YG[I.>8l');
define('SECURE_AUTH_SALT', '+]4QyHH]?a=1LH4Q-`QS?fL77RBe]p<55T&6jqQM;)Q_Y{.z@w7AB&c/71y}ptFD');
define('LOGGED_IN_SALT',   '&FGT!7>mx#OsviJM*CkiK6_&JT9m~xk$::BAk`+ ,b[fsJ]mon>7Qs*Jm/>xF J]');
define('NONCE_SALT',       'Jgbu&u#&4wosA0[/UIY1-UQXvG>u$;9<n1NcY``,xl6`  TB7&f>5,A@uL[J02ag');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'tddevoper_';

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
define('WP_DEBUG', false);
define('FS_METHOD','direct');

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
