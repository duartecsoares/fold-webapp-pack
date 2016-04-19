define(["fold/controller"], function(FoldController){

	var TourController = FoldController.extend({

		setup: function(tourData){

			console.info("tour controller setup");

			var controller = this;

			this.on("controller:enable", function(){

				controller.generateSteps(tourData);

			});

		},

		generateSteps: function(tourData){

			console.info("generation steps");

		}


	});

	return TourController;

});