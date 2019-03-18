(function (format, $) {

    format.cost = function (card) {
        return card.cost === null ? 'X' : card.cost;
    };

    format.type = function (card) {
        var type = '<span class="card-type">' + card.type.name + '</span>';
        if (card.keywords)
            type += '<span class="card-keywords">: ' + card.keywords + '</span>';
        if (card.type_code == "agenda")
            type += ' &middot; <span class="card-prop">' + card.advancement_cost + '/' + card.agenda_points + '</span>';
        if (card.type_code == "identity" && card.side_code == "corp")
            type += ' &middot; <span class="card-prop">' + card.minimum_deck_size + '/' + (card.influence_limit || '&infin;') + '</span>';
        if (card.type_code == "identity" && card.side_code == "runner")
            type += ' &middot; <span class="card-prop">' + card.minimum_deck_size + '/' + (card.influence_limit || '&infin;') + ' ' + card.base_link + '<span class="icon icon-link" aria-hidden="true"></span><span class="icon-fallback">link</span></span>';
        if (card.type_code == "operation" || card.type_code == "event")
            type += ' &middot; <span class="card-prop">' + format.cost(card) + '<span class="icon icon-credit" aria-hidden="true"></span><span class="icon-fallback">credit</span>' + ('trash_cost' in card ? ' ' + card.trash_cost + '<span class="icon icon-trash" aria-hidden="true"></span><span class="icon-fallback">trash</span>' : '') + '</span>';
        if (card.type_code == "resource" || card.type_code == "hardware")
            type += ' &middot; <span class="card-prop">' + format.cost(card) + '<span class="icon icon-credit" aria-hidden="true"></span><span class="icon-fallback">credit</span></span>';
        if (card.type_code == "program")
            type += ' &middot; <span class="card-prop">' + format.cost(card) + '<span class="icon icon-credit" aria-hidden="true"></span><span class="icon-fallback">credit</span> ' + card.memory_cost + '<span class="icon icon-mu" aria-hidden="true"></span><span class="icon-fallback">memory unit</span></span>';
        if (card.type_code == "asset" || card.type_code == "upgrade")
            type += ' &middot; <span class="card-prop">' + format.cost(card) + '<span class="icon icon-credit" aria-hidden="true"></span><span class="icon-fallback">credit</span> ' + card.trash_cost + '<span class="icon icon-trash" aria-hidden="true"></span><span class="icon-fallback">trash</span></span>';
        if (card.type_code == "ice")
            type += ' &middot; <span class="card-prop">' + format.cost(card) + '<span class="icon icon-credit" aria-hidden="true"></span><span class="icon-fallback">credit</span>' + ('trash_cost' in card ? ' ' + card.trash_cost + '<span class="icon icon-trash" aria-hidden="true"></span><span class="icon-fallback">trash</span>' : '') + '</span>';
        return type;
    };

    format.text = function (card) {
        var text = card.text || '';

        text = text.replace(/\[subroutine\]/g, '<span class="icon icon-subroutine" aria-hidden="true"></span><span class="icon-fallback">subroutine</span>');
        text = text.replace(/\[credit\]/g, '<span class="icon icon-credit" aria-hidden="true"></span><span class="icon-fallback">credit</span>');
        text = text.replace(/\[trash\]/g, '<span class="icon icon-trash" aria-hidden="true"></span><span class="icon-fallback">trash</span>');
        text = text.replace(/\[click\]/g, '<span class="icon icon-click" aria-hidden="true"></span><span class="icon-fallback">click</span>');
        text = text.replace(/\[recurring-credit\]/g, '<span class="icon icon-recurring-credit" aria-hidden="true"></span><span class="icon-fallback">recurring credit</span>');
        text = text.replace(/\[mu\]/g, '<span class="icon icon-mu" aria-hidden="true"></span><span class="icon-fallback">memory unit</span>');
        text = text.replace(/\[link\]/g, '<span class="icon icon-link" aria-hidden="true"></span><span class="icon-fallback">link</span>');
        text = text.replace(/\[anarch\]/g, '<span class="icon icon-anarch" aria-hidden="true"></span><span class="icon-fallback">anarch</span>');
        text = text.replace(/\[criminal\]/g, '<span class="icon icon-criminal" aria-hidden="true"></span><span class="icon-fallback">criminal</span>');
        text = text.replace(/\[shaper\]/g, '<span class="icon icon-shaper" aria-hidden="true"></span><span class="icon-fallback">shaper</span>');
        text = text.replace(/\[jinteki\]/g, '<span class="icon icon-jinteki" aria-hidden="true"></span><span class="icon-fallback">jinteki</span>');
        text = text.replace(/\[haas-bioroid\]/g, '<span class="icon icon-haas-bioroid" aria-hidden="true"></span><span class="icon-fallback">haas bioroid</span>');
        text = text.replace(/\[nbn\]/g, '<span class="icon icon-nbn" aria-hidden="true"></span><span class="icon-fallback">nbn</span>');
        text = text.replace(/\[weyland-consortium\]/g, '<span class="icon icon-weyland-consortium" aria-hidden="true"></span><span class="icon-fallback">weyland consortium</span>');

        text = text.replace(/<trace>([^<]+) ([X\d]+)<\/trace>/g, '<strong>$1 [$2]</strong>–');
        text = text.replace(/<errata>(.+)<\/errata>/, '<em><span class="glyphicon glyphicon-alert"></span> $1</em>');

        text = text.split("\n").join('</p><p>');

        return '<p>' + text + '</p>';
    };

})(NRDB.format = {}, jQuery);
