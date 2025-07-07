// LIA TODO: Make sure double sided cards are accounted for.
// LIA TODO: Warn user that we're limited to NSG cards
// LIA TODO: Don't default to uprising booster printings
// LIA TODO: Allow printing selection for exact matches
// LIA TODO: Hover to see printing
// LIA TODO: options - cut lines, cut marks, bleed, A4
// LIA TODO: click to remove cards from list.
// LIA TODO: Fixup print stats.
// LIA TODO: decklist view..?
// LIA TODO: fixup card ordering
// LIA TODO: FIx card option select in a small card list
// LIA TODO: Fix selection without number
// LIA TODO: Get trash buttons working.
//
// Potential enhancements:
// Card preview are interactable to add or remove cards or select printings.
// Card preview is laid out in 3 wide format with dividers every 3 rows as if they are on a print page.
// Save user preferences.

$(document).on('data.app', function() {
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
});

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
  var elem = $(this).closest('li.list-group-item');
  var data_elem = elem.children("input")[0];
  var index = data_elem.name;
  var value = event.target.value;

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
  /*
  cards = {index : card};
  card = {
      index: int,
      qty: int,
      fuzzy: true|false,
      options: [],  // For matches, list of different printings, for fuzzied,
                    // list of other options
      selected_option: options[0],
  };
  */
  imported_cards.cards = {};
  imported_cards.curr_index = 0;

  imported_cards.insert = function(card){
    card.index = imported_cards.curr_index;
    imported_cards.cards[imported_cards.curr_index++] = card;
  }

  imported_cards.get_matches = function() {
    return Object.values(imported_cards.cards).filter((card) => {
      return !card.fuzzy;
    });
  }

  imported_cards.get_fuzzies = function() {
    return Object.values(imported_cards.cards).filter((card) => {
      return card.fuzzy;
    });
  }

  imported_cards.remove = function(index) {
    delete imported_cards.cards[index];
  }

  imported_cards.get_card = function(index) {
    return imported_cards.cards[index];
  }

  imported_cards.select_card = function(index, code) {
    var selection = imported_cards.cards[index].options.filter((card) => {
     return card.code == code;
    }) [0];
    if(selection) {
      imported_cards.cards[index].selected_option = selection;
    }
    // else: don't do anything.
  }

  imported_cards.change_qty = function(index, qty) {
    imported_cards.cards[index].qty = qty;
  }

  imported_cards.errors = [];

  imported_cards.remove_error = function(error) {
    imported_cards.errors = imported_cards.errors.filter(item => item !== error);
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

function find_by_code (code) {
  return NRDB.data.cards.findOne({code : { "$eq": code }});
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

function build_one_line(imported_line) {
  var card = imported_line.selected_option;
  var options = imported_line.options;
  var qty_int = imported_line.qty;
  var index = imported_line.index;
  var elem = $(`<li class="list-group-item form-inline"><a class="pull-right glyphicon glyphicon-trash"></a></li>`);
  elem.append(`<input class="pnp-data" type="hidden" name="${index}" value="${card.code}:${qty_int}">`);
  elem.append(`<input type="number" class="form-control pnp-number-input" placeholder="${qty_int}">`);
  elem.append('x ');
  var a = $(`<a class="card" data-code="${card.code}" href="javascript:void(0)">${card.title} </a>`);
  if(imported_line.fuzzy) {
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
  var qty = null;
  var name = null;
  if(line.match(/^(\d+)x?\s*(.*)/)) {
    qty = Number(RegExp.$1);
    name = RegExp.$2;
  } else {
    return null;  // Should be impossible...
  }
  if(qty == null) {
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

function import_cards() {
  var errors = [];
  var content = $('textarea[name="content"]').val();
  var lines = content.split(/[\r\n]+/);
  for(let i = 0; i < lines.length; i++) {
    var imported_line = import_one_line(lines[i]);

    if(imported_line == null) {
      imported_cards.errors.push(lines[i]);
    } else {
      imported_cards.insert(imported_line);
    }
  }
  update_imported_list();
  $('#pnp-text-area').val("");
}


function update_stats() {
  var deck = {}, size = 0, types = {};
  $('#analyzed input[type="hidden"]').each(function (index, element) {
    var card = $(element).val().split(':');
    var code = card[0], qty = parseInt(card[1], 10);
    deck[code] = qty;
    var record = NRDB.data.cards.findById(code);
    types[record.type.name] = types[record.type.name] || 0;
    types[record.type.name] += qty;
  });
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
  let cards = {};
  $("#analyzed > .list-group-item > input.pnp-data").each((_, e) => {
    let [code, qty] = e.value.split(":");
    qty = Number(qty);
    if(code in cards) {
      cards[code].qty += qty;
    } else {
      let card = this.find_by_code(code);
      cards[code] = {
        qty: qty,
        image_url: card.imageUrl};
    }
  });
  return cards;
}

function preview_cards() {
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

function do_import_pnp() {
  import_cards();
  update_stats();
  preview_cards();
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
  var pnp = new PNP(NRDB.settings.getItem("pnp-cut-marks"),
                    NRDB.settings.getItem("pnp-page-format"));
  pnp.print(print_button_done);
}

class PNP {
  constructor (cutlines, format) {
    this.settings = {
      cutlines: cutlines,
      format: format,
    }

    const { jsPDF } = window.jspdf;
    this.doc = new jsPDF({
      unit: "mm",
      format: this.settings.format,
    });
    /* 2.5in x 3.5in */
    this.CARD_WIDTH = 63.5;  // mm
    this.CARD_HEIGHT = 88.9;  // mm
    this.page_width = this.doc.internal.pageSize.getWidth();
    this.page_height = this.doc.internal.pageSize.getHeight();
    this.MARGIN_LEFT = (this.page_width - this.CARD_WIDTH*3)/2;
    this.MARGIN_TOP = (this.page_height - this.CARD_HEIGHT*3)/2;

  }

  draw_cutlines(){
    for(let p = 1; p <= this.doc.getNumberOfPages(); p++) {
      this.doc.setPage(p);
      // Draw 4 horizontal and 4 vertical cutlines.
      for(let i = 0; i < 4; i++) {
        // Horizontal
        this.doc.line(0, this.MARGIN_TOP + this.CARD_HEIGHT*i,
                      this.page_width, this.MARGIN_TOP + this.CARD_HEIGHT*i);
      }
      for(let i = 0; i < 4; i++) {
        // Vertical
        this.doc.line(this.MARGIN_LEFT + this.CARD_WIDTH*i, 0,
                      this.MARGIN_LEFT + this.CARD_WIDTH*i, this.page_height);
      }
    }
  }

  print(done_callback){
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
                       this.MARGIN_LEFT + (this.CARD_WIDTH)*col,
                       this.MARGIN_TOP + (this.CARD_HEIGHT)*row,
                       this.CARD_WIDTH, this.CARD_HEIGHT);

          cur_index += 1;
        }
      }

      if(this.settings.cutlines) {
        this.draw_cutlines();
      }
      this.doc.save();
      if(done_callback) {
        done_callback();
      }
    }, 0);
  }
}
