define(["fold/view",
		"text!templates/layout/header.html"], function(FoldView, template){
	
	var AppHeaderView = FoldView.extend({

		className: "layout-header-view",
		template: _.template(template)

	});

	return AppHeaderView;

});