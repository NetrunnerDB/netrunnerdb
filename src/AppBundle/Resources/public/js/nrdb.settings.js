(function(settings, $) {
	
	// all the settings, initialized with their default value
	var cache = {
			display_columns: 1,
			core_sets: 3,
			show_suggestions: 3,
			buttons_behavior: 'cumulative'
	};
	
	settings.load = function load() {
		return Promise.all(_.keys(cache))
		.then(function (keys) {
			return _.map(keys, function (key) {
				return localforage.getItem(key).then(function (value) {
					if(value !== null) cache[key] = value;
				});
			});
		})
		.then(function () {
			$(document).trigger('settings.app');
		});
	};
	
	settings.getItem = function (key) {
		return cache[key];
	};
	
	settings.setItem = function (key, value) {
		localforage.setItem(key, value);
	};
	
	settings.getAll = function () {
		var copy = {};
		_.assign(copy, cache);
		return copy;
	}
	
	settings.promise = new Promise(function (resolve, reject) {
		$(document).on('settings.app', resolve);
	});
	
	settings.load();
	
})(NRDB.settings = {}, jQuery);
