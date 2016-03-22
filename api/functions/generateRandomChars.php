<?php
	//
	//
	// base: http://stackoverflow.com/questions/5438760/generate-random-5-characters-string
	//
	function generateRandomChars( $length = 28, $specials = true ) {

		if ( $specials == true ) {
			$seed = str_split('abcdefghijklmnopqrstuvwxyz'
					                 .'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
					                 .'0123456789!@#$%^&*()'); // and any other characters
		} else {
			$seed = str_split('abcdefghijklmnopqrstuvwxyz'
					                 .'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
					                 .'0123456789'); // and any other characters
		}
		
		shuffle($seed); // probably optional since array_is randomized; this may be redundant
		$rand = '';
		foreach (array_rand($seed, $length) as $k) $rand .= $seed[$k];

		return $rand;

	}
