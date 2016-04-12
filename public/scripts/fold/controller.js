define(["backbone"], function(Backbone){
	
	var FoldController = Backbone.View.extend({

		initialize : function(opt){

			opt = opt || {};

			if (typeof this.setup === "function") this.setup();
			if (this.type === "page-controller") this.addViewControllerEvents(opt.viewObj);

		},

		set: function(data, model, callback){

			/*
			
				[{
					value : "www.builditwith.me",
					key : "link"

				}]

			*/

			data.map(function(itemData){

				model.set(itemData.key, itemData.value);

			});

			if (typeof callback === "function") callback();

		},

		addViewControllerEvents : function(viewObj){

			var controller    = this,
				childrenViews = viewObj.children || [];

			controller.on("controller:enable", function(){

				var view = new viewObj.constructor(viewObj),
					instancedChildrenViews = childrenViews.map(function(childrenViewOptions){

					return new childrenViewOptions.constructor({ idView : childrenViewOptions.idView, Model: childrenViewOptions.Model });

				});

				view.children = instancedChildrenViews;

				window.dev = {

					view: view

				}

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