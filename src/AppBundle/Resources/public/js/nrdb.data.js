if (typeof NRDB != "object")
	var NRDB = { 
		data_loaded: jQuery.Callbacks(), 
		api_url: {
			sets: 'https://netrunnerdb.com/api/sets/',
			cards: 'https://netrunnerdb.com/api/cards/',
			mwl: 'https://netrunnerdb.com/api/get_mwl/'
		},
		locale: 'en'
	};
NRDB.data = {};
(function(data, $) {
	data.sets = {};
	data.cards = {};
	data.mwl = {};

	var sets_data = null;
	var cards_data = null;
	var mwl_data = null;
	var is_modified = null;

	data.query = function() {
		data.initialize();
		data.promise_sets = $
				.ajax(NRDB.api_url.sets+"?jsonp=NRDB.data.parse_sets&_locale="
						+ NRDB.locale);
		data.promise_cards = $
				.ajax(NRDB.api_url.cards+"?jsonp=NRDB.data.parse_cards&_locale="
						+ NRDB.locale);
		data.promise_mwl = $
				.ajax(NRDB.api_url.mwl+"?jsonp=NRDB.data.parse_mwl");

		$.when(data.promise_sets, data.promise_cards, data.promise_mwl).done(data.initialize);
	};

	data.initialize = function() {
		if (is_modified === false)
			return;

		if(!sets_data) {
			try {
				var json = localStorage.getItem('sets_data_' + NRDB.locale);
				sets_data = JSON.parse(json);
			} catch(e) {
				localStorage.removeItem('sets_data_' + NRDB.locale);
				sets_data = [];
			}
		}
		if(!sets_data) return;
		data.sets = TAFFY(sets_data);
		data.sets.sort("cyclenumber,number");

		if(!cards_data) {
			try {
				var json = localStorage.getItem('cards_data_' + NRDB.locale);
				cards_data = JSON.parse(json);
			} catch(e) {
				localStorage.removeItem('cards_data_' + NRDB.locale);
				cards_data = [];
			}
		}
		if(!cards_data) return;
		data.cards = TAFFY(cards_data);
		data.cards.sort("code");

		if(!mwl_data) {
			try {
				var json = localStorage.getItem('mwl_data');
				mwl_data = JSON.parse(json);
			} catch(e) {
				localStorage.removeItem('mwl_data');
				mwl_data = [];
			}
		}
		if(!mwl_data) return;
		data.mwl = TAFFY(mwl_data);
		data.mwl.sort("start");

		NRDB.data_loaded.fire();
	};

	data.parse_sets = function(response) {
		if(typeof response === "undefined") return;
		var json = JSON.stringify(sets_data = response);
		is_modified = is_modified
				|| json != localStorage.getItem("sets_data_" + NRDB.locale);
		localStorage.setItem("sets_data_" + NRDB.locale, json);
	};

	data.parse_cards = function(response) {
		if(typeof response === "undefined") return;
		var json = JSON.stringify(cards_data = response);
		is_modified = is_modified
				|| json != localStorage.getItem("cards_data_" + NRDB.locale);
		localStorage.setItem("cards_data_" + NRDB.locale, json);
	};

	data.parse_mwl = function(response) {
		if(typeof response === "undefined") return;
		mwl_data = response.version ? response.data : response;
		var json = JSON.stringify(mwl_data);
		is_modified = is_modified
			|| json != localStorage.getItem("mwl_data");
		localStorage.setItem("mwl_data", json);
	};
	
	data.get_card_by_code = function(code) {
		if(data.cards) {
			return data.get_cards_by_code(code).first();
		}
	};
	
	data.get_cards_by_code = function(code) {
		if(data.cards) {
			return data.cards({code:String(code)});
		}
	};
	
	$(function() {
		if(NRDB.api_url) data.query();
	});

})(NRDB.data, jQuery);


