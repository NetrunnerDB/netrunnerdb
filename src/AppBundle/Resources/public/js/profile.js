$(function () {

    $.when(NRDB.user.deferred).then(function () {
        if(NRDB.user.data.is_authenticated) {
            if(NRDB.user.data.following.indexOf(UserId) > -1) {
                $('#unfollow').show();
            } else {
                $('#follow').show();
            }
        }
    });

    $('#unfollow').mouseover(function (event) {
        $(this).addClass('btn-danger').removeClass('btn-info').html('<span class="glyphicon glyphicon glyphicon-eye-close"></span> Unfollow');
    }).mouseout(function (event) {
        $(this).addClass('btn-info').removeClass('btn-danger').html('<span class="glyphicon glyphicon glyphicon-eye-open"></span> Following');
    });

    $('a.btn').click(function (event) {
        event.preventDefault();
        var href = $(this).attr('href');
        setTimeout(function () {
            location.href = href;
        }, 100);
    });

});
