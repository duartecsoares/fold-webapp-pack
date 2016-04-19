define(["fold/view",
		"text!templates/components/dropdown/dropdown.html",
		"layout"], function(FoldView, template, layout){
	
	var DropdownView = FoldView.extend({

		template : _.template(template),
		tagName : "ul",
		className : "dropdown",

		setup :  function(options){

			var view = this;

			this.$el.addClass(options.alignment || "");
			this.on("view:render", this.addEvents);

			layout.on("layout:dropdown:close", function(idView){

                if (view.idView !== idView) view.close();

            });

		},

		addEvents : function(){

			var view 	= this,
				$items 	= view.$el.find("li"),
				model 	= this.model;
			
			$items.on("click", function(e){

				var $target = $(e.currentTarget),
					$anchor = $target.find("a");

				view.trigger("dropdown:item:click", $target);

				if($anchor.attr("href") === "") e.preventDefault();

			});

		},

		change : function(model){

			this.model = model;

			return model;

		},

		open : function(e, $target){

			e.stopPropagation();
			layout.add([this], $target);

			layout.trigger("layout:dropdown:close", this.idView);

		},

		close : function(e){

			if (e) e.stopPropagation();
			layout.remove(this.idView);

		}

	});

	return DropdownView;

});