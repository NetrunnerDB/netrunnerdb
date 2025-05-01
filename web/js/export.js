function open_decklist_modal(current_deck_uuid, current_deck_side) {
    // Clear any previous state
    var $modal = $('#exportTournamentSheetModal');
    $modal.find('.alert').remove();
    $('#btn-select-deck').prop('disabled', true);

    var complementarySide = (current_deck_side === 'Runner') ? 'Corp' : 'Runner';

    $modal.modal('show');
    $modal.find('.modal-body').html('<div class="text-center"><span class="fa fa-spinner fa-spin fa-2x"></span></div>');

    $.ajax({
        url: Routing.generate('decks_list_complementary', {
            side: complementarySide
        }),
        success: function(decks) {
            if (decks) {
                decks = JSON.parse(decks);
                if (decks.length === 0) {
                    $('#exportTournamentSheetModal .modal-body').html(
                        '<div class="alert alert-warning">' +
                        '<h4>No Compatible Decks Found</h4>' +
                        'You don\'t have any ' + complementarySide + ' decks to pair with this ' + current_deck_side + ' deck.<br><br>' +
                        '<a href="' + Routing.generate('deck_buildform', {side_text: complementarySide.toLowerCase()}) + '" class="btn btn-primary">Create New ' + complementarySide + ' Deck</a>' +
                        '</div>'
                    );
                } else {
                    var html = '<p>If you are about to compete in a Netrunner tournament, you can use this modal to create a detailed decklist of a Corp and Runner decks.</p>';
                    html += '<p>Select a ' + complementarySide + ' deck to include in the tournament sheet:</p>';
                    html += '<div class="list-group">';
                    
                    decks.forEach(deck => {
                        html += '<a href="#" class="list-group-item deck-list-group-item select-complementary-deck" data-deck-id="' + deck.uuid + '">';
                        html += '<div class="deck-list-identity-image hidden-xs" style="background-image:url(' + deck.identity_image_path + ')"></div>';
                        html += '<h4 class="decklist-name">' + deck.name + '</h4>';
                        html += '<div>' + deck.identity_title + '</div>';
                        html += '<div class="deck-list-tags">';
                        deck.tags.forEach(tag => {
                            html += '<span class="label label-default tag-' + tag + '">' + tag + '</span>';
                        });
                        html += '</div>';
                        html += '</a>';
                    });
                    
                    html += '</div>';
                    $('#exportTournamentSheetModal .modal-body').html(html);
                    
                    $('.select-complementary-deck').click(function(event) {
                        event.preventDefault();
                        var complementaryDeckId = $(this).data('deck-id');
                        window.location = Routing.generate('deck_export_tournament', {
                            deck_uuid: current_deck_uuid,
                            second_deck_uuid: complementaryDeckId
                        });
                    });
                }
            } else {
                $('#exportTournamentSheetModal .modal-body').html(
                    '<div class="alert alert-danger">Error loading decks. Please try again.</div>'
                );
            }
        },
        error: function() {
            $('#exportTournamentSheetModal .modal-body').html(
                '<div class="alert alert-danger">Error loading decks. Please try again.</div>'
            );
        }
    });
};
