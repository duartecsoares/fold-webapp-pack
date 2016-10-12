define(["fold/controller",
		"router",
		"layout",
		"settings/app",
		"controllers/pages/home",
		"controllers/pages/features"], function(FoldController, appRouter, layout, appConfig, homeController, featuresController){
	
	var AppController = FoldController.extend({

		boot : function(){

			layout.$dom.find("[data-view='app-loader']").remove();
			Backbone.router = appRouter;
			layout.build();
			this.assignRoutes();
			Backbone.history.loadUrl();

		},

		changeController : function(removeableModule){

			var controller 	= this;

			if(removeableModule){

				layout.scrollTop();

				controller.listenToOnce(removeableModule, "controller:disable", function(){					

					var view = removeableModule.view;

					if(view) layout.remove(view.idView);

					removeableModule.stopListening();
					layout.stopListening(removeableModule);

				});

				removeableModule.disable();				

			}

		},

		assignRoutes : function(){

			var appController 	= this,
				controllersMap  = {
					
					"*path"	 		: homeController,
					"features" 		: featuresController,
					""   	 		: homeController

				},
				loadModule = function(module, id, route){
									
					var lastModule = appController.management.moduleEnable || null;

					appController.changeController(lastModule);

					layout.listenTo(module, "controller:load:view", function(moduleView){

						if (lastModule){

							if (lastModule.view) layout.remove(lastModule.view.idView);
							
						}

						module.view = moduleView;
						layout.add([moduleView], layout.base.container);					

					});

					appController.management.moduleEnable = module.enable(id);					

				};

			Object.keys(controllersMap).map(function(key){

				appRouter.route(key, function(param){
					
					loadModule(controllersMap[key], param, key);

				});

			});

			setTimeout(function(){

				layout.calcViewport();

			});

		},

		management : {

			loadTime : null,
			hasBoot : false,
			hasSession : false,
			moduleEnable : null

		}

	});

	return new AppController();

});