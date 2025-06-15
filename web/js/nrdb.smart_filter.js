(function(smart_filter, $) {
  var SmartFilterQuery = [];

  smart_filter.get_query = function(FilterQuery) {
      var query = SmartFilterQuery.slice();
      query.push(FilterQuery);
      // Wrap the query with an $and operator
      query = {"$and" : query};
      return query;
  };

  smart_filter.handler = function (value, callback) {
    var conditions = filterSyntax(value);
    SmartFilterQuery = [];

    for (var i = 0; i < conditions.length; i++) {
      var condition = conditions[i];
      var type = condition.shift();
      var operator = condition.shift();
      var values = condition.map(v => {
        return v.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') // Escape regex special characters
      });

      /* NOTE: some parameters are missing (e.g. faction, cycle number) because
       * they interact weird with the UI. */
      switch (type) {
      case "":
      case "_":
        add_string_sf('stripped_title', operator, values);
        break;
      case "x":
        add_string_sf('text', operator, values);
        break;
      case "a":
        add_string_sf('flavor', operator, values);
        break;
      case "e":
        add_string_sf('pack_code', operator, values);
        break;
      case "t":
        add_string_sf('type_code', operator, values);
        break;
      case "s":
        add_string_sf('keywords', operator, values);
        break;
      case "i":
        add_string_sf('illustrator', operator, values);
        break;
      case "o":
        add_integer_sf('cost', operator, values);
        break;
      case "g":
        add_integer_sf('advancement_cost', operator, values);
        break;
      case "l":
        add_integer_sf("base_link", operator, values);
        break;
      case "m":
        add_integer_sf("memory_cost", operator, values);
        break;
      case "n":
        add_integer_sf('faction_cost', operator, values);
        break;
      case "p":
        add_integer_sf('strength', operator, values);
        break;
      case "v":
        add_integer_sf('agenda_points', operator, values);
        break;
      case "h":
        add_integer_sf('trash_cost', operator, values);
        break;
      case "u":
        add_boolean_sf('uniqueness', operator, values);
        break;
      case "y":
        add_integer_sf('quantity', operator, values);
        break;
      }
    }

    callback();
  };

  function add_integer_sf(key, operator, values) {
    var tmp_array = [];
    var op = "$or";
    for (var j = 0; j < values.length; j++) {
      value = parseInt(values[j], 10);
      switch (operator) {
      case ":":
        tmp_array.push({
          [key]: {'$eq': value}
        });
        break;
      case "<":
        tmp_array.push({
          [key]: {'$lt': value}
        });
        break;
      case ">":
        tmp_array.push({
          [key]: {'$gt': value}
        });
        break;
      case "!":
        tmp_array.push({
          [key]: {'$ne': value}
        });
        op = "$and";
        break;
      }
    }
    if(values.length > 1) {
      // Create a wrapping OR around the conditions
      SmartFilterQuery.push({[op]: tmp_array});
    }
    else {
      SmartFilterQuery.push({[key]: tmp_array[0][key]});
    }
  }
  function add_string_sf(key, operator, values) {
    for (var j = 0; j < values.length; j++) {
      // Do exact matches for packs
      values[j] = key == 'pack_code' ? new RegExp('^(' + values[j] + ')$', 'i') : new RegExp(values[j], 'i');
    }
    switch (operator) {
    case ":":
      SmartFilterQuery.push({[key]: {
        '$in' : values
      }});
      break;
    case "!":
      SmartFilterQuery.push({[key]: {
        '$nee': null,
        '$nin' : values
      }});
      break;
    }
  }
  function add_boolean_sf(key, operator, values) {
    var condition = {}, value = parseInt(values.shift());
    switch (operator) {
    case ":":
      SmartFilterQuery.push({[key]: !!value});
      break;
    case "!":
      SmartFilterQuery.push({[key]: {
        '$ne': !!value
      }});
      break;
    }
  }
  function filterSyntax(query) {
    /* Returns a list of conditions (array)
       Each condition is an array with n>1 elements
       The first is the condition type (0 or 1 character)
       The following are the arguments, in OR */

    query = query.replace(/^\s*(.*?)\s*$/, "$1").replace('/\s+/', ' ');

    var list = [];
    var cond = null;
    /* The automaton has three states:
       1: type search
       2: main argument search
       3: additional argument search
       4: parsing error, we search for the next condition
       If it encounters an argument while searching for a type, then the
       type is empty */
    var etat = 1;
    while (query != "") {
      if (etat == 1) {
        if (cond !== null && etat !== 4 && cond.length > 2) {
          list.push(cond);
        }
        // we start by looking for a type of condition
        if (query.match(/^(\w)([:<>!])(.*)/)) { // token "condition:"
          cond = [ RegExp.$1.toLowerCase(), RegExp.$2 ];
          query = RegExp.$3;
        } else {
          cond = [ "", ":" ];
        }
        etat = 2;
      } else {
        if (   query.match(/^"([^"]*)"(.*)/) // token "text with quotes"
          || query.match(/^([^\s|]+)(.*)/) // token text-without-quotes
        ) {
          if ((etat === 2 && cond.length === 2) || etat === 3) {
            cond.push(RegExp.$1);
            query = RegExp.$2;
            etat = 2;
          } else {
            // erreur
            query = RegExp.$2;
            etat = 4;
          }
        } else if (query.match(/^\|(.*)/)) { // token "|"
          if ((cond[1] === ':' || cond[1] === '!')
              && ((etat === 2 && cond.length > 2) || etat === 3)) {
            query = RegExp.$1;
            etat = 3;
          } else {
            // erreur
            query = RegExp.$1;
            etat = 4;
          }
        } else if (query.match(/^ (.*)/)) { // token " "
          query = RegExp.$1;
          etat = 1;
        } else {
          // erreur
          query = query.substr(1);
          etat = 4;
        }
      }
    }
    if (cond !== null && etat !== 4 && cond.length > 2) {
      list.push(cond);
    }
    return list;
  }


})(NRDB.smart_filter = {}, jQuery);
