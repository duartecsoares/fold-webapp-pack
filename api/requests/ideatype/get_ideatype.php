<?php

	$log->addFile( __FILE__ );

	// requireCollection('TypesCollection');
	// $list = new TypesCollection();
	// $list->setPreFilter('visible', 1);
	// $json['idea_type'] = $list->get();

	requireFunction('loadIdeaTypes');
	$json['idea_type'] = $__types__->toArray();