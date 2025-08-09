(function(settings, $) {

  // all the settings, initialized with their default value
  var cache = {
    'show-disabled': false,
    'only-deck': false,
    'show-onesies': false,
    'show-cacherefresh': false,
    'display-columns': 1,
    'core-sets': 3,
    'show-suggestions': 3,
    'buttons-behavior': 'cumulative',
    'sort-order': 'type',
    'check-rotation': true,
    'card-limits': 'legal',

    'pnp-cut-marks': 'None',
    'pnp-page-format': 'Letter',
    'pnp-bleed': 'None',
  };

  settings.load = function load() {
    // first give them the default values
    _.forIn(cache, function (defaultValue, key) {
      $('[name='+key+']').each(function (index, element) {
        var $element = $(element);
        switch($element.attr('type')) {
        case 'checkbox':
          $element.prop('checked', defaultValue);
          break;
        case 'radio':
          $element.prop('checked', $element.val() == defaultValue);
          break;
        default:
          $element.val(defaultValue);
          break;
        }
      })
    });

    // then overwrite those default values with the persisted values
    $('[data-persistence]').on('persistence:change', function (event, value) {
      var key = $(this).attr('name');
      cache[key] = value;
    })
    .persistence('load')
    .then(function () {
      $(document).trigger('settings.app');
    });
  };

  settings.getItem = function (key) {
    return cache[key];
  };

  settings.promise = new Promise(function (resolve, reject) {

    $(document).on('settings.app', resolve);

  });

  $(function () {
    settings.load();
    settings.dismiss_alerts();
    $(".lazy-alert").css("display", "block");
  });

  settings.persist_dismiss = function(selector) {
    /* Permanently dismiss an alert. Call with selector to id to alert.
     * Remember to add "lazy-alert" to class to prevent alerts from loading
     * in before being deleted by persisted dismiss */
    var dismissed = [];
    if(localStorage.dismissed_alerts) {
      dismissed = JSON.parse(localStorage.dismissed_alerts);
    }
    dismissed.push(selector);
    localStorage.dismissed_alerts = JSON.stringify(dismissed);
  }
  settings.dismiss_alerts = function() {
    if(!localStorage.dismissed_alerts) {
      return;
    }
    var dismissed = JSON.parse(localStorage.dismissed_alerts);
    for(id of dismissed) {
      $(id).remove();
    }
  }

})(NRDB.settings = {}, jQuery);
