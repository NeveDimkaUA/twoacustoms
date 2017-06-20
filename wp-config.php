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
define('DB_NAME', 'twoacustomsDB');

/** MySQL database username */
define('DB_USER', 'twoacustoms');

/** MySQL database password */
define('DB_PASSWORD', 'Monkeyman23');

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
define('AUTH_KEY',         'H_ti8d]l|(+{|YUx.z5*D<PJ|%WFUEx6^BbX~ck,/(wz!zrlMZF38;|8$j6;HyHM');
define('SECURE_AUTH_KEY',  'nun,+#3N,zYRFu[dWO)*V_!~;T6 lN^1!wS6-S>@`@BRiv9gfHZ?CqE{p2Hgm1t[');
define('LOGGED_IN_KEY',    'Z+>B@&KIw7UQPP2x^W0j)-:_#Ww.Px-#VOM:TZ#76{#J.d/CL&oddGP$^~eY;M a');
define('NONCE_KEY',        ')xIIK_6v,TeF]vDK2C(]e{.aDq9^wlq[ocuIvd>#&i;aAIFO!&U0$)PbQ%r.PZPg');
define('AUTH_SALT',        'cRTIvU.gO;BxQA!K;i<mH3Vt6:4~^`0?R~@=Vmh??,IP/(bB21#1D$o)4C#EYra~');
define('SECURE_AUTH_SALT', 'CL_GZ8ICfc3%ISm#*+G#|Mj$|;Ur7t`)2x^Oj:Q;-b*{FI$V*!F]~#**F[-6un`U');
define('LOGGED_IN_SALT',   'fGlAs7V?P|&}W(:YQt!XzaBIZ0.c{iKCN-,]_9@qPyu&C;_Q=i(bFW( U|mF!D$7');
define('NONCE_SALT',       '{o=uoK%+)OL]:BeY/ n5qVD=g^R[`&W=Iqf-yJIz;%d -3o=9;tv_Ry9ase*a<(^');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'tac_';

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

