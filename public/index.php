<!DOCTYPE html>
<html lang="en">
   <head>
      <?php
        include "../api/config.php";
        $data = array();
        $images = array(
          array("id"=>0, "title"=>"", "data"=>"", "url"=>""),
          array("id"=>1, "title"=>"", "data"=>"", "url"=>"")
        );
        $data['meta'] = array(
            "title" => "Fold WebApp Pack",
            "desc"  => "",
            "image" => $images[0]['url']
          );
      ?>
      <meta charset="utf-8">
      <title><?php echo $data["meta"]["title"]; ?></title>
      <meta name="description" content="<?php echo $data["meta"]["desc"]; ?>">
      <meta name="author" content="Carlos Gavina @carlosgavina, Duarte Corvelo @duartecsoares, Joao Santiago @joaodsantiago">

      <meta property="og:title" content="<?php echo $data["meta"]["title"]; ?>" />
      <meta property="og:type" content="website" />
      <meta property="og:image" content="<?php echo $data["meta"]["image"]; ?>" />
      <meta property="og:description" content="<?php echo $data["meta"]["desc"]; ?>" />

      <meta name="twitter:card" content="summary_large_image">
      <meta name="twitter:site" content="@madebyfold">
      <meta name="twitter:creator" content="@madebyfold">
      <meta name="twitter:title" content="<?php echo $data["meta"]["title"]; ?>">
      <meta name="twitter:description" content="<?php echo $data["meta"]["desc"]; ?>">
      <meta name="twitter:image" content="<?php echo $data["meta"]["image"]; ?>">

      <meta name="viewport" content="width=device-width, user-scalable=no">
      <link rel="stylesheet" href="dist/css/foldwebapppack.css">
      
      <link rel="icon" type="image/png" sizes="16x16" href="#">
      <link rel="icon" type="image/png" sizes="32x32" href="#">
      <link rel="icon" type="image/png" sizes="96x96" href="#">

      <!--[if lt IE 10]> <style> #app { display: none } </style>  <![endif]-->
      <noscript><style> .jsonly { display: none } </style></noscript>
      <script src="//use.typekit.net/mrc0srj.js"></script>
      <script>try{Typekit.load();}catch(e){}</script>
    
    </head>
    <body>
        
        <noscript>
          <div class="unsupported">
            <a href="http://www.madebyfold.com"> 
              <span class="unsupported-name">Fold's WebApp Pack</span>
            </a>
            <p class="unsupported-paragraph">
              We’re sorry but our site requires JavaScript.<br />
              Please enable JavaScript to continue.
            </p>
            <div class="unsupported-copyright" > Fold's WebApp Pack © Copyright 2016 </div>
          </div>
        </noscript>

        <!--[if lt IE 11]>
        
          <div class="unsupported jsonly">
            <a href="http://www.madebyfold.com/">
              <img class="unsupported-logo" src="/resources/assets/header/logo.svg" alt="logo"> 
              <span class="unsupported-name"> Fold's WebApp Pack </span>
            </a>
            <div class="unsupported-paragraphs">
              <p>
                Sorry but our site requires a more recent browser. <br />
                Please update to a more recent browser.<br /><br /><br />
                Here a few good options:
              </p>
            </div>
            <p class="unsupported-browsers">
              <a class="unsupported-browser" target="_blank" href="https://www.google.com/chrome/browser">
                <img class="unsupported-browser-logo" src="https://s3.amazonaws.com/buildit-storage/webapp/interface/browser-chrome.png" alt="logo"> 
                <span class="unsupported-browser-name">Chrome</span>
              </a>
              <a class="unsupported-browser" target="_blank" href="https://www.mozilla.org/firefox">
                <img class="unsupported-browser-logo" src="https://s3.amazonaws.com/buildit-storage/webapp/interface/browser-firefox.png" alt="logo"> 
                <span class="unsupported-browser-name">Firefox</span>
              </a>
              <a class="unsupported-browser" target="_blank" href="https://www.apple.com/safari/">
                <img class="unsupported-browser-logo" src="https://s3.amazonaws.com/buildit-storage/webapp/interface/browser-safari.png" alt="logo"> 
                <span class="unsupported-browser-name">Safari</span>
              </a>
              <a class="unsupported-browser" target="_blank" href="http://windows.microsoft.com/en-us/internet-explorer/download-ie">
                <img class="unsupported-browser-logo" src="https://s3.amazonaws.com/buildit-storage/webapp/interface/browser-ie.png" alt="logo">
                <span class="unsupported-browser-name">IE (latest)</span>
              </a>
              <a class="unsupported-browser" target="_blank" href="http://www.opera.com">
                <img class="unsupported-browser-logo" src="https://s3.amazonaws.com/buildit-storage/webapp/interface/browser-opera.png" alt="logo">
                <span class="unsupported-browser-name">Opera</span>
              </a>
            </p>
            <div class="unsupported-copyright" > Fold's WebApp Pack © Copyright 2016 </div>
          </div>
        
        <![endif]-->

        <div data-view="notify" id="notify"></div>
        <div data-target-ref="progress-bar"></div><div data-view="progress-bar-view" max="100" value="0"></div></header>
        
        <div id="app" data-view="app">            
            
            <div data-view="loader" class="loader jsonly">
                <xml version="1.0" encoding="utf-8"?><svg width='38px' height='38px' xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid" class="uil-ring-alt"><rect x="0" y="0" width="100" height="100" fill="none" class="bk"></rect><circle cx="50" cy="50" r="40" stroke="#ffffff" fill="none" stroke-width="10" stroke-linecap="round"></circle><circle cx="50" cy="50" r="40" stroke="#92999a" fill="none" stroke-width="6" stroke-linecap="round"><animate attributeName="stroke-dashoffset" dur="1.4s" repeatCount="indefinite" from="0" to="502"></animate><animate attributeName="stroke-dasharray" dur="1.4s" repeatCount="indefinite" values="150.6 100.4;1 250;150.6 100.4"></animate></circle></svg>
            </div>

        </div>


  <script data-main="dist/js/foldwebapppack.js?<?php echo time(); ?>" src="vendor/requirejs/require.js"></script>

  <?php
  if ( $__serverEvironment__ == 'prod' || $__serverEvironment__ == 'beta' ) {
  ?>
  <script>
    window.ga=window.ga||function(){(ga.q=ga.q||[]).push(arguments)};ga.l=+new Date;
    //ga('create', 'UA-69302820-1', 'auto');    
    //ga('send', 'pageview');
  </script>
  <script async src='//www.google-analytics.com/analytics.js'></script>
  <?php
  }
  ?>
   </body>
</html>

