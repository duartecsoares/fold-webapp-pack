define(["fold/view",
		"text!templates/layout/footer.html"], function(FoldView, template){
	
	var AppFooterView = FoldView.extend({

		className: "layout-footer-view",
		template: _.template(template)

	});

	return AppFooterView;

});