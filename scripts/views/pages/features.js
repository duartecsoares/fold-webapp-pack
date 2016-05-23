define(["fold/view",
		"text!templates/pages/features.html"], function(FoldView, template){
	
	var FeatureView = FoldView.extend({

		template: _.template(template),

		setup: function(){

			/* setup fn serves as an specific initializer */

		}

	});

	return FeatureView;

});