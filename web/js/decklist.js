/* global Decklist, NRDB, Markdown, Commenters, moment, SelectedDeck, Promise */

NRDB.data.promise.then(function () {
    $(this).closest('tr').siblings().removeClass('active');
    $(this).closest('tr').addClass('active');
    for(var i = 0; i < Decklist.cards.length; i++) {
        var slot = Decklist.cards[i];
        NRDB.data.cards.updateById(slot.card_code, {indeck: parseInt(slot.qty, 10)});
    }
    $('.change_mwl').on('click', on_mwl_click);
    update_mwl(MWL);
    update_deck();
    NRDB.draw_simulator.init();

    $('a[href="#tools"]').on('shown.bs.tab', function (e) {
        make_cost_graph();
        make_strength_graph();
    });
});

Promise.all([NRDB.data.promise, NRDB.user.promise]).then(function () {
    if(NRDB.user.data.moderation_status || NRDB.user.data.is_moderator) {
        setup_moderation(NRDB.user.data.moderation_status, NRDB.user.data.moderation_reason, NRDB.user.data.is_moderator);
    }
});

function setup_moderation(moderation_status, moderation_reason, is_moderator) {
    switch(moderation_status) {
        case 0:  // MODERATION_PUBLISHED
            break;
        case 1: // MODERATION_RESTORED
            NRDB.ui.showBanner('This decklist has been restored to the public directories.', 'info');
            break;
        case 2: // MODERATION_TRASHED
            NRDB.ui.showBanner('This decklist has been removed from the public directories. Reason: <b>'+moderation_reason+'</b>.', 'danger');
            break;
        case 3: // MODERATION_DELETED
            NRDB.ui.showBanner('This decklist has been deleted.', 'warning');
            break;
    }

    if(!is_moderator) {
        return;
    }        

    var $dropdown = $('#btn-group-decklist');
    $('<li class="dropdown-header"><span class="glyphicon glyphicon-ban-circle"></span> Moderation</li>').appendTo($dropdown);
    $('<li class="disabled"><a href="#" id="btn-moderation-trash">Trash</a></li>').appendTo($dropdown);
    $('<li class="disabled"><a href="#" id="btn-moderation-absolve">Absolve</a></li>').appendTo($dropdown);
    $('<li class="disabled"><a href="#" id="btn-moderation-delete">Delete</a></li>').appendTo($dropdown);

    switch(moderation_status) {
        case 0:  // MODERATION_PUBLISHED
            $('#btn-moderation-trash').parent().removeClass('disabled');
            break;
        case 1: // MODERATION_RESTORED
            $('#btn-moderation-trash,#btn-moderation-absolve,#btn-moderation-delete').parent().removeClass('disabled');
            break;
        case 2: // MODERATION_TRASHED
            $('#btn-moderation-restore,#btn-moderation-absolve').parent().removeClass('disabled');
            break;
        case 3: // MODERATION_DELETED
            $('#btn-moderation-restore').parent().removeClass('disabled');
            break;
    }
}

