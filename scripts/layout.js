define(["fold/layout",
		"views/layout/header",
		"views/layout/container",
		"views/layout/footer"], function(FoldLayout, HeaderView, ContainerView, FooterView){
	
	var Layout = FoldLayout.extend({

		initialize : function(options){

			var layout 	= this,				
				$loader;
			
			layout.$win = $(window);
			layout.$dom = $(document);
			layout.$body = $("body");
			layout.$el = $("#app");

			$loader = layout.$el.find("[data-view='loader']");
			$loader.remove();

			layout.addEvents();
			layout.base = { header: new HeaderView({ idView: "layout-header-view" }), container: new ContainerView({ idView: "layout-container-view" }), footer: new FooterView({ idView: "layout-footer-view" }) }

			layout.cmdKeyPressed = false;

		},

		addEvents : function(){

			var layout  = this;
				
			layout.$win.on("scroll", function(){

				layout.calcViewport();
				layout.trigger("layout:window:scroll", layout.$win);

			});

			layout.$el.on("click", function(e){

				layout.trigger("layout:window:click", e);

			});

			layout.$win.on("resize", function(){

				layout.trigger("layout:window:resize", layout.$win);

			});

			layout.$win.on("keydown", function(e){

				layout.cmdKeyPressed = (e.keyCode === 91) ? true : false;
				layout.trigger("layout:window:keydown", e);

			});

			layout.$win.on("keyup", function(e){
				
				if(e.keyCode === 91) layout.cmdKeyPressed = false;

			});

			if ( "ontouchstart" in window || navigator.msMaxTouchPoints ) {
				
				layout.$body.addClass('touch-device');
			
			} else {
				
				layout.$body.addClass('not-touch-device');
			
			}

		},

		tourMode : function(action){

			var layout 			= this,
				containerView	= layout.model.views["app-container"],
				transitionEvent	= "transitionend webkitTransitionEnd oTransitionEnd otransitionend MSTransitionEnd";

			if (action === "on") {

				containerView.$el.addClass("tour-mode");

			}else{	
				
				containerView.$el.removeClass("tour-mode");

			}

		}

	});

	return new Layout();

});