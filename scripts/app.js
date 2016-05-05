"use strict";

requirejs.config({

    baseUrl : "scripts/",

	paths : {

        "jquery" : "../vendor/jquery/dist/jquery",
        "backbone" : "../vendor/backbone/backbone",
        "underscore" : "../vendor/underscore/underscore",
        "colorThief" : "../vendor/color-thief-umd/src/color-thief",
        "text" : "../vendor/requirejs-text/text",
        "json" : "../vendor/requirejs-plugins/src/json"

    },

    shim: {

        "backbone": {
            deps: ["underscore", "jquery", "text", "json", "colorThief"],
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
        }
    },

    urlArgs: "bust=" + (new Date()).getTime()

});

require(["backbone", "controllers/app"], function(Backbone, appController){

    Backbone.history.start({ pushState: true});
    appController.boot();

});
