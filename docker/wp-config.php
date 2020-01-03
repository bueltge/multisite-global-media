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

// ** MySQL settings ** //
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

/**
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          'VZyXJ<Tl/+Mg]8HBx,PTW,5l|o;#vu[[e4FGFF|g]P^s4`0oiMw)qZo ~*$lV73q' );
define( 'SECURE_AUTH_KEY',   '&xR- ]0G}(y-5Kb+D`:#%&c7NK]X;7,;_Wv)skugw~+U]nq`qey~OG|ZTxZ5c;=^' );
define( 'LOGGED_IN_KEY',     'mO*CN/~&]y&A*3:$qfGM3Km/)?_6q!ph*znCSnWULl$vQ)exEC(bQ$|RNU1i{ 0w' );
define( 'NONCE_KEY',         'YafY sgP+RAo~:0Lj#Rf$gP(i01N<:fI&ombS !| FfF@rAR%r,|RLos2nKxeq~E' );
define( 'AUTH_SALT',         'rcRtXE::ax>_[{V:LR^WJzo juXti*q}A(!:)c4yi!U%C8oK/Z!A;p30l?LW+*8A' );
define( 'SECURE_AUTH_SALT',  '3[qjnu<bnz|sEJLtR#}0n^VEf ^]rXcA[hRa2VDitLhsng>m$jNG|=uy{1x29J_x' );
define( 'LOGGED_IN_SALT',    'Z}FU-B#c6W=l$WQ0Jw#(BRzDc@*/~LZxZ1;;*^ODa,Z>`r%<FuHmj_gyLepSn@V/' );
define( 'NONCE_SALT',        'j_1pqvHVGF?6Ya[UX^O6efup}yU;sCbT%`dOEI@=Wv;faF5BuM1fwB64(Q*IDuA$' );
define( 'WP_CACHE_KEY_SALT', ' yPByarc]!MHyu4S|HfR?oat*d$Umt)/XHUh_K,l%mJ^UT$DYy t@X}i,/j3lhv:' );

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

define( 'WP_ALLOW_MULTISITE', true );
define( 'MULTISITE', true );
define( 'SUBDOMAIN_INSTALL', false );
$base = '/';
define( 'DOMAIN_CURRENT_SITE', 'multisite-global-media.docker' );
define( 'PATH_CURRENT_SITE', '/' );
define( 'SITE_ID_CURRENT_SITE', 1 );
define( 'BLOG_ID_CURRENT_SITE', 1 );

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) )
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
