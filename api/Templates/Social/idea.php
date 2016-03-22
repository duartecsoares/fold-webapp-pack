<?php
	include "_nav.php";

	$name 			= null;
	$description 	= null;
	$image 			= null;
	$about 			= null;
	$website 		= null;
	$twitter 		= null;
	$user 			= null;
	$user_name 		= null;
	$user_username 	= null;
	$user_string 	= null;
	$images 		= null;
	$extra_info 	= null;

	if ( $data ) {
		if ( isset($data['idea']) ) {
			
			$image 			= $data['idea']['avatar'];
			$description 	= $data['idea']['description'];
			$name 			= $data['idea']['name'];

			$about 			= $data['idea']['about'];
			$extra_info 		= $data['idea']['extra_info'];
			
			$images 		= $data['idea']['images'];

			$website 		= $data['idea']['website'];
			$twitter 		= $data['idea']['twitter'];

			$user 			= $data['idea']['user'];


			if ( $user ) {
				$user_name 		= $user['fullname'];
				$user_username 	= $user['username'];
				$user_string 	= $user_name ? $user_name : $user_username;
			}
		}
	?>

	<header>
		<h1><?php echo $data['meta']['title']; ?></h1>
		<h2><?php echo $data['meta']['desc']; ?></h2>
		<img src="<?php echo $data['meta']['image']; ?>">
	</header>
	<article>
		
		<?php if( $name ) { ?>
			<h1><?php echo $name; ?></h1>
		<?php } ?>
		
		<?php if( $description ) { ?>
			<h2><?php echo $description; ?></h2>
		<?php } ?>

		<?php if( $user && $user_username ) { ?>
			<p>idea by <a href="/people/<?php echo $user_username; ?>" rel="author"><?php echo $user_string; ?></a></p>
		<?php } ?>

		<?php if( $image ) { ?>
			<img src="<?php echo $image; ?>" />
		<?php } ?>

		<?php if( $about ) { ?>
			<p><?php echo $about; ?></p>
		<?php } ?>

		<?php
		//
		// gallery
		//
		if( $images ) {
			?>
			<section rel="gallery">
			<?php
			for( $i = 0; $i < count($images); $i++) {
		?>	

			<img src="<?php echo $images[$i]['image']; ?>" />
		<?php 
			}
			?>
			</section>
			<?php
		} ?>

		<?php if( $extra_info ) { ?>
			<p><?php echo $extra_info; ?></p>
		<?php } ?>

		<?php if( $website ) { ?>
			<p>Website: <a href="<?php echo $website; ?>" ref="website"><?php echo $website; ?></a></p>
		<?php } ?>

		<?php if( $twitter ) { ?>
			<p>Twitter: <a href="http://twitter.com/<?php echo $twitter; ?>" ref="website">@<?php echo $twitter; ?></a></p>
		<?php } ?>

		<section></section>
	</article>
	<?php
	}

	include "_footer.php";
