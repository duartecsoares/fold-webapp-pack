define(["fold/view",
		"controllers/request",
		"text!templates/components/form/form.html",
		"json!data/forms/messages.json"], function(FoldView, request, template, messages){
	
	var checkInUseTimeOut,
		FormView = FoldView.extend({

		tagName : "form",
		className: "form",
		template: _.template(template),

		setup : function(opt){

			this.on("view:render", this.addEvents);

			this.type = opt.type;
			this.persistence = opt.persistence || null;			

		},

		addEvents: function(){

			var view 			= this,
				$form 			= view.$el;
				$inputs 		= $form.find("[data-form]"),
				$labeltronics 	= $form.find("[data-labeltronic]"),
				$questions 		= $form.find("[data-question]");
			
			if ( view.targetRef == "modal-wrapper" ) {

				_.defer(function(){

					$("#"+$inputs[0].id).focus();

				});

			}
			
			var labeltronicsTypes 	= {

				counter : function($input, $labeltronic) {

					var message = $labeltronic.text(),
						count 	= function(e){
							
							var max 	  = $input.attr("data-validation").match(/max-(\d+)/gi)[0].split("max-")[1],
								wordCount = $input.val().length,
								wordsLeft = max - wordCount;
								
							$labeltronic.text(message.replace("%s", wordsLeft));

							if (wordsLeft < 0) {

								$labeltronic.addClass("error");

							} else if ($labeltronic.hasClass("error")) {

								$labeltronic.removeClass("error");

							}

						};

					count();

					$input.on("input", count);

				},

				errorLink : function($input, $labeltronic) {

					var clearErrorLink = function(e){

						if($labeltronic.hasClass("error")){

							$labeltronic.removeClass("error");

						}

					};

					view.on("form:labeltronic:error", function(a){

						$labeltronic.addClass("error");

					});

					$input.on("input", clearErrorLink);

					$labeltronic.on("click", function(e){

						view.trigger("form:labeltronic:error:click");

					});

				}

			};

			$labeltronics.map(function(index, labeltronic){

				var $labeltronic 	= $(labeltronic),
					name 			= $labeltronic.attr("data-labeltronic"),
					type 			= $labeltronic.attr("data-labeltronic-type"),
					$input 			= $form.find("[name='" + name + "']");		

				if (labeltronicsTypes[type]) {

					labeltronicsTypes[type]($input, $labeltronic);

				}

			});

			$questions.map(function(index, question){

				var $question 	= $(question),
					name 		= $question.attr("data-question"),
					type 		= $question.attr("data-question-type"),
					$input 		= $form.find("[name='" + name + "']");		

				if (labeltronicsTypes[type]) {

					labeltronicsTypes[type]($input, $question);

				}

			});

			$form.on("submit", function(e){

				e.preventDefault();

				view.process(e);
			
			});

			$inputs.on("input", function(e){

				var $target = $(e.currentTarget),
					$parent = $target.parent(),
					data 	= view.extract(),
					inputWrapper; 

				if($parent.hasClass("input-wrapper")) {

					$parent = $parent.parent();
					inputWrapper = true;

				}

				$target.removeAttr("data-valid");
				$parent.removeClass("show-tooltip");

				if ($target.attr("data-live-edit")) {
					
					view.trigger("form:live:edit", data);

				}
				
				if (inputWrapper) {

					$target.siblings(".pseudo-input").removeAttr("data-valid");

				}

				// to check is username our email are already in use
				if ($target.hasClass("form-check-padding")){

					view.checkInUse($target);

				}

				view.trigger("form:change", data);

			});

			$inputs.on("focus", function(e){

				var $target = $(e.currentTarget),
					$parent = $target.parent();

				if($parent.hasClass("input-wrapper")) {

					$parent = $parent.parent();

				}

				if ($target.attr("data-valid") === "false" || $target.attr("data-unique") === "false" ) {

					$parent.addClass("show-tooltip");

				}

			});

			$inputs.on("blur", function(e){

				var $target = $(e.currentTarget),
					$parent = $target.parent();

				if($parent.hasClass("input-wrapper")) {

					$parent = $parent.parent();

				}

				$parent.removeClass("show-tooltip");

			});

		},

		extract : function(){

			var view 		 = this,
				$form 		 = this.$el,
				formData 	 = $form.serializeArray(),
				encodeFields = ["not-password", "password", "old_password", "new_password", "confirm_password"],
				processData	 = formData.reduce(function(accumulator, item){

					var name  = (item.name !== "") ? item.name : null;
						value = (item.value !== "") ? item.value.trim() : null;

					encodeFields.reduce(function(accumulator, field){

						if (name === field) value = btoa(value);

					}, {});

					if(item.name === "not-password"){

						accumulator["password"] = value;

					}else{

						accumulator[item.name] = value;

					}

					if (item.name === "username") {

						if(item.value[0] === "@") accumulator["username"] = value.slice(1, value.length);

					}

					return accumulator;

				}, {});			

				//todo: add input validation

			return processData;

		},

		sync : function(data){

			var view 	= this,
				model 	= view.model,
				inputs 	= model.inputs;

			if(!view.persistence) return;
					
			var updatedInputs = inputs.map(function(input){
				
				if((input.name !== "password") || (input.name !== "not-password")) input.value = data[input.name];

				return input;

			});

			view.model.inputs = updatedInputs;

			return updatedInputs;

		},

		rules : {				//should return true if valid

			min : function(name, value, params){

				return (value.length < params[0]) ? false : true;

			},
			max : function(name, value, params){

				return (value.length <= params[0]) ? true : false;

			},
			number : function(name, value, params){

				return (isNaN(value) || value === "" )  ? false : true;

			},

			username : function(name, value, params){

				//taken from http://stackoverflow.com/questions/8650007/regular-expression-for-twitter-username

				var value = (value[0] === "@") ? value.slice(1, value.length) : value;

				return ((/^@?(\w){1,20}$/.test(value)) || (value === "")) ? true : false;

			},

			nospaces: function(name, value, params){

				return (value.search(" ") === -1) ? true : false;

			},

			equalTo : function(name, value, params){

				var $form 		= this.$el,
					$equalTo 	= $form.find("[data-key='" + params[0].replace("_", " ") + "']") || $form.find("[name='" + params[0] + "']");

				return (value === $equalTo.val()) ? true : false;
				
			},
			required : function(name, value, params){

				return (value !== "") ? true : false;

			},

			email : function(name, value, params){

    			var regex = /^[a-z0-9!#$%&*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/i; 
				var m;
				 
				if ((m = regex.exec(value)) !== null) {
				    if (m.index === regex.lastIndex) {
				        regex.lastIndex++;
				    }
				}

				if (m) {
					
					return true;					

				} else {

					return false;

				}

			},

			domain : function(name, value, params){

				//var domainRegex = /^(?:(ftp|http|https):\/\/)?(?:[\w-]+\.)+[a-z]{2,30}$/; old regex

				var domainRegex = /^(?:(ftp|http|https):\/\/)?(?:[\w-]+\.)+(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;
				return ((domainRegex.test(value)) || (value === "")) ? true : false;

			}

		},

		getTests : function(validation){

			var isValid 	= true,
				errorList 	= [];

			Object.keys(validation).map(function(key){

				validation[key].map(function(test){

					if(!test.passed){

						isValid = false;

						var error = messages[test.method];

						if(!error) {

							console.warn("Error message is missing", test.method);
							return

						}

						if (error.search("%f") > -1) error = error.replace("%f", key).replace("_", " ");

						test.params.map(function(param, iterator){

							var paramMatcher = String("%p" + (iterator + 1));

							error = error.replace(paramMatcher, param.replace("_", " "));

						});

						errorList.push(error);

					}

				});

			});

			return { isValid : isValid, errors : errorList };

		},

		validate : function(){

			var view 			= this;
				$form 			= view.$el,
				$inputs 		= $form.find("[data-validation]"),
				ruler 			= view.rules,	
				validationResult= {},			
				extractRules 	= function(name, value, rulesArray){

					var rules 		= rulesArray.split(" "),
						validations = [],
						result 		= {};

					rules.map(function(rule){
						
						var dismantle 	= rule.split("-"),
							method 		= dismantle[0],
							params 		= dismantle.slice(1).map(function(param){

								return param;

							});

						if(!ruler[method]){

							if(method) console.warn("Unidentified rule", method);
							return;

						}

						validations.push({ method : method, passed : ruler[method].call(view, name, value, params), params : params });

					});

					result[name] = validations

					return result;

				}

			$inputs.map(function(iterator, item){

				var $item 		= $(item),
					rules 		= $item.attr("data-validation"),
					name  		= $item.attr("data-key") || $item.attr("name"),
					value 		= $item.val(),
					extractor 	= extractRules(name, value, rules),
					isValid 	= extractor[name].reduce(function(accumulator, validation){

						if(!validation.passed) accumulator = false;

						return accumulator

					}, true),
					inputWrapper;

				var $parent = $item.parent();

				if($parent.hasClass("input-wrapper")) {

					$parent = $parent.parent();
					inputWrapper = true;

				}

				if (isValid) {

					$item.attr("data-valid", true);

				} else {

					$item.attr("data-valid", false);

					var test = {},
						message;

					test[name] = extractor[name];
					message = view.getTests(test).errors[0] || "unknown error, must add entry";				

					$parent.attr("data-tooltip", message);

					if ( inputWrapper ) {

						$item.siblings(".pseudo-input").attr("data-valid", false);

					}

					if ($item.attr("data-labeltronic-type")) { 

						var $label 			= $form.find("[for="+$item[0].id+"]"),
							$labeltronic 	= $label.find("[data-labeltronic]");

						$labeltronic.addClass("error");

					}

					if ($item.attr("data-question-type")) { 

						var $question = $form.find("[data-question="+$item[0].id+"]");

						$question.addClass("error");

					}

				}

				validationResult[name] = extractor[name];

			});

			return { tests : validationResult, isValid : this.getTests(validationResult).isValid };

		},

		validateSingle : function($input) {

			var view 			= this,
				$form 			= view.$el,
				ruler 			= view.rules,	
				validationResult= {},			
				extractRules 	= function(name, value, rulesArray){

					var rules 		= rulesArray.split(" "),
						validations = [],
						result 		= {};

					rules.map(function(rule){
						
						var dismantle 	= rule.split("-"),
							method 		= dismantle[0],
							params 		= dismantle.slice(1).map(function(param){

								return param;

							});

						if(!ruler[method]){

							if(method) console.warn("Unidentified rule", method);
							return;

						}

						validations.push({ method : method, passed : ruler[method].call(view, name, value, params), params : params });

					});

					result[name] = validations

					return result;

				},
				rules 		= $input.attr("data-validation"),
				name  		= $input.attr("name"),
				value 		= $input.val(),
				extractor 	= extractRules(name, value, rules),
				isValid 	= extractor[name].reduce(function(accumulator, validation){

					if(!validation.passed) accumulator = false;

					return accumulator

				}, true);

			return isValid;

		},

		checkInUse : function ($input) {

			var view  = this,
				$parent = $input.parent(),
				inputWrapper,
				showFormCheck = "",
				inputKey = $input.attr("data-key") || $input[0].name,
				value = ($input[0].value[0] === "@") ? $input[0].value.slice(1, $input[0].value.length) : $input[0].value;

			if (value === "" || value === null || value === undefined) return;

			if($parent.hasClass("input-wrapper")) {

				$parent = $parent.parent();
				inputWrapper = true;

			}
					
			if ( view.validateSingle($input) ) {

				var initialValue = $input[0].value;

				if(checkInUseTimeOut) clearTimeout(checkInUseTimeOut);

				checkInUseTimeOut = setTimeout(function(){

					request.get("users/"+value+"?exists=true", null, function(response){

						//  stupid way of seeing if the value is still the same as when request was made
						if ( initialValue === $input[0].value ) {

							if (response.user) {

								showFormCheck = "false";
								var message = inputKey+" is already in use";

								$input.attr("data-unique", showFormCheck);
								$parent.attr("data-tooltip", message);
								$parent.addClass("show-tooltip");
							
							} else {
								
								showFormCheck = "true";
								$input.attr("data-unique", showFormCheck);
								$parent.removeClass("show-tooltip");
								
							}

							if ( inputWrapper ) {

								$input.siblings(".pseudo-input").attr("data-unique", showFormCheck);

							}

							$input.siblings(".form-check").attr("data-check-valid", showFormCheck);

						}

					});

				}, 300);

			}

			if ( inputWrapper ) {

				$input.siblings(".pseudo-input").attr("data-unique", showFormCheck);

			}

			$input.siblings(".form-check").attr("data-check-valid", showFormCheck);

		},

		process : function(){

			var view  = this,
				tests = view.validate();

			if (tests.isValid) {

				var formData = view.extract();
			
				view.trigger("form:submit", { formData : formData, isValid : tests.isValid });
				
				if (view.persistence) view.sync(formData);

				view.clean();

			}

			return { validation : tests, formData : formData };

		},

		clean : function(){

			var $form = this.$el;

			$form.find("[data-valid]").removeAttr("data-valid");

		},

		reset : function(){

			var $form = this.$el

			$form[0].reset();

		}

	});

	return FormView;

});