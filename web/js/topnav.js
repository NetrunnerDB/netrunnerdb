/* global NRDB, Promise, _ */

Promise.all([NRDB.data.promise, NRDB.ui.promise]).then(function() {
  var all_cards = NRDB.data.cards.find();

  function findMatches(q, cb) {
    if (q.match(/^\w:/)) { return; }

    var regexp = new RegExp(q, 'i');
    var matchingCards = _.filter(all_cards, function (card) {
      return regexp.test(_.deburr(card.title).toLowerCase().trim());
    });
    matchingCards.sort((card1, card2) => {
        if(card1.title.startsWith(q) && !card2.title.startsWith(q)) {
            return -1;
        }
        if(card2.title.startsWith(q) && !card1.title.startsWith(q)) {
            return 1;
        }
        return card1.title < card2.title ? -1 : 1;
    });
    cb(matchingCards);
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

  $('#top_nav_card_search').keypress(function(event) {
    var keycode = (event.keyCode ? event.keyCode : event.which);
    if(keycode == '13'){
      $('#top_nav_card_search_form').submit();
    }
  });
});
