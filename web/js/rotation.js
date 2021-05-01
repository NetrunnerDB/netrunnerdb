Promise.all([NRDB.data.promise]).then(function() {
	console.log('Loaded!');
 
  function handleRotationChange(e) {
    showLegalCyclesAndPacks(e);
  }

  function showLegalCyclesAndPacks(e) {
	  if (e.target.value === '') {
		  return;
	  }
	  let container = $('#' + e.target.id + '_cycles_and_packs');
	  $(container).empty();

	  var rotatedCycles = rotations[e.target.value]['rotated_cycles'];
	  _.sortBy(NRDB.data.cycles.find(), 'position').reverse().forEach(function (cycle) {
		var packs = _.sortBy(NRDB.data.packs.find({cycle_code:cycle.code}), 'position').reverse();
		if (cycle.code !== 'draft' && !(cycle.code in rotatedCycles)) {	
			var $div = $('<div>' + cycle.name + '</div>');
			$(container).append($div);

			if(cycle.size === 1) {
			  if(packs.length) {
			  }
			} else {
			  packs.forEach(function (pack) {
				var $div = $('<div>&bull; ' + pack.name + '</div>');
				$(container).append($div);
			  });
			}
		}
	  });
  }

  $('#rotation_a').change(handleRotationChange);
  $('#rotation_b').change(handleRotationChange);
});
