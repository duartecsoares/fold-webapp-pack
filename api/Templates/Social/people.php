<?php
	include "_nav.php";

	$username 		= null;
	$name 			= null;
	$bio 			= null;
	$image 			= null;
	$traits 		= null;
	$skills 		= null;
	$extra_info 	= null;
	$website 		= null;
	$twitter 		= null;
	$user_string 	= null;

	$ideas 			= null;

	if ( $data ) {
		if ( isset($data['user']) ) {
				
			$username 		= $data['user']['username'];
			$name 			= $data['user']['fullname'];
			$bio 			= $data['user']['bio'];
			$image 			= $data['user']['avatar'];

			$traits 		= $data['user']['traits'];

			$skills 		= $data['user']['skills'];
			$extra_info 	= $data['user']['extra_info'];
			
			$website 		= $data['user']['website'];
			$twitter 		= $data['user']['twitter'];

			$user_string 	= $name ? $name : $username;

			$ideas 			= $data['user']['ideas'];

		}
	?>
	
	<header>
		<h1><?php echo $data['meta']['title']; ?></h1>
		<h2><?php echo $data['meta']['desc']; ?></h2>
		<img src="<?php echo $data['meta']['image']; ?>">
	</header>
	<article>
		
		<?php if( $user_string ) { ?>
			<h1><?php echo $user_string; ?></h1>
		<?php } ?>
		
		<?php if( $bio ) { ?>
			<h2><?php echo $bio; ?></h2>
		<?php } ?>

		<?php if( $image ) { ?>
			<img src="<?php echo $image; ?>" />
		<?php } ?>


		<?php
		//
		// traits
		//
		if( $traits ) {
			?>
			<section>
			<h3>Traits</h3>
			<ul>
			<?php
			for( $i = 0; $i < count($traits); $i++) {
		?>
			<li><span><?php echo $traits[$i]['name']; ?></span></li>
		<?php 
			}
			?>
			</ul>
			</section>
			<?php
		}
		?>

		<?php if( $skills ) { ?>
			<section>
				<h3>Skills</h3>
				<p><?php echo $skills; ?></p>
			</section>
		<?php } ?>


		<?php
		//
		// traits
		//
		if( $ideas ) {
			?>
			<section>
			<h3><?php echo count($ideas);?> Ideas</h3>
			<ul>
			<?php
			for( $i = 0; $i < count($ideas); $i++) {
		?>
			<li>
				<article>
					<a rel="ideas" href="ideas/<?php echo $ideas[$i]['id']; ?>">
						<?php if( $ideas[$i]['avatar'] ) { ?><img src="<?php echo $ideas[$i]['avatar']; ?>" /><?php } ?>
						<h4><?php echo $ideas[$i]['name']; ?></h4>
						<p><?php echo $ideas[$i]['description']; ?></p>
					</a>
				</article>
			</li>
		<?php 
			}
			?>
			</ul>
			</section>
			<?php
		}
		?>

		<?php if( $extra_info ) { ?>
			<section>
				<h3>Extra Info</h3>
				<p><?php echo $extra_info; ?></p>
			</section>
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
