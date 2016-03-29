define(["layout"], function(layout){
	
	var FoldView = Backbone.View.extend({

		initialize : function(options){

			var view 				= this,
				automationType 		= options.dataAutomation || "directive",
				dataAutomationTypes	= {

					directive : view.directiveDataBinding,
					render 	  : view.render

				},
				actions 			= {

					idView 	: function(idView){

						if(!idView) throw new Error("View created without an IdView.");

						this.$el.attr("data-view", idView);
						this.idView = idView;
						this.$el.addClass(idView);

					},
					Model 	: function(Model){

						var view = this;

						this.model = (typeof Model === "function") ? new Model() : Model;
						this.model.on("change", function(model){

							dataAutomationTypes[automationType].call(view, model.toJSON(), true);

						});

					},
					children : function(children){

						this.children = children || false;

					},

					targetRef : function(targetRef){

						if (targetRef) this.targetRef = targetRef;

					},

					isAnimated : function(isAnimated){

						this.isAnimated = isAnimated || false;

					},
					viewType : function(viewType){

						if (viewType) this.$el.addClass(viewType);

					},
					loadSpinner : function(loadSpinner){

						this.loadSpinner = loadSpinner || false;

					}

				};

			Object.keys(options).map(function(key){

				if(actions[key]) return actions[key].call(view, options[key]);				

			});

			this.status = "activate";

			this.on("view:render", function(){

				view.buttonsBinding();
				view.linksOverride();
				view.removeNoTouch();

			});			

			if (typeof view.preparation === "function") view.preparation(options);

		},

		directiveDataBinding : function(){

			var view 	 		= this,
				model 			= this.model,
				$viewElement 	= this.$el,
				attributes 		= model.attributes,
				bindedElements	= $viewElement.find("[data-binding]");

			bindedElements.map(function(index){

				var diggIntoObject  = function(model, attributeMap){

						var value 			= null,
							levels 			= attributeMap.split("."),
							numberOfLevels 	= levels.length - 1,
							iterator 		= 0;

						while(iterator <= numberOfLevels){

							value = (value) ? value[levels[iterator]] : model.attributes[levels[iterator]];
							iterator += 1;

						}

						return (value) ? value : "";

					},
					element 		= bindedElements[index],
					bindingAttr 	= element.getAttribute("data-binding"),
					attributeValue 	= model.get(bindingAttr);

				if(!(model.get(bindingAttr) instanceof Array)){

					element.innerHTML = diggIntoObject(model, bindingAttr);

				}else{

					while (element.firstChild) {

					    element.removeChild(element.firstChild);

					}

					attributeValue.map(function(item){

						var spanElement = document.createElement("span");

						spanElement.innerHTML = item;
						element.appendChild(spanElement);

					});

				}

			});

		},

		setStatus : function(status){

			this.status = status;

			if (typeof this[status] === "function") this[status]();

		},

		activate : function(){

			this.$el.removeClass("view-deactivate");
			this.trigger("view:status:change", "activate");

		},

		deactivate : function(){

			this.$el.addClass("view-deactivate");
			this.trigger("view:status:change", "deactivate");

		},

		linksOverride : function(){

			var view 			= this,
				executeRoute	= function(e){

					if (e.shiftKey || e.ctrlKey || layout.cmdKeyPressed) {

						return true;

					}else{

						e.preventDefault();
						
						var $target = $(e.currentTarget),
							route 	= $target.attr("href");

						Backbone.router.navigate(route, true);

					}
				
				},
				executeRouteSilent	= function(e){
					
					if (e.shiftKey || e.ctrlKey || layout.cmdKeyPressed) {

						return true;

					}else{

						e.preventDefault();
						
						var $target = $(e.currentTarget),
							route 	= $target.attr("href");

						Backbone.router.navigate(route, false);

					}

				};

			view.$el.find("a[rel='internal-silent']").on("click", executeRouteSilent);
			view.$el.find("a[rel='internal']").on("click", executeRoute);
			view.$el.find("[data-click='log-in']").on("click", function(e){

				e.preventDefault();
				layout.trigger("login-open");

			});

		},

		buttonsBinding : function(){

			var view 		= this,
				$buttons 	= view.$el.find("[data-button]"),
				sync 		= function(data, extra){

					var status 	= data.split("->")[0] || null,
						message = data.split("->")[1] || null,
						text 	= message || $buttons.attr("data-button-" + status);

					$buttons.attr("data-status", status);
					
					if($buttons.is("input")){

						$buttons.val(text);

					}else{

						$buttons.html(text);
						
					}

					if(status === "disable"){

						$buttons.attr("disabled", true);

					}else{

						$buttons.removeAttr("disabled");

					}

					if (view.loadSpinner) {

						if (status === "loading") {

							var $loaderEl = "<div class='loader-spinner'><p class='paragraph'><xml version='1.0' encoding='utf-8' ?=''><svg width='38px' height='38px' xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100' preserveAspectRatio='xMidYMid' class='uil-ring-alt'><rect x='0' y='0' width='100' height='100' fill='none' class='bk'></rect><circle cx='50' cy='50' r='40' stroke='transparent' fill='none' stroke-width='10' stroke-linecap='round'></circle><circle cx='50' cy='50' r='40' stroke='#fff' fill='none' stroke-width='6' stroke-linecap='round'><animate attributeName='stroke-dashoffset' dur='1.4s' repeatCount='indefinite' from='0' to='502'></animate><animate attributeName='stroke-dasharray' dur='1.4s' repeatCount='indefinite' values='150.6 100.4;1 250;150.6 100.4'></animate></circle></svg></xml></p></div>";
							
							$buttonSub.append($loaderEl);
							$buttonSub.addClass("loader-padding");

						}else{

							$buttonSub.find(".loader").remove();
							$buttonSub.removeClass("loader-padding");

						}

					}

					if (extra === "labeltronic-error") {

						view.trigger("form:labeltronic:error");

					}

				};

			var $buttonSub = view.$el.find("button[type='submit']");

			view.listenTo(view, "view:buttons:sync", sync);

		},

		inViewport : function(viewport){
 
		    var bounds = this.$el.offset();
		    
		    bounds.right = bounds.left + this.$el.outerWidth();
		    bounds.bottom = bounds.top + this.$el.outerHeight();

		    return (!(viewport.right < bounds.left || viewport.left > bounds.right || viewport.bottom < bounds.top || viewport.top > bounds.bottom));

		},

		requestAnimation : function(isAnimated){

			var view 				= this,
				transitionEvent		= "transitionend webkitTransitionEnd oTransitionEnd otransitionend MSTransitionEnd",			
				classesList 		= {

					animation 		: "animation",
					transition 		: "transition",
					inViewport		: "in-viewport",
					afterRender 	: "after-render",					
					beforeDestroy 	: "before-destroy"

				},
				toggleInViewportClass = function(viewportInfo){

					if (view.inViewport(viewportInfo)) {

						view.$el.addClass(classesList.inViewport);

					}else{

						view.$el.removeClass(classesList.inViewport);

					}

				},
				addAfterRenderClass = function(){

					setTimeout(function(){

						view.$el.addClass(classesList.transition +  " " + classesList.afterRender);

					});

				},
				removeAfterRenderClass = function(){					

					view.$el.removeClass(classesList.transition +  " " + classesList.afterRender);

				},
				destroyView = function(){

					this.trigger("view:destroy");

		            this.stopListening();			
            		this.undelegateEvents();
            		this.$el.removeData().unbind();
            		this.remove();
            		Backbone.View.prototype.remove.call(this);
            		this.active = false;            		

		            return this;

		        };

			if (isAnimated) {

				view.listenToOnce(view, "view:render", function(){

					addAfterRenderClass();
					view.listenTo(layout, "layout:scroll", toggleInViewportClass);
					view.listenToOnce(view, "view:destroy", removeAfterRenderClass);

				});

				view.destroy = function(){

					var $target = view.$animated || view.$el;

					$target.one(transitionEvent, function(){												

						destroyView.call(view);
						
						view.$el.removeClass(classesList.beforeDestroy);

					});

					view.$el.addClass(classesList.beforeDestroy);

				}

			}

		},

		animateEnd : function(callback, $targetEl){

			var view 				= this,
				transitionEvent		= "transitionend webkitTransitionEnd oTransitionEnd otransitionend MSTransitionEnd",
				$target 			= view.$animator || $targetEl || view.$el;			

			$target.removeClass("after-render");
			$target.removeClass("before-destroy");

			$target.one(transitionEvent, function(e){

				if (callback) callback();

			});

			$target.addClass("before-destroy");

		},

		animateOut : function(callback, className){

			var view 			= this,
				transitionEvent	= "transitionend webkitTransitionEnd oTransitionEnd otransitionend MSTransitionEnd",
				animateClass 	= className || "animate-out";

			view.$el.one(transitionEvent, function(e){

				view.$el.removeClass(animateClass)

				if (callback) callback();

			});

			view.$el.addClass(animateClass);

		},

		render : function(data, removeEvents){
			
			var view 		= this,
				model 		= view.model || {},
				toObject 	= (typeof model.toJSON === "function") ? model.toJSON() : model;

			if (removeEvents){

				this.stopListening();
				this.undelegateEvents();
			}

			this.requestAnimation(this.isAnimated);			
				
			if(view.template){

				var toTemplate = {

					model : data || toObject

				};

				view.$el.html(view.template(toTemplate));
				
			}

			view.trigger("view:render");
			view.active = true;

			return view;

		},

		destroy : function(){			
			
			this.stopListening();			
            this.undelegateEvents();
            this.$el.removeData().unbind();
            this.remove();
            Backbone.View.prototype.remove.call(this);
            this.active = false;

            this.trigger("view:destroy");

            return this;

        },

        removeNoTouch : function () {

		    if ('ontouchstart' in document) {
			
		    	this.$el.find(".no-touch").removeClass("no-touch");

			}

        }

        // to do make generic minifyCount and dayDiff

	});

	return FoldView;

});