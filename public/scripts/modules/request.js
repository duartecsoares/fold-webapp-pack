define(["fold/module",
		"settings/app"], function(FoldModule, settings){
	
	var RequestModule = FoldModule.extend({

		initialize : function(){

			this.setup();

		},

		setup : function(){

			var controller 		= this,
				$requestHandler = $.ajax,
				crud 			= ["get", "post", "put", "delete"];

			crud.map(function(item){

				(function(verb){
					
					request = function(service, data, callback){

						if(!service) return false;

						var eventName	= "request:" + service + ":" + verb + ":",
							type 		= verb.toUpperCase(),							
							toService  	= "/" + service,
							serviceName = service.split("?")[0],
							data 		= ((type === "POST" || (type === "PUT") || "DELETE") && data) ? data : null,
							url 		= settings.api.url + toService,
							$request 	= $requestHandler({

								url 	: url,
								type 	: type,
								data	: data,

								success : function(response){

									controller.trigger("controller:" + serviceName + ":" + verb + ":success", response);
									controller.trigger("request:" + serviceName + ":" + verb + ":success", response);								

									var serviceNameArray = serviceName.split('/'),
										cleanServiceName = [];

									_.each( serviceNameArray, function( value ) {

										if ( isNaN(value) ) cleanServiceName.push(value);
																				
									});

									cleanServiceName = cleanServiceName.join('/');

									controller.trigger("controller:" + serviceName + ":" + verb + ":end");
									controller.trigger("request:" + serviceName + ":" + verb + ":end");
									controller.trigger("request:end");
									controller.trigger("app:versions:match", response.versions.app);

									if (callback) callback(response);

								},

								error : function(response){

									controller.trigger("controller:" + serviceName + ":" + verb + ":error", response);
									controller.trigger("request:" + serviceName + ":" + verb + ":error", response);
									controller.trigger("controller:" + serviceName + ":" + verb + ":end");
									controller.trigger("request:" + serviceName + ":" + verb + ":end");
									controller.trigger("request:end");
									controller.trigger("request:error", response.status);

									if (callback) callback(response.responseJSON);

								}

							});

							controller.trigger("controller:" + serviceName + ":" + verb + ":start");
							controller.trigger("request:" + serviceName + ":" + verb  + ":start");
							controller.trigger("request:start");

						return $request;

					}

					controller[verb] = request;

				})(item);

			});

		}

	});

	return new RequestModule();

});