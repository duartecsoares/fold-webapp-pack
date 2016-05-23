define(["fold/controller",
		"views/pages/features"], function(FoldController, FeaturesView){
	
	var FeaturesController = FoldController.extend({

		type: "page-controller",

	});

	return new FeaturesController({ viewDetails: { constructor: FeaturesView, idView: "features-page-view" }});


});