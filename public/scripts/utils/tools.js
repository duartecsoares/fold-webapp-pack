define(function(){

	var dom = function(){

		return document;

	},

	diggIntoObject  = function(JSON, attributeMap){

		var value 			= null,
			levels 			= attributeMap.split("."),
			numberOfLevels 	= levels.length - 1,
			iterator 		= 0;

		while(iterator <= numberOfLevels){

			value = (value) ? value[levels[iterator]] : JSON[levels[iterator]];
			iterator += 1;

		}

		return (value) ? value : "";

	},

	createElement = function(element){

		return document.createElement(element);

	},

	capitalize = function(string){

		return string.charAt(0).toUpperCase() + string;

	};
	
	return {

		capitalize : capitalize,
		diggIntoObject: diggIntoObject,
		createElement : createElement,
		dom : dom

	}

});