<?php
/**
 * @package wordpress-prep
 * @version 1.0
 * @author Jared Atchison
 * @copyright Copyright (c) 2012, Jared Atchison
 * @link http://github.com/jaredatch/wp-prep
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 *
 * Based from the WordPress Downloader
 * http://www.farinspace.com/wordpress-downloader/
 *
 */

/** The base configuration */
define( 'PASSWORD', 'password' );   // Change this to a very long password or passphrase
define( 'GENESIS_URL' , 'http://yourdomain.com/files/genesis.zip' );
define( 'BASE_URL' , 'https://github.com/jaredatch/Genesis-Starter-Theme/zipball/master' );
define( 'TGMPA_URL' , 'http://yourdomain.com/files/plugin-install.txt' );

/** Setup the variables we will be using. */
$pass                 = isset( $_POST['pass'] ) ? $_POST['pass'] : '';
$url                  = isset( $_POST['url'] ) ? $_POST['url'] : '';
$directory            = isset( $_POST['directory'] ) ? $_POST['directory'] : '';
$option_this          = isset( $_POST['option_this'] ) ? $_POST['option_this'] : '';
$option_genesis       = isset( $_POST['option_genesis'] ) ? $_POST['option_genesis'] : '';
$option_base          = isset( $_POST['option_base'] ) ? $_POST['option_base'] : '';
$option_plugins       = isset( $_POST['option_plugins'] ) ? $_POST['option_plugins'] : '';
$option_twentyten     = isset( $_POST['option_twentyten'] ) ? $_POST['option_twentyten'] : '';
$option_twentyeleven  = isset( $_POST['option_twentyeleven'] ) ? $_POST['option_twentyeleven'] : '';
$option_hello         = isset( $_POST['option_hello'] ) ? $_POST['option_hello'] : '';

if ( isset( $_POST['finish'] ) && isset( $pass ) && $pass == PASSWORD ) {
	
	/** Instal WordPress **********************************************************/
	
	// If some how the URL is blank, fall back to default
	if ( !isset( $url ) ) $url = 'http://wordpress.org/latest.zip';
	
	download( 'wordpress.zip' , $url );
	unzip( 'wordpress.zip' );	
	move_and_delete( 'wordpress', '' );
	
	/** Install Genesis ***********************************************************/
	
	if ( isset( $option_genesis ) && $option_genesis == 1 ) {	
		download( 'genesis.zip' , GENESIS_URL );
		unzip( 'genesis.zip' );
		move_and_delete( 'genesis', '/wp-content/themes/genesis' );
	}
	
	/** Install Base Theme ********************************************************/
	
	if ( isset( $option_base ) && $option_base == 1 ) {
		download( 'base-theme.zip' , BASE_URL );
		unzip( 'base-theme.zip' );
		
		// This is needed because GitHub adds characters to the directory
		// which contains the unzip. If you are not using GitHub this step 
		// is not needed. If you ARE using GitHub, adjust the name accordingly. 
		$containing_dir =  glob( 'jaredatch-Genesis-Starter'. '*' , GLOB_ONLYDIR );
		$containing_dir = $containing_dir[0];
		
		move_and_delete( $containing_dir, '/wp-content/themes/base' );
	}
	
	/** Install Plugin Bundle *****************************************************/
	
	if ( isset( $option_plugins ) && $option_plugins == 1 ) {
		download( 'plugin-install.php' , TGMPA_URL );
		if ( !empty( $directory ) ) {
			// move to specified directory
			copy( 'plugin-install.php', './' . $directory . '/wp-content/plugins/plugin-install.php' );
		} else {
			// move to root directory
			copy( 'plugin-install.php','./wp-content/plugins/plugin-install.php' );
		}		
		unlink( 'plugin-install.php' );
	}	
	
	/** Delete TwentyTen/Eleven ***************************************************/
	
	if ( isset( $option_twentyten ) && $option_twentyten == 1 ){
		if ( !empty( $directory ) ) {
			recursive_remove( './' . $directory . '/wp-content/themes/twentyten' );
		} else {
			recursive_remove( './wp-content/themes/twentyten' );
		}
	}
	if ( isset( $option_twentyeleven ) && $option_twentyeleven == 1 ){
		if ( !empty( $directory ) ) {
			recursive_remove( './' . $directory . '/wp-content/themes/twentyeleven' );
		} else {
			recursive_remove( './wp-content/themes/twentyeleven' );
		}
	}
	
	/** Delete This Script ********************************************************/
	
	if ( $option_this ) {
		unlink( 'wp-prep.php' );
	}
	
	/** Delete Hello Dolly ********************************************************/
	
	if ( !empty( $directory ) ) {
		unlink( './' . $directory . '/wp-content/plugins/hello.php' );
	} else {
		unlink( './wp-content/plugins/hello.php' );
	}
	
	$success = 'win!';
	
} else {
	// Do nothing at the moment. Take in the moment.
}

