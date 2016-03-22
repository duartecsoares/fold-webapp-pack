<?php

	function loadTraits() {
		global $pdo, $__traits__, $log;

		$version = 3;

		$log->append('loading_traits', '-- init --');

		//
		// get a list of traits
		//
		if ( empty( $__traits__ ) ) {

			$log->append('loading_traits', '-- start --');

			if ( !empty( $_SESSION['traits']) && $_SESSION['traits']['version'] == $version ) {
				
				$__traits__ = $_SESSION['traits']['list'];

				$log->append('loading_traits', 'From Session Variable');
				$log->append('loading_traits', $__traits__);

			} else {

				$_traits = array();

				$query_traits = "SELECT id, name, parent, string_id FROM traits";
				$result_traits = $pdo->query($query_traits);
				// $log->addQuery( $query_traits );

				while ($trait = $result_traits->fetch(PDO::FETCH_OBJ)) {
				    $_traits[] = $trait;
				}

				$__traits__ = $_traits;
				$_SESSION['traits'] = array("version"=>$version, "list"=>$_traits);

				$log->append('loading_traits', 'From Database');
				$log->append('loading_traits', $__traits__);

			}

		}

	}

	loadTraits();