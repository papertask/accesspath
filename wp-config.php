<?php
define('WP_CACHE', true); // Added by WP Rocket
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
define('DB_NAME', 'accesspath');
/** MySQL database username */
define('DB_USER', 'accesspath');
/** MySQL database password */
define('DB_PASSWORD', 'R3SZwL6Tj548KtbX');
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
define('AUTH_KEY',         'P<b~u58y)O<%VSGa=VGgbbKy_9`BJtnfBUAY$s4V`i)hEb `95#A!Z>dK{rc?Pch');
define('SECURE_AUTH_KEY',  '{Q0F)v<I~!t`eO=9,=NR 1Xj3hPaW&;b*U?s(JQ~qMhi@hZBtecpq]m.pRmoz)#U');
define('LOGGED_IN_KEY',    '^VZU@SxoZ6#rs<uHRfs=3moWpsjk?p|,=`,;c7k2f$FFSq03,]@:- a/7Kp$YkH+');
define('NONCE_KEY',        '*oyw6v%HOkBX>cSP0&{bIMLTa1Z54-lnj}05hWY,hQ9vWs$GSwZvZGx_oIm~O<aj');
define('AUTH_SALT',        't>-kT*#Ci*v;ecDa(0OVwD~e.Z*UwxjU3NUX/0y%C.n/xA7sA1.uJ3sa{/yV-(~*');
define('SECURE_AUTH_SALT', 'JzEGDbPb.;@c6VdR:$QQ; ~$Ft/:g>p6?d1Wx8sBM#QB 0N7;HVueBl=6v7eI~Ba');
define('LOGGED_IN_SALT',   '9vGl+gEp1]oI)?j4la]QV+8(Gs|T9Af+Ib4^;+)Qm[N/7RgdjFXyb%JDx,L22Qq2');
define('NONCE_SALT',       '7Ae*Qj0CJG@:UlfMI]W]=E-NAV<s~lWujt.E6(^7i5sZ2pG7adQfoOt&c:x4@.!y');
/**#@-*/
/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';
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
/* That's all, stop editing! Happy blogging. */
/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');
/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
