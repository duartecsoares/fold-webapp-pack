<!DOCTYPE html>
<html lang="en">
	<?php include "../api/social.php"; ?>

   <head>
		<meta charset="utf-8">
		<title><?php echo $data["meta"]["title"]; ?></title>
        <meta name="description" content="<?php echo $data["meta"]["desc"]; ?>">
        <meta name="author" content="Carlos Gavina @carlosgavina, Duarte Corvelo @duartecsoares, Joao Santiago @joaodsantiago">

        <meta property="og:title" content="<?php echo $data["meta"]["title"]; ?>" />
        <meta property="og:type" content="website" />
        <meta property="og:image" content="<?php echo $data["meta"]["image"]; ?>" />
        <meta property="og:description" content="<?php echo $data["meta"]["desc"]; ?>" />

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:site" content="@builditwithme">
        <meta name="twitter:creator" content="@madebyfold">
        <meta name="twitter:title" content="<?php echo $data["meta"]["title"]; ?>">
        <meta name="twitter:description" content="<?php echo $data["meta"]["desc"]; ?>">
        <meta name="twitter:image" content="<?php echo $data["meta"]["image"]; ?>">
	</head>
	<body>

		<?php
			$file = "../api/Templates/Social/default.php";
			
			if ( isset($data) && isset($data['type']) ) {
				$file = "../api/Templates/Social/".$data['type'].".php";
			}

			if ( file_exists($file)) {
				include $file;
			}
		?>

	</body>
</html>
