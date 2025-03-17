$(document).on('data.app', function() {
  if (Decklist == null) {
    return;
  }
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

  $('#update-log a').click(function (event) {
    event.preventDefault();
    let l = event.currentTarget;
    if (l.text == '(expand)') {
      l.text = '(shrink)';
      $('#update-log tbody').css("max-height", "400px");
    } else {
      l.text = '(expand)';
      $('#update-log tbody').css("max-height", "");
    }
    return false;
  });

  $('#close-updates').click(function () {
    $('#update-log').hide();
  });

});
