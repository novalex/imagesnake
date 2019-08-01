<?php

function disable_ob() {
	// Turn off output buffering
	ini_set( 'output_buffering', 'off' );
	// Turn off PHP output compression
	ini_set( 'zlib.output_compression', false );
	// Implicitly flush the buffer(s)
	ini_set( 'implicit_flush', true );
	ob_implicit_flush( true );
	// Clear, and turn off output buffering
	while ( ob_get_level() > 0 ) {
		// Get the curent level
		$level = ob_get_level();
		// End the buffering
		ob_end_clean();
		// If the current level has not changed, abort
		if ( ob_get_level() == $level ) {
			break;
		}
	}
	// Disable apache output buffering/compression
	if ( function_exists( 'apache_setenv' ) ) {
		apache_setenv( 'no-gzip', '1' );
		apache_setenv( 'dont-vary', '1' );
	}
}

function display_image_grid( $images ) {
	if ( empty( $images ) ) {
		return;
	}

	?>
	<div id="image-grid">
		<?php

		foreach ( $images as $image ) {
			?>
			<div class="image-wrap <?php echo $image['brightness'] < 180 ? 'image-dark' : 'image-light'; ?>">
				<div class="image">
					<img src="<?php echo $image['src']; ?>" alt="<?php echo $image['alt']; ?>">
				</div>
				<p class="content">
					<strong><?php echo $image['name']; ?></strong>
					<small><?php echo $image['format']; ?>, <?php echo number_format( floatval( $image['size'] / 1024 ), 2 ); ?>kB, <?php echo $image['width']; ?>x<?php echo $image['height']; ?>px</small>
				</p>
			</div>
			<?php
		}

		?>
	</div>
	<?php
}

function process_request() {
	if ( empty( $_REQUEST['url'] ) ) {
		return;
	}

	$empty = false;

	$url = urldecode( $_REQUEST['url'] );

	// Run Python script.
	$command = 'python3 scraper.py ' . escapeshellarg( $url );
	$handle  = proc_open(
		$command,
		array(
			0 => array( 'pipe', 'r' ),
			1 => array( 'pipe', 'w' ),
			2 => array( 'file', '/tmp/errors.txt', 'a' ),
		),
		$pipes
	);

	if ( is_resource( $handle ) ) {
		$progress = 0;
		flush();
		while ( $line = fgets( $pipes[1] ) ) {
			preg_match( '/(\[[A-Z]+\])\s?(.*)/', $line, $matches );
			if ( ! empty( $matches[1] ) && ! empty( $matches[2] ) ) {
				switch ( $matches[1] ) {
					case '[START]':
					case '[STEP]':
						// echo '<pre>';
						// print $matches[2];
						// echo '</pre>';
						break;
					case '[PROGRESS]':
						$progress = intval( $matches[2] );
						break;
					case '[JSON]':
						$images = json_decode( $matches[2], true );
						break;
				}
			}
		}

		if ( empty( $images ) ) {
			$empty = true;
		}

		proc_close( $handle );
	} else {
		$empty = true;
	}

	?>
	<h2><?php echo ( $empty ? 'No images' : sprintf( '%d image(s)', count( $images ) ) ) . " found at \"$url\""; ?></h2>
	<?php

	display_image_grid( $images );
}

disable_ob();

if ( isset( $_GET['url'] ) ) {
	// GET request.
	process_request();
	exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>ImageSnake</title>
	<link rel="stylesheet" href="style.css">
</head>
<body>
	<div id="wrap">
		<?php $url = empty( $_POST['url'] ) ? '' : urldecode( $_REQUEST['url'] ); ?>
		<h1>Scrape imagesss</h1>

		<form id="scrape-form" action="<?php echo htmlspecialchars( $_SERVER['PHP_SELF'] ); ?>" method="post">
			<input type="text" name="url" placeholder="Enter URL..." value="<?php echo $url; ?>">
			<button name="scrape">Scrape</button>
		</form>

		<?php

		if ( isset( $_POST['scrape'] ) && ! empty( $_POST['url'] ) && filter_var( $_POST['url'], FILTER_VALIDATE_URL ) ) {
			process_request();
		}

		?>
	</div>
</body>
</html>
