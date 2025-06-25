// LIA TODO: Make sure double sided cards are accounted for.
// LIA TODO: Limit printing to NSG only. And warn user when they try to.
// LIA TODO: Don't default to uprising booster printings
// LIA TODO: Hover to see printing
// LIA TODO: options - cut lines, cut marks, bleed, A4
// LIA TODO: click to remove cards from list.
// LIA TODO: Fixup print stats.
// LIA TODO: decklist view..?

function find_by_code (code) {
  return NRDB.data.cards.findOne({code : { "$eq": code }});
}

function retrieve_cards() {
  let cards = {};
  $("#analyzed > .list-group-item > input").each((_, e) => {
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

function do_import_pnp() {
  do_import();
  var cards = retrieve_cards();

  // Setup the dom
  for(let code in cards) {
    for(let i = 0; i < cards[code].qty; i++) {
      jQuery(`<img class="img-responsive card-image pnp-image" src=${cards[code].image_url}></img>`)
        .appendTo("#pnp-container");
    }
  }
}

class PNP {
  constructor () {
    const { jsPDF } = window.jspdf;
    this.FORMAT = "letter";
    this.doc = new jsPDF({
      unit: "mm",
      format: this.FORMAT,
    });
    /* 2.5in x 3.5in */
    this.CARD_WIDTH = 63.5;  // mm
    this.CARD_HEIGHT = 88.9;  // mm
    this.page_width = this.doc.internal.pageSize.getWidth();
    this.page_height = this.doc.internal.pageSize.getHeight();
    this.MARGIN_LEFT = (this.page_width - this.CARD_WIDTH*3)/2;
    this.MARGIN_TOP = (this.page_height - this.CARD_HEIGHT*3)/2;
  }

  draw_cut_lines(){
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

  do_print(){
    var cards = retrieve_cards();

    var cur_index = 0;
    for(let code in cards) {
      for(let i = 0; i < cards[code].qty; i++) {
        if (cur_index == 9) {
          cur_index = 0;
          // Make a new page every 9 cards.
          this.doc.addPage(this.FORMAT);
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

    this.draw_cut_lines();
    this.doc.save();
  }
}

var pnp = new PNP();
