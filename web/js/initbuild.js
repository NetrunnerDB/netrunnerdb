$(function() {
    $('.list-group').on('mouseenter touchstart', 'a', function(event) {
        var card_code = $(this).data('code');
        var card = NRDB.data.cards.findById(card_code);
        $('#cardimg').prop('src', card.imageUrl);
    });
    $(window).scroll(function() {
        var scrollingDiv = $("#initIdentity");
        if(!scrollingDiv.is(':visible')) return;
        var y = $(this).scrollTop(),
            maxY = $('footer').offset().top,
            scrollHeight = scrollingDiv.height(),
            scrollTop = $(window).scrollTop();

        if(y < maxY - scrollHeight - 200) {
            scrollingDiv.stop().animate({"marginTop": scrollTop + "px"}, "slow");
        }
    });
    function updateFormat(format) {
      $('.identity').each(function(id, i) {
        // All
        if (format == 'all') {
          $(this).removeClass('hidden-format');
          return;
        }
        // Other formats
        let visible = true;
        // Startup
        if (format == 'startup') {
          visible = ['df', 'urbp', 'ur', 'sg', 'su21'].some(p => $(this).hasClass('pack-' + p));
        }
        // Standard
        else if (format == 'standard') {
          visible = !($(this).hasClass('banned') || $(this).hasClass('rotated'));
        }
        // Eternal
        else if (format == 'eternal') {
          visible = true
        }
        // Draft
        if ($(this).hasClass('pack-draft')) {
          visible = format == 'draft';
        }
        else if (format == 'draft') {
          visible = false
        }
        // Other (neutral Gateway IDs and the multiplayer NAPD ID)
        if (['24001', '30076', '30077'].includes($(this).attr('data-code'))) {
          visible = format == 'other';
        }
        else if (format == 'other') {
          visible = false
        }
        // Apply effect
        if (visible)
          $(this).removeClass('hidden-format');
        else
          $(this).addClass('hidden-format');
      });
    }
    function updateMisc() {
      let faction = $('#faction_filter').val();
      let search = $('#title-filter').val().toLowerCase();
      $('.identity').each(function(id, i) {
        if (faction != 'all' && !$(this).hasClass('faction-' + faction) || !$(this).find('.name').html().toLowerCase().includes(search)) {
          $(this).addClass('hidden-misc');
          return;
        }
        $(this).removeClass('hidden-misc');
      });
    }
    function applyUpdate() {
      $('.identity:not(.hidden-format, .hidden-misc)').show();
      $('.identity.hidden-format, .identity.hidden-misc').hide();
    }
    // Filter on format selected
    $('#format-filter > a').on('click', function(event) {
      updateFormat($(this).attr('value'));
      applyUpdate()
      event.preventDefault();
    });
    // Filter on faction selected
    $('#faction_filter').change(function() {
      updateMisc();
      applyUpdate()
    });
    // Filter on search updated
    $('#title-filter').on('input', function() {
      updateMisc();
      applyUpdate()
    });
    // Filter on page refresh
    updateMisc();
    applyUpdate()
});
