define(["fold/view",
		"text!templates/components/modal/modal.html",
		"layout"], function(FoldView, template, layout){
	
	var ModalView = FoldView.extend({

		tagName 	: "div",
		className 	: "modal",
		template	: _.template(template),
		isAnimated 	: true,

		setup : function(){

			this.on("view:render", this.addEvents);

		},

		addEvents : function(){
						
			function eventTrigger(e){

				escPressed(e);

			}

			var view 				= this,
				$closeButton		= view.$el.find("[data-action='close']"),
				$modalBackground 	= view.$el.find(".modal-bg"),
				removeEvents 		= function(){

					layout.$dom.off("keyup", eventTrigger);

				},
				close 			= function(){

					view.close();

				}
				escPressed 		= function(e){

					if (e.keyCode === 27){

						close();
						removeEvents();

					}

				};				

			$closeButton.on("click", close);
			$modalBackground.on("click", close);

			layout.$dom.on("keyup", eventTrigger);

		},

		open : function(views, settings){

			var view 		= this,
				children 	= (views instanceof Array) ? views : [views],
				setTheme 	= function(themeName){

					return (themeName) ? "theme-" + themeName : "theme-default";

				};

			view.model = {

				title 			 : settings.title || null,
				thumb 			 : settings.thumb || null,
				backgroundHeader : settings.backgroundHeader || null

			}
			
			children = children.map(function(child){

				child.targetRef = "modal-wrapper";

				return child;

			});

			view.children 	= children;	
			layout.add([view]);

			layout.trigger("layout:modal:open");

			view.$el.addClass(setTheme(settings.theme));
			layout.disableScroll();

		},

		shake : function(){

			var view 			= this,
				eventName 		= "animationend oAnimationEnd animationend webkitAnimationEnd",
				shakeClass 		= "animation shake",
				$modalDialog	= view.$el.find(".modal-dialog");

			$modalDialog.one(eventName, function(){

				$modalDialog.removeClass(shakeClass);

			});

			$modalDialog.addClass(shakeClass);

		},

		close : function(){

			var view 			= this,
				transitionEvent	= "transitionend webkitTransitionEnd oTransitionEnd otransitionend MSTransitionEnd";

			view.$el.one(transitionEvent, function(e){

				view.model = null;						
				view.$el.attr("class", view.$el.attr("class").replace(new RegExp(/theme-?(\w+)?/g), ""));

			});

			layout.trigger("layout:modal:close");
			layout.remove(view.idView);
			layout.enableScroll();
			
		}

	});

	return new ModalView({ idView : "modal" });

});