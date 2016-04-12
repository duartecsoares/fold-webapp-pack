define(["utils/tools"],function(tools){
	
	var BindedEventsHandlers = function(bindingDetails, $element, bindedData){

		var diggedValue = tools.diggIntoObject(bindedData.model.toJSON(), $element.attr("fold-binding")),
			bindedActions = {

			value : function(writeInHTML){

				if (!writeInHTML) return;

				var value = bindedData.value;

				if (!(value instanceof Array)) {

					$element.html(diggedValue);

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

			attributes : function(listOfAttributes){

				listOfAttributes.map(function(key){

					$element.attr(key, diggedValue);

				});			

			},

			classToggler : function(classConfig){

				var condition = classConfig.condition || function(model){

					return (diggedValue) ? true : false;

				};

				if(condition(bindedData.model)){

					$element.addClass(classConfig.name);

				}else{

					$element.removeClass(classConfig.name);

				}

			},
			customAction : function(action){

				if (typeof action === "function") action(bindedData);

			}

		};

		if (bindingDetails.actions) {

			Object.keys(bindingDetails.actions).map(function(key){	

				bindedActions[key](bindingDetails.actions[key]);

			});

		}

	}
	
	return BindedEventsHandlers;

});