<?php

	requireCollection('TypesCollection');


	function loadIdeaTypes() {
		global $__types__, $log;

		$version = 2;

		$log->append('loading_types', '-- init --');

		//
		// get a list of Idea Types
		//
		if ( empty( $__types__ ) ) {

			$log->append('loading_types', '-- start --');

			if ( !empty( $_SESSION['types']) && $_SESSION['types']['version'] == $version ) {
					
				$json = $_SESSION['types']['list'];

				$list = new TypesCollection();
				$list->setPreFilter('visible', 1);
				$list->set($json);

				$__types__ = $list;

				$log->append('loading_types', 'From Session Variable');
				$log->append('loading_types', $__types__);

			} else {

				$list = new TypesCollection();
				$list->setPreFilter('visible', 1);
				$list->get();
				$__types__ = $list;
				$_SESSION['types'] = array("version"=>$version, "list"=>$list->toArray());

				$log->append('loading_types', 'From Database');
				$log->append('loading_types', $__types__);

			}
		}

	}

	loadIdeaTypes();