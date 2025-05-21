<?php
/**
 * The base configuration for WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'merakiweddingplanner' );

/** MySQL database username */
define( 'DB_USER', 'merakiwp' );

/** MySQL database password */
define( 'DB_PASSWORD', 'Scrap-layup-7dipped' );

/** MySQL hostname */
define( 'DB_HOST', 'mysql' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**
 * Authentication Unique Keys and Salts.
 */
define('AUTH_KEY',         'zVM(-&)(D)/*2z2+NjORw0xy7g-ASVP*>.oh% Pt+N8WV,[Ic/{vhCn@N[,5KHOa');
define('SECURE_AUTH_KEY',  '+Dr4BhK~]NZCe$|m7zMF=N>y|aP/NS0@*E2-RLwS5Dm-%];JKI8!^K4mSG@5eqrW');
define('LOGGED_IN_KEY',    'oa5+zt]Q?mbu:D1G/z~lW7o*#@O$l`9G|}*^]IUL1I<PR=NW4(Y+d8N-~;0W-f*I');
define('NONCE_KEY',        'A!g{nHEH[@R%o>-y3KiAge[]o9QlPcLyx6:<gxzzoAzk-8vAX0f6=x3}hx^fk dA');
define('AUTH_SALT',        'u=;i|WX8Y;*s;nYdddt_Q%b%K~kBj%]pAOB6v_%N7:kdG6~;:T0<V]wUXvy$S)xX');
define('SECURE_AUTH_SALT', 'C$IY5*zF2/Db(A2*H6d#vpbcNB6z%F)}:QDHqWiDq=B|@V7yN,|XW<Wk+|54`)U=');
define('LOGGED_IN_SALT',   'DE#U|amjc_5(K$0Qm+!+RNr1qx4QJV~2#Z-r!M&uY[b01kFX+_+<O=HIX-8KCTiQ');
define('NONCE_SALT',       '9u#u}StKyObDk)sY-U5LjiF5o=]FMb@erjzUsM?mVO7h$I+@6j:?}v$`zVwpS=|c');

/**
 * WordPress Database Table prefix.
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 */
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WP_DEBUG_DISPLAY', true );
define( 'SCRIPT_DEBUG', true );
define( 'WP_MEMORY_LIMIT', '256M' );
define('WP_HOME', isset($_SERVER['HTTP_HOST']) ? 'http://' . $_SERVER['HTTP_HOST'] : 'http://localhost' );
define('WP_SITEURL', isset($_SERVER['HTTP_HOST']) ? 'http://' . $_SERVER['HTTP_HOST'] : 'http://localhost' );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', '/usr/src/wordpress/' );
}
/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' ); 