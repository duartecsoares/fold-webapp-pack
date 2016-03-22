<?php
	//
	// http://stackoverflow.com/questions/2310558/how-to-delete-all-cookies-of-my-website-in-php
	// base: http://stackoverflow.com/questions/5438760/generate-random-5-characters-string
	//
	function deleteCookie() {
        global $log;

        if (isset($_SERVER['HTTP_COOKIE'])) {
            // $cookies = explode(';', $_SERVER['HTTP_COOKIE']);

            // $log->append('COOKIE_VALUE_HASH', $_COOKIE['hash']);

            // foreach($cookies as $cookie) {
            //     $parts = explode('=', $cookie);
            //     $name = trim($parts[0]);
            //     setcookie($name, '', time()-1000);
            //     setcookie($name, '', time()-1000, '/');
            // }

            $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
            foreach($cookies as $cookie) {
                $parts = explode('=', $cookie);
                $name = trim($parts[0]);
                setcookie($name, '', time()-999999);
                setcookie($name, '', time()-999999, '/');
                setcookie($name, '', time()-999999, '/', $_SERVER['SERVER_NAME']);
            }

            setcookie('user', '',  time() - 999999, '/', $_SERVER['SERVER_NAME']);
            setcookie('hash', '',  time() - 999999, '/', $_SERVER['SERVER_NAME']);
            setcookie('version', '',  time() - 999999, '/', $_SERVER['SERVER_NAME']);

            $_COOKIE['user'] = null;
            $_COOKIE['hash'] = null;
            $_COOKIE['version'] = null;

            unset($_COOKIE['user']);
            unset($_COOKIE['hash']);
            unset($_COOKIE['version']);

            // $log->append('COOKIE_VALUE_HASH', $_COOKIE['hash']);

        }

	}
