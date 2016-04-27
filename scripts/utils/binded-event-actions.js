define(["utils/tools"],function(tools){
	
	var BindedEventsHandlers = function(bindingDetails, $element, bindedData){

		var diggedValue = tools.diggIntoObject(bindedData.model.toJSON(), $element.attr("fold-binding")),
			bindedActions = {

			value : function(writeInHTML, $element){

				if (!writeInHTML) return;

				var value = diggedValue;

				if (!(value instanceof Array)) {

					$element.html(diggedValue);

				}else{							

					while($element[0].firstChild){

						$element[0].removeChild($element[0].firstChild);

					}

					value.map(function(itemObj, index){

						var itemElement = tools.createElement(bindingDetails.arrayElementItem || "span"),
							$itemElement = $(itemElement),
							itemAttributes = itemObj.attributes || [],
							itemClassList = itemObj.classList || [];

						$itemElement.attr("fold-" + bindedData.property + "-item", index);							
						itemAttributes.map(function(key){

							$itemElement.attr(key, itemObj.value);

						});

						itemClassList.map(function(key){

							itemElement.addClass(key);

						});

						$itemElement.html(itemObj.value);
						$element.append($itemElement);

					});

				}
		
			},

			attributes : function(listOfAttributes, $element){

				listOfAttributes.map(function(key){

					$element.attr(key, diggedValue);

				});			

			},

			classToggler : function(classConfig, $element){

				var condition = classConfig.condition || function(model){

					return (diggedValue) ? true : false;

				};

				if(condition(bindedData.model)){

					$element.addClass(classConfig.name);

				}else{

					$element.removeClass(classConfig.name);

				}

			},
			custom : function(action){

				if (typeof action === "function") action(bindedData);

			}

		};

		if (bindingDetails.actions) {

			Object.keys(bindingDetails.actions).map(function(key){	

				bindedActions[key](bindingDetails.actions[key], $element);

			});

		}

	}
	
	return BindedEventsHandlers;

});