define(["fold/controller"], function(FoldController){
	
	var StorageController = FoldController.extend({

		version : 1,

		hasLocalStorage : function() {
			try {
				return window.localStorage && typeof window.localStorage.getItem === 'function';
			} catch(e) {}
			return false;
		},

		reset : function() {
			localStorage.setItem('buildit', '{}');
		},

		get : function( attr, ver ) {
			var data 	= null,
				dataObj,
				wholeDataObj,
				version = ver ? ver : this.version;

			if ( this.hasLocalStorage() && typeof attr === 'string' ) {

				try {
					wholeDataObj = JSON.parse(localStorage.getItem('buildit'));
					if ( wholeDataObj && typeof wholeDataObj === 'object' && typeof wholeDataObj[attr] === 'object' && typeof wholeDataObj[attr].data !== 'undefined' && wholeDataObj[attr].version == version ) {
						return wholeDataObj[attr].data;
					}
				} catch(e) {}

			} else {

				try {
					return JSON.parse(localStorage.getItem('buildit'));
				} catch(e) {}
			}

			return data;
		},

		set : function( attr, val, ver ) {
			var success = false,
				data 	= null,
				date,
				dataObj,
				wholeData,
				version = ver ? ver : this.version,
				currentData,
				currentWholeData = this.get();

			if ( !currentWholeData ) {
				wholeData = {};
			} else {
				wholeData = currentWholeData;
			}

			if ( this.hasLocalStorage() && typeof attr === 'string' && typeof val !== 'undefined' ) {
				try {
					data = val;
					date = new Date();

					dataObj = {
						version : version,
						data : val,
						date : date.getDate() + '-' + date.getMonth() + '-' + date.getFullYear()
					}

					wholeData[attr] = dataObj;
					localStorage.setItem('buildit', JSON.stringify(wholeData));

					success = true;
				} catch(e) {}
			}

			return success;
		}

	});
	
	var storage = new StorageController();

	return storage;

});