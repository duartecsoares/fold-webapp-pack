define(["fold/controller"], function(FoldController){

	var TourController = FoldController.extend({

		type: "tour-controller",

		setup: function(tourData){

			console.info("tour controller setup");

			var tour = this;

			this.on("controller:enable", function(){

				tour.generateSteps(tourData);
				

			});

			this.on("controller:load:view", function(){

				console.warn("B");
				tour.addActionEvents();

			});

		},

		generateSteps: function(tourData){

			console.info("generation steps");

		},

		start: function(){

			console.info("starting tour");

		},

		finish: function(){


		},

		next: function(){

		},

		previous: function(){

		},

		addActionEvents : function(){

			var tour = this,
				tourView = tour.view;

			console.info(tour);
			console.info(tourView);

			tour.listenTo(tourView, "view:action:start", tour.start);

		}


	});

	return TourController;

});