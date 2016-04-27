define(["fold/module"], function(FoldModule){

	var UrlModule = FoldModule.extend({

		getFragment : function(){

			return Backbone.history.fragment;

		},
	
		getParamsFromFragment: function(){

			var fragment 	= this.getFragment(),
				queryString = fragment.split("?")[1] || null;
				params 		= (queryString) ? queryString.split("&").reduce(function(accumulator, item){				
										
					var spliter = item.split("=");
			
					accumulator[spliter[0]] = spliter[1];
					
					return accumulator;

				}, {}) : null;

			return params;		

		},

		setParamsFragment : function(params){

			var fragment 	= this.getFragment().split("?")[0] || "",
				router 		= Backbone.router,
				numOfParams = 0,
				queryString = (params) ? Object.keys(params).reduce(function(accumulator, key, index){

				if (params[key]) {

					if (numOfParams > 0) accumulator += "&";

					accumulator += key + "=" + params[key];
					numOfParams += 1;

				}

				return accumulator;

			}, "?") : "";

			router.navigate(fragment + queryString, false);

			this.trigger("url:set:params", params);

		},

		clearFragment: function(force){

			var fragment = this.getFragment().split("?")[0]

			Backbone.router.navigate(fragment, force || false);

		}

	});

	return new UrlModule();

});