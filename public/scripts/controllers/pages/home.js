define(["fold/controller",
		"views/pages/home",
		"fold/model",
		"dev/views/data-binding-dev"], function(FoldController, HomeView, FoldModel, DataBindingViewDev){
	
	var HomeController = FoldController.extend({

		type: "page-controller",

	});

	return new HomeController({ viewObj: { constructor: HomeView, idView: "home-page-view", children: [{ constructor: DataBindingViewDev, idView: "data-binding-dev-view", Model: FoldModel}]}});

});