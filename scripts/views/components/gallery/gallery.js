define(["fold/view",
		"text!templates/components/gallery/gallery.html",
		"views/modal/modal",
		"views/gallery/image",
		"layout",
		"colorThief"], function(FoldView, template, modalView, ImageGalleryView, layout, colorThief){
	
	var GalleryView = FoldView.extend({

		template: _.template(template),
		className : "gallery-cover page-cover",

		setup : function(){

			this.on("view:render", this.addEvents);

		},

		addEvents : function(){

			var view 	= this,
				model 	= view.model,
				$images = view.$el.find("[data-gallery]"),
				cover 	= model.get("cover"),
				canvas 	= view.$el.find(".cover-canvas")[0],
				imageEl = new Image();

			$images.on("click", function(e){

				var $target 			= $(e.currentTarget),
					index 				= parseInt($target.attr("data-gallery")),
					galleryModel 		= model.get("images"),
					imageModel  		= model.get("images")[index],
					imageGalleryView 	= new ImageGalleryView({ image : imageModel.image }),
					showImage 			= function(imageSrc){

						var $image = imageGalleryView.$el;

						$image.attr("src", imageSrc);

					};

				modalView.open(imageGalleryView, { title : imageModel.description, theme: "gallery" });

				view.listenTo(layout, "layout:window:keydown", function(e){

					var keyCode = e.keyCode,
						rightArrow = function(){

							index = (index >= (galleryModel.length - 1)) ? 0 : index + 1;
							showImage(galleryModel[index].image);

						},
						leftArrow = function(){

							index = (index >= 1) ? index - 1 : galleryModel.length - 1;
							showImage(galleryModel[index].image);

						},
						keys = {

							39 : rightArrow,
							37 : leftArrow

					};

					if (keys[keyCode]) keys[keyCode]();

				});

			});

			if (cover && canvas) {

				imageEl.onload = function(){

					var color 		= colorThief.getColor(imageEl),
						palette 	= colorThief.getPalette(imageEl, 2),					
						context 	= canvas.getContext("2d"),
						gradient 	= context.createLinearGradient(0, 0, 0, 170);

						gradient.addColorStop(0, "rgba(" + palette[0].toString() + ", .25)");
						gradient.addColorStop(1, "rgba(" + palette[2].toString() + ", .45)");
	       			 
						context.fillStyle = gradient;
						context.fillRect(0, 0, canvas.width, canvas.height);

				}

				imageEl.crossOrigin = "Anonymous";
				imageEl.src = cover + "?1440788264";

			}

		}

	});

	return GalleryView;

});