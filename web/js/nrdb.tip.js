/* global NRDB */

(function (tip, $) {

    var hide_event = 'mouseout',
            prevent_all = false;

    tip.prevent = function (event) {
        prevent_all = true;
    };

    tip.display = function (event) {
        var $this = $(this);

        if(prevent_all || $this.hasClass('no-popup')) {
            return;
        }

        var code = $this.data('index')
                || $this.closest('.card-container').data('index')
                || ($this.attr('href') && $this.attr('href').replace(
                        /.*\/card\/(\d\d\d\d\d).*/,
                        "$1"));
        var card = NRDB.data.cards.findById(code);
        if(!card)
            return;
        var type = '<p class="card-info">' + NRDB.format.type(card) + '</p>';
        var influence = '';
        for(var i = 0; i < card.faction_cost; i++)
            influence += "●";
        if(card.strength != null)
            type += '<p>Strength <b>' + card.strength + '</b></p>';
        var image_svg = '';
        if($('#nrdb_svg_hex').length) {
            image_svg = '<div class="card-image-wrapper"><div class="card-image card-image-' + card.side_code + '-' + card.type_code + '"' + (card.imageUrl ? ' style="background-image:url(' + NRDB.card_image_url + '/small/' + card.code+ '.jpg)"' : '') + '></div></div>';
        }

        $('.qtip').each(function(){
            $(this).qtip('api').destroy(true);
        });

        $this.qtip(
                {
                    content: {
                        text: image_svg
                                + '<h4 class="card-title">'
                                + (card.uniqueness ? "&diams; " : "")
                                + card.title + '</h4>' + type
                                + '<div class="card-text border-' + card.faction_code + '">' + NRDB.format.text(card) + '</div>'
                                + '<p class="card-faction" style="text-align:right;clear:right"><span class="influence influence-' + card.faction_code + '">' + influence
                                + '</span> ' + card.faction.name + ' &ndash; ' + card.pack.name + (card.pack.cycle.size !== 1 ? ' (' + card.pack.cycle.name + ')' : '') + '</p>'
                    },
                    style: {
                        classes: 'qtip-bootstrap qtip-nrdb'
                    },
                    position: {
                        my: 'left center',
                        at: 'right center',
                        viewport: $(document.body),
                        adjust: {
                            method: 'flip'
                        }
                    },
                    show: {
                        ready: true,
                        solo: true
                    },
                    hide: {
                        event: hide_event
                    }
                }, event);
    };

    tip.set_hide_event = function set_hide_event(opt_hide_event) {
        if(opt_hide_event === 'mouseout' || opt_hide_event === 'unfocus') {
            hide_event = opt_hide_event;
        }
    };

    $(document).on('data.app', function () {
        $('body').on({
            touchstart: tip.prevent
        });
        $('body').on({
            mouseover: tip.display
        }, 'a');
    });

})(NRDB.tip = {}, jQuery);
