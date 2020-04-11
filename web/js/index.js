$(document).on('data.app', function() {
  for (var i = 0; i < Decklist.cards.length; i++) {
    var slot = Decklist.cards[i];
    NRDB.data.cards.update({
      code : slot.card_code
    }, {
      indeck : parseInt(slot.qty, 10)
    });
  }
    if(Decklist.mwl_code) {
        MWL = NRDB.data.mwl.findById(Decklist.mwl_code);
    }
  update_deck();
});

function update_cardsearch_result() {
  $('#card_search_results').empty();
  var query = NRDB.smart_filter.get_query();
  if ($.isEmptyObject(query))
    return;
  var tabindex = 2;
  NRDB.data.cards.apply(window, query).order("title asec").each(
      function(record) {
        $('#card_search_results').append(
            '<tr><td><span class="icon icon-' + record.faction_code
                + ' ' + record.faction_code
                + '"></td><td><a tabindex="'
                + (tabindex++)
                + '" href="'
                + Routing.generate('cards_zoom', {card_code:record.code})
                + '" class="card" data-index="' + record.code
                + '">' + record.title
                + '</a></td><td class="small">'
                + record.pack.name + '</td></tr>');
      });
}

function handle_input_change(event) {
  NRDB.smart_filter.handler($(this).val(), update_cardsearch_result);
}

$(function() {
  $('#version-popover').popover({
    html : true
  });

  $('#card_search_form').on({
    keyup : debounce(handle_input_change, 250)
  });

});
