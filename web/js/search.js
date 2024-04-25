$(document).on('data.app', function() {
  var latestCards = select_only_latest_cards(NRDB.data.cards.find());

  function findMatches(q, cb) {
    if (q.match(/^\w:/)) { return; }

    var regexp = new RegExp(q, 'i');
    function normalizeTitle(cardTitle) {
      return _.deburr(cardTitle).toLowerCase().trim();
    }
    var matchingCards = _.filter(latestCards, function (card) {
      return regexp.test(normalizeTitle(card.stripped_title));
    });
    matchingCards.sort((card1, card2) => {
        var card1title = normalizeTitle(card1.title);
        var card2title = normalizeTitle(card2.title);
        var normalizedQuery = normalizeTitle(q);
        if(card1title.startsWith(normalizedQuery) && !card2title.startsWith(normalizedQuery)) {
            return -1;
        }
        if(card2title.startsWith(normalizedQuery) && !card1title.startsWith(normalizedQuery)) {
            return 1;
        }
        return card1.title < card2.title ? -1 : 1;
    });
    cb(matchingCards);
  }

  $('#filter-text').typeahead({
    hint: true,
    highlight: true,
    minLength: 2
  }, {
    name: 'cardnames',
    display: function(card) { return card.title; },
    source: findMatches
  });

  $('.cycle_checkbox').each(function() {
    $(this).closest('.checkbox').checklist();
  });
  handle_checkbox_change();

  $('#filter-text').on('typeahead:selected typeahead:autocompleted', function(event, card) {
    var line = $(
      '<p class="background-' + card.faction_code +
      '-20" style="padding: 3px 5px;border-radius: 3px;border: 1px solid silver">' +
      '<button type="button" class="close" aria-hidden="true">&times;</button>' +
      '<input type="hidden" name="cards[]" value="' + card.code + '">' +
      card.title + ' (' + card.pack.name + ')</p>'
    );
    line.on({
      click: function(event) { line.remove(); }
    });
    line.insertBefore($('#filter-text'));
    $(event.target).typeahead('val', '');
  });

  $('#allowed_packs').on('change', handle_checkbox_change);
  $('#toggle_show_packs').on('click', function(event) {
    event.preventDefault();
    var hidden = $('#allowed_packs').is(":visible");
    $(this).text(hidden ? "Show card packs" : "Hide card packs");
  });

  let rotated_cycles = Array();
  rotated_cycles['draft'] = 1;
  rotated_cycles['napd'] = 1;
  NRDB.data.cycles.find( { "rotated": true } ).forEach(function(cycle) { rotated_cycles[cycle.code] = 1; });

  var startup_cycles = Array(); // Hardcoded Startup Codes
  startup_cycles['system-gateway'] = 1;
  startup_cycles['system-update-2021'] = 1;
  startup_cycles['liberation'] = 1;
  var nsg_cycles = Array(); // Hardcoded NSG Codes
  nsg_cycles['system-gateway'] = 1;
  nsg_cycles['system-update-2021'] = 1;
  nsg_cycles['ashes'] = 1;
  nsg_cycles['borealis'] = 1;
  nsg_cycles['liberation'] = 1;
  var rotated_packs = Array();
  var startup_packs = Array();
  var nsg_packs = Array();
  NRDB.data.packs.find().forEach(function(pack) {
    if (rotated_cycles[pack.cycle.code]) { rotated_packs[pack.code] = 1; }
    if (startup_cycles[pack.cycle.code]) { startup_packs[pack.code] = 1; }
    if (nsg_cycles[pack.cycle.code]) { nsg_packs[pack.code] = 1; }
  });

  $('#select_startup').on('click', function (event) {
    $('#allowed_packs').find('input[type="checkbox"]').each(function() {
      $(this).prop('checked', Boolean(startup_cycles[this.value] || startup_packs[this.value]));
    });
    handle_checkbox_change();
    return false;
  });

  $('#select_nsg').on('click', function (event) {
    $('#allowed_packs').find('input[type="checkbox"]').each(function() {
      $(this).prop('checked', Boolean(nsg_cycles[this.value] || nsg_packs[this.value]));
    });
    handle_checkbox_change();
    return false;
  });

  $('#select_standard').on('click', function (event) {
    $('#allowed_packs').find('input[type="checkbox"]').each(function() {
      $(this).prop('checked', Boolean(!(rotated_cycles[this.value] || rotated_packs[this.value])));
    });
    handle_checkbox_change();
    return false;
  });

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

function select_only_latest_cards(matchingCards) {
  var latestCardsByTitle = {};
  for (var card of matchingCards) {
    var latestCard = latestCardsByTitle[card.title];
    if (!latestCard || card.code > latestCard.code) {
      latestCardsByTitle[card.title] = card;
    }
  }
  return matchingCards.filter(function(value, index, arr) {
    return value.code == latestCardsByTitle[value.title].code;
  });
}

function handle_checkbox_change() {
  $('#packs-on').text($('#allowed_packs').find('input[type="checkbox"]:not(.cycle_checkbox):checked').length + ' on / ');
  $('#packs-off').text($('#allowed_packs').find('input[type="checkbox"]:not(.cycle_checkbox):not(:checked)').length + ' off');
}
