define(["fold/controller",
		"views/simple/simple"], function(FoldController, SimpleView){
	
	var SimpleController = FoldController.extend({

		initialize : function(){

			this.addEvents();

		},

		addEvents : function(){

			var controller = this;

			controller.on("controller:enable", function(param){

				var module 		= Backbone.history.fragment.split("/")[0],
					simpleView 	= new SimpleView({ idView: "simple-view-" + module, template: module, param: param});

				controller.trigger("app:load:view", simpleView);

				controller.view = simpleView;

			});

		}

	});

	return new SimpleController();

});