/**
 * Downloads the a file from the url provided.
 *
 * @param file, what it will be saved as
 * @param url, location of file to download
 */
function download( $file = '', $url = '' ) {
	
	$user_agent = 'WordPress Setup Script';
	
	$file_open  = fopen( $file, 'w' );
	$file_setup = curl_init();

	curl_setopt(  $file_setup, CURLOPT_USERAGENT,      $user_agent );
	curl_setopt(  $file_setup, CURLOPT_URL,            $url        );
	curl_setopt(  $file_setup, CURLOPT_SSL_VERIFYHOST, 0           );
	curl_setopt(  $file_setup, CURLOPT_SSL_VERIFYPEER, 0           );
	curl_setopt(  $file_setup, CURLOPT_FAILONERROR,    true        );
	curl_setopt(  $file_setup, CURLOPT_HEADER,         0           ); 	
	@curl_setopt( $file_setup, CURLOPT_FOLLOWLOCATION, true        );
	curl_setopt(  $file_setup, CURLOPT_AUTOREFERER,    true        );
	curl_setopt(  $file_setup, CURLOPT_BINARYTRANSFER, true        );
	curl_setopt(  $file_setup, CURLOPT_TIMEOUT,        120         );
	curl_setopt(  $file_setup, CURLOPT_FILE,           $file_open  );

	$file_grab = curl_exec( $file_setup ) ;

	if ( !$file_grab ) {
		die( 'File download error: '. curl_error( $file_setup ) .', please try again.' );
		curl_close( $file_setup );
	}

	// Closing time, open all the doors and let you out into the world
	// Closing time, turn all of the lights on over every boy and every girl
	// Closing time, one last call for alcohol so finish your whiskey or beer
	// Closing time, you don't have to go home but you can't stay here
	fclose( $file_open );

	// Check to see if it downloaded properly
	if ( !file_exists( $file ) ) {
		die( 'Hmmm, looks like the file did not downloaded. (Cannot be found)' );
	}
}

/**
 * Unzips a file provided
 *
 * @param file, name of file to unzip
 */
function unzip( $file ) {
	// Let's unzip this bad boy to a temp wordpr...
	if ( class_exists( 'ZipArchive' ) ) {
		$zip = new ZipArchive;
		if ( $zip->open( $file ) !== TRUE ) {
			die( 'Seems we were unable to open zip file!' );
		}
		$zip->extractTo( './' );
		$zip->close();
	} else {
		// attempt tp fallback on shell command
		@shell_exec( 'unzip -d ./ '. $file );
	}
	// Proceed to nuke the original zip file
	unlink( $file );
}

/**
 * Move and delete. Simple.
 */
function move_and_delete( $what = '', $where = '') {
	global $directory;
	if ( !empty( $directory ) ) {
		// move to specified directory
		recursive_move( './' . $what, './' . $directory . $where );
	} else {
		// move to root directory
		recursive_move( './' . $what, '.' . $where );
	}
	// Remove the old copy, we are done with it!
	recursive_remove( './' . $what );
}

/**
 * Recursive move.
 */
function recursive_move( $src, $dst ) { 
	$dir = opendir( $src ); 
	@mkdir( $dst ); 
	while( false !== ( $file = readdir( $dir ) ) ) { 
		if ( $file != '.' AND $file != '..' ) { 
			if ( is_dir( $src . '/' . $file ) ) { 
				recursive_move( $src . '/' . $file,$dst . '/' . $file ); 
			} else { 
				rename( $src . '/' . $file,$dst . '/' . $file ); 
			} 
		} 
	} 
	closedir($dir); 
} 

/**
 * Recursive REmove.
 */
function recursive_remove ($src ) { 
	$dir = opendir( $src) ; 
	while( false !== ( $file = readdir( $dir ) ) ) { 
		if ( $file != '.' AND $file != '..' ) { 
			if ( is_dir( $src . '/' . $file ) ) { 
				recursive_remove( $src . '/' . $file ); 
			} else { 
				unlink( $src . '/' . $file );
			} 
		} 
	}
	rmdir( $src );
	closedir( $dir ); 
}

/**
 * Promt for the password.
 */
