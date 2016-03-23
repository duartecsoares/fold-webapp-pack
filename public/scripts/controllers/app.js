define(["fold/controller",
		"router",
		"layout",
		"settings/app",
		"controllers/pages/home",
		"dev/controllers/data-binding"], function(FoldController, appRouter, layout, appConfig, homeController, dataBindingController){
	
	var AppController = FoldController.extend({

		boot : function(){

			Backbone.router = appRouter;
			layout.build();
			this.startRouting();
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

		startRouting : function(){

			var appController 	= this,
				controllersMap  = {
					
					"home"			: homeController,
					"data-binding"	: dataBindingController,
					"*path"	 		: dataBindingController,
					""   	 		: dataBindingController					

				},
				loadModule = function(module, id, route){
									
					var lastModule = appController.management.moduleEnable || null;

					appController.changeController(lastModule);

					layout.listenTo(module, "app:load:view", function(moduleView){

						if (lastModule){

							if (lastModule.view) layout.remove(lastModule.view.idView);
							
						}

						module.view = moduleView;
						layout.add([moduleView], layout.base.container);					

					});
/*
					layout.listenTo(module, "app:swap:view", function(moduleView){									

							if(module.view) layout.remove(module.view.idView);

							module.view = moduleView;
							layout.add([moduleView], layout.base.container);

					});
*/
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