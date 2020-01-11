/* global NRDB */

(function (user, $) {

    user.params = {};
    user.deferred = $.Deferred().always(function () {
        if(user.data.is_authenticated) {
            user.update();
        } else {
            user.anonymous();
        }
        user.always();
    });

    user.query = function () {
        $.ajax(Routing.generate('user_info', user.params), {
            cache: false,
            dataType: 'json',
            success: function (data, textStatus, jqXHR) {
                user.data = data;
                user.deferred.resolve();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                user.deferred.resolve();
            }
        });
    };

    user.anonymous = function () {
        $('#login').append('<ul class="dropdown-menu"><li><a href="' + Routing.generate('fos_user_security_login') + '">Login or Register</a></li></ul>');
    };

    user.update = function () {
        var unchecked_activity_label = user.data.unchecked_activity ? '<span class="label label-success label-as-badge">' + user.data.unchecked_activity + '</span>' : '';
        $('#login a span').after(unchecked_activity_label);
        $('#login').addClass('dropdown').append('<ul class="dropdown-menu">'
				+ '<li><a href="' + Routing.generate('user_profile', {_locale: NRDB.locale}) + '">Edit account</a></li>'
				+ '<li><a href="' + Routing.generate('user_profile_view', {user_id: user.data.id, user_name: user.data.name, _locale: NRDB.locale}) + '">Public profile</a></li>'
				+ '<li><a href="https://alwaysberunning.net/profile/' + user.data.id + '" target="_blank">Always Be Running profile</a></li>'
				+ '<li><a href="' + Routing.generate('activity_feed', {_locale: NRDB.locale}) + '">Activity ' + unchecked_activity_label + '</a></li>'
				+ '<li><a href="' + Routing.generate('fos_user_security_logout') + '">Jack out</a></li>'
				+ '</ul>');
    };
    
    user.always = function () {
        // show ads if not donator
        if(user.data && user.data.is_supporter) {
            // thank you!
        } else {
            user.showAds();
        }
 
        $(document).trigger('user.app');
    };

    user.showAds = function () {
        adsbygoogle = window.adsbygoogle || [];

        $('div.ad').each(function (index, element) {
            $(element).show();
            adsbygoogle.push({});
        });

        if($('ins.adsbygoogle').filter(':visible').length === 0) {
            $('div.ad').each(function (index, element) {
                $(element).addClass('ad-blocked').html("No ad,<br>no <span class=\"icon icon-credit\"></span>.<br>Like NRDB?<br>Whitelist us<br>or <a href=\"" + Routing.generate('donators') + "\">donate</a>.");
            });
        }
    };

    user.promise = new Promise(function (resolve, reject) {
        $(document).on('user.app', resolve);
    });

    $(function () {
        user.query();
    });

})(NRDB.user = {}, jQuery);
