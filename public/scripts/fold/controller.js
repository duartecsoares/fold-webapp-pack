define(["backbone"], function(Backbone){
	
	var FoldController = Backbone.View.extend({

		initialize : function(opt){

			opt = opt || {};

			if (typeof this.setup === "function") this.setup();
			if (this.type === "page-controller") this.addViewControllerEvents(opt.viewObj);

		},

		addViewControllerEvents : function(viewObj){

			var controller  = this;

			controller.on("controller:enable", function(){

				var view = new viewObj.instance({ idView : viewObj.idView });

				controller.trigger("app:load:view", view);
				controller.view = view;

			});

		},

		enable : function(id){

			var controller = this;

			controller.trigger("controller:enable", id);
			controller.enabled = true;

			return controller;

		},

		disable : function(){

			var controller = this;

			controller.trigger("controller:disable");
			controller.enabled = false;

			return controller;

		},

		restart : function(id){

			this.disable();
			this.enable(id);

		}

	});

	return FoldController;

});