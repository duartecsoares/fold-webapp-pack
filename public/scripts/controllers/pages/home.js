define(["fold/controller",
		"views/pages/home"], function(FoldController, HomeView){
	
	var HomeController = FoldController.extend({

		type: "page-controller",

	});

	return new HomeController({ viewObj: { instance: HomeView, idView: "home-page-view" }});

});