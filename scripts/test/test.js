"use strict";

requirejs.config({

    baseUrl : "../scripts/",

	paths : {

        "jquery" : "../vendor/jquery/dist/jquery",
        "backbone" : "../vendor/backbone/backbone",
        "underscore" : "../vendor/underscore/underscore",
        "colorThief" : "../vendor/color-thief-umd/src/color-thief",
        "text" : "../vendor/requirejs-text/text",
        "json" : "../vendor/requirejs-plugins/src/json",
        "mocha" : "../vendor/mocha/mocha",
        "chai" : "../vendor/chai/chai"

    },

    shim: {

        "backbone": {
            deps: ["mocha", "underscore", "jquery", "text", "json", "colorThief"],
            exports: "Backbone"
        },
        "jquery" : {
            exports : "$"
        },
        "underscore": {
            exports: "_"
        },
        "json": {
            deps: ["text"]
        },
        "colorThief": {
            exports: "colorThief"
        },
        "mocha": {
        	"deps": ["chai"],
			"exports": "mocha",     	
	      	init: function () {
	        	this.mocha.setup("bdd");
	        	return this.mocha;
	      }
	    },
        "chai": {
        	exports:"chai"
        }
      },

    urlArgs: "bust=" + (new Date()).getTime()

});

require(["backbone", "mocha"], function(Backbone){

	(function(){

		require([
				"test/layout.test",
				"test/request.test"], function(layoutTest, requestTest){

			mocha.run();

		});

	})();

});
