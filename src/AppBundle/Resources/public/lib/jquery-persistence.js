/*
 * jQuery Persistence plugin
 * Saves and Loads the value of an input in localForage
 */

(function ($) {
	"use strict";
	
	var toggle   = '[data-persistence]',
		eventName = 'persistence:change',
    	eventNamespace = '.persistence';
	
	function Persistence (element) {
		var self = this,
			$element = $(element);
		
		$element.on('change' + eventNamespace, function (event) {
			self.save.call($element);
		});
	}
	
	var proto = Persistence.prototype;
	
	proto.load = function () {
		var $element = $(this),
			name = $element.attr('name');
		
		return localforage.getItem(name).then(function (value) {
			if(value === null) return;
			switch($element.attr('type')) {
			case 'checkbox':
				$element.prop('checked', value);
				break;
			case 'radio':
				$element.prop('checked', $element.val() === value);
				break;
			default:
				$element.val(value);
				break;
			}
			$element.trigger(eventName, [ value ]);
		});
	}
	
	proto.save = function () {
		var $element = $(this),
			name = $element.attr('name');
	
		var value;
		switch($element.attr('type')) {
		case 'checkbox':
			value = $element.is(':checked');
			break;
		case 'radio':
			value = $element.is(':checked') ? $element.val() : null;
			break;
		default:
			value = $element.val();
			break;
		}
		
		if(value === null) return;
		return localforage.setItem(name, value).then(function (value) {
			$element.trigger(eventName, [ value ]);
		});
	}
	
    // PERSISTENCE PLUGIN DEFINITION
    // ==========================

    $.fn.persistence = function (option) {
    	return Promise.all(
			this.map(function () {
	            var $this = $(this);
	            var data = $this.data('jq.persistence');

	            if (!data) $this.data('jq.persistence', (data = new Persistence(this)));
	            if (typeof option == 'string') return data[option].call($this);
	        })
    	);
    };

    $.fn.persistence.Constructor = Persistence;
	
})(jQuery);