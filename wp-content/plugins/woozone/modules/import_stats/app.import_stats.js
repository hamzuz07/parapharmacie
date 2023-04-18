/*
Document   :  Import Stats
Author     :  Andrei Dinca, AA-Team http://codecanyon.net/user/AA-Team
*/

// Initialization and events code for the app
WooZoneImportStats = (function($) {
	"use strict";

	// public
	var debug_level                     = 0,
		maincontainer                   = null,
		lang                            = null;

	// init function, autoload
	(function init() {
		// load the triggers
		$(document).ready(function() {

			maincontainer = $("#WooZone");

			// language messages
			lang = maincontainer.find('#WooZone-lang-translation').length
				? maincontainer.find('#WooZone-lang-translation').html()
				: $('#WooZone-wrapper #WooZone-lang-translation').html();
			//lang = JSON.stringify(lang);
			lang = typeof lang != 'undefined'
				? JSON && JSON.parse(lang) || $.parseJSON(lang) : lang;

			triggers();

			aateam_tooltip();
		});
	})();


	function aateam_tooltip() {
		WooZone.aateam_tooltip( 'span.tooltip, th.tooltip' );
	}


	// :: TRIGGERS
	function triggers() {
	}


	// :: MISC
	var misc = {

		hasOwnProperty: function(obj, prop) {
			var proto = obj.__proto__ || obj.constructor.prototype;
			return (prop in obj) &&
			(!(prop in proto) || proto[prop] !== obj[prop]);
		},

		arrayHasOwnIndex: function(array, prop) {
			return array.hasOwnProperty(prop) && /^0$|^[1-9]\d*$/.test(prop) && prop <= 4294967294; // 2^32 - 2
		},

		size: function(obj) {
			var size = 0;
			for (var key in obj) {
				if (misc.hasOwnProperty(obj, key)) size++;
			}
			return size;
		}
	}

	// external usage
	return {
		//"background_loading": background_loading
	}
})(jQuery);

