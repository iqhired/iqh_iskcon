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
define( 'DB_NAME', 'iqh_isk' );

/** MySQL database username */
define( 'DB_USER', 'ashams001' );

/** MySQL database password */
define( 'DB_PASSWORD', 'iqHired@123' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         'uBu-!.V_,3L!i-#L|`xf1Nww:c616kWNEQ:^U@8KZa@Z$pz5v/)QbGU!:HEcm}VJ' );
define( 'SECURE_AUTH_KEY',  'wQJH3)z:.P,<PNha;s[fUGvpE,:f-eihvUoFTm.Bc;@*rYLD|`NLS`NMG3lx3bb|' );
define( 'LOGGED_IN_KEY',    '[XtEZc[{EB4qi&4RC2BcZj^LW;Mi6a@)AdRHy~~yU,bt02tW54]X/v) doc)9 ^2' );
define( 'NONCE_KEY',        '}:^7xbtKkK^;4g9y*Y:Tr}-Jb0Ct,g-0.<-M$OTe~9o G=s]Kl+-HK2g[^9^8Zb*' );
define( 'AUTH_SALT',        ']aA/I)U%O?Bt&iDRF6S(>6P,g-3]_pn1Faw%UAqXcHa1z!MW}3>MK8)l)Die~7:O' );
define( 'SECURE_AUTH_SALT', '~D&0~!_+}3)9{T:*coNaR_0D6T]a<~Cl|JeLnXuOwKETU%?1Gg$D}w[}`Vk_,OG6' );
define( 'LOGGED_IN_SALT',   '!X Wk{c[F~9x< WR2,b59RJ- A5 m*q7D)%c?,V,S z}u,$K`A*H|X8oy,(B;(EN' );
define( 'NONCE_SALT',       'rpUKjd61bYT:Ti|1xw.s,A|o]8%*)s?czB;l!3X=MT*8c;cu+o[fXj,+1v9FdtPD' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'ik_';

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

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
