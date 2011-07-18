<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'lockw4_lockwp');

/** MySQL database username */
define('DB_USER', 'lockw4_lockwp1');

/** MySQL database password */
define('DB_PASSWORD', 'tRTTL1EmU80G');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

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
define('AUTH_KEY',         'a|.tl$c:!Rn:|/VmmWF|1wal=/nb8>lns--[G{JSh$xyY@{`#*g:`6=/-! q|q$d');
define('SECURE_AUTH_KEY',  '>+AYNA/]8U)eLMSe[3!EV;i,;c%xyb#*l[RW+fQA@PCPf/J+Tk(VE~[LpXk$X*RP');
define('LOGGED_IN_KEY',    'ja(Yl5M5uKM&=<xM3N_5zJS`#W-LEkQy7oE5J>61Q!hu:OsvE6ypUGH=eGx2ut;c');
define('NONCE_KEY',        '`Wt0nj*H-PugM?PnEtYHV2*KiQ}(0R|myP9ZQzq5TdaoR|,3~UEE@FbnG+qeQfE.');
define('AUTH_SALT',        'r@LS,k(Xr}`fC(:`94.9!T;c.,|pZoe7yE<UP,/+Q}`3n5]OW5:|povt^>gigeWF');
define('SECURE_AUTH_SALT', '%hYg7QbI=u<4|-yrwLd_u8U4EWx8y*Lhi))4}bgN:+/58k3beE&&^n%4 y=t2$in');
define('LOGGED_IN_SALT',   '~n|xj|(F;;[*#YR1sU>O4#F;jU&=}6m+*g(#WGRye{yo2&F/8RkT3ou`,>Ta?%$f');
define('NONCE_SALT',       'py-Wgxr]g|DAT!!m1r}}:ahO`M!{F0M1oL: @P$I+BQPr<[ILX(H{i(Y`qaCuu:M');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
