define(["fold/view",
		"text!templates/pages/home.html"], function(FoldView, template){
	
	var HomeView = FoldView.extend({

		template: _.template(template)

	});

	return HomeView;

});