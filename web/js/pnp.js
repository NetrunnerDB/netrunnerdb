// Potential enhancements:
// Card preview are interactable to add or remove cards or select printings.
// Hover over card names to see printing. Hover over card preview to select printing.
// Options: Bleed
// Imported list sorting options
// Drag to reorder imported list.
// Deselect specific sets from print (for users who own sets and only care about printing certain sets).
// Decklist view...?

/* {code: [side2_url, side3_url, ...]
 * Does not include front side.
 *
 * Umm, I was going to populate this by making an APIv3 call but I literally cannot
 * find a query that would let me search for identities printed >=2019-03-18 (downfall).
 * So I'm hardcoding this until that is figured out
 * - Lia
 * */
var multi_side_cards = {
  "26066" : ["https://card-images.netrunnerdb.com/v2/large/26066-0.jpg"], // Hoshiko
  "26120" : ["https://card-images.netrunnerdb.com/v2/large/26120-0.jpg"], // Earth Station
  "35023" : ["https://card-images.netrunnerdb.com/v2/large/35023-0.jpg"], // Dewi
  "35057" : ["https://card-images.netrunnerdb.com/v2/large/35057-0.jpg"], // Nebula
};

Promise.all([NRDB.data.promise, NRDB.ui.promise]).then( () => {
  $('#btn-import').prop('disabled', false);
  $('#analyzed').on({
    click: click_option
  }, 'ul.dropdown-menu a');
  $('#analyzed').on({
    click: click_trash
  }, 'a.glyphicon-trash');
  $('#analyzed').on({
    change: on_number_change,
  }, 'input.pnp-number-input');

  /* Typeahead for card search box.
   * LIA TODO: Refactor this duplicate typeahead code from topnav.js and deck.v2.js */
  var card_pool = filter_for_nsg(NRDB.data.cards.find());
  function findMatches(q, cb) {
    if (q.match(/^\w:/)) { return; }

    var regexp = new RegExp(q, 'i');
    function normalizeTitle(cardTitle) {
      return _.deburr(cardTitle).toLowerCase().trim();
    }
    var matchingCards = _.filter(card_pool, function (card) {
      return regexp.test(normalizeTitle(card.stripped_title));
    });
    matchingCards.sort((card1, card2) => {
        var card1title = normalizeTitle(card1.stripped_title);
        var card2title = normalizeTitle(card2.stripped_title);
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
    return matchingCards;
  }
  $('#pnp-card-search').typeahead({
    hint: true,
    highlight: true,
    minLength: 2
  }, {
    display: function(card) { return card.title + ' (' + card.pack.name + ')'; },
    source: findMatches
  });
  $('#pnp-card-search').on('typeahead:selected typeahead:autocomplete', function(event, data) {
    imported_cards.add_card(data, 1, {prepend: true});
    update_imported_list();
    preview_cards();
    update_stats();
    setTimeout(() => {$('#pnp-card-search').typeahead("val", "");}, 10);
  });
  $('#pnp-card-search').keypress(function(event) {
    var keycode = (event.keyCode ? event.keyCode : event.which);
    if(keycode == '13'){
      match = findMatches($(event.target).typeahead("val"), (_) => {})[0];
      if(!match) {
        return;
      }
      imported_cards.add_card(match, 1, {prepend: true});
      update_imported_list();
      preview_cards();
      update_stats();
      $('#pnp-card-search').typeahead("val", "");
    }
  });

  // For routes with a deck code, automatically import it.
  if (document.querySelector('#pnp-text-area').value.trim() != '') {
    do_import_pnp(/* first_time */ true);
  }
});

function do_import_pnp(first_time=false) {
  import_cards(first_time);
  update_stats();
  preview_cards();
}

function do_clear() {
  if(!window.confirm("Are you sure you want to clear all cards?")) {
    return;
  }
  imported_cards.clear_all();
  update_imported_list();
  update_stats();
  preview_cards();
}

function click_option(event) {
  var code = $(this).data('code');
  var elem = $(this).closest('li.list-group-item');
  var index = elem.children("input")[0].name;
  imported_cards.select_card(index, code);
  update_imported_list();
  preview_cards();
}

function click_trash(event) {
  var elem = $(this).closest('li.list-group-item');
  if(elem[0].classList.contains('text-danger')) {
    imported_cards.remove_error(elem[0].textContent.trim());
  } else {
    let index = elem.children("input")[0].name;
    imported_cards.remove(index);
  }
  $(this).closest('li.list-group-item').remove();
  update_imported_list();
  preview_cards();
  update_stats();
}

function on_number_change(event) {
  /* Number input for card entries */
  var elem = $(this).closest('li.list-group-item');
  var data_elem = elem.children("input")[0];
  var index = data_elem.name;
  var value = Number(event.target.value);

  value = value < 1? 1: value;
  var [code, _] = data_elem.value.split(':');
  data_elem.value = `${code}:${value}`;
  imported_cards.change_qty(index, value);
  update_imported_list();
  preview_cards();
  update_stats();
}

var imported_cards = {};
(function(imported_card) {
  /* Object that contains every imported line entries.
   *
   * Relevant data structures:
   * entries = [entry, ...];
   * entry = {
   *     index: int,  // index != cards[index]. Use get_card(index).
   *     qty: int,
   *     fuzzy: true|false,
   *     options: [],  // For matches, list of different printings, for fuzzied,
   *                   // list of other options
   *     selected_option: options[0],
   *};
   * */
  imported_cards.entries = [];
  imported_cards.errors = [];
  imported_cards.curr_index = 0;

  imported_cards.prepend = function(entry){
    entry.index = imported_cards.curr_index++;
    imported_cards.entries.unshift(entry);
  }

  imported_cards.append = function(entry){
    entry.index = imported_cards.curr_index++;
    imported_cards.entries.push(entry);
  }

  imported_cards.add_card = function(card, qty, {fuzzy=false, prepend=false} = {}) {
    // Directly add a card as a new entry, assuming no options.
    entry = {
      qty: qty,
      fuzzy: fuzzy,
      options: [card],
      selected_option: card
    }
    if(prepend) {
      imported_cards.prepend(entry);
    } else {
      imported_cards.append(entry);
    }
  }

  imported_cards.get_matches = function() {
    return imported_cards.entries.filter((entry) => {
      return !entry.fuzzy;
    });
  }

  imported_cards.get_fuzzies = function() {
    return imported_cards.entries.filter((entry) => {
      return entry.fuzzy;
    });
  }

  imported_cards.remove = function(index) {
    imported_cards.entries = imported_cards.entries.filter(item => item.index != index);
  }

  imported_cards.get_entry = function(index) {
    return imported_cards.entries.filter((entry) => {
        return entry.index == index;
    })[0];
  }

  imported_cards.select_card = function(index, code) {
    var selection = imported_cards.get_entry(index).options.filter((entry) => {
     return entry.code == code;
    }) [0];
    if(selection) {
      imported_cards.get_entry(index).selected_option = selection;
    }
    // else: don't do anything.
  }

  imported_cards.change_qty = function(index, qty) {
    imported_cards.get_entry(index).qty = qty;
  }

  imported_cards.remove_error = function(error) {
    imported_cards.errors = imported_cards.errors.filter(item => item !== error);
  }

  imported_cards.sort_by_type = function() {
    /* Sort by type, prioritizing identities */
    imported_cards.entries.sort((e1, e2) => {
      o1 = e1.selected_option;
      o2 = e2.selected_option;
      if(o1.type.code == o2.type.code) {
        return o1.title === o2.title? 0: o1.title > o2.title? 1 : -1;
      }
      else if(o1.type.code == "identity") return -1;
      else if(o2.type.code == "identity") return 1;
      else return o1.type.code > o2.type.code? 1 : -1;
    });
  }
  
  imported_cards.clear_all = function() {
    imported_cards.entries = [];
    imported_cards.errors = [];
  }

}) (imported_cards);

function filter_for_nsg(cards) {
  return cards.filter(card => {
    return new Date(card.pack.date_release) >= new Date('2019-03-18')  // Downfall
        && card.pack.name != "Magnum Opus Reprint"
        && card.pack.name != "System Update 2021"
        && card.pack.name != "Salvaged Memories"
  });
}

function find_by_title (title, case_sensitive = true) {
  // Sort: newest first.
  var ret;
  if(case_sensitive) {
    ret = NRDB.data.cards.find({title : { "$eq": title }},
                               {"$orderBy" : {"pack.date_release":-1}});
  } else {
    ret = NRDB.data.cards.find({title : new RegExp(`^${title}$`, "i")},
                               {"$orderBy" : {"pack.date_release":-1}});
  }
  ret = filter_for_nsg(ret);
  return ret;
}

function build_one_line(imported_entry) {
  /* Build one line for imported list from an entry from imported_cards. */
  var card = imported_entry.selected_option;
  var options = imported_entry.options;
  var qty_int = imported_entry.qty;
  var index = imported_entry.index;
  var elem = $(`<li class="list-group-item form-inline"><a class="pull-right glyphicon glyphicon-trash"></a></li>`);
  elem.append(`<input class="pnp-data" type="hidden" name="${index}" value="${card.code}:${qty_int}">`);
  elem.append(`<input type="number" class="form-control pnp-number-input" placeholder="${qty_int}">`);
  elem.append(' x ');
  var a = $(`<a class="card" data-code="${card.code}" href="javascript:void(0)">${card.title} </a>`);
  if(imported_entry.fuzzy) {
    a[0].classList.add("text-warning");
  }
  if(options.length > 1) {
    a[0].classList.add("dropdown-toggle");
    a[0].dataset.toggle = "dropdown";
    a.append('<span class="caret"></span>');
    let dropdown = $(`<ul class="dropdown-menu"></ul>`);
    $.each(options, function (index, option) {
      dropdown.append(`<li><a href="javascript:void(0)" data-code="${option.code}">
                        ${option.title} (${option.pack.name})
                       </a></li>`);
    });
    a = a.add(dropdown);
  }
  elem.append(a);
  return elem;
}

function update_imported_list() {
  /* Build the entire imported list */
  $('#analyzed').empty();
  var label_elem = $("<label class='list-group-label'></label>");
  if(imported_cards.errors.length > 0) {
    let e = label_elem.clone();
    e.html("Errors");
    $('#analyzed').append(e);
    for(let l of imported_cards.errors) {
      $('#analyzed').append(
        `<li class="list-group-item text-danger">
          ${l}
          <a class="pull-right glyphicon glyphicon-trash"></a>
        </li>`
      )
    }
  }
  var fuzzies = imported_cards.get_fuzzies();
  if(fuzzies.length > 0){
    let e = label_elem.clone();
    e.html("Inexact matches (click to see options)");
    $('#analyzed').append(e);
    for(let l of fuzzies) {
      $('#analyzed').append(build_one_line(l));
    }
  }
  var matches = imported_cards.get_matches();
  if(matches.length > 0) {
    let e = label_elem.clone();
    e.html("Matches");
    $('#analyzed').append(e);
    for(let l of matches) {
      $('#analyzed').append(build_one_line(l));
    }
  }
}

function import_one_line(line) {
  /* Parse one line from the import textarea */
  var qty = null;
  var name = null;
  if(line.match(/^(\d*)x?\s*(.*)/)) {
    qty = Number(RegExp.$1);
    name = RegExp.$2;
  } else {
    return null;  // Should be impossible...
  }
  if(qty == null || qty == 0) {
    qty = 1;
  }
  var result = find_by_title(name, false);  // Case insensitive
  var ret = {
      index: null,
      qty: qty,
      fuzzy: false,
      options: result,
      selected_option: result[0],
  };
  if(!result || !result.length) {
    result = NRDB.fuzzy_search.lookup(name);
    if(!result || !result.cards || !result.cards.length) return null;
    let options = filter_for_nsg(result.cards);
    if(!options || !options.length) return null;
    ret.fuzzy = true;
    ret.options = options;
    ret.selected_option = options[0];
  }
  return ret;
}

function import_cards(first_time=false) {
  /* Parse the entire import textarea, creating imported_cards entries */
  var errors = [];
  var content = $('textarea[name="content"]').val();
  var lines = content.split(/[\r\n]+/);
  for(let i = 0; i < lines.length; i++) {
    var imported_entry = import_one_line(lines[i]);

    if(imported_entry == null) {
      imported_cards.errors.push(lines[i]);
    } else {
      // if imported list is not empty, we add to the top of the list
      // so that it feels more responsive for user.
      imported_cards.prepend(imported_entry);
    }
  }
  if(first_time) {
    imported_cards.sort_by_type();
  }
  update_imported_list();
  // Clear the imported textarea.
  $('#pnp-text-area').val("");
}


function update_stats() {
  var deck = {}, size = 0, types = {};
  var cards = retrieve_cards();
  for (card of cards) {
    var code = card.data.code, qty = card.qty;
    deck[code] = qty;
    types[card.data.type.name] = types[card.data.type.name] || 0;
    types[card.data.type.name] += qty;
  }
  var html = '';
  $.each(types, function (key, value) {
    size+=value;
    key = key == "Identity"? "Identities" : key + 's';
    html += value+' '+key+'<br>';
  });
  html = Math.ceil(size/9) + ' Pages<hr style="width:7em;margin-left:0;">' + html;
  html = size+' Cards<br>'+html;
  $('#stats').html(html);
  if($('#analyzed li').length > 0) {
    $('#btn-print').prop('disabled', false);
  } else {
    $('#btn-print').prop('disabled', true);
  }
}

function retrieve_cards() {
  /* Retrieve cards for printing in imported list order.
   * Returns:
   * cards = [{ data: card db entry, qty: int, image_url: string }, ...]
   * */
  let cards = [];

  var fuzzies = imported_cards.get_fuzzies();
  if(fuzzies.length > 0) {
    for(let entry of fuzzies) {
      cards.push({
        data: entry.selected_option,
        qty: entry.qty,
        image_url: entry.selected_option.imageUrl,
      });

      if(entry.selected_option.code in multi_side_cards) {
        for(side_url of multi_side_cards[entry.selected_option.code]) {
          cards.push({
            data: entry.selected_option,
            qty: entry.qty,
            image_url: side_url
          });
        }
      }
    }
  }

  var matches = imported_cards.get_matches();
  if(matches.length > 0) {
    for(let entry of matches) {
      cards.push({
        data: entry.selected_option,
        qty: entry.qty,
        image_url: entry.selected_option.imageUrl,
      });

      if(entry.selected_option.code in multi_side_cards) {
        for(side_url of multi_side_cards[entry.selected_option.code]) {
          cards.push({
            data: entry.selected_option,
            qty: entry.qty,
            image_url: side_url
          });
        }
      }
    }
  }
  return cards;
}

function preview_cards() {
  /* Build the print preview images area */
  var cards = retrieve_cards();
  var curr_index = 0;
  $("#preview-container").empty();
  for(let code in cards) {
    for(let i = 0; i < cards[code].qty; i++) {
      // Draw a divider every 9 cards (to represent a new page).
      if(curr_index >= 9 && curr_index % 9 == 0) {
        $("#preview-container").append(
          '<hr style="display:inline-block;width:100%">'
        );
      }

      $("#preview-container").append(
        `<img class="img-responsive card-image pnp-image" src=${cards[code].image_url}></img>`);

      curr_index++;
    }
  }
}

function print_button_busy() {
  var elem = $("#btn-print");
  elem[0].dataset.original_html = elem.html();
  elem.prop("disabled", true);
  elem.html('<span class="glyphicon glyphicon-refresh spinning"></span> Printing...');
}

function print_button_done() {
  var elem = $("#btn-print");
  elem.prop("disabled", false);
  elem.html(elem[0].dataset.original_html);
}

function do_print() {
  print_button_busy();
  var bleed = 0;
  switch (NRDB.settings.getItem("pnp-bleed")) {
    case "Narrow":
      bleed = 3;
      break;
    case "Wide":
      bleed = 6;
      break;
  }
  var pnp = new PNP(NRDB.settings.getItem("pnp-cut-marks"),
                    NRDB.settings.getItem("pnp-page-format"),
                    bleed);
  pnp.print(print_button_done);
}

class PNP {
  constructor (cutmarks, format, bleed) {
    this.settings = {
      cutmarks: cutmarks,
      format: format,
      bleed: bleed,  // mm
    }
    const { jsPDF } = window.jspdf;
    this.doc = new jsPDF({
      unit: "mm",
      format: this.settings.format,
    });

    /* 1/4in or 6.35mm */
    this.MIN_MARGIN = 6.35;
    /* Default 2.5in x 3.5in (this may be scaled to fit bleed) */
    this.CARD_WIDTH = 63.5;  // mm
    this.CARD_HEIGHT = 88.9;  // mm

    this.page_width = this.doc.internal.pageSize.getWidth();
    this.page_height = this.doc.internal.pageSize.getHeight();

    /* Need to scale down cards when using bleed to stay within margin */
    if(this.settings.bleed > 0) {
      let scale_width = ((this.page_width - this.MIN_MARGIN*2 - this.settings.bleed*2)/3)/this.CARD_WIDTH;
      let scale_height = ((this.page_height - this.MIN_MARGIN*2 - this.settings.bleed*2)/3)/this.CARD_HEIGHT;
      let scale = Math.min(scale_width, scale_height);
      this.CARD_WIDTH *= scale;
      this.CARD_HEIGHT *= scale;
    }
    this.MARGIN_LEFT = (this.page_width - (this.CARD_WIDTH*3 + this.settings.bleed*2))/2;
    this.MARGIN_TOP = (this.page_height - (this.CARD_HEIGHT*3 + this.settings.bleed*2))/2;
  }

  draw_cutlines(){
    /* With bleed this draws the line in the middle of the bleed gutter. This is
     * different behavior than cut marks */
    for(let p = 1; p <= this.doc.getNumberOfPages(); p++) {
      this.doc.setPage(p);
      // Draw 4 horizontal and 4 vertical cutlines.
      for(let i = 0; i < 4; i++) {
        // Horizontal
        let y = this.MARGIN_TOP + this.CARD_HEIGHT*i + (this.settings.bleed*i) - this.settings.bleed/2;
        this.doc.line(0, y,
                      this.page_width, y);
      }
      for(let i = 0; i < 4; i++) {
        // Vertical
        let x = this.MARGIN_LEFT + this.CARD_WIDTH*i + (this.settings.bleed*i) - this.settings.bleed/2;
        this.doc.line(x, 0,
                      x, this.page_height);
      }
    }
  }

  draw_cutmarks(padding) {
    /* Draw non-invasive cutmarks, padding is space between mark and cards.
     * With bleed this draws marks on each edge of cards, unlike how cut lines
     * are drawn with bleed. */
    for(let p = 1; p <= this.doc.getNumberOfPages(); p++) {
      this.doc.setPage(p);
      // 4 by 4 card intersection points, including corners. We will
      // only be draw marks on corner and edge points.
      for(let row = 0; row < 4; row++) {
        for(let col = 0; col < 4; col++) {
          let x = this.MARGIN_LEFT + this.CARD_WIDTH*col + this.settings.bleed*Math.min(2, col);
          let y = this.MARGIN_TOP + this.CARD_HEIGHT*row + this.settings.bleed*Math.min(2, row);
          if(row == 0) {
            this.doc.line(x, 0,
                          x, this.MARGIN_TOP - padding);
            if(col == 1 || col == 2) {
              this.doc.line(x - this.settings.bleed, 0,
                            x - this.settings.bleed, this.MARGIN_TOP - padding);
            }
          }
          if(col == 0) {
            this.doc.line(0, y,
                          this.MARGIN_LEFT - padding, y);
            if(row == 1 || row == 2) {
              this.doc.line(0, y - this.settings.bleed,
                            this.MARGIN_LEFT - padding, y - this.settings.bleed);
            }
          }
          if(row == 3) {
            this.doc.line(x, this.MARGIN_TOP + this.CARD_HEIGHT*row + this.settings.bleed*2 + padding,
                          x, this.page_height);
            if(col == 1 || col == 2) {
              this.doc.line(x - this.settings.bleed, this.MARGIN_TOP + this.CARD_HEIGHT*row + this.settings.bleed*2 + padding,
                            x - this.settings.bleed, this.page_height);
            }
          }
          if(col == 3) {
            this.doc.line(this.MARGIN_LEFT + this.CARD_WIDTH*col + this.settings.bleed*2 + padding, y,
                          this.page_width, y);
            if(row == 1 || row == 2) {
              this.doc.line(this.MARGIN_LEFT + this.CARD_WIDTH*col + this.settings.bleed*2 + padding, y - this.settings.bleed,
                            this.page_width, y - this.settings.bleed);
            }
          }
        }
      }
    }

  }

  print(done_callback = null){
    /* setTimeout is a little trick to get print button spinner to work.
     * See https://stackoverflow.com/questions/779379/why-is-settimeoutfn-0-sometimes-useful */
    setTimeout(() => {
      var cards = retrieve_cards();

      var cur_index = 0;
      for(let code in cards) {
        for(let i = 0; i < cards[code].qty; i++) {
          if (cur_index == 9) {
            cur_index = 0;
            // Make a new page every 9 cards.
            this.doc.addPage(this.settings.format);
          }

          // Setup the cards in a 3x3 grid
          let row = Math.floor(cur_index / 3);
          let col = cur_index % 3;

          const img = new Image();
          img.src = cards[code].image_url;
          this.doc.addImage(img, "JPEG",
                       this.MARGIN_LEFT + this.CARD_WIDTH*col + this.settings.bleed*(col),
                       this.MARGIN_TOP + this.CARD_HEIGHT*row + this.settings.bleed*(row),
                       this.CARD_WIDTH, this.CARD_HEIGHT);

          cur_index += 1;
        }
      }

      switch(this.settings.cutmarks) {
        case "Lines":
          this.draw_cutlines();
          break;
        case "Marks":
          this.draw_cutmarks(/*padding*/ 2);
          break;
      }
      this.doc.save();
      if(done_callback) {
        done_callback();
      }
    }, 0);
  }
}
