define(["fold/view"], function(FoldView){
		
	var ImageGalleryView = FoldView.extend({

		tagName : "img",
		setup : function(opt){

			this.$el.attr("src", opt.image);
			this.$el.attr("data-action", "gallery-image");

		}

	});

	return ImageGalleryView;

});