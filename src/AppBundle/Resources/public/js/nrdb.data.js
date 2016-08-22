NRDB.data = {};
(function(data, $) {
	data.query = function() {
		var apiNames = ['cycles', 'packs', 'cards', 'prebuilts', 'factions', 'types', 'sides', 'mwl'];
		var promises = [];
		
		apiNames.forEach(function (apiName) {
			var promise = $.ajax(NRDB.api_url[apiName], {data: {locale: NRDB.locale}}).then(function (response) {
				if(!response || !response.success) return;
				if(apiName === 'cards') {
					response.data.forEach(function (card) {
						card.imageUrl = response.imageUrlTemplate.replace(/{code}/, card.code);
					})
				}
				data[apiName] = TAFFY(response.data);
			});
			promises.push(promise);
		});
		
		$.when.apply($, promises).done(function () {
			data.cards().each(function (card) {
				card.faction = data.factions({code:card.faction_code}).first();
				card.type = data.types({code:card.type_code}).first();
				card.pack = data.packs({code:card.pack_code}).first();
				card.side = data.sides({code:card.side_code}).first();
				if(card.cost === null && card.type_code !== 'agenda' && card.type_code !== 'identity') {
					card.cost = 'X';
				}
				if(card.strength === null && card.keywords.toLowerCase().indexOf('icebreaker') !== -1) {
					card.strength = 'X';
				}
			});
			data.packs().each(function (pack) {
				pack.cycle = data.cycles({code:pack.cycle_code}).first();
			})
			NRDB.data_loaded.fire();
		});
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
	
	$(data.query);

})(NRDB.data, jQuery);


