<?php
	$log->addFile( __FILE__ );

	requireCollection('TraitCollection');
	requireFunction('traits');
	$json['traits'] = $__traits__;
	$json['looking_for'] = $__traits__;

	requireFunction('loadIdeaTypes');
	$json['idea_type'] = $__types__->toArray();

	$json['has_image'] = true;
	$json['work_status'] = array("available", "unavailable");
	$json['lists'] = array("popular", "new", "favorite");