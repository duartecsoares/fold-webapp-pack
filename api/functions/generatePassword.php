<?php
	//
	//
	function generatePassword( $string = null ) {
		$pwd;
		if ( !empty($string) ) {
			$pwd = md5($string);
		}
		return $pwd;
	}