function password_prompt( $status = '') {
	if ( $status == 'fail' ) {
		echo '<div class="message error">Oops! Incorrect password.</div>';
	}
	?>
	<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><label for="pass">Password</label></th>
				<td>
					<input name="pass" id="pass" type="password" size="25" value="" />
					<small>A password is required to prevent unauthorized usage of this script.</small>
				</td>
			</tr>
		</tbody>
	</table>
	<p class="submit"><input type="submit" name="submit" value="Submit" /></p>
	</form>
	<?php
}

/** Get the current version of WordPress */
$contents = @file_get_contents( 'http://api.wordpress.org/core/version-check/1.1/' );
if ( !empty( $contents ) ) {
	$version = explode( "\n", $contents );
	$version = $version[2];
}

/** Set fields and checkboxes */
$url                     = !isset( $url ) ? 'http://wordpress.org/latest.zip' : $url ;
$directory               = !isset( $directory ) ? '' : $directory ;
$input_p                 = !isset( $pass ) ? '' : $pass ;
$option_this             = ( empty ( $option_this ) || ( isset( $option_this ) && $option_this == 1 ) ) ? 'checked="checked"' : '' ;
$option_genesis          = ( isset ( $option_genesis ) && $option_genesis == 1 ) ? 'checked="checked"' : '' ;
$option_base             = ( isset ( $option_base ) && $option_base == 1 ) ? 'checked="checked"' : '' ;
$option_plugins          = ( isset ( $option_plugins  ) && $option_plugins  == 1 ) ? 'checked="checked"' : '' ;
$option_twentyten        = ( isset ( $option_twentyten ) &&  $option_twentyten == 1 ) ? 'checked="checked"' : '' ; 
$option_twentyeleven     = ( isset ( $option_twentyeleven ) &&  $option_twentyeleven == 1 ) ? 'checked="checked"' : '' ; 
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>WordPress Prep</title>
	<meta name="robots" content="noindex,nofollow,noarchive">
	<style>
		body { background: #F9F9F9; font-family: Helvetica,Arial,sans-serif; }
		#container { background: white; color: #333; margin: 2em auto; padding: 1em 2em; border-radius: 3px; border: 1px solid #DFDFDF; width: 700px; font-size: 14px; }
		a { color: #21759B; text-decoration: none; }
		a:hover { color: #D54E21 }
		h1 { border-bottom: 1px solid #dadada; clear: both; color: #666; font: 30px Georgia,"Times New Roman",Times,serif; margin: 10px 0 20px 0; padding: 0 0 10px 0; text-align: center; }
		h2 { font-size: 16px }
		p { padding-bottom: 2px; font-size: 14px; line-height: 1.5; }
		code, .code { font-family: Monaco, Andale Mono, Courier New, monospace; border-radius: 5px; background: #f5f5f5; padding: 2px 4px; }
		a img { border: 0 }
		.submit input, .button, .button-secondary { font-family: sans-serif; text-decoration: none; font-size: 14px!important; line-height: 16px; padding: 6px 12px; cursor: pointer; border: 1px solid #bbb; color: #464646; border-radius: 15px; -moz-box-sizing: content-box; -webkit-box-sizing: content-box; box-sizing: content-box; }
		.button:hover, .button-secondary:hover, .submit input:hover { color: #000; border-color: #666; }
		.button, .submit input, .button-secondary { background: #f2f2f2 }
		.button:active, .submit input:active, .button-secondary:active { background: #eee }
		input[type=text], input[type=password] { line-height: 20px; color: #333; font-size: 15px; padding: 3px 5px; border: 1px #DFDFDF solid; border-radius: 3px; }
		.form-table { border-collapse: collapse; margin-top: 1em; width: 100%; }
		.form-table td { margin-bottom: 9px; padding: 10px 20px 10px 0; border-bottom: 8px solid #fff; font-size: 14px; vertical-align: top; }
		.form-table th { font-size: 14px; text-align: left; padding: 18px 20px 10px 0; border-bottom: 8px solid #fff; width: 140px; vertical-align: top; }
		.form-table p { margin: 4px 0 0 0; font-size: 11px; }
		.form-table input[type=text], .form-table input[type=password] { width: 500px }
		.form-table th p { font-weight: normal }
		.form-table small { font-size: 11px; color: #666; display: block; margin-top: 10px; line-height: 16px; }
		.message { border: 1px solid #e6db55; padding: 10px; margin: 10px 0; background-color: #ffffe0; border-radius: 3px; }
		.message.error { background-color: #F2DEDE; border-color: #EED3D7; color: #B94A48; }
		span.note { color: #999; font-style: italic; font-size: 12px; }
		#footer { text-align: center; font-size: 10px; color: #999; padding: 0; margin: 0 0 5px 0; }
		#footer a { color: #999 }
		#footer a:hover { text-decoration: underline }
		#credits { padding: 10px 0; font-size: 12px; line-height: 18px; display: none; }
	</style>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
	<script type="text/javascript" charset="utf-8">
		jQuery(document).ready(function($){
			// Toggle WordPress download URLs
			var $url_field = $('#wp-url');
			var $url_toggle = $('#url-toggle');
			var $url_current = $('#current');
			var version = '<?php echo $version; ?>';			
			$url_toggle.click(function() {
				if ( $url_toggle.text() == 'trunk' ) {
					$url_field.val('http://wordpress.org/nightly-builds/wordpress-latest.zip');
					$url_toggle.text('the latest version (' + version + ')'); 
					$url_current.text('trunk'); 
				} else {
					$url_field.val('http://wordpress.org/latest.zip');
					$url_toggle.text('trunk'); 
					$url_current.text('the latest version (' + version + ')');  
				};
				return false;
			});
			// Toggle creits
			$('#credits-toggle').click(function() {
				$('#credits').fadeToggle();
				return false;
			});
		});
	</script>
</head>
<body>
	<div id="container">
		<h1>WordPress Prep</h1>
		<?php
		if ( isset( $success ) ) :
			echo '<div class="message success">All done! Jump to the ';
			if ( !empty( $directory ) ) {
				echo '<a href="' . $directory . '/index.php">WordPress configuration</a>.';
			} else {
				echo '<a href="index.php">WordPress configuration</a>.';
			}	
			echo '</div>';
		elseif ( !isset( $pass ) ) :
			password_prompt();
		elseif ( isset( $pass ) && $pass !=  PASSWORD ) :
			password_prompt( 'fail' );
		elseif ( isset( $pass ) && $pass == PASSWORD ) :
		?>
		<p>Let's get started! Just a few handy options below.</p>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><label for="wp-url">WordPress Zip URL</label></th>
						<td>
							<input name="url" id="wp-url" type="text" size="25" value="<?php echo $url; ?>" />
							<small>Currently set to <span id="current">the latest version (<?php echo $version; ?>)</span>, would you like to use <a href="#" id="url-toggle">trunk</a>?</small>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="wp-directory">Directory Name</label></th>
						<td>
							<input name="directory" id="wp-directory" type="text" size="25" value="" />
							<small>Specify a directory for the WordPress install. It will be created if given.<em>(Optional)</em></small>
						</td>
					</tr>
				</tbody>
			</table>
			<p><input type="checkbox" name="option_genesis" value="1" <?php echo $option_genesis; ?> /> Install <a href="http://jaredatchison.com/go/genesis/">Genesis Framework</a></p>
			<p><input type="checkbox" name="option_base" value="1" <?php echo $option_base; ?> /> Install base theme</a></p>
			<p><input type="checkbox" name="option_plugins" value="1" <?php echo $option_plugins; ?> /> Install TGMPA plugin bundle</p>
			<p><input type="checkbox" name="option_this" checked="checked" value="1"<?php echo $option_this; ?> /> Delete <code><?php echo $_SERVER['PHP_SELF']; ?></code> when done <span class="note">(recommended)</span></p>
			<p><input type="checkbox" name="option_hello" value="1"<?php echo $option_hello; ?> /> Delete <code>wp-content/plugins/hello.php</code></p>
			<p><input type="checkbox" name="option_twentyten" value="1"<?php echo $option_twentyten; ?> /> Delete <code>wp-content/themes/twentyten</code></p>
			<p><input type="checkbox" name="option_twentyeleven" value="1"<?php echo $option_twentyeleven; ?> /> Delete <code>wp-content/themes/twentyeleven</code></p>
			<input type="hidden" name="pass" value="<?php echo $input_p; ?>">
			<p class="submit"><input type="submit" name="finish" value="Finish Setup!"/></p>
		</form>
		<? endif; ?>
	</div>
	<div id="footer">
		<a href="http://github.com/jaredatch/">WP Setup script</a> by <a href="http://jaredatchison.com">Jared Atchison</a> - <a href="#" id="credits-toggle">Credits</a>
		<div id="credits"> The script is loosely based off of <a href="http://www.farinspace.com/wordpress-downloader/">WordPress Downloader</a> by farinspace. Plugin bundle activation is powered by <a href="http://tgmpluginactivation.com/">TGM Plugin Activation</a>.<br />The other elements were hacked together by <a href="http://jaredatchison.com">me</a>!</div>
	</div>
</body>
</html>