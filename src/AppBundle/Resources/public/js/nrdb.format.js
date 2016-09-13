(function(format, $) {

	format.type = function(card) {
		var type = '<span class="card-type">'+card.type.name+'</span>';
		if(card.keywords) type += '<span class="card-keywords">: '+card.keywords+'</span>';
		if(card.type_code == "agenda") type += ' &middot; <span class="card-prop">'+card.advancement_cost+'/'+card.agenda_points+'</span>';
		if(card.type_code == "identity" && card.side_code == "corp") type += ' &middot; <span class="card-prop">'+card.minimum_deck_size+'/'+(card.influence_limit || '&infin;')+'</span>';
		if(card.type_code == "identity" && card.side_code == "runner") type += ' &middot; <span class="card-prop">'+card.minimum_deck_size+'/'+(card.influence_limit || '&infin;')+' '+card.base_link+'<span class="icon icon-link"></span></span>';
		if(card.type_code == "operation" || card.type_code == "event") type += ' &middot; <span class="card-prop">'+card.cost+'<span class="icon icon-credit"></span>'+('trash' in card ? ' '+card.trash_cost+'<span class="icon icon-trash">' : '')+'</span>';
		if(card.type_code == "resource" || card.type_code == "hardware") type += ' &middot; <span class="card-prop">'+card.cost+'<span class="icon icon-credit"></span></span>';
		if(card.type_code == "program") type += ' &middot; <span class="card-prop">'+card.cost+'<span class="icon icon-credit"></span> '+card.memory_cost+'<span class="icon icon-mu"></span></span>';
		if(card.type_code == "asset" || card.type_code == "upgrade") type += ' &middot; <span class="card-prop">'+card.cost+'<span class="icon icon-credit"></span> '+card.trash_cost+'<span class="icon icon-trash"></span></span>';
		if(card.type_code == "ice") type += ' &middot; <span class="card-prop">'+card.cost+'<span class="icon icon-credit"></span>'+('trash' in card ? ' '+card.trash_cost+'<span class="icon icon-trash">' : '')+'</span></span>';
		return type;
	};

	format.text = function(card) {
		var text = card.text || '';

		text = text.replace(/\[subroutine\]/g, '<span class="icon icon-subroutine"></span>');
		text = text.replace(/\[credit\]/g, '<span class="icon icon-credit"></span>');
		text = text.replace(/\[trash\]/g, '<span class="icon icon-trash"></span>');
		text = text.replace(/\[click\]/g, '<span class="icon icon-click"></span>');
		text = text.replace(/\[recurring-credit\]/g, '<span class="icon icon-recurring-credit"></span>');
		text = text.replace(/\[mu\]/g, '<span class="icon icon-mu"></span>');
		text = text.replace(/\[link\]/g, '<span class="icon icon-link"></span>');
		text = text.replace(/\[anarch\]/g, '<span class="icon icon-anarch"></span>');
		text = text.replace(/\[criminal\]/g, '<span class="icon icon-criminal"></span>');
		text = text.replace(/\[shaper\]/g, '<span class="icon icon-shaper"></span>');
		text = text.replace(/\[jinteki\]/g, '<span class="icon icon-jinteki"></span>');
		text = text.replace(/\[haas-bioroid\]/g, '<span class="icon icon-haas-bioroid"></span>');
		text = text.replace(/\[nbn\]/g, '<span class="icon icon-nbn"></span>');
		text = text.replace(/\[weyland-consortium\]/g, '<span class="icon icon-weyland-consortium"></span>');
		
		text = text.replace(/<trace>([^<]+) ([X\d]+)<\/trace>/g, '<strong>$1<sup>$2</sup></strong>–');
		text = text.replace(/<errata>(.+)<\/errata>/, '<em><span class="glyphicon glyphicon-alert"></span> $1</em>');
		
		text = text.split("\n").join('</p><p>');
		
		return '<p>'+text+'</p>';
	};

})(NRDB.format = {}, jQuery);
