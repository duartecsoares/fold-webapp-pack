define(["backbone"], function(Backbone){
	
	var FoldListView = Backbone.View.extend({

		initialize : function(options){

			var view 		= this,
				actions 	= {

					idView 	: function(idView){

						if(!idView) throw new Error("View created without an IdView.");

						this.$el.attr("data-view", idView);
						this.idView = idView;

					},
					Model 	: function(Model){

						if (Model) this.model = new Model();

					},
					children : function(children){

						this.children = children || false;

					},
					isAnimated : function(isAnimated){

						this.isAnimated = isAnimated || false;

					}

				};	

			if (typeof view.preparation === "function") {

				view.preparation();

			}

			view = Object.keys(options).map(function(key){

				return actions[key].call(view, options[key]);

			});

		},

		render : function(data){
			
			var view 	= this,
				model 	= (typeof view.model === "function") ? view.model.toJSON() : view.model;

			if(view.template){

				var toTemplate = {

					model : data || model

				};

				view.$el.html(view.template(toTemplate));
				
			}

			view.trigger("view:render");

			return view;

		},

		renderList : function(models, ItemView, ItemModel){

			var view = this;

			models.map(function(item){

				var itemView = new ItemView({ idView : view.idView + "-" + item.id, model : ItemModel });

				itemView.model.set(item);

				view.$el.append(itemView.render().el);				

				return item;

			});

		},

		destroy : function(){

			var view = this;

			view.stopListening();			
            view.undelegateEvents();
            view.$el.removeData().unbind();
            view.remove();
            Backbone.View.prototype.remove.call(view);

            view.trigger("view:destroy");

            return view;

        }

	});

	return FoldListView;

});