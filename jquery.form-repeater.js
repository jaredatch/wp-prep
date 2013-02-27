/**
 * jQuery Form Repeater Plugin 0.1.0
 *
 * Copyright (c) 2011 Corey Ballou
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * Example Usage:
 *
<div class="container">
    <div class="r-group">
		<p>
			<label for="vehicle_0_0" data-pattern-text="Vehicle Name +=:">Vehicle Name 1:</label>
			<input type="text" name="vehicle[0][name]" id="vehicle_0_name" data-pattern-name="vehicle[++][name]" data-pattern-id="vehicle_++_name" />
		</p>
		<p>
			<label for="vehicle_0_0" data-pattern-text="Vehicle Type +=:">Vehicle Type 1:</label>
			<input type="text" name="vehicle[0][type]" id="vehicle_0_type" data-pattern-name="vehicle[++][type]" data-pattern-id="vehicle_++_type" />
		</p>
		<p>
			<-- Add a remove button for the item. If one didn't exist, it would be added to overall group -->
			<button type="button" class="r-btnRemove">Remove -</button>
		</p>
    </div>
	<button type="button" class="r-btnAdd">Add +</button>
</div>
<script type="text/javascript">
$('.container').repeater({
	btnAddClass: 'r-btnAdd',
	btnRemoveClass: 'r-btnRemove',
	groupClass: 'r-group',
	minItems: 1,
	maxItems: 0,
	startingIndex: 0,
	reindexOnDelete: true,
	repeatMode: 'append',
	animation: null,
	animationSpeed: 400,
	animationEasing: 'swing',
	clearValues: true
});
</script>
 */
