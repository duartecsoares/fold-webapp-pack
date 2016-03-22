define(["fold/controller",
		"settings/app"], function(FoldController, appSettings){
	
	var UploadController = FoldController.extend({

		preview : function(file){

			var controller = this,
				fileReader = new FileReader();

			fileReader.onload = function(e){

				controller.trigger("upload:preview:done", e.target.result);

			}

			fileReader.readAsDataURL(file);			

		},

		delete : function(url, data){

			var controller 		= this,
				xhr 			= new XMLHttpRequest(),
				method 			= "DELETE",
				url 			= appSettings.api.url + "/" + url,
				idImage,
				appendUrlParams	= function(param){

					url += "/" + param;

					return url;

				};

			url = Object.keys(data).reduce(function(accumulator, param){

				if(param === "idImage") idImage = data[param];

				accumulator = appendUrlParams(data[param]);

				return accumulator;

			}, url);

			xhr.open(method, url, true);

			xhr.onload = function(){

				var responseJSON = JSON.parse(xhr.response);

				controller.trigger("upload:request:done");

				if (responseJSON.status === 200) controller.trigger("upload:file:delete", { response: responseJSON, id : idImage });
			}

			xhr.send(null);

		},

		upload : function(config, data){

			var controller 		= this,
				formData 		= new FormData(),
				xhr		 		= new XMLHttpRequest(),
				url 			= appSettings.api.url + "/" + config.url,
				maxSize 		= 6000000,						//6mb
				method 			= config.method.toUpperCase(),
				appendFormData 	= function(key){

					formData.append(key, data[key]);

				};

			Object.keys(data).map(appendFormData);

			xhr.open(method, url, true);

			xhr.onload = function(e){

				var responseJSON = JSON.parse(xhr.response);

				controller.trigger("upload:request:done");

				if (responseJSON.status === 200) controller.trigger("upload:file:done", responseJSON);
			
			}

			xhr.upload.onprogress = function(e) {

				if (e.lengthComputable) {

					var value = Math.round((e.loaded / e.total) * 100);
			
					controller.trigger("upload:file:progress", value);

				}
			}

			xhr.send(formData);
			controller.trigger("upload:file:start");

		}

	});

	return new UploadController();

});
