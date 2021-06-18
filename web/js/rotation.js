Promise.all([NRDB.data.promise]).then(function() {
  $('div.checkbox').each(function() {
    $(this).checklist();
  });

  // set up the filter bars
  $('#faction_code').empty();
  $('#type_code').empty();

  let addTypeButton = function(type) {
    let label = $('<label class="btn btn-default btn-sm" data-code="'
        + type.code + '" title="'+type.name+'"><input type="checkbox" name="' + type.code
        + '"><img src="' + Url_TypeImage.replace('xxx', type.code)
        + '" style="height:12px" alt="'+type.code+'"></label>');
    label.tooltip({container: 'body'});
    $('#type_code').append(label);
  };
  addTypeButton({code: 'identity', name: 'Identity'});

  ['corp', 'runner'].forEach(Side => {
    var factions = NRDB.data.factions.find({side_code: Side}).sort(function(a, b) {
    return b.code.substr(0,7) === "neutral"
      ? -1
      : a.code.substr(0,7) === "neutral"
      ? 1
      : a.code.localeCompare(b.code);
    });
    factions.forEach(function(faction) {
      var label = $('<label class="btn btn-default btn-sm" data-code="' + faction.code
          + '" title="'+faction.name+'"><input type="checkbox" name="' + faction.code
          + '"><img src="'
          + Url_FactionImage.replace('xxx', faction.code)
          + '" style="height:12px" alt="'+faction.code+'"></label>');
      label.tooltip({container: 'body'});
      $('#faction_code').append(label);
    });

    $('#faction_code').button();

    var types = NRDB.data.types.find({
        is_subtype:false,
        '$or': [{side_code: Side }, {side_code: null}]
    }).sort();
    types.forEach(function(type) {
      if (type.code !== 'identity') {
        addTypeButton(type);
      }
    });
  });

  $('#faction_code').children('label').each(function(index, elt) {
    $(elt).addClass('active');
  });
  $('#type_code').children('label').each(function(index, elt) {
    $(elt).addClass('active');
  });

  function check_all_inactive() {
    $(this).closest(".filter").find("label:not(.active)").button('toggle');
  }

  var update_filter = debounce(update_filtered, 250);
  $('#faction_code').on('click', 'label', update_filter);
  $('#type_code').on('click', 'label', update_filter);
  
  var validPacks = new Array();
  validPacks['rotation_a'] = new Array();
  validPacks['rotation_b'] = new Array();
  
  var cards = new Array();
  cards['rotation_a'] = new Array();
  cards['rotation_b'] = new Array();
  
  var diffs = [];
  // Keep a set of all cards around for our sorter to use for attribute lookup.
  var allCards = new Array();
  _.sortBy(NRDB.data.cards.find(), 'code').reverse().forEach(card => {
    if (card.title in allCards === false) {
      allCards[card.title] = card;
    }
  });

  var factions = [];
  var types = [];

  function update_filtered(e) {
    factions = [];
    $('#faction_code').children('.active').each(function() {
      factions.push($(this).attr('data-code'));
    });
    types = [];
    $('#type_code').children('.active').each(function() {
      types.push($(this).attr('data-code'));
    });
  
    $('#diffs').children('div').each(function() {
      let visible = (factions.includes($(this).attr('data-faction'))) && (types.includes($(this).attr('data-type'))); 
      $(this).css('display', visible ? 'block' : 'none');
    });
  }

  function displayDiffs() {
    Object.keys(cards).forEach(function(rotation) {
        cards[rotation] = new Array();
    validPacks[rotation] = new Array();
      $('input[name="' + rotation + '_pack"]:checked').each(function() {
        validPacks[rotation].push($(this).val());
      });
        _.sortBy(NRDB.data.cards.find({pack_code: validPacks[rotation]}), 'code').reverse().forEach(card => {
          if (card.title in cards[rotation] === false) {
              cards[rotation][card.title] = card;
          }
        });
    });
  
    let a = Object.keys(cards['rotation_a']);
    let b = Object.keys(cards['rotation_b']);
  
    // Sort by Side, Faction, type (identity forced first), title
    let sorter = function(a, b) {
      if (a.side.code < b.side.code) return -1;
      if (a.side.code > b.side.code) return 1;
  
    let factionA = (a.faction.code == 'neutral-corp' || a.faction.code == 'neutral-runner') ? a.faction.code : 'z' + a.faction.code;
    let factionB = (b.faction.code == 'neutral-corp' || b.faction.code == 'neutral-runner') ? b.faction.code : 'z' + b.faction.code;
      if (factionA < factionB) return -1;
      if (factionA > factionB) return 1;
  
    // sort identity first to match button layout
    let typeA = (a.type.code == 'identity') ? a.type.code : 'z' + a.type.code;
    let typeB = (b.type.code == 'identity') ? b.type.code : 'z' + b.type.code;
    
    if (typeA < typeB) return -1;
      if (typeA > typeB) return 1;
      
      if (a.title < b.title) return -1;
      if (a.title > b.title) return 1;
  
      return 0;
    }
  
    diffs = [];
  
    $('#diffs').empty();
  
    _.difference(a, b).forEach(title => {
      let card = allCards[title];
      card['diff'] = 'banned';
      diffs.push(card);
    });
    _.difference(b, a).forEach(title => {
      let card = allCards[title];
      card['diff'] = 'legal';
      diffs.push(card);
    });
    diffs = diffs.sort(sorter);
  
    diffs.forEach(card => {
      let visible = false;
      $('#diffs').append(
        $('<div style="display:' + (visible ? 'block' : 'none') + '" data-title="' + card.title.replaceAll('"', '') + '" data-faction="' + card.faction.code + '" data-type="' + card.type.code + '">' +
            '<span class="icon icon-' + card.faction.code + ' influence-' + card.faction.code + '"></span>' + 
            ' <img src="' + Url_TypeImage.replace('xxx', card.type.code) + '" style="height:12px" alt="'+card.type.code+'">' +
            ' <a href="' + Routing.generate('cards_zoom', {card_code:card.code}) + '">' + card.title + '</a> <span class="legality-' + card['diff'] + '"></span></div>')
      );
    });
    update_filter();
  }

  function handleRotationChange(e) {
    showLegalCyclesAndPacks(e);
    displayDiffs();
  }

  function handlePackChange(e) {
    displayDiffs();
  }

  function showLegalCyclesAndPacks(e) {
    if (e.target.value === '') {
      return;
    }
    let container = $('#' + e.target.id + '_cycles_and_packs');

    validPacks[e.target.id] = new Array();
    let rotatedCycles = rotations[e.target.value]['rotated_cycles'];
    _.sortBy(NRDB.data.cycles.find(), 'position').reverse().forEach(function (cycle) {
      var packs = _.sortBy(NRDB.data.packs.find({cycle_code:cycle.code}), 'position').reverse();
      if (cycle.code !== 'draft' && !(cycle.code in rotatedCycles)) {    
        packs.forEach(function (pack) {
          // Terminal Directive Campaign cards are irrelevant for rotation purposes.
          if (pack.code != 'tdc') {
            validPacks[e.target.id].push(pack.code);
          }
        });
      }
    });
    container.find(':checkbox').each(function() {
      $(this).prop('checked', (this.name == e.target.id + '_cycle') ? !Boolean(rotatedCycles[$(this).val()]) : validPacks[e.target.id].includes($(this).val())  );
    });
  }

  // Set up event handlers.
  $('.cycle_checkbox').click(handlePackChange);
  $('.pack_checkbox').click(handlePackChange);
  $('#rotation_a').change(handleRotationChange);
  $('#rotation_b').change(handleRotationChange);

  // Default to most recent rotation.
  $('#rotation_a').prop('selectedIndex', 2).trigger('change');
  $('#rotation_b').prop('selectedIndex', 1).trigger('change');

  // Wire up All / None / Corp / Runner shortcuts
  $('#all').on('click', function(event) {
    event.preventDefault();
    $('.btn').each(function() { $(this).addClass('active'); });
    $('.btn').each(function() { $(this).addClass('active'); });
    update_filter();
  });

  $('#none').on('click', function(event) {
    event.preventDefault();
    $('.btn').each(function() { $(this).removeClass('active'); });
    $('.btn').each(function() { $(this).removeClass('active'); });
    update_filter();
  });

  let only_side = function(event, side) {
    event.preventDefault();
    var factions = NRDB.data.factions.find({side_code: side}).sort(function(a, b) {
        return b.code.substr(0,7) === "neutral"
            ? -1
            : a.code.substr(0,7) === "neutral"
            ? 1
            : a.code.localeCompare(b.code);
    }).map(f => f.code);

    $('#faction_code').children('label').each(function() {
      if (factions.includes($(this).attr('data-code'))) {
        $(this).addClass('active');
      } else {
        $(this).removeClass('active');
      }
    });
    var types = NRDB.data.types.find({
        is_subtype:false,
        '$or': [{side_code: side}, {side_code: null}]
    }).map(t => t.code);
    $('#type_code').children('label').each(function() {
      if (types.includes($(this).attr('data-code'))) {
        $(this).addClass('active');
      } else {
        $(this).removeClass('active');
      }
    });
    update_filter();
  };

  let only_side_types = function(event, side) {
    event.preventDefault();
    var types = NRDB.data.types.find({
        is_subtype:false,
        '$or': [{side_code: side}, {side_code: null}]
    }).map(t => t.code);
    $('#type_code').children('label').each(function() {
      if (types.includes($(this).attr('data-code'))) {
        $(this).addClass('active');
      } else {
        $(this).removeClass('active');
      }
    });
    update_filter();
  };

  $('#only_corp').on('click', function(event) {
    only_side(event, 'corp');
  });
  $('#only_corp_types').on('click', function(event) {
    only_side_types(event, 'corp');
  });
  $('#only_runner').on('click', function(event) {
    only_side(event, 'runner');
  });
  $('#only_runner_types').on('click', function(event) {
    only_side_types(event, 'runner');
  });

  let cardpoolShowHide = function(event, element, pool) {
    event.preventDefault();
    element.text(element.text() === 'hide' ? 'show' : 'hide');
    pool.toggle();
  };

  $('#show_hide_a').on('click', function(event) {
    cardpoolShowHide(event, $(this), $('#card_pool_a'));
  });
  $('#show_hide_b').on('click', function(event) {
    cardpoolShowHide(event, $(this), $('#card_pool_b'));
  });
});
