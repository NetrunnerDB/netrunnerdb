(function(deck_gallery, $) {
	var images = null;

	deck_gallery.update = function() {

		images = [ Identity.imageUrl ];
		qtys = [ 1 ];
		NRDB.data.cards.find({
			indeck : {
				'$gt' : 0
			},
			type_code : {
				'$ne' : 'identity'
			}
		}, {
			'$orderBy': {
				type_code: 1,
				title: 1
			}
		}).forEach(function(card) {
			images.push(card.imageUrl);
			qtys.push(card.indeck);
		});
		for (var i = 0; i < images.length; i++) {
			var cell = $('<td><div><img data-src="' + images[i] + '" class="lazyload" alt="Card Image"><div>'+qtys[i]+'</div></div></td>');
			$('#deck_gallery tr').append(cell.data('index', i));
		}
	};

})(NRDB.deck_gallery = {}, jQuery);
