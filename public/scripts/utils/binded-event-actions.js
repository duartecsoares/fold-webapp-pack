define(["utils/tools"],function(tools){
	
	var BindedEventsHandlers = function(bindingDetails, $element, bindedData){

		var bindedActions = {

			"value" : function(writeInHTML){

				if (!writeInHTML) return;

				var value = bindedData.value;

				if (!(value instanceof Array)) {

					$element.html(tools.diggIntoObject(bindedData.model.toJSON(), $element.attr("fold-binding")));

				}else{							

					while($element[0].firstChild){

						$element[0].removeChild($element[0].firstChild);

					}

					value.map(function(key){

						var itemElement = tools.createElement(bindingDetails.arrayElementItem || "span");

						itemElement.setAttribute("fold-" + key + "-item", true);
						itemElement.innerHTML = key;

						$element.append(itemElement);

					});

				}
		
			},

			"attributes" : function(listOfAttributes){

				listOfAttributes.map(function(key){

					$element.attr(key, tools.diggIntoObject(bindedData.model.toJSON(), $element.attr("fold-binding")));

				});			

			},

			"class" : function(classConfig){

				var condition = classConfig.condition || function(){

					return (bindedData.value) ? true : false;

				};

				if(condition(bindedData.model)){

					$element.addClass(classConfig.name);

				}else{

					$element.removeClass(classConfig.name);

				}

			},
			"customAction" : function(action){

				if (typeof action === "function") action();

			}

		};

		console.info("constructor", bindingDetails);

		if (bindingDetails.actions) {

			Object.keys(bindingDetails.actions).map(function(key){	

				bindedActions[key](bindingDetails.actions[key]);

				console.info("data binding on", key);

			});

		}

	}
	
	return BindedEventsHandlers;

});