define(["chai",
		"fold/layout",
		"fold/model"], function(chai, LayoutComponent, FoldModel){

    console.info("loading layout");

    var expect = chai.expect;

    console.info(chai);

    describe("Layout Component", function(){

    	var layout;

    	beforeEach(function(){

    		layout = new LayoutComponent();

    	});

		it("Expect be an instance of LayoutComponent", function() {
      		
	      	expect(layout).to.be.an.instanceof(LayoutComponent);

    	});

    	it("Should add views to the DOM", function(){

    		layout.add({});

    	});

    });

});
