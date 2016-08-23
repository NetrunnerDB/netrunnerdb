if (typeof NRDB != "object")
	var NRDB = { data_loaded: jQuery.Callbacks() };

NRDB.tip = {};
(function(tip, $) {
	
	var hide_event = 'mouseout';
	
	tip.display = function(event) {
		if($(this).hasClass('no-popup')) return;
		var code = $(this).data('index')
				|| $(this).closest('.card-container').data('index')
				|| ($(this).attr('href') && $(this).attr('href').replace(
						/.*\/card\/(\d\d\d\d\d).*/,
						"$1"));
		var card = NRDB.data.cards.findById(code);
		if (!card) return;
		var type = '<p class="card-info">' + NRDB.format.type(card) + '</p>';
		var influence = '';
		for (var i = 0; i < card.faction_cost; i++)
			influence += "●";
		if (card.strength != null)
			type += '<p>Strength <b>' + card.strength + '</b></p>';
		var image_svg = ''; 
		if($('#nrdb_svg_hex').length && typeof InstallTrigger === 'undefined') {
			// no hexagon for Firefox, bad boy!
			image_svg = '<div class="card-image card-image-'+card.side_code+'-'+card.type_code+'"'+(card.imageUrl ? ' style="background-image:url('+card.imageUrl+')"': '')
			+ '><svg width="103px" height="90px" viewBox="0 0 677 601" xmlns="http://www.w3.org/2000/svg" xmlns:svg="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><mask id="mask"><use xlink:href="#rect" style="fill:white" /><use xlink:href="#hex" style="fill:black"/></mask><use xlink:href="#rect" mask="url(#mask)"/><use xlink:href="#hex" style="stroke:black;fill:none;stroke-width:15" /></svg></div>';
		}
		$(this).qtip(
				{
					content : {
						text : image_svg
								+ '<h4 class="card-title">'
								+ (card.uniqueness ? "&diams; " : "")
								+ card.title + '</h4>' + type
								+ '<div class="card-text border-'+card.faction_code+'">' + NRDB.format.text(card) + '</div>'
								+ '<p class="card-faction" style="text-align:right;clear:right"><span class="influence influence-'+card.faction_code+'">' + influence
								+ '</span> ' + card.faction.name + ' &ndash; ' + card.pack.name + '</p>'
					},
					style : {
						classes : 'qtip-bootstrap qtip-nrdb'
					},
					position : {
						my : 'left center',
						at : 'right center'
					},
					show : {
						event : event.type,
						ready : true,
						solo : true
					},
					hide : {
						event: hide_event
					}
				}, event);
	};

	tip.set_hide_event = function set_hide_event(opt_hide_event) {
		if(opt_hide_event == 'mouseout' || opt_hide_event == 'unfocus') {
			hide_event = opt_hide_event;
		}
	}

	$(document).on('data.app', function() {

		if(!Modernizr.touch) {
			$('body').on({
				mouseover : tip.display,
				focus : tip.display
			}, 'a');
		}

	});

})(NRDB.tip, jQuery);

