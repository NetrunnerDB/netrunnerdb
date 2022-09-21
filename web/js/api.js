// Fetches all cards from the v2 API
async function fetchCards() {
  data = [];
  json = await fetch(`${v3_api_url}/api/v3/public/cards`).then(data => data.json());
  data.push(...json.data);
  while ('next' in json.links) {
    json = await fetch(json.links.next).then(data => data.json());
    data.push(...json.data);
  }
  return data;
}

// Remove all cards with any of the given subtypes from a list of cards
function removeCardsWithSubtypes(cards, subtypes) {
  if (!subtypes || subtypes.length == 0)
    return cards;
  return cards.filter(c => subtypes.some(s => !(c.attributes.display_subtypes && c.attributes.display_subtypes.toLowerCase().split(' - ').includes(s))));
}

// Searches a list of restrictions for the one with the given ID
function getRestrictionById(restrictions, id) {
  for (i = 0; i < restrictions.length; i++) {
    if (restrictions[i].id == id) {
      return restrictions[i];
    }
  }
  return null;
}

// Filters a list of cards for the one with the given ID
function getCardsById(cards, ids) {
  return cards.filter(card => ids.includes(card.id));
}

// Maps a _=>card_id object to a _=>card object
function getCardsByIdFromObj(cards, obj) {
  return Object.keys(obj).reduce(function(newObj, key) {
    newObj[key] = getCardsById(cards, obj[key])
    return newObj
  }, {});
}

// Splits a list of cards into corp cards and runner cards
function splitBySide(cards) {
  corp = [];
  runner = [];
  cards.forEach (card => {
    if (card.attributes.side_id == 'corp')
      corp.push(card);
    else
      runner.push(card);
  });
  return [corp, runner];
}

// Generates a link to a card by creating a link to its most recent printing
function cardToLink(card) {
  return Routing.generate('cards_zoom', {card_code:card.attributes.latest_printing_id});
}
