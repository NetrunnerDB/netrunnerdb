/* global NRDB, Promise, _ */

Promise.all([NRDB.data.promise, NRDB.ui.promise]).then(function() {
	// TODO(plural): Find a better place for this and remove the duplicate definitions.
	// This will filter matchingCards to only the latest version of each card, preserving the original order of matchingCards.
	function select_only_latest_cards(matchingCards) {
		var latestCardsByTitle = {};
		for (var card of matchingCards) {
			var latestCard = latestCardsByTitle[card.title];
			if (!latestCard || card.code > latestCard.code) {
				latestCardsByTitle[card.title] = card;
			}
		}
		return matchingCards.filter(function(value, index, arr) {
			return value.code == latestCardsByTitle[value.title].code;
		});
	}

	// We only need to calculate the latest_cards once and not on every findMatches call.
	var latest_cards = select_only_latest_cards(NRDB.data.cards.find());

	function findMatches(q, cb) {
		if (q.match(/^\w:/)) { return; }

		var regexp = new RegExp(q, 'i');
		var matchingCards = _.filter(latest_cards, function (card) {
			return regexp.test(_.deburr(card.title).toLowerCase().trim());
		});
		cb(_.sortBy(matchingCards, 'title'));
	}
	$('#top_nav_card_search_menu').on('shown.bs.dropdown', function (element) {
		$("#top_nav_card_search").focus();
	});

	$('#top_nav_card_search').typeahead({
		hint: true,
		highlight: true,
		minLength: 2
	}, {
		display: function(card) { return card.title + ' (' + card.pack.name + ')'; },
		source: findMatches
	});
	$('#top_nav_card_search').on('typeahead:selected typeahead:autocomplete', function(event, data) {
		location.href=Routing.generate('cards_zoom', {card_code:data.code, _locale:NRDB.locale});
	});

$('#top_nav_card_search').keypress(function(event){
                var keycode = (event.keyCode ? event.keyCode : event.which);
                if(keycode == '13'){
			console.log('enter....');
			$('#top_nav_card_search_form').submit();
                }
            });
});
