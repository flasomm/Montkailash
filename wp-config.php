<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, and ABSPATH. You can find more information by visiting
 * {@link https://codex.wordpress.org/Editing_wp-config.php Editing wp-config.php}
 * Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'montkailash');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root');

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
define('AUTH_KEY',         '|b@%6cj~O)eKAK!J*tG<F+M0?gBz`{ -Gxy56n?tvauNU5UE+N-hBw4JL@]#yhO=');
define('SECURE_AUTH_KEY',  'GbH34A5+*7>C-:sA`c:Yk[*ixx`%Z|=}nzW.#d8b?1J9S$x%kH.#4odsj~uvpKg$');
define('LOGGED_IN_KEY',    'i}HDXez(KHj3^l3E8j]A&us]3PH/$T$+R!M80m{k%&*]dQ|2|Z$0lL+Ji5ys]qwj');
define('NONCE_KEY',        'oab/8*+w-i]95cI_]nH|1r2qx^dI)EAEpj`^nT6?_My6R|Z%}X+VXA8$/eiwyQ-U');
define('AUTH_SALT',        'C!oWWzTUK[j2; zX)LCw.8HsS1yx%:aKjQ-n|UXTN P;D4B,2!R-W-XtAs`_pi2%');
define('SECURE_AUTH_SALT', 'ibtMs;O:Gn.zG<d[!]5-AZw!oh/#keFWq0p}/6ZT/ nRx^|)b0Nfw{`9U4aK O6H');
define('LOGGED_IN_SALT',   'V3*[BaJ9mLLqWs9fvD+]1]w eLT>MrG^x (z(jy.ON+<eAm*m-d$j-tOW+K}N@rC');
define('NONCE_SALT',       'M-a5r^Yqj=g@fBy?[[HF6h~U*s0Qx&Czow1_8D_LLhL8uP+/a;fU~6=CT`qb=g-+');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define( 'WP_DEBUG',        true );
define( 'WP_DEBUG_LOG',    true );
define( 'WP_DEBUG_DISPLAY',true );
define( 'SCRIPT_DEBUG',    true );
define( 'SAVEQUERIES',     true );
define( 'WP_MAILCATCHER',  true );

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
