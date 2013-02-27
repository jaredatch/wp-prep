<?php
/**
 * Retrieves and creates the wp-config.php file.
 *
 * The permissions for the base directory must allow for writing files in order
 * for the wp-config.php to be created using this page.
 *
 * @internal This file must be parsable by PHP4.
 * @package wordpress-prep
 * @version 1.1.0
 * @author Travis Smith
 * @copyright Copyright (c) 2012, Travis Smith
 * @link http://github.com/jaredatch/wp-prep
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * Based from the WordPress setup-config.php
 *
 */ 

/**
 * We are installing.
 *
 * @package WordPress
 */
define('WP_INSTALLING', true);

/**
 * We are blissfully unaware of anything.
 */
define('WP_SETUP_CONFIG', true);

/**
 * Disable error reporting
 *
 * Set this to error_reporting( E_ALL ) or error_reporting( E_ALL | E_STRICT ) for debugging
 */
error_reporting(0);

/**#@+
 * These three defines are required to allow us to use require_wp_db() to load
 * the database class while being wp-content/db.php aware.
 * @ignore
 */
if ( isset( $_GET['directory'] ) )
	define('ABSPATH', dirname(__FILE__).'/'.$_GET['directory'].'/');
else
	define('ABSPATH', dirname(__FILE__).'/');
define('WPINC', 'wp-includes');
define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
define('WP_DEBUG', false);

/**#@-*/

require(ABSPATH . WPINC . '/load.php');
require(ABSPATH . WPINC . '/version.php');

// Check for the required PHP version and for the MySQL extension or a database drop-in.
wp_check_php_mysql_versions();

require_once(ABSPATH . WPINC . '/functions.php');

// Also loads plugin.php, l10n.php, pomo/mo.php (all required by setup-config.php)
wp_load_translations_early();

// Turn register_globals off.
wp_unregister_GLOBALS();

require_once(ABSPATH . WPINC . '/compat.php');
require_once(ABSPATH . WPINC . '/class-wp-error.php');
require_once(ABSPATH . WPINC . '/formatting.php');

// Add magic quotes and set up $_REQUEST ( $_GET + $_POST )
wp_magic_quotes();

if ( ! file_exists( ABSPATH . 'wp-config-sample.php' ) )
	wp_die( __( 'Sorry, I need a wp-config-sample.php file to work from. Please re-upload this file from your WordPress installation.' ) );

$config_file = file(ABSPATH . 'wp-config-sample.php');

// Check if wp-config.php has been created
if ( file_exists( ABSPATH . 'wp-config.php' ) )
	wp_die( '<p>' . sprintf( __( "The file 'wp-config.php' already exists. If you need to reset any of the configuration items in this file, please delete it first. You may try <a href='%s'>installing now</a>." ), 'install.php' ) . '</p>' );

// Check if wp-config.php exists above the root directory but is not part of another install
if ( file_exists(ABSPATH . '../wp-config.php' ) && ! file_exists( ABSPATH . '../wp-settings.php' ) )
	wp_die( '<p>' . sprintf( __( "The file 'wp-config.php' already exists one level above your WordPress installation. If you need to reset any of the configuration items in this file, please delete it first. You may try <a href='install.php'>installing now</a>."), 'install.php' ) . '</p>' );

$step = isset( $_GET['step'] ) ? (int) $_GET['step'] : 0;

/**
 * Display setup wp-config.php file header.
 *
 * @ignore
 * @since 2.3.0
 * @package WordPress
 * @subpackage Installer_WP_Config
 */
if ( !function_exists('setup_config_display_header') ) {
function setup_config_display_header() {
	global $wp_version;

	header( 'Content-Type: text/html; charset=utf-8' );
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml"<?php if ( is_rtl() ) echo ' dir="rtl"'; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php _e( 'WordPress &rsaquo; Setup Configuration File' ); ?></title>
<?php
if ( isset( $_GET['directory'] ) ):
?>
<link rel="stylesheet" href="<?php echo $_GET['directory']; ?>/wp-admin/css/install.css?ver=<?php echo preg_replace( '/[^0-9a-z\.-]/i', '', $wp_version ); ?>" type="text/css" />
<link rel="stylesheet" href="<?php echo $_GET['directory']; ?>/wp-includes/css/buttons.css?ver=<?php echo preg_replace( '/[^0-9a-z\.-]/i', '', $wp_version ); ?>" type="text/css" />
<?php
else:
?>
<link rel="stylesheet" href="wp-admin/css/install.css?ver=<?php echo preg_replace( '/[^0-9a-z\.-]/i', '', $wp_version ); ?>" type="text/css" />
<link rel="stylesheet" href="wp-includes/css/buttons.css?ver=<?php echo preg_replace( '/[^0-9a-z\.-]/i', '', $wp_version ); ?>" type="text/css" />
<?php
endif;
?>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>
<script type="text/javascript" src="jquery.form-repeater.js"></script>
</head>
<body class="wp-core-ui<?php if ( is_rtl() ) echo ' rtl'; ?>">
<h1 id="logo"><a href="<?php esc_attr_e( 'http://wordpress.org/' ); ?>"><?php _e( 'WordPress' ); ?></a></h1>
<?php
} // end function setup_config_display_header();
}

