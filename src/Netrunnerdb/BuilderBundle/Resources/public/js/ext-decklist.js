/* Lie library 3.0.4, Copyright (c) 2014 Calvin Metcalf -- https://github.com/calvinmetcalf/lie
Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/
!function e(t,n,r){function o(u,c){if(!n[u]){if(!t[u]){var s="function"==typeof require&&require;if(!c&&s)return s(u,!0);if(i)return i(u,!0);var f=new Error("Cannot find module '"+u+"'");throw f.code="MODULE_NOT_FOUND",f}var a=n[u]={exports:{}};t[u][0].call(a.exports,function(e){var n=t[u][1][e];return o(n?n:e)},a,a.exports,e,t,n,r)}return n[u].exports}for(var i="function"==typeof require&&require,u=0;u<r.length;u++)o(r[u]);return o}({1:[function(e,t,n){"use strict";function r(){}function o(e){if("function"!=typeof e)throw new TypeError("resolver must be a function");this.state=w,this.queue=[],this.outcome=void 0,e!==r&&s(this,e)}function i(e,t,n){this.promise=e,"function"==typeof t&&(this.onFulfilled=t,this.callFulfilled=this.otherCallFulfilled),"function"==typeof n&&(this.onRejected=n,this.callRejected=this.otherCallRejected)}function u(e,t,n){d(function(){var r;try{r=t(n)}catch(o){return v.reject(e,o)}r===e?v.reject(e,new TypeError("Cannot resolve promise with itself")):v.resolve(e,r)})}function c(e){var t=e&&e.then;return e&&"object"==typeof e&&"function"==typeof t?function(){t.apply(e,arguments)}:void 0}function s(e,t){function n(t){i||(i=!0,v.reject(e,t))}function r(t){i||(i=!0,v.resolve(e,t))}function o(){t(r,n)}var i=!1,u=f(o);"error"===u.status&&n(u.value)}function f(e,t){var n={};try{n.value=e(t),n.status="success"}catch(r){n.status="error",n.value=r}return n}function a(e){return e instanceof this?e:v.resolve(new this(r),e)}function l(e){var t=new this(r);return v.reject(t,e)}function h(e){function t(e,t){function r(e){u[t]=e,++c!==o||i||(i=!0,v.resolve(f,u))}n.resolve(e).then(r,function(e){i||(i=!0,v.reject(f,e))})}var n=this;if("[object Array]"!==Object.prototype.toString.call(e))return this.reject(new TypeError("must be an array"));var o=e.length,i=!1;if(!o)return this.resolve([]);for(var u=new Array(o),c=0,s=-1,f=new this(r);++s<o;)t(e[s],s);return f}function p(e){function t(e){n.resolve(e).then(function(e){i||(i=!0,v.resolve(c,e))},function(e){i||(i=!0,v.reject(c,e))})}var n=this;if("[object Array]"!==Object.prototype.toString.call(e))return this.reject(new TypeError("must be an array"));var o=e.length,i=!1;if(!o)return this.resolve([]);for(var u=-1,c=new this(r);++u<o;)t(e[u]);return c}var d=e("immediate"),v={},y=["REJECTED"],m=["FULFILLED"],w=["PENDING"];t.exports=o,o.prototype["catch"]=function(e){return this.then(null,e)},o.prototype.then=function(e,t){if("function"!=typeof e&&this.state===m||"function"!=typeof t&&this.state===y)return this;var n=new this.constructor(r);if(this.state!==w){var o=this.state===m?e:t;u(n,o,this.outcome)}else this.queue.push(new i(n,e,t));return n},i.prototype.callFulfilled=function(e){v.resolve(this.promise,e)},i.prototype.otherCallFulfilled=function(e){u(this.promise,this.onFulfilled,e)},i.prototype.callRejected=function(e){v.reject(this.promise,e)},i.prototype.otherCallRejected=function(e){u(this.promise,this.onRejected,e)},v.resolve=function(e,t){var n=f(c,t);if("error"===n.status)return v.reject(e,n.value);var r=n.value;if(r)s(e,r);else{e.state=m,e.outcome=t;for(var o=-1,i=e.queue.length;++o<i;)e.queue[o].callFulfilled(t)}return e},v.reject=function(e,t){e.state=y,e.outcome=t;for(var n=-1,r=e.queue.length;++n<r;)e.queue[n].callRejected(t);return e},o.resolve=a,o.reject=l,o.all=h,o.race=p},{immediate:2}],2:[function(e,t,n){(function(e){"use strict";function n(){a=!0;for(var e,t,n=l.length;n;){for(t=l,l=[],e=-1;++e<n;)t[e]();n=l.length}a=!1}function r(e){1!==l.push(e)||a||o()}var o,i=e.MutationObserver||e.WebKitMutationObserver;if(i){var u=0,c=new i(n),s=e.document.createTextNode("");c.observe(s,{characterData:!0}),o=function(){s.data=u=++u%2}}else if(e.setImmediate||"undefined"==typeof e.MessageChannel)o="document"in e&&"onreadystatechange"in e.document.createElement("script")?function(){var t=e.document.createElement("script");t.onreadystatechange=function(){n(),t.onreadystatechange=null,t.parentNode.removeChild(t),t=null},e.document.documentElement.appendChild(t)}:function(){setTimeout(n,0)};else{var f=new e.MessageChannel;f.port1.onmessage=n,o=function(){f.port2.postMessage(0)}}var a,l=[];t.exports=r}).call(this,"undefined"!=typeof global?global:"undefined"!=typeof self?self:"undefined"!=typeof window?window:{})},{}],3:[function(e,t,n){(function(t){"use strict";"function"!=typeof t.Promise&&(t.Promise=e("./lib"))}).call(this,"undefined"!=typeof global?global:"undefined"!=typeof self?self:"undefined"!=typeof window?window:{})},{"./lib":1}]},{},[3]);

/* Copyright (c) 2016 Cédric Bertolini -- https://github.com/Alsciende/netrunnerdb */
(function (container) {
  if(!container) {
    // no document.currentScript (IE11 ?)
    var elements = document.getElementsByClassName('nrdb-ext-decklist');
    container = elements[elements.length - 1];
  }
  if(!container || !container.getAttribute || !container.getAttribute('data-id')) return;
  var decklist_id = container.getAttribute('data-id'), db = {}, active_mwl = {}, decklist, decklist_content, identity, url;
  function getJson(url) {
    return new Promise(function(resolve, reject) {
    var req = new XMLHttpRequest();
    req.open('GET', url);
    req.onload = function() {
      if (req.status == 200) {
      var response = JSON.parse(req.response);
      if(!response.success) reject(Error(response.msg));
      resolve(response.data);
      }
      else {
      reject(Error(req.statusText));
      }
    };
    req.onerror = function() {
      reject(Error("Network Error"));
    };
    req.send();
    });
  }
  function createDatabase(name) {
    db[name] = {};
    return getJson('https://netrunnerdb.com/api/2.0/public/' + name)
    .then(function(data) {
      if(!data.length) return;
      var primaryKey = ('code' in data[0] ? 'code' : 'id');
      data.forEach(function (record) {
        db[name][record[primaryKey]] = record;
      })
    });
  }
  function createDatabases(names) {
    return Promise.all(names.map(createDatabase));
  }
  function loadDecklist(decklist_id) {
    return getJson('https://netrunnerdb.com/api/2.0/public/decklist/' + decklist_id)
    .then(function (data) {
      return decklist = data[0];
    });
  }
  function fetchData() {
    return Promise.all([
      createDatabases(['types', 'sides', 'factions', 'cycles', 'packs', 'cards', 'mwl']),
      loadDecklist(decklist_id)
    ]);
  }
  function findActiveMWL() {
    // wish I could use Array.prototype.find
    var active_id = Object.keys(db.mwl).filter(function (mwl_id) {
      return db.mwl[mwl_id].active;
    }).pop();
    if(active_id) {
      active_mwl = db.mwl[active_id].cards;
    }
  }
  function findCard(card_code) {
    return db.cards[card_code];
  }
  function findCards() {
    return decklist_content = Object.keys(decklist.cards).map(findCard);
  }
  function renderCardLink(card) {
    return '<a href="https://netrunnerdb.com/en/card/'+card.code+'" data-code="'+card.code+'">'+card.title+'</a>';
  }
  function renderCardInfluenceCost(card, quantity) {
    var html = '', faction = db.factions[card.faction_code];
    if(card.faction_code !== identity.faction_code && card.faction_cost) {
      html += ' <span class="nrdb-ext-decklist-influence" data-faction="'+faction.code+'" style="color:#'+faction.color+'">';
      for(var i=0; i<card.faction_cost; i++) {
        html += '•';
      }
      html += '</span>';
    }
    if(active_mwl[card.code]) {
      html += ' <span class="nrdb-ext-decklist-mwl">';
      for(var i=0; i<quantity*active_mwl[card.code]; i++) {
        html += '☆';
      }
      html += '</span>';
    }
    return html;
  }
  function renderCardQuantity(card, quantity) {
	if(card.type_code === 'identity') {
		return '';
	}
	else {
		return '<span class="nrdb-ext-decklist-quantity">'+quantity+'x</span> ';
	}
  }
  function renderCardLine(card, quantity) {
    return '<div class="nrdb-ext-decklist-card">'+renderCardQuantity(card, quantity)+renderCardLink(card)+renderCardInfluenceCost(card, quantity)+'</div>';
  }
  function renderTypeSection(type_code) {
    var type = db.types[type_code], lines = [];
    decklist_content.filter(function (card) {
      if(card.displayed) return false;
      if(type.is_subtype) {
        return card.keywords && card.keywords.indexOf(type.name) > -1;
      } else {
        return card.type_code === type.code;
      }
    }).sort(function (card_a, card_b) {
      return card_a.title.localeCompare(card_b.title);
    }).forEach(function (card) {
      card.displayed = true;
      var quantity = decklist.cards[card.code];
      lines.push(renderCardLine(card, quantity));
    });
    if(lines.length) {
      var header = (type_code === 'identity' ? '<h4>'+type.name+'</h4>' : '');
      return '<section class="nrdb-ext-decklist-section" data-type="'+type.code+'">' + header + lines.join('') + '</section>';
    }
    else {
      return '';
    }
  }
  function findIdentity() {
    // wish I could use Array.prototype.find
    identity = decklist_content.filter(function (card) { return card.type_code === 'identity' }).pop();
    if(!identity) throw Error("Cannot find the Identity");
  }
  function createHeader() {
    return '<header><h3>'+decklist.name+'</h3></header>';
  }
  function createBody() {
    var html = '<div class="nrdb-ext-decklist-content">';
    Object.keys(db.types).sort(function (type_code_a, type_code_b) {
      return db.types[type_code_a].position - db.types[type_code_b].position;
    }).forEach(function (type_code) {
      html += renderTypeSection(type_code);
    });
    html += '</div>';
    return html;
  }
  function createFooter() {
    var date = new Date(decklist.date_creation);
    return '<footer><p>Decklist published by <a href="https://netrunnerdb.com/en/profile/'+decklist.user_id+'">'+decklist.user_name+'</a> on <time datetime="'+date.toISOString()+'">'+date.toLocaleDateString()+'</time>. View it on <a href="'+url+'">NetrunnerDB</a>.</p></footer>';
  }
  function createHTML() {
    return '<article>'+createHeader()+createBody()+createFooter()+'</article>';
  }
  function createURL() {
    url = 'https://netrunnerdb.com/en/decklist/'+decklist_id;
    container.setAttribute('cite', url);
  }
  function insertHTML(html) {
    container.insertAdjacentHTML('afterbegin', html);
  }
  fetchData()
  .then(createURL)
  .then(findCards)
  .then(findIdentity)
  .then(findActiveMWL)
  .then(createHTML)
  .then(insertHTML)
  .catch(console.log.bind(console));
})(document && document.currentScript && document.currentScript.parentNode);
