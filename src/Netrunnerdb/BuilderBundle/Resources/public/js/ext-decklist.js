!function(e){if("object"==typeof exports&&"undefined"!=typeof module)module.exports=e();else if("function"==typeof define&&define.amd)define([],e);else{var t;t="undefined"!=typeof window?window:"undefined"!=typeof global?global:"undefined"!=typeof self?self:this,t.Promise=e()}}(function(){return function e(t,n,r){function o(u,s){if(!n[u]){if(!t[u]){var c="function"==typeof require&&require;if(!s&&c)return c(u,!0);if(i)return i(u,!0);var f=new Error("Cannot find module '"+u+"'");throw f.code="MODULE_NOT_FOUND",f}var a=n[u]={exports:{}};t[u][0].call(a.exports,function(e){var n=t[u][1][e];return o(n?n:e)},a,a.exports,e,t,n,r)}return n[u].exports}for(var i="function"==typeof require&&require,u=0;u<r.length;u++)o(r[u]);return o}({1:[function(e,t,n){"use strict";function r(){}function o(e){if("function"!=typeof e)throw new TypeError("resolver must be a function");this.state=j,this.queue=[],this.outcome=void 0,e!==r&&c(this,e)}function i(e,t,n){this.promise=e,"function"==typeof t&&(this.onFulfilled=t,this.callFulfilled=this.otherCallFulfilled),"function"==typeof n&&(this.onRejected=n,this.callRejected=this.otherCallRejected)}function u(e,t,n){d(function(){var r;try{r=t(n)}catch(o){return v.reject(e,o)}r===e?v.reject(e,new TypeError("Cannot resolve promise with itself")):v.resolve(e,r)})}function s(e){var t=e&&e.then;return e&&"object"==typeof e&&"function"==typeof t?function(){t.apply(e,arguments)}:void 0}function c(e,t){function n(t){i||(i=!0,v.reject(e,t))}function r(t){i||(i=!0,v.resolve(e,t))}function o(){t(r,n)}var i=!1,u=f(o);"error"===u.status&&n(u.value)}function f(e,t){var n={};try{n.value=e(t),n.status="success"}catch(r){n.status="error",n.value=r}return n}function a(e){return e instanceof this?e:v.resolve(new this(r),e)}function l(e){var t=new this(r);return v.reject(t,e)}function h(e){function t(e,t){function r(e){u[t]=e,++s!==o||i||(i=!0,v.resolve(f,u))}n.resolve(e).then(r,function(e){i||(i=!0,v.reject(f,e))})}var n=this;if("[object Array]"!==Object.prototype.toString.call(e))return this.reject(new TypeError("must be an array"));var o=e.length,i=!1;if(!o)return this.resolve([]);for(var u=new Array(o),s=0,c=-1,f=new this(r);++c<o;)t(e[c],c);return f}function p(e){function t(e){n.resolve(e).then(function(e){i||(i=!0,v.resolve(s,e))},function(e){i||(i=!0,v.reject(s,e))})}var n=this;if("[object Array]"!==Object.prototype.toString.call(e))return this.reject(new TypeError("must be an array"));var o=e.length,i=!1;if(!o)return this.resolve([]);for(var u=-1,s=new this(r);++u<o;)t(e[u]);return s}var d=e("immediate"),v={},y=["REJECTED"],m=["FULFILLED"],j=["PENDING"];t.exports=o,o.prototype["catch"]=function(e){return this.then(null,e)},o.prototype.then=function(e,t){if("function"!=typeof e&&this.state===m||"function"!=typeof t&&this.state===y)return this;var n=new this.constructor(r);if(this.state!==j){var o=this.state===m?e:t;u(n,o,this.outcome)}else this.queue.push(new i(n,e,t));return n},i.prototype.callFulfilled=function(e){v.resolve(this.promise,e)},i.prototype.otherCallFulfilled=function(e){u(this.promise,this.onFulfilled,e)},i.prototype.callRejected=function(e){v.reject(this.promise,e)},i.prototype.otherCallRejected=function(e){u(this.promise,this.onRejected,e)},v.resolve=function(e,t){var n=f(s,t);if("error"===n.status)return v.reject(e,n.value);var r=n.value;if(r)c(e,r);else{e.state=m,e.outcome=t;for(var o=-1,i=e.queue.length;++o<i;)e.queue[o].callFulfilled(t)}return e},v.reject=function(e,t){e.state=y,e.outcome=t;for(var n=-1,r=e.queue.length;++n<r;)e.queue[n].callRejected(t);return e},o.resolve=a,o.reject=l,o.all=h,o.race=p},{immediate:2}],2:[function(e,t,n){(function(e){"use strict";function n(){a=!0;for(var e,t,n=l.length;n;){for(t=l,l=[],e=-1;++e<n;)t[e]();n=l.length}a=!1}function r(e){1!==l.push(e)||a||o()}var o,i=e.MutationObserver||e.WebKitMutationObserver;if(i){var u=0,s=new i(n),c=e.document.createTextNode("");s.observe(c,{characterData:!0}),o=function(){c.data=u=++u%2}}else if(e.setImmediate||"undefined"==typeof e.MessageChannel)o="document"in e&&"onreadystatechange"in e.document.createElement("script")?function(){var t=e.document.createElement("script");t.onreadystatechange=function(){n(),t.onreadystatechange=null,t.parentNode.removeChild(t),t=null},e.document.documentElement.appendChild(t)}:function(){setTimeout(n,0)};else{var f=new e.MessageChannel;f.port1.onmessage=n,o=function(){f.port2.postMessage(0)}}var a,l=[];t.exports=r}).call(this,"undefined"!=typeof global?global:"undefined"!=typeof self?self:"undefined"!=typeof window?window:{})},{}]},{},[1])(1)});

(function (container) {
	if(!container || !container.getAttribute || !container.getAttribute('data-id')) return;
    var db = {}, decklist, decklist_id = container.getAttribute('data-id');
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
            data.forEach(function (record) {
                db[name][record.code] = record;
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
            createDatabases(['types', 'sides', 'factions', 'cycles', 'packs', 'cards']),
            loadDecklist(decklist_id)
        ]);
    }
    function findCard(card_code) {
        return db.cards[card_code];
    }
    function findCards() {
        return Promise.all(Object.keys(decklist.cards).map(findCard));
    }
    function renderCardLink(card) {
        return '<a href="https://netrunnerdb.com/en/card/'+card.code+'">'+card.title+'</a>';
      }
    function renderCardLine(card, quantity) {
        return '<div>'+quantity+'x '+renderCardLink(card)+'</div>';
    }
    function renderTypeSection(cards, type_code) {
        var lines = [];
        cards.filter(function (card) {
            return card.type_code === type_code;
        }).sort(function (card_a, card_b) {
            return card_a.title.localeCompare(card_b.title);
        }).forEach(function (card) {
            var quantity = decklist.cards[card.code];
            lines.push(renderCardLine(card, quantity));
        });
        if(lines.length) {
            return '<h4>'+db.types[type_code].name+'</h4>' + lines.join('');
        }
        else {
            return '';
        }
    }
    function createHTML(cards) {
        var html = '<h3>'+decklist.name+'</h3>';
        Object.keys(db.types).forEach(function (type_code) {
            html += renderTypeSection(cards, type_code);
        });
        return html;
    }
    function insertHTML(html) {
        container.insertAdjacentHTML('afterbegin', html);
    }
    fetchData()
    .then(findCards)
    .then(createHTML)
    .then(insertHTML)
    .catch(console.log.bind(console));
})(document && document.currentScript && document.currentScript.parentNode);
