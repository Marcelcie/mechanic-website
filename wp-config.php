<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'mechanic-website' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

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
define( 'AUTH_KEY',         'SJT21(Kh`KPv;Q//OnG.MW^=3f;])3~$E-o|@Z5#b0N!M}0Kb1bcOtMnpE.may2Y' );
define( 'SECURE_AUTH_KEY',  '%$p.,&*2?x:rIv|:Ud&LRnx_+bXH3Hi?cX6*n2fLe,%O+G^veZ Ol1>vH{Vyz}df' );
define( 'LOGGED_IN_KEY',    'Yd5{#v5Sv;7eo._.z(8]FG{zY]Kd-+?P;)F1-5S0DXB_I w WUt4%2$p8Q$T@2ps' );
define( 'NONCE_KEY',        'gQ>AjZ*X`e5`pJ-%D&cb(R98DE>xLA)z?)EN7EkDk%[;xEoS9d/N]9j?G-&0!&ll' );
define( 'AUTH_SALT',        'u>n9Nuv=,JI!i 4TL)pf}zKsX([x4a=#>m?2`] RUZmBU`}3tTSWS>B{aC|=&cP,' );
define( 'SECURE_AUTH_SALT', '=(yuK33>=H.a2@**,xocdO[,u`Prp$zvv!X]AYMf6(b+@}&!^zF70=X|3o?^Q4E+' );
define( 'LOGGED_IN_SALT',   '*n,a#!v+P .(}.[ Mk&7WhAq#BS@cihNs]-7=Q:,&:h](~aodU`jm6$!QrO{),Ii' );
define( 'NONCE_SALT',       ']t8/ZjUwUd D$@0 ?r5i)rA7@(_`AVlSD<aGm;o3=jadDaoc!5^~j*I2lvf^s%dH' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
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
