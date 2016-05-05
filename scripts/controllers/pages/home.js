define(["fold/controller",
		"views/pages/home",
		"fold/model"], function(FoldController, HomeView, FoldModel){
	
	var HomeController = FoldController.extend({

		type: "page-controller",

	});

	return new HomeController({ viewDetails: { constructor: HomeView, idView: "home-page-view"}});

});