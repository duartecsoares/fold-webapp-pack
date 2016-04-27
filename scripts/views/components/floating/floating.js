define(["fold/view",
		"layout"], function(FoldView, layout){
    
    var FloatingView = FoldView.extend({

        className : "floating-view",
        isAnimated: true,

        setup : function(options){

            var view = this;

        	this.$el.addClass(options.alignment || "center");
            this.$el.addClass(options.position || "");
            this.on("view:render", this.addEvents);

            layout.on("layout:dropdown:close", function(idView){

                if (view.idView !== idView) view.close();

            });

        },

        open : function(target){

        	layout.add([this], target);
            layout.trigger("layout:dropdown:close", this.idView);

        },

        close : function(e){

            if (e) e.stopPropagation();

        	layout.remove(this.idView);

        },

        addEvents : function(){            

            var view = this;

            setTimeout(function(){

                var $items  = view.$el.find("[data-floating-item]");

                 $items.on("click", function(e){

                    var $target = $(e.currentTarget),
                        value   = $target.attr("data-floating-item");

                    view.trigger("view:floating:set", value);

                    view.close(e);

                });

            });

        }

    });

    return FloatingView;

});