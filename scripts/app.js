"use strict";

requirejs.config({

    baseUrl : "scripts/",

	paths : {

        "jquery" : "../vendor/jquery/dist/jquery",
        "backbone" : "../vendor/backbone/backbone",
        "underscore" : "../vendor/underscore/underscore",
        "text" : "../vendor/requirejs-text/text",
        "json" : "../vendor/requirejs-plugins/src/json"

    },

    shim: {

        "backbone": {
            deps: ["underscore", "jquery", "text", "json"],
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
        }
    },

    urlArgs: "bust=" + (new Date()).getTime()

});

require(["backbone", "controllers/app"], function(Backbone, appController){

    Backbone.history.start({ pushState: true});
    appController.boot();

});
