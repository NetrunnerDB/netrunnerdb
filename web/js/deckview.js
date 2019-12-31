

$(document).on('data.app', function() {
	var sets_in_deck = {};
	NRDB.data.cards.find().forEach(function(card) {
		var indeck = 0;
		if (SelectedDeck.slots[card.code]) {
			indeck = parseInt(SelectedDeck.slots[card.code], 10);
			sets_in_deck[card.pack_code] = 1;
		}
		NRDB.data.cards.updateById(card.code, {
			indeck : indeck,
			factioncost : card.factioncost || 0
		});
	});

	MWL = SelectedDeck.code && NRDB.data.mwl.findById(SelectedDeck.code);

	update_deck();
	NRDB.draw_simulator.init();
	NRDB.deck_gallery.update();
	
	make_cost_graph();
	make_strength_graph();
});


function do_action_deck(event) {
	var action_id = $(this).attr('id');
	if(!action_id || !SelectedDeck) return;
	switch(action_id) {
		case 'btn-edit': location.href=Routing.generate('deck_edit', {deck_id:SelectedDeck.id}); break;
		case 'btn-publish': confirm_publish(); break;
		case 'btn-delete': confirm_delete(); break;
		case 'btn-download-text': location.href=Routing.generate('deck_export_text', {deck_id:SelectedDeck.id}); break;
		case 'btn-download-octgn': location.href=Routing.generate('deck_export_octgn', {deck_id:SelectedDeck.id}); break;
		case 'btn-print': window.print(); break;
		case 'btn-sort-type': DisplaySort = 'type'; DisplaySortSecondary = null; switch_to_web_view(); break;
		case 'btn-sort-number': DisplaySort = 'number'; DisplaySortSecondary = null; switch_to_web_view(); break;
		case 'btn-sort-faction': DisplaySort = 'faction'; DisplaySortSecondary = null; switch_to_web_view(); break;
		case 'btn-sort-faction-type': DisplaySort = 'faction'; DisplaySortSecondary = 'type'; switch_to_web_view(); break;
		case 'btn-sort-faction-number': DisplaySort = 'faction'; DisplaySortSecondary = 'number'; switch_to_web_view(); break;
		case 'btn-sort-title': DisplaySort = 'title'; DisplaySortSecondary = null; switch_to_web_view(); break;
		case 'btn-display-plain': export_plaintext(); break;
		case 'btn-display-bbcode': export_bbcode(); break;
		case 'btn-display-markdown': export_markdown(); break;
		case 'btn-display-jintekinet': export_jintekinet(); break;
	}
}

$(function() {
	$('#cardModal').on({
		keypress : function(event) {
			var num = parseInt(event.which, 10) - 48;
			$('.modal input[type=radio][value=' + num + ']').trigger('change');
		}
	});

	var converter = new Markdown.Converter();
	$('#description').html(converter.makeHtml(SelectedDeck.description ? SelectedDeck.description : '<i>No description.</i>'));

	$('.btn-actions').on({
		click: do_action_deck
	}, 'button[id],a[id]');
	
	$('#btn-publish').prop('disabled', !!SelectedDeck.problem);

});

function confirm_publish() {
	$('#publish-form-warning').remove();
	$('#btn-publish-submit').text("Checking...").prop('disabled', true);
	$.ajax(Routing.generate('deck_publish', {deck_id:SelectedDeck.id}), {
		success: function( response ) {
			var type = response.allowed ? 'warning' : 'danger';
			if(response.message) {
				$('#publish-decklist-form').prepend('<div id="publish-form-warning" class="alert alert-'+type+'">'+response.message+'</div>');
			}
			if (response.allowed) {
				$('#btn-publish-submit').text("Go").prop('disabled', false);
			}

			var converter = new Markdown.Converter();
			$('#publish-decklist-description-preview').html(converter.makeHtml($('#publish-decklist-description').val()));
			$('#publish-decklist-description').on(
				'keyup',
				function () {
					$('#publish-decklist-description-preview').html(converter.makeHtml($('#publish-decklist-description').val()));
				}
			);

			$('#publish-decklist-description').textcomplete([
				{
					match: /\B#([\-+\w]*)$/,
					search: function (term, callback) {
						var regexp = new RegExp('\\b' + term, 'i');
						callback(NRDB.data.cards.find({
						title: regexp
						}));
					},
					template: function (value) { return value.title; },
					replace: function (value) {
						return '[' + value.title + ']('
							+ Routing.generate('cards_zoom', {card_code: value.code})
							+ ')';
					},
					index: 1
				},
				{
					match: /\$([\-+\w]*)$/,
					search: function (term, callback) {
						var regexp = new RegExp('^' + term);
						callback($.grep(
								// TODO(plural): Extract this out somewhere and insure it has all the right symbols.
								['credit', 'recurring-credit', 'click', 'link', 'trash', 'subroutine', 'mu', '1mu', '2mu', '3mu', 'anarch', 'criminal', 'shaper', 'haas-bioroid', 'weyland-consortium', 'jinteki', 'nbn'],
								function (symbol) { return regexp.test(symbol); }
						));
					},
					template: function (value) { return value; },
					replace: function (value) {
						return '<span class="icon icon-' + value + '"></span>';
					},
					index: 1
				}]);
		},
		error: function( jqXHR, textStatus, errorThrown ) {
			console.log('['+moment().format('YYYY-MM-DD HH:mm:ss')+'] Error on '+this.url, textStatus, errorThrown);
			$('#publish-decklist-form').prepend('<div id="publish-form-alert" class="alert alert-danger">'+jqXHR.responseText+'</div>');
		}
	});
	$('#publish-decklist-name').val(SelectedDeck.name);
	$('#publish-deck-id').val(SelectedDeck.id);
	$('#publish-decklist-description').val(SelectedDeck.description);
	$('#publishModal').modal('show');
}

function confirm_delete() {
	$('#delete-deck-name').text(SelectedDeck.name);
	$('#delete-deck-id').val(SelectedDeck.id);
	$('#deleteModal').modal('show');
}


function switch_to_web_view() {
	$('#deck').html('<div class="row"><div class="col-sm-12"><h3 id="identity"></h3><div id="influence"></div><div id="agendapoints"></div><div id="cardcount"></div><div id="latestpack"></div><div id="restricted"></div><div id="limited"></div></div></div><div class="row" id="deck-content" style="margin-bottom:10px"></div>');
	update_deck();
}