switch($step) {
	case 0:
		setup_config_display_header();
?>

<p><?php _e( 'Welcome to WordPress. Before getting started, we need some information on the database. You will need to know the following items before proceeding.' ) ?></p>
<ol>
	<li><?php _e( 'Database name' ); ?></li>
	<li><?php _e( 'Database username' ); ?></li>
	<li><?php _e( 'Database password' ); ?></li>
	<li><?php _e( 'Database host' ); ?></li>
	<li><?php _e( 'Table prefix (if you want to run more than one WordPress in a single database)' ); ?></li>
</ol>
<p><strong><?php _e( "If for any reason this automatic file creation doesn&#8217;t work, don&#8217;t worry. All this does is fill in the database information to a configuration file. You may also simply open <code>wp-config-sample.php</code> in a text editor, fill in your information, and save it as <code>wp-config.php</code>." ); ?></strong></p>
<p><?php _e( "In all likelihood, these items were supplied to you by your Web Host. If you do not have this information, then you will need to contact them before you can continue. If you&#8217;re all ready&hellip;" ); ?></p>

<p class="step"><a href="setup-config.php?step=1<?php if ( isset( $_GET['directory'] ) ) echo '&amp;directory='.$_GET['directory']; if ( isset( $_GET['noapi'] ) ) echo '&amp;noapi'; ?>" class="button button-large"><?php _e( 'Let&#8217;s go!' ); ?></a></p>
<?php
	break;

	case 1:
		setup_config_display_header();
		
		if ( isset($_GET['directory']) )
			$url = 'setup-config.php?step=2&directory=' . $_GET['directory'];
		else
			$url = 'setup-config.php?step=2';
	?>
<form method="post" action="<?php echo $url; ?>">
	<p><?php _e( "Below you should enter your database connection details. If you&#8217;re not sure about these, contact your host." ); ?></p>
	<table class="form-table">
		<tr>
			<th scope="row"><label for="dbname"><?php _e( 'Database Name' ); ?></label></th>
			<td><input name="dbname" id="dbname" type="text" size="25" value="wordpress" /></td>
			<td><?php _e( 'The name of the database you want to run WP in.' ); ?></td>
		</tr>
		<tr>
			<th scope="row"><label for="uname"><?php _e( 'User Name' ); ?></label></th>
			<td><input name="uname" id="uname" type="text" size="25" value="<?php echo htmlspecialchars( _x( 'username', 'example username' ), ENT_QUOTES ); ?>" /></td>
			<td><?php _e( 'Your MySQL username' ); ?></td>
		</tr>
		<tr>
			<th scope="row"><label for="pwd"><?php _e( 'Password' ); ?></label></th>
			<td><input name="pwd" id="pwd" type="text" size="25" value="<?php echo htmlspecialchars( _x( 'password', 'example password' ), ENT_QUOTES ); ?>" /></td>
			<td><?php _e( '&hellip;and your MySQL password.' ); ?></td>
		</tr>
		<tr>
			<th scope="row"><label for="dbhost"><?php _e( 'Database Host' ); ?></label></th>
			<td><input name="dbhost" id="dbhost" type="text" size="25" value="localhost" /></td>
			<td><?php _e( 'You should be able to get this info from your web host, if <code>localhost</code> does not work.' ); ?></td>
		</tr>
		<tr>
			<th scope="row"><label for="prefix"><?php _e( 'Table Prefix' ); ?></label></th>
			<td><input name="prefix" id="prefix" type="text" value="wp_" size="25" /></td>
			<td><?php _e( 'If you want to run multiple WordPress installations in a single database, change this.' ); ?></td>
		</tr>
		<tr>
			<th scope="row"><label for="custom_0_0" data-pattern-text="Custom +=1:"><?php _e( 'Custom Fields' ); ?></label></th>
			<td>
			<div id="setup">
				<div class="r-group">
				<p>
					<label for="custom_0_0" data-pattern-text="Define Comment +=1:">Define Comment 1:</label>
					<input type="text" name="custom[0][comment]" id="custom_0_key" data-pattern-name="custom[++][comment]" data-pattern-id="custom_++_comment" />
				</p>
				<p>
					<label for="custom_0_0" data-pattern-text="Define Key +=1:">Define Key 1:</label>
					<input type="text" name="custom[0][key]" id="custom_0_key" data-pattern-name="custom[++][key]" data-pattern-id="custom_++_key" />
				</p>
				<p>
					<label for="custom_0_0" data-pattern-text="Define Value +=1:">Define Value 1:</label>
					<input type="text" name="custom[0][value]" id="custom_0_value" data-pattern-name="custom[++][value]" data-pattern-id="custom_++_value" />
				</p>
				<p>
					<!-- Add a remove button for the item. If one didn't exist, it would be added to overall group -->
					<button type="button" class="r-btnRemove">Remove -</button>
				</p>
				</div>
				<button type="button" class="r-btnAdd"><?php _e( 'Add +' ); ?></button>
			</div>
			<td><?php _e( 'For custom define() statements for licenses.' ); ?></td>
		</tr>
	</table>
<script type="text/javascript">
jQuery('#setup').repeater({
  btnAddClass: 'r-btnAdd',
  btnRemoveClass: 'r-btnRemove',
  groupClass: 'r-group',
  minItems: 1,
  maxItems: 0,
  startingIndex: 0,
  reindexOnDelete: true,
  repeatMode: 'append',
  animation: null,
  animationSpeed: 400,
  animationEasing: 'swing',
  clearValues: true
});
</script>
	<?php if ( isset( $_GET['noapi'] ) ) { ?><input name="noapi" type="hidden" value="1" /><?php } ?>
	<p class="step"><input name="submit" type="submit" value="<?php echo htmlspecialchars( __( 'Submit' ), ENT_QUOTES ); ?>" class="button button-large" /></p>
</form>
<?php
	break;

	case 2:
	$custom = false;
	foreach ( array( 'dbname', 'uname', 'pwd', 'dbhost', 'prefix' ) as $key )
		$$key = trim( wp_unslash( $_POST[ $key ] ) );
	
	$tryagain_link = '</p><p class="step"><a href="setup-config.php?step=1" onclick="javascript:history.go(-1);return false;" class="button button-large">' . __( 'Try again' ) . '</a>';

	if ( empty( $prefix ) )
		wp_die( __( '<strong>ERROR</strong>: "Table Prefix" must not be empty.' . $tryagain_link ) );

	// Validate $prefix: it can only contain letters, numbers and underscores.
	if ( preg_match( '|[^a-z0-9_]|i', $prefix ) )
		wp_die( __( '<strong>ERROR</strong>: "Table Prefix" can only contain numbers, letters, and underscores.' . $tryagain_link ) );

	// Test the db connection.
	/**#@+
	 * @ignore
	 */
	define('DB_NAME', $dbname);
	define('DB_USER', $uname);
	define('DB_PASSWORD', $pwd);
	define('DB_HOST', $dbhost);
	/**#@-*/

	// We'll fail here if the values are no good.
	require_wp_db();
	if ( ! empty( $wpdb->error ) )
		wp_die( $wpdb->error->get_error_message() . $tryagain_link );

	// Fetch or generate keys and salts.
	$no_api = isset( $_POST['noapi'] );
	if ( ! $no_api ) {
		require_once( ABSPATH . WPINC . '/class-http.php' );
		require_once( ABSPATH . WPINC . '/http.php' );
		wp_fix_server_vars();
		/**#@+
		 * @ignore
		 */
		if ( !function_exists('get_bloginfo') ) {
		function get_bloginfo() {
			return ( ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . str_replace( $_SERVER['PHP_SELF'], '/wp-admin/setup-config.php', '' ) );
		}
		}
		/**#@-*/
		$secret_keys = wp_remote_get( 'https://api.wordpress.org/secret-key/1.1/salt/' );
	}

	if ( $no_api || is_wp_error( $secret_keys ) ) {
		$secret_keys = array();
		require_once( ABSPATH . WPINC . '/pluggable.php' );
		for ( $i = 0; $i < 8; $i++ ) {
			$secret_keys[] = wp_generate_password( 64, true, true );
		}
	} else {
		$secret_keys = explode( "\n", wp_remote_retrieve_body( $secret_keys ) );
		foreach ( $secret_keys as $k => $v ) {
			$secret_keys[$k] = substr( $v, 28, 64 );
		}
	}

	$key = 0;
	// Not a PHP5-style by-reference foreach, as this file must be parseable by PHP4.
	foreach ( $config_file as $line_num => $line ) {
		if ( '$table_prefix  =' == substr( $line, 0, 16 ) ) {
			$config_file[ $line_num ] = '$table_prefix  = \'' . addcslashes( $prefix, "\\'" ) . "';\r\n";
			continue;
		}
		
		if ( preg_match( '/^\/\**#@+/', $line, $match ) && false === $custom ) {
			if ( ! empty( $_POST['custom'] ) ) {
				$insert = array();
				// Build new constants
				foreach ( $_POST['custom'] as $key => $array ) {
					// Capture line number
					$insert_line_num = $line_num;
					
					// Build Custom Insert Array
					$insert[] = "/** " . $array['comment'] . " */\r\n";
					$insert[] = "define('" . $array['key'] . "', '" . addcslashes( $array['value'], "\\'" ) . "'); // Added by WP-Prep\r\n";
					$insert[] = "\r\n";
					//$insert[] = "/**#@+";
				}
				// Prevent this from happening again
				$custom = true;
			}
		}

		if ( ! preg_match( '/^define\(\'([A-Z_]+)\',([ ]+)/', $line, $match ) )
			continue;

		$constant = $match[1];
		$padding  = $match[2];

		switch ( $constant ) {
			case 'DB_NAME'     :
			case 'DB_USER'     :
			case 'DB_PASSWORD' :
			case 'DB_HOST'     :
				$config_file[ $line_num ] = "define('" . $constant . "'," . $padding . "'" . addcslashes( constant( $constant ), "\\'" ) . "');\r\n";
				break;
			case 'AUTH_KEY'         :
			case 'SECURE_AUTH_KEY'  :
			case 'LOGGED_IN_KEY'    :
			case 'NONCE_KEY'        :
			case 'AUTH_SALT'        :
			case 'SECURE_AUTH_SALT' :
			case 'LOGGED_IN_SALT'   :
			case 'NONCE_SALT'       :
				$config_file[ $line_num ] = "define('" . $constant . "'," . $padding . "'" . $secret_keys[$key++] . "');\r\n";
				break;
		}
	}
	unset( $line );
	
	// Add custom defines.
	array_splice( $config_file, $insert_line_num, 0, $insert );

	if ( ! is_writable(ABSPATH) ) :
		setup_config_display_header();
?>
<p><?php _e( "Sorry, but I can&#8217;t write the <code>wp-config.php</code> file." ); ?></p>
<p><?php _e( 'You can create the <code>wp-config.php</code> manually and paste the following text into it.' ); ?></p>
<textarea id="wp-config" cols="98" rows="15" class="code" readonly="readonly"><?php
		foreach( $config_file as $line ) {
			echo htmlentities($line, ENT_COMPAT, 'UTF-8');
		}
?></textarea>
<p><?php _e( 'After you&#8217;ve done that, click &#8220;Run the install.&#8221;' ); ?></p>
<p class="step"><a href="install.php" class="button button-large"><?php _e( 'Run the install' ); ?></a></p>
<script>
(function(){
var el=document.getElementById('wp-config');
el.focus();
el.select();
})();
</script>
<?php
	else :
		$handle = fopen(ABSPATH . 'wp-config.php', 'w');
		foreach( $config_file as $line ) {
			fwrite($handle, $line);
		}
		fclose($handle);
		chmod(ABSPATH . 'wp-config.php', 0666);
		setup_config_display_header();
?>
<p><?php _e( "All right sparky! You&#8217;ve made it through this part of the installation. WordPress can now communicate with your database. If you are ready, time now to&hellip;" ); ?></p>

<p class="step"><a href="install.php" class="button button-large"><?php _e( 'Run the install' ); ?></a></p>
<?php
	endif;
	break;
}
?>
</body>
</html>
