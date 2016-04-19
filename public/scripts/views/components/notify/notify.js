define(["fold/view",
		"text!templates/components/notify/notify.html"], function(FoldView, template){
	
	var NotifyView = FoldView.extend({
		className: "notify",
		template: _.template(template),

		setup : function(){

			this.on("view:render", this.addEvents);

		},

		addEvents : function(){

			var view 		= this,
				$closeButton = view.$el.find("[data-action='close']");

			$closeButton.on("click", function(e){

				e.preventDefault();
				e.stopPropagation();

				view.trigger("notify:suppress", view.idView);

			});

		}

	});

	return NotifyView;

});