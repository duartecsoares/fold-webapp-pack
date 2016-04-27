define(["fold/controller"], function(FoldController){
	
	var AnalyticsController = FoldController.extend({

		hasTracker : function() {
			if ( typeof ga == 'function' ) {
				return true;
			} else {
				return false;
			}
		},

		//
		// todo, track page changes
		//
		setPage : function(){

			if ( this.hasTracker() ) {
			
				ga("send", { hitType: 'pageview', page: location.pathname });

			}

		},

		//
		// listen for trackable items
		//
		track : function( $el ) {

			var controller = this;

			var getName = function( $el ) {

					var elName = $el.attr('data-track-area');

					if ( typeof elName == 'string' && elName.length > 0 ) {
						return elName;
					} else {
						var $parent = $el.parents('[data-track-area]');
						if ( $parent.length > 0 ) {
							return $parent.attr('data-track-area');
						} else {
							return false;
						}
					}

				};

			if ( $el instanceof Backbone.View ) {
				$el = $el.$el ? $el.$el.find('[data-track]') : false;
			}

			if ( $el && this.hasTracker() ) {
				$el.on('click', function() {
					var $el 		= $(this),
						name 		= getName( $el ),
						trackVal 	= $el.attr('data-track'),
						text 		= typeof trackVal == 'string' && trackVal.length > 0 ?  trackVal : $el.text();

					if ( name) {
						var data = $(this).attr('data-track');
						controller.send(name, 'Click', text);
					}
				});
			}

		},

		//
		// trigger analytics to google
		//
		// https://developers.google.com/analytics/devguides/collection/analyticsjs/sending-hits
		//
		send: function( evNa, evAc, evLa ) {

			//
			// example : 
			//
			//	send( 'Signup Form', 'click', 'Start Here!');
			// 
			// Signup Form:click
			var eventAction = typeof evAc == 'string' ? evAc : 'Click',
				eventName 	= typeof evNa == 'string' ? evNa : 'Unknown',
				eventLabel 	= typeof evLa == 'string' ? evLa : 'Unknown';

			if ( this.hasTracker() ) {

				var obj = {
				  hitType 		: 'event',
				  eventCategory : eventName,
				  eventAction 	: eventAction,
				  eventLabel	: eventLabel
				};

				ga('send', obj);

			} else {
				return false;
			}
		}

	});
	
	var analytics = new AnalyticsController();

	return analytics;

});