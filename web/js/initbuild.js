$(function() {
    // Shows a preview of IDs the user mouses over
    $('.list-group').on('mouseenter touchstart', 'a', function(event) {
        let card_code = $(this).data('code');
        let card = NRDB.data.cards.findById(card_code);
        $('#cardimg').prop('src', card.imageUrl)
                     .attr('data-code', card_code)
                     .show();
    });

    // Check if the preview is showing a card that is currently visible
    function checkPreview() {
        let id = $(`.identity[data-code="${$('#cardimg').attr('data-code')}"]`);
        if (id.hasClass('hidden-format') || id.hasClass('hidden-misc')) {
            $('#cardimg').hide();
        }
    }

    // Force the preview to follow the scroll position of the user
    $(window).scroll(function() {
        let scrollingDiv = $("#initIdentity");
        if(!scrollingDiv.is(':visible')) return;
        let y = $(this).scrollTop(),
            maxY = $('footer').offset().top,
            scrollHeight = scrollingDiv.height(),
            scrollTop = $(window).scrollTop();

        if(y < maxY - scrollHeight - 200) {
            scrollingDiv.stop().animate({"marginTop": scrollTop + "px"}, "slow");
        }
    });

    // Update the ID list when the format is changed
    function updateFormat(format) {
        $('.identity').each(function(id, i) {
            // All (only show legality indicators on all)
            if (format === 'all') {
                $(this).removeClass('hidden-format');
                $(this).find('.legality-indicator').show ();
                return;
            } else {
                $(this).find('.legality-indicator').hide ();
            }
            // Other formats
            let visible = true;
            // Startup
            if (format === 'startup') {
                visible = ['sg', 'su21', 'msbp', 'ms', 'ph'].some(p => $(this).hasClass('pack-' + p)); // Hardcoded Startup Codes
            }
            // Standard
            else if (format === 'standard') {
                visible = !($(this).hasClass('banned') || $(this).hasClass('rotated'));
            }
            // Eternal
            else if (format === 'eternal') {
                visible = true
            }
            // Draft
            if ($(this).hasClass('pack-draft')) {
                visible = format === 'draft';
            }
            else if (format === 'draft') {
                visible = false
            }
            // Other (neutral Gateway IDs and the multiplayer NAPD ID)
            if (['24001', '30076', '30077'].includes($(this).attr('data-code'))) {
                visible = format === 'other';
            }
            else if (format === 'other') {
                visible = false
            }
            // Apply effect
            if (visible)
                $(this).removeClass('hidden-format');
            else
                $(this).addClass('hidden-format');
        });
    }

    // Update the ID list when any other parameter is changed
    function updateMisc() {
        let faction = $('#faction-filter').val();
        let search = $('#title-filter').val().toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "");
        $('.identity').each(function(id, i) {
            if (faction !== 'all' && !$(this).hasClass('faction-' + faction) || !$(this).find('.name').html().toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").includes(search)) {
                $(this).addClass('hidden-misc');
                return;
            }
            $(this).removeClass('hidden-misc');
        });
    }

    // Filter on format selected
    $('.option-format').on('click', function(event) {
        updateFormat($(this).attr('value'));
        checkPreview();
        event.preventDefault();
    });

    // Filter on faction selected
    $('#faction-filter').change(function() {
        updateMisc();
        checkPreview();
    });

    // Filter on search updated
    $('#title-filter').on('input', function() {
        updateMisc();
        checkPreview();
    });

    // Filter on page refresh
    updateMisc();
});
