define(["fold/controller",
		"layout",
		"views/components/console/console"], function(FoldController, layout, consoleView){
	
	var Console = FoldController.extend({

		initialize : function(){
				
			//this.addEvents();

		},

		toggle : function(){

			var setState 	  = function(currentState){

					return (currentState) ? false : true;

				},
				state = setState(this.active);

			if (state) {

				this.enable();

			}else{

				this.disable();

			}

			this.active = state;

		},

		active : false,

		addEvents : function(){

			var controller 	  = this,
				toggleKeyCode = 67;						// c

			layout.$win.on("keydown", function(e){

				if(e.shiftKey && (e.keyCode === toggleKeyCode)){

					controller.toggle();

				}

			});

			layout.on("layout:change", function(){
				 
				if (controller.active) {

					consoleView.render({ activeViews : layout.log().stats.numberOfViews, views : layout.log().views });

				};

			});

		},

		enable : function(){

			layout.$el.append(consoleView.render({ activeViews : layout.log().stats.numberOfViews, views : layout.log().views }).el);

		},

		disable : function(){

			consoleView.destroy();

		}

	});

	return new Console();

});