/*
 * Bootstrap Toggle Checklist
 * Turns a parent checkbox into a toggle all/none button
 */

(function ($) {
	"use strict";
	
	var toggle   = '[data-toggle="checklist"]',
		toggleSelector = toggle+' input[type=checkbox]',
		childClass = 'checklist-items',
		childSelector = '.'+childClass+' input[type=checkbox]',
		containerClass = '-bs-checklist',
    	eventNamespace = '.bs.checklist';
	
	function Checklist (element) {
		this.$container = $(element).parent();
		this.$container.addClass(containerClass);

		this.$container.on('change' + eventNamespace, toggleSelector, toggleChanged);
		this.$container.on('change' + eventNamespace, childSelector, childChanged);
		
		setParentState(this.$container);
	}
	
	var proto = Checklist.prototype;
	
	proto.toggle = function () {
		var $toggle = getToggleInput(this.$container);
		$toggle.prop('checked', $toggle.is(':checked'));
		applyParentState(this.$container);
	}
	
	function toggleChanged () {
		var $container = getContainer($(this));
		applyParentState($container);
	}
	
	function applyParentState ($container) {
        var checked = getToggleInput($container).is(':checked');

        var $children = getChildInputs($container);
        $children.each(function (index, child) {
        	$(child).prop('checked', checked);
        });
	}
	
	function childChanged () {
		var $container = getContainer($(this));
        setParentState($container);
	}
	
	function setParentState ($container) {
		var $toggle = getToggleInput($container);
		var $children = getChildInputs($container);
		var nbChildren = $children.length;
		var nbCheckedChildren = $children.filter(':checked').length;
		
		$toggle.prop('indeterminate', false);
		
		if(nbCheckedChildren) {
			if(nbCheckedChildren < nbChildren) {
				$toggle.prop('indeterminate', true);
			} else {
				$toggle.prop('checked', true);
			}
		} else {
			$toggle.prop('checked', false);
		}
	}
	
	/**
	 * Returns the child inputs
	 */
	function getChildInputs ($container) {
		return $container.find(childSelector);
	} 
	
	/**
	 * Returns the parent toggle input 
	 */
	function getToggleInput ($container) {
		return $container.find(toggleSelector);
	}
	
	/**
	 * Returns the element that contains both parent and childs
	 */
	function getContainer ($element) {
		return $element.is('.'+containerClass) ? $element : $element.closest('.'+containerClass);
	}
	

    // CHECKLIST PLUGIN DEFINITION
    // ==========================

    $.fn.checklist = function (option) {
        return this.each(function () {
            var $this = $(this);
            var data = $this.data('bs.checklist');

            if (!data) $this.data('bs.checklist', (data = new Checklist(this)));
            if (typeof option == 'string') data[option].call($this);
        })
    };

    $.fn.checklist.Constructor = Checklist;
	
})(jQuery);