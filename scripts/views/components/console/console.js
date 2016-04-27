define(["fold/view",
		"text!templates/components/console/console.html"], function(FoldView, template){
	
	var ConsoleView = FoldView.extend({

		idView : "console-view",
		className : "console transition",

		template : _.template(template),

		initialize : function(){}

	});

	return new ConsoleView();

});