(function($) {
	var $container, $group, $groupClone, opts, repeatCount = 0;

	$.fn.repeater = function(options, data) {
		$container = $(this);
		opts = $.extend({}, $.fn.repeater.defaults, options);
		var $btnAdd = $container.find('.' + opts.btnAddClass);
		if (!$btnAdd.length) {
			alert('You must specify a valid jQuery selector for the add button option in Form Repeater.');
			return false;
		}

		// parse out group details
		$group = $('.' + opts.groupClass);
		if (!$group.length) {
			alert('You must specify a valid jQuery selector for the form element grouping option in Form Repeater.');
			return false;
		}

		// ensure the remove button exists
		$btnRemove = $group.find('.' + opts.btnRemoveClass);
		if (!$btnRemove.length) {
			$btnRemove = $('<button type="button" name="rBtnRemove" class="' + opts.btnRemoveClass + '" style="display:none" />')
			$btnRemove.appendTo($container);
		} else {
			// default hidden
			$btnRemove.hide();
		}

		// narrow the group down to the first copy
		$group = $group.eq(0);
		// retrieve form elements
		$groupClone = $group.clone();
		// watch for add
		$container.find('.' + opts.btnAddClass).live('click', addRepeater);
		// watch for remove
		$container.find('.' + opts.btnRemoveClass).live('click', removeRepeater);
		
		// allows for initial population of form data
		if (data && data.length) {
			var patternName, patternId, patternText,
				idVal, nameVal, labelText, labelFor,
				$elem, elemName, $label;

			// create grouping for every row of data
			for (var row in data) {
				
				// keep cloning
				var $newClone = $groupClone.clone();
				
				if ($.isFunction(opts.beforeAdd)) {
					$newClone = opts.beforeAdd.call(this, $newClone);
				}
				
				var $formElems = $newClone.find(':input');
				if ($formElems.length) {
				
					// populate each input field
					$formElems.each(function() {
						$elem = $(this);
		
						// check for elements naming
						elemName = $elem.data('name');
						
						// check for matching value
						if (typeof data[row][elemName] != 'undefined') {
							$elem.val(data[row][elemName]);
						} else {
							$elem.val('');
						}

						patternName = $elem.data('pattern-name');
						if (patternName) {
							nameVal = $elem.attr('name');
							nameVal = parsePattern(patternName, nameVal, row);
							$elem.attr('name', nameVal);
						}
		
						patternId = $elem.data('pattern-id');
						if (patternId) {
							idVal = $elem.attr('id');
							idVal = parsePattern(patternId, idVal, row);
							$elem.attr('id', idVal);
						}
		
						$label = $newClone.find('label[for=' + $elem.attr('id')  + ']');
						if (!$label.length) $label = $elem.parent('label');
						if (!$label.length) $label = $elem.siblings('label');
						if ($label.length) {
							// ensure we have one copy
							$label = $label.eq(0);
							// update label text
							patternText = $label.data('pattern-text');
							labelText = $label.html();
							if (labelText) {
								labelText = parsePattern(patternText, labelText, row);
								$label.html(labelText);
							}
							// update label attribute
							labelFor = $label.attr('for');
							if (labelFor && idVal) {
								$label.attr('for', idVal);
							}
						}
					});
				
				}
				
				// append new clone to container
				$newClone.insertAfter($('.' + opts.groupClass).last());
				
				if ($group) {
					$group.remove();
					$group = null;
				}
				
				if ($.isFunction(opts.afterAdd)) {
					opts.afterAdd.call(this, $newClone);
				}
				
			}
			
			// show removal buttons
			$('.' + opts.groupClass + ' .' + opts.btnRemoveClass).show();

		}
		
		// daisy chain
		return this;
	}

	/**
	 * Add a new repeater.
	 */
	function addRepeater() {
		var tmpCount = repeatCount + 1,
			$doppleganger = $groupClone.clone();

		if ($.isFunction(opts.beforeAdd)) {
			$doppleganger = opts.beforeAdd.call(this, $doppleganger);
		}

		// don't exceed the max allowable items
		if (opts.maxItems > 0 && repeatCount == opts.maxItems) {
			alert('You have hit the maximum allowable items.');
			return false;
		}

		_reindex($doppleganger, tmpCount);

		// ensure remove button is showing
		$doppleganger.find('.' + opts.btnRemoveClass).show();

		// append repeater to container
		if (opts.repeatMode == 'append') {
			$doppleganger.appendTo($container);
		} else if (opts.repeatMode == 'prepend') {
			$doppleganger.prependTo($container);
		} else if (opts.repeatMode == 'insertAfterLast') {
			$doppleganger.insertAfter($container.find('.' + opts.groupClass).last());
		}

		repeatCount++;

		if ($.isFunction(opts.afterAdd)) {
			opts.afterAdd.call(this, $doppleganger);
		}

		return false;
	}
	
	/**
	 * Remove a repeater.
	 */
	function removeRepeater() {
		// determine if the button is nested in a repeater
		var $btn = $(this);

		// get all instances of repeaters
		var $repeaters = $container.find('.' + opts.groupClass);
		var numRepeaters = $repeaters.length;
		if (numRepeaters > opts.minItems) {

			// check if removing a specific repeater instance
			var $match = $btn.closest('.' + opts.groupClass);
			if (!$match.length) {
				// determine if removing first or last repeater
				if (opts.repeatMode == 'append') {
					var $match = $repeaters.filter(':last');
				} else if (opts.repeatMode == 'prepend') {
					var $match = $repeaters.filter(':first');
				} else if (opts.repeatMode == 'insertAfterLast') {
					var $match = $repeaters.filter(':last');
				}
			}

			// ensure we have a match
			if ($match.length) {
				// remove the repeater
				if (opts.animation) {
					if (opts.animation == 'slide') {
						$match.slideUp(opts.animationSpeed, opts.animationEasing, function() {
							_remove($match);
						});
					} else if (opts.animation == 'fade') {
						$match.fadeOut(opts.animationSpeed, opts.animationEasing, function() {
							_remove($match);
						});
					} else if (typeof opts.animation == 'object') {
						$match.animate(opts.animation, opts.animationSpeed, opts.animationEasing, function() {
							_remove($match);
						});
					}
				} else {
					_remove($match);
				}

			}

		}

		return false;
	}

	/**
	 * Parse the pattern.
	 */
	function parsePattern(pattern, replaceText, count) {
		var returnVal = replaceText;
		count = parseInt(count);
		if (pattern) {
			// check pattern type
			if (pattern.indexOf('+=') > -1) {
				var matches = pattern.match(/\+=(\d+)/i);
				if (matches && matches.length && matches[1]) {
					var incr = parseInt(matches[1]);
					returnVal = pattern.replace(/\+=(\d)+/i, opts.startingIndex + count + incr);
				}
			}

			if (pattern.indexOf('++') > -1) {
				returnVal = pattern.replace(/\+\+/gi, opts.startingIndex + count);
			}
		}
		return returnVal;
	}

	/**
	 * Wrapper to handle re-indexing form elements in a group.
	 */
	function reindex() {
		var $repeaters, $curGroup;
		var startIndex = opts.startingIndex;
		var $repeaters = $container.find('.' + opts.groupClass);
		$repeaters.each(function() {
			$curGroup = $(this);
			_reindex($curGroup, startIndex);
			startIndex++;
		});
	}

	/**
	 * Remove a match and reindex.
	 */
	function _remove($match) {
		$match.remove();
		if (repeatCount) {
			repeatCount--;
		}
		reindex();
	}

	/**
	 * Handle reindexing each form element in a group.
	 */
	function _reindex($curGroup, index) {
		var patternName, patternId, patternText,
			idVal, nameVal, labelText, labelFor,
			$elem;

		var $formElems = $curGroup.find(':input');
		if ($formElems.length) {
			$formElems.each(function() {
				$elem = $(this);
				//$elem.removeClass('chzn-done');
				//$elem.val('');

				patternName = $elem.data('pattern-name');
				if (patternName) {
					nameVal = $elem.attr('name');
					nameVal = parsePattern(patternName, nameVal, index);
					$elem.attr('name', nameVal);
				}

				patternId = $elem.data('pattern-id');
				if (patternId) {
					idVal = $elem.attr('id');
					idVal = parsePattern(patternId, idVal, index);
					$elem.attr('id', idVal);
				}

				$label = $curGroup.find('label[for=' + $elem.attr('id')  + ']');
				if (!$label.length) $label = $elem.parent('label');
				if (!$label.length) $label = $elem.siblings('label');
				if ($label.length) {
					// ensure we have one copy
					$label = $label.eq(0);
					// update label text
					patternText = $label.data('pattern-text');
					labelText = $label.html();
					if (labelText) {
						labelText = parsePattern(patternText, labelText, index);
						$label.html(labelText);
					}
					// update label attribute
					labelFor = $label.attr('for');
					if (labelFor && idVal) {
						$label.attr('for', idVal);
					}
				}
			});
		}
		return $curGroup;
	}

})(jQuery);

// default values
$.fn.repeater.defaults = {
	groupClass: 'r-group',
	btnAddClass: 'r-btnAdd',
	btnRemoveClass: 'r-btnRemove',
	minItems: 1,
	maxItems: 0,
	startingIndex: 0,
	reindexOnDelete: true,
	repeatMode: 'insertAfterLast', // append, prepend, insertAfterLast
	animation: null,
	animationSpeed: 400,
	animationEasing: 'swing',
	clearValues: true,
	beforeAdd: function($doppleganger) { return $doppleganger; },
	afterAdd: function($doppleganger) { },
	beforeDelete: function() { },
	afterDelete: function() { }
};
