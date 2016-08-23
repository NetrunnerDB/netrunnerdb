$(document).on('data.app', function() {
	function findMatches(q, cb) {
		if(q.match(/^\w:/)) return;
		var matches = NRDB.data.cards.find({title: new RegExp(q, 'i')}).map(function (card) {
			return { value: card.title };
		});
		cb(matches);
	}
	
	$('#card').typeahead({
		  hint: true,
		  highlight: true,
		  minLength: 3
		},{
		name : 'cardnames',
		displayKey: 'value',
		source: findMatches
	});
});

function handle_checkbox_change() {
	$('#packs-on').text($('#allowed_packs').find('input[type="checkbox"]:checked').length);
	$('#packs-off').text($('#allowed_packs').find('input[type="checkbox"]:not(:checked)').length);
}

$(function() {
	$('#card').on('typeahead:selected typeahead:autocompleted', function(event, data) {
		console.log(data);
		var card = NRDB.data.cards.find({
			title : data.value
		}).pop();
		var line = $('<p class="background-'+card.faction_code+'-20" style="padding: 3px 5px;border-radius: 3px;border: 1px solid silver"><button type="button" class="close" aria-hidden="true">&times;</button><input type="hidden" name="cards[]" value="'+card.code+'">'+
				  card.title + '</p>');
		line.on({
			click: function(event) { line.remove(); }
		});
		line.insertBefore($('#card'));
		$(event.target).typeahead('val', '');
	});
	
	$('#allowed_packs').on('change', handle_checkbox_change);
	
	$('#select_all').on('click', function (event) {
		$('#allowed_packs').find('input[type="checkbox"]:not(:checked)').prop('checked', true);
		handle_checkbox_change();
		return false;
	});
	
	$('#select_none').on('click', function (event) {
		$('#allowed_packs').find('input[type="checkbox"]:checked').prop('checked', false);
		handle_checkbox_change();
		return false;
	});
});
