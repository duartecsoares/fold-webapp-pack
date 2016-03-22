define(function(){

	capitalize = function(string){

		return string.charAt(0).toUpperCase() + string;

	}
	
	return {

		capitalize : capitalize

	}

});