<?php
	function forceBool( $str ) {
		if ( $str == "true" ) {
			return true;
		} else if ( $str == "false") {
			return false;
		} else {
			return $str;
		}
	}

	function forceBoolsInArray( $array ) {
		global $log;

		$log->append('forceBoolsInArray', '# New Array # ---------------');

		foreach ($array as $i => $values) {

			$log->append('forceBoolsInArray', '# ---------------');
			$log->append('forceBoolsInArray',$i);
			$log->append('forceBoolsInArray',$values);
			$log->append('forceBoolsInArray',$array[$i]);

			if ( is_string( $values ) ) {
				$array[$i] = forceBool($values);

				$log->append('forceBoolsInArray', '--[String]------------');
				$log->append('forceBoolsInArray', $array[$i]);

			} else if( is_array($values) || is_object($values)){

				$log->append('forceBoolsInArray', '--[Array/Object]------------');

				foreach ($values as $key => $value) {

					$log->append('forceBoolsInArray', '-------------');

			        if ( is_string( $value ) ) {
			        	$log->append('forceBoolsInArray', $array[$i]);
			        	$log->append('forceBoolsInArray', $values);
			        	$log->append('forceBoolsInArray', $key);
			        	$log->append('forceBoolsInArray', $values[$key]);

						$array[$i][$key] = forceBool($value);

						$log->append('forceBoolsInArray', $array[$i][$key]);
					}
			    }
			}

		}

		return $array;
	}

