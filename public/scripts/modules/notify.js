define(["fold/module",
		"collections/notify",
		"views/notify/notify",
		"layout"], function(FoldModule, NotifyCollection, NotifyView, layoutController){

	var notifyInterval,
		suppressInterval,
		NotifyModule = FoldModule.extend({

		initialize : function(){

			this.model = {

				notificationsEmitted : 0

			};

		},

		emit : function(message, type, time){

			var controller 			= this,
				id 					= controller.model.notificationsEmitted + 1,
				time 				= (time >= 0) ? time : 5000,
				notificationType 	= type || "info",
				notifyView 			= new NotifyView({ idView: "notify-" + id, isAnimated: true }),
				addNotification 	= function(timer){

					controller.idCurrent = notifyView.idView;
					controller.hasNotification = true;
					controller.model.notificationsEmitted += 1;

					if (timer) {

						if (notifyInterval) clearInterval(notifyInterval);

						notifyInterval = setTimeout(function(){

							layoutController.add([notifyView], $("#notify"));

						}, timer);

					}else{

						layoutController.add([notifyView], $("#notify"));

					}

					if (time > 0) {

						clearInterval(suppressInterval);

						suppressInterval = setTimeout(function(){

							controller.suppress(notifyView.idView);

						}, time);					

						controller.listenToOnce(notifyView, "notify:suppress", function(idView){

							controller.suppress(idView);

						});			

					}							

				};

			notifyView.model =  { id: id, message : message, type : notificationType, sticky : (time > 0) ? false : true };

			if (!controller.hasNotification) {
				
				addNotification();

			}else{		
		
				controller.suppress(controller.idCurrent);
				addNotification(500);

			}

		},

		suppress : function(idView){

			var controller = this;

			controller.hasNotification = false;
			controller.idCurrent = false;
			layoutController.remove(idView);

		}

	});

	return new NotifyModule();

});
