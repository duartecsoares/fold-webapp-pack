define(["backbone"], function(Backbone){
	
	var Layout = Backbone.View.extend({

		model : {

			views : {},
			stats : {

				numberOfViews : 0

			}

		},

		/**
		 * [log - shows layout controller's model or specific model attribute]
		 * @param  { string }
		 * @return { object }					//todo: improve this function coz is returning more than one type
		*/
		
		log : function(type){

			var layout = this,
				model  = layout.model,
				log;

				if (model.hasOwnProperty(type)) {

					log = model[type];

				}else{

					log = layout.model;

				}

			return log;

		},

		/**
		 * [register - links a view to the layout controller]
		 * @param  { object } view
		 * @return { object } view
		*/
		
		register : function(view){

			var layout 	= this,
				model 	= layout.model,
				views 	= model.views;

			views[view.idView] = view;

			model.views = views;

			layout.model = model;

			layout.countViews();

		},

		/**
		 * [unregister - unlinks a view to the layout controller]]
		 * @param  { object }
		 * @return { object }
		*/

	
		unregister : function(view){

			var layout 	= this,
				model 	= layout.model,
				views 	= model.views;

			delete views[view.idView];			

			model.views = views;

			layout.model = model;

			layout.countViews();

		},

		countViews : function(){

			var layout  		= this,
				model 			= layout.model,
				stats 			= model.stats,
				views 			= model.views,
				counter 		= function(accumulator){

					accumulator = accumulator + 1;

					return accumulator;

				};

			var total = Object.keys(views).reduce(counter, 0);

			stats.numberOfViews = total;

			model.stats = stats;
			layout.model = model;	

			return total;

		},

		/**
		 * [update - forces a view to render]
		 * @param  { string }
		 * @return { object } view
		*/
	
		update : function(idView){

			var layout 	= this,
				model 	= layout.model,
				stats 	= model.stats,
				views 	= model.views;

			if(views.hasOwnProperty(idView)){

				var view = views[idView];

				view.render();

				return view;

			}

		},

		/**
		 * [build - renders an entire layout using main views]
		 * @return { object } controller
		*/
	
		build : function (){

			var layout 		= this,
				base 		= layout.base,
				renderer 	= [];

			if(base){

				for(var v in base){						//todo: add priority attribute to a better order of the base layout

					if (base.hasOwnProperty(v)) {

						renderer.push(base[v]);

					}

				}

				layout.add(renderer);

			}			

			return layout;

		},

		/**
		 * [destroy - destroys an entire layout]
		 * @return { object } controller
		*/
		
		destroy : function(){

			var layout 	= this,
				model 	= layout.model,
				views 	= model.views;

			for(var v in views){

				if (views.hasOwnProperty(v)) {

					var view = views[v];

					layout.remove(view.idView);

				}

			}

			return layout;

		},

		swapChildren : function(idView, children){

			var layout = this,
				model  = layout.model,
				views  = model.views;

			if(views.hasOwnProperty(idView)){

				var view 			 = views[idView];		

				if (children) {

					view.children = children;

					children.map(function(childView){

						var $targetRef = (childView.targetRef) ? view.$el.find("[data-target-ref='" + childView.targetRef + "']") : view.$el;

						layout.add([childView], $targetRef, true);

					});			

				}				

				return view;

			}

		},

		/**
		 * [refresh - forces an entire layout to render]
		 * @return { object } controller
		*/
	
		refresh : function(){

			var layout 	= this,
				model 	= layout.model;
				views 	= model.views;

			for(var v in views){

				if (views.hasOwnProperty(v)) {

					var view = views[v];

					view.render();

				};

			}

			return layout;

		},

		/**
		 * [isRendered - checks if a view is already rendered or not]
		 * @return { Boolean } [true if already rendered]
		 */
		
		isRendered : function(idView){

			var layout 		= this,
				model 		= layout.model,
				views 		= model.views,
				isRendered	= Object.keys(views).map(function(key){

				return views[key];

			}).filter(function(view){

				return (view.idView === view);

			});
			
			return (isRendered[0]) ? true : false;
		},

		/**
		 * [add - appends a set of views and its children views to the layout]
		 * @param { array } views
		 * @param { object } view
		 * @return { object } controller
		*/
	
		add : function(views, targetView, force, action){

			var layout 		= this,
				action 		= action || "append",
				renderer 	= function(view, $target){

						$target[action](view.render().el);

						layout.register(view);

						if(view.children){

							view.children.map(function(child){

								var $targetRef = (child.targetRef) ? view.$el.find("[data-target-ref='" + child.targetRef + "']") : view.$el;

								renderer(child, $targetRef);

								return child;

							});

						}

						layout.trigger("layout:change", view);

					},
				notRenderedViews = views.filter(function(view){
				
				return (!layout.isRendered(view.idView));

			}).map(function(view){

				var $targetElement = (targetView) ? targetView.$el || targetView : layout.$el;

					if (!view.active || force) renderer(view, $targetElement);

				return view;

			});

			return views;
			
		},

		/**
		 * [remove - destroys a view from the layout]
		 * @param  { string }
		 * @return { object } controller
		*/

		remove : function(idView){

			var layout 		= this,
				model 		= layout.model,
				views 		= model.views,
				destroyer 	= function(currentView){	

					var children = currentView.children;

					if(children) {

						children.map(function(childView){
							
							childView.listenToOnce(currentView, "view:destroy", function(){

								destroyer(childView);

							});
					
						});
					}

					currentView.destroy();

					layout.unregister(currentView);
					layout.trigger("layout:change");

					return currentView;

				};

			Object.keys(views).map(function(key){

				return views[key];

			}).filter(function(view){

				return (view.idView === idView);

			}).map(function(view){

				destroyer(view);

				return view;

			});
	
			return layout;
			
		},

		scrollTop : function(){

		    $('html, body').animate({ scrollTop: 0 }, 300);

		},

		enableScroll : function(){

			this.$body.removeClass("unscrollable");

		},

		disableScroll : function(){
	
			this.$body.addClass("unscrollable");

		},

		calcViewport : function(){

			var layout 			= this,
				winHeight 		= layout.$win.height(),
				winWidth 		= layout.$win.width(),
				domHeight		= layout.$dom.height(),
			 	viewport 		= {

		        top : layout.$win.scrollTop(),
		        left : layout.$win.scrollLeft()

		    };

	    	viewport.right 	= viewport.left + winWidth;
	    	viewport.bottom = viewport.top + winHeight;

			layout.trigger("layout:scroll", viewport);

			if ((domHeight - winHeight) - viewport.top < 400){

				layout.trigger("layout:scroll:atbottom", true);

			}

		}

	});

	return Layout;

});