function setup_comment_form() {

    var form = $('<form method="POST" action="' + Routing.generate('decklist_comment') + '"><input type="hidden" name="id" value="' + Decklist.id + '"><div class="form-group">'
            + '<textarea id="comment-form-text" class="form-control" maxlength="10000" rows="4" name="comment" placeholder="Enter your comment in Markdown format. Type # to enter a card name. Type $ to enter a symbol. Type @ to enter a user name."></textarea>'
            + '</div><div class="well text-muted" id="comment-form-preview"><small>Preview. Look <a href="http://daringfireball.net/projects/markdown/dingus">here</a> for a Markdown syntax reference.</small></div>'
            + '<div class="form-group small"><span class="help-block">By submitting content, you agree to the <a href="'+Routing.generate('cards_about')+'#code-of-conduct">Code of Conduct</a> of the website.</span></div>'
            + '<button type="submit" class="btn btn-success">Submit comment</button></form>').insertAfter('#comment-form');

    var already_submitted = false;
    form.on('submit', function (event) {
        event.preventDefault();
        var data = $(this).serialize();
        if(already_submitted)
            return;
        already_submitted = true;
        $.ajax(Routing.generate('decklist_comment'), {
            data: data,
            type: 'POST',
            success: function (data, textStatus, jqXHR) {
                form.replaceWith('<div class="alert alert-success" role="alert">Your comment has been posted. It will appear on the site in a few minutes.</div>');
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log('[' + moment().format('YYYY-MM-DD HH:mm:ss') + '] Error on ' + this.url, textStatus, errorThrown);
                form.replaceWith('<div class="alert alert-danger" role="alert">An error occured while posting your comment (' + jqXHR.statusText + '). Reload the page and try again.</div>');
            }
        });
    });

    $('#social-icon-comment').on('click', function () {
        $('#comment-form-text').trigger('focus');
    });


    var converter = new Markdown.Converter();
    $('#comment-form-text').on(
            'keyup',
            function () {
                $('#comment-form-preview').html(converter.makeHtml($('#comment-form-text').val()));
            }
    );

    $('#comment-form-text').textcomplete([{
            match: /\B#([\-+\w]*)$/,
            search: function (term, callback) {
                var regexp = new RegExp('\\b' + term, 'i');
                var result = NRDB.data.cards.find({
                    title: regexp
                });
                callback(result);
            },
            template: function (value) {
                return value.title + ' (' + value.pack.name + ')';
            },
            replace: function (value) {
                return '[' + value.title + ']('
                        + Routing.generate('cards_zoom', {card_code: value.code})
                        + ')';
            },
            index: 1,
            idProperty: 'code'
        }, {
            match: /\B@([\-+\w]*)$/,
            search: function (term, callback) {
                var regexp = new RegExp('^' + term);
                callback($.grep(Commenters, function (commenter) {
                    return regexp.test(commenter);
                }));
            },
            template: function (value) {
                return value;
            },
            replace: function (value) {
                return '`@' + value + '`';
            },
            index: 1
        }, {
            match: /\$([\-+\w]*)$/,
            search: function (term, callback) {
                var regexp = new RegExp('^' + term);
                callback($.grep(['credit', 'recurring-credit', 'click', 'link', 'trash', 'subroutine', 'mu', '1mu', '2mu', '3mu',
                    'anarch', 'criminal', 'shaper', 'haas-bioroid', 'weyland-consortium', 'jinteki', 'nbn'],
                        function (symbol) {
                            return regexp.test(symbol);
                        }
                ));
            },
            template: function (value) {
                return value;
            },
            replace: function (value) {
                return '<span class="icon icon-' + value + '"></span>';
            },
            index: 1
        }]);
}

function setup_social_icons() {

    if(!NRDB.user.data.is_authenticated || NRDB.user.data.is_author || NRDB.user.data.is_liked) {
        var element = $('#social-icon-like');
        element.replaceWith($('<span class="social-icon-like"></span>').html(element.html()));
    }

    if(!NRDB.user.data.is_authenticated) {
        var element = $('#social-icon-favorite');
        element.replaceWith($('<span class="social-icon-favorite"></span>').html(element.html()));
    } else if(NRDB.user.data.is_favorite) {
        var element = $('#social-icon-favorite');
        element.attr('title', "Remove from favorites");
    } else {
        var element = $('#social-icon-favorite');
        element.attr('title', "Add to favorites");
    }

    if(!NRDB.user.data.is_authenticated) {
        var element = $('#social-icon-comment');
        element.replaceWith($('<span class="social-icon-comment"></span>').html(element.html()));
    }

}

function setup_title() {
    var title = $('h1.decklist-name');
    if(NRDB.user.data.is_author && NRDB.user.data.can_delete) {
        title.prepend('<a href="#" title="Delete decklist" id="decklist-delete"><span class="glyphicon glyphicon-trash pull-right text-danger"></span></a>');
    }
    if(NRDB.user.data.is_author) {
        title.prepend('<a href="#" title="Edit decklist name / description" id="decklist-edit"><span class="glyphicon glyphicon-pencil pull-right"></span></a>');
    }
}

function setup_comment_hide() {
    if(NRDB.user.data.is_author || NRDB.user.data.is_moderator) {
        $('.comment-hide-button').remove();
        $('<a href="#" class="comment-hide-button"><span class="text-danger glyphicon glyphicon-remove" style="margin-left:.5em"></span></a>').appendTo('.collapse.in > .comment-date').on('click', function (event) {
            if(confirm('Do you really want to hide this comment for everybody?')) {
                hide_comment($(this).closest('td'));
            }
            return false;
        });
        $('<a href="#" class="comment-hide-button"><span class="text-success glyphicon glyphicon-ok" style="margin-left:.5em"></span></a>').appendTo('.collapse:not(.in) > .comment-date').on('click', function (event) {
            if(confirm('Do you really want to unhide this comment?')) {
                unhide_comment($(this).closest('td'));
            }
            return false;
        });
    }
}

function hide_comment(element) {
    var id = element.attr('id').replace(/comment-/, '');
    $.ajax(Routing.generate('decklist_comment_hide', {comment_id: id, hidden: 1}), {
        type: 'POST',
        dataType: 'json',
        success: function (data, textStatus, jqXHR) {
            if(data === true) {
                $(element).find('.collapse').collapse('hide');
                $(element).find('.comment-toggler').show().prepend('The comment will be hidden for everyone in a few minutes.');
                setTimeout(setup_comment_hide, 1000);
            } else {
                alert(data);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log('[' + moment().format('YYYY-MM-DD HH:mm:ss') + '] Error on ' + this.url, textStatus, errorThrown);
            alert('An error occured while hiding this comment (' + jqXHR.statusText + '). Reload the page and try again.');
        }
    });
}

function unhide_comment(element) {
    var id = element.attr('id').replace(/comment-/, '');
    $.ajax(Routing.generate('decklist_comment_hide', {comment_id: id, hidden: 0}), {
        type: 'POST',
        dataType: 'json',
        success: function (data, textStatus, jqXHR) {
            if(data === true) {
                $(element).find('.collapse').collapse('show');
                $(element).find('.comment-toggler').hide();
                setTimeout(setup_comment_hide, 1000);
            } else {
                alert(data);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log('[' + moment().format('YYYY-MM-DD HH:mm:ss') + '] Error on ' + this.url, textStatus, errorThrown);
            alert('An error occured while unhiding this comment (' + jqXHR.statusText + '). Reload the page and try again.');
        }
    });
}

function do_action_decklist(event) {
    var action_id = $(this).attr('id');
    if(!action_id || !SelectedDeck)
        return;
    switch(action_id) {
        case 'btn-download-text':
            location.href = Routing.generate('decklist_export_text', {decklist_id: Decklist.id});
            break;
        case 'btn-download-octgn':
            location.href = Routing.generate('decklist_export_octgn', {decklist_id: Decklist.id});
            break;
    }
}

$(function () {

    $.when(NRDB.user.deferred).then(function () {
        if(NRDB.user.data.is_authenticated) {
            setup_comment_form();
            setup_title();
            setup_comment_hide();
        } else {
            $('<p>You must be logged in to post comments.</p>').insertAfter('#comment-form');
        }
        setup_social_icons();
    });

    $(document).on('click', '#decklist-edit', edit_form);
    $(document).on('click', '#decklist-delete', delete_form);
    $(document).on('click', '#social-icon-like', send_like);
    $(document).on('click', '#social-icon-favorite', send_favorite);
    $(document).on('click', '#btn-download-text', do_action_decklist);
    $(document).on('click', '#btn-download-octgn', do_action_decklist);
    $(document).on('click', '#btn-export-bbcode', export_bbcode);
    $(document).on('click', '#btn-export-markdown', export_markdown);
    $(document).on('click', '#btn-export-plaintext', export_plaintext);
    $(document).on('click', '#btn-export-jintekinet', export_jintekinet);
    $(document).on('click', '#btn-compare', compare_form);
    $(document).on('click', '#btn-compare-submit', compare_submit);
    $(document).on('click', '#btn-copy-decklist', copy_decklist);
    $(document).on('click', '#btn-moderation-absolve', moderation_absolve);
    $(document).on('click', '#btn-moderation-trash', moderation_trash);
    $(document).on('click', '#btn-moderation-restore', moderation_restore);
    $(document).on('click', '#btn-moderation-delete', moderation_delete);

    $('div.collapse').each(function (index, element) {
        $(element).on('show.bs.collapse', function (event) {
            $(this).closest('td').find('.glyphicon-eye-open').removeClass('glyphicon-eye-open').addClass('glyphicon-eye-close');
        });
        $(element).on('hide.bs.collapse', function (event) {
            $(this).closest('td').find('.glyphicon-eye-close').removeClass('glyphicon-eye-close').addClass('glyphicon-eye-open');
        });
    });

    $('#btn-group-decklist').on({
        click: function (event) {
            event.preventDefault();
            if($(this).attr('id').match(/btn-sort-(\w+)/)) {
                DisplaySort = RegExp.$1;
                DisplaySortSecondary = null;
                update_deck();
            }
            if($(this).attr('id').match(/btn-sort-(\w+)-(\w+)/)) {
                DisplaySort = RegExp.$1;
                DisplaySortSecondary = RegExp.$2;
                update_deck();
            }
        }
    }, 'a');

});

function copy_decklist() {
    $.ajax(Routing.generate('deck_copy', {decklist_id: Decklist.id}), {
        type: 'POST',
        success: function (data, textStatus, jqXHR) {
            alert("Decklist copied");
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log('[' + moment().format('YYYY-MM-DD HH:mm:ss') + '] Error on ' + this.url, textStatus, errorThrown);
            alert('An error occured while copying this decklist (' + jqXHR.statusText + '). Reload the page and try again.');
        }
    });
}

function compare_submit() {
    var url = $('#decklist2_url').val();
    var id = null;
    if(url.match(/^\d+$/)) {
        id = parseInt(url, 10);
    } else if(url.match(/decklist\/(\d+)\//)) {
        id = parseInt(RegExp.$1, 10);
    }
    if(id) {
        var id1, id2;
        if(Decklist.id < id) {
            id1 = Decklist.id;
            id2 = id;
        } else {
            id1 = id;
            id2 = Decklist.id;
        }
        location.href = Routing.generate('decklists_diff', {decklist1_id: id1, decklist2_id: id2});
    }
}

function compare_form() {
    $('#compareModal').modal('show');
    setTimeout(function () {
        $('#decklist2_url').focus();
    }, 1000);
}

function edit_form() {
    $('#publishModal').modal('show');

	var converter = new Markdown.Converter();
	$('#publish-decklist-description-preview').html(
		converter.makeHtml($('#publish-decklist-description').val()));
	$('#publish-decklist-description').on('keyup', function() {
		$('#publish-decklist-description-preview').html(
				converter.makeHtml($('#publish-decklist-description').val()));
	});

	$('#publish-decklist-description').textcomplete([{
		match : /\B#([\-+\w]*)$/,
		search : function(term, callback) {
			var regexp = new RegExp('\\b' + term, 'i');
			// In the Notes section, we want to allow completion for *all* cards regardless of side.
			callback(NRDB.data.cards.find({
				title : regexp
			}));
		},
		template : function(value) {
			return value.title + ' (' + value.pack.name + ')';
		},
		replace : function(value) {
			return '[' + value.title + ']('
					+ Routing.generate('cards_zoom', {card_code:value.code})
					+ ')';
		},
		index : 1
	}, {
		match : /\$([\-+\w]*)$/,
		search : function(term, callback) {
			var regexp = new RegExp('^' + term);
			callback($.grep(['credit', 'recurring-credit', 'click', 'link', 'trash', 'subroutine', 'mu', '1mu', '2mu', '3mu',
				'anarch', 'criminal', 'shaper', 'haas-bioroid', 'weyland-consortium', 'jinteki', 'nbn'],
				function(symbol) { return regexp.test(symbol); }
			));
		},
		template : function(value) {
			return value;
		},
		replace : function(value) {
			return '<span class="icon icon-' + value + '"></span>';
		},
		index : 1
	}]);
}

function delete_form() {
    $('#deleteModal').modal('show');
}

function send_like() {
    var obj = $(this);
    $.post(Routing.generate('decklist_like'), {
        id: Decklist.id
    }, function (data, textStatus, jqXHR) {
        obj.find('.num').text(data);
    });
}

function send_favorite() {
    var obj = $(this);
    $.post(Routing.generate('decklist_favorite'), {
        id: Decklist.id
    }, function (data, textStatus, jqXHR) {
        obj.find('.num').text(data);
        var title = obj.data('original-tooltip');
        obj.data('original-tooltip',
                title === "Add to favorites" ? "Remove from favorites"
                : "Add to favorites");
        obj.attr('title', obj.data('original-tooltip'));
    });

    send_like.call($('#social-icon-like'));
}
function on_mwl_click(event) {
    event.preventDefault();
    var mwl_code = $(this).data('code');
    update_mwl(mwl_code);
    return false;

}
function update_mwl(mwl_code) {
    MWL = null;
    if(mwl_code) {
        MWL = NRDB.data.mwl.findById(mwl_code);
    }
    update_deck();
    $('a[href="#deck"]').tab('show');
}

function moderation_absolve(event) {
    if($(this).parent().hasClass('disabled')) {
        return;
    }
    change_moderation_status(0);
}

function moderation_restore(event) {
    if($(this).parent().hasClass('disabled')) {
        return;
    }
    change_moderation_status(1);
}

function moderation_trash(event) {
    if($(this).parent().hasClass('disabled')) {
        return;
    }
    ask_modflag().then(function (modflag_id) {
        change_moderation_status(2, modflag_id);
    });
}

function moderation_delete(event) {
    if($(this).parent().hasClass('disabled')) {
        return;
    }
    change_moderation_status(3);
}

function ask_modflag() {
    return get_modflags().then(show_modflag_modal);
}

function get_modflags() {
    return new Promise(function (resolve, reject) {
        var url = Routing.generate('modflags_get');
        $.get(url).then(function (response) {
            resolve(response.data);
        });
    });
}

function show_modflag_modal(data) {
    return new Promise(function (resolve, reject) {
        var $modal = $('#moderationModal');
        var $list = $('#moderation-reason');
        data.forEach(function (modflag) {
           $list.append($('<option value="'+modflag.id+'">'+modflag.reason+'</option>'));
        });
        var $button = $('#btn-moderation-submit');
        $button.click(function (event) {
            $modal.modal('hide');
            resolve($list.val());
        });
        $modal.modal('show');
    });
}

function change_moderation_status(status, modflag_id) {
    var url = Routing.generate('decklist_moderate', {
        decklist_id: Decklist.id,
        status: status,
        modflag_id: modflag_id
    });
    $.post(url).then(function () {
        if(status !== 3) {
            location.reload();
        } else {
            location = Routing.generate('decklists_list');
        }
    });
}
