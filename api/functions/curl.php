<?php

	function curl( $url, $details = false ) {
		global $log;

	    $ch = curl_init();

	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);


	    $data = curl_exec($ch);

	    if ($details == true ) {
	    	$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	    }

	    curl_close($ch);

	    if ($details == true) {
	    	$data = array('status'=>$status, 'response'=>$data, 'url'=>$url);
	    }

	    $log->append('curl', $data);

	    return $data;
	}

	//
	// http://davidwalsh.name/curl-post
	//
	function curl_post( $url, $details = false, $fields = [] ) {
		global $log;

	    $ch = curl_init();

	    $log->append('curl_post', '------- init ------');

	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	    curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);

	    $log->append('curl_post', $url);
	    $log->append('curl_post', $fields);

		//url-ify the data for the POST
		foreach($fields as $key=>$value) { $fields_string .= $key.'='.urlencode($value).'&'; }
		rtrim($fields_string, '&');

	    $log->append('curl_post', $fields_string);

		curl_setopt($ch, CURLOPT_POST, count($fields));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

	    $data = curl_exec($ch);

	    if ($details == true ) {
	    	$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	    }

	    curl_close($ch);

	    if ($details == true) {
	    	$data = array('status'=>$status, 'response'=>$data, 'url'=>$url);
	    }

	    $log->append('curl_post', $data);

	    if ( isset($data['response']) ) {
	    	$log->append('curl_post', json_decode($data['response']));
	    }

	    return $data;
	}
