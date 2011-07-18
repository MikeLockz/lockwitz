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
define('DB_USER', 'lockw4_lockwp');

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
define('AUTH_KEY',         'D+U$RBSWHPnR`>3(h>z[([u+t34HO18o<TYt<zI;i<-)1v;-*(5x],H[%zG[h6+ ');
define('SECURE_AUTH_KEY',  'Z}i|Mj2&,j~zIf?TL{s+P>t+GH.FsY/|z+/=C+aQ{}Z}wn_RW&{f1-r7EW+h=R[e');
define('LOGGED_IN_KEY',    '$/:,08g2X(v_O2BD1!/{N+^Z`R-1vQ9&uq&O!`lw= s-+wc;qiR0}uL%O>IzP@QA');
define('NONCE_KEY',        'yAj[_A(brcTGWv+lG-4WQ@rx+N~^&$*OEvui^%f]@C2M 0re1{=rq8,.SZEEe_2a');
define('AUTH_SALT',        'w=px2,v]$JC|3H*!yt7-Wa{ef~n}Y:h|4/jKRa-=aE7p|+#1J<Gq{-l?]Ja88[Nc');
define('SECURE_AUTH_SALT', 'l0WrX9%p)VPiuR|2ci2&~@BvDuH857UUC>!#bpSY$?[Q1#yy&]J>PRzmu.p|pDp3');
define('LOGGED_IN_SALT',   'Scxl@pBpB}C4{xi_tnus9Nwk=(:^j[1lk)k[gJutWW&B7[}*4h!yQZtGqW/vzmzc');
define('NONCE_SALT',       '_#dh$ XfHqJd@i6-(-aa,~9WR+u&smJL)1|]u^(?fnF6-&|92(YG1x[dv~)H1)tA');

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
