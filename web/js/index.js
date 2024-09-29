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

$(function() {
  $('#version-popover').popover({
    html : true
  });
});
