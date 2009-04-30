
	/**
	 * A compact time slider with minimum and maximum date preset and start and end date values.
	 * The input and return of the date values is done with unix timestamps.
	 *
	 * @author Niels Jäckel (http://racoonia.de/)
	 * @license MIT License
	 */

	var
	TimeSlider = new Class({

		Implements: Options,
	
		options: {container: {gutter: undefined,
		                      startKnob: undefined,
		                      endKnob: undefined,
		                      range: undefined,
		                      startDate: undefined,
		                      endDate: undefined,
		                      minDate: undefined,
		                      maxDate: undefined},
		          
		          date: {min: undefined,
		                 max: undefined,
		                 start: undefined,
		                 end: undefined},
		                 
		          callbacks: {onChange: undefined,
		                      onDrag: undefined,
		                      onKnobSwap: undefined,
		                      formatDate: function(date) {
		
			                                 var month = date.getMonth();
			                                 month = parseInt(month);
			                                 month++;
											
			                                 return date.getFullYear() + '/' + 
			                                        date.getDate() + '/' + 
			                                        month;
		                                   }
		                     }
		          },
		
		// draggables
		drag: {start: undefined,
		       end: undefined},
		       
		// start and end knob in correct order?
		knobOrder: true,
		
		// current runtime styles
		styles: {fullWidth: 0,
		         knobWidth: 0,
		         knobStartLeft: 0,
		         knobEndLeft: 0,
		         rangeLeft: 0,
		         rangeWidth: 0},
		         
		         
		         
		initialize: function(options) {

			// import all the options		
			this.setOptions(options);

			// set basic position attributes on elements
			this.options.container.gutter.setStyle('position', 'relative');
			this.options.container.startKnob.setStyle('position', 'absolute');
			this.options.container.endKnob.setStyle('position', 'absolute');
			this.options.container.range.setStyle('position', 'absolute');

			// Init draggables
			this.drag.start = new Drag.Move(this.options.container.startKnob, {
			                           	    	container: this.options.container.gutter,
			                           	    	onDrag: this.onDrag.bind(this),
			                           	    	onComplete: this.onComplete.bind(this)
			                                });
			                              
			this.drag.end = new Drag.Move(this.options.container.endKnob, {
			                              	container: this.options.container.gutter,
			                              	onDrag: this.onDrag.bind(this),
			                              	onComplete: this.onComplete.bind(this)
			                              });

			// initial calculation of values and drawing of positions and dates
			this.display();
		},
		
		
		// call this if the time slider was hidden first
		display: function() {
		
			// grab all values
			
			// get full width if unknown
			var gutterSize = this.options.container.gutter.getSize();
			var knobSize = this.options.container.startKnob.getSize();
		
			this.styles.fullWidth = gutterSize.x - knobSize.x;
			this.styles.knobWidth = knobSize.x;

			// grab positions or default values
			this.determinePositions();
			
			// update range values
			this.calculateRange();

			// use them for drawing
			this.updatePositions();
			this.updateDates(true);
		},
		
		
		determinePositions: function() {

			var startDateX = Math.round(this.mapRange(this.options.date.min, this.options.date.max, 0, this.styles.fullWidth, this.options.date.start));
			var endDateX = Math.round(this.mapRange(this.options.date.min, this.options.date.max, 0, this.styles.fullWidth, this.options.date.end));

			this.styles.knobStartLeft = (($defined(this.drag.start.value.now.x))
			                             ? this.drag.start.value.now.x
			                             : ((this.knobOrder) 
			                                ? startDateX
			                                : endDateX));

			this.styles.knobEndLeft = (($defined(this.drag.end.value.now.x))
			                           ? this.drag.end.value.now.x
			                           : ((this.knobOrder) 
		                                  ? endDateX
			                              : startDateX));
		},
		
		
		calculateRange: function() {
		
			if (this.knobOrder) {

				this.styles.rangeLeft = this.styles.knobStartLeft + (this.styles.knobWidth / 2);
				this.styles.rangeWidth = this.styles.knobEndLeft - this.styles.knobStartLeft;
			}
			else {
			
				this.styles.rangeLeft = this.styles.knobEndLeft + (this.styles.knobWidth / 2);
				this.styles.rangeWidth = this.styles.knobStartLeft - this.styles.knobEndLeft;
			}
		},

		
		timestampToDate: function(timestamp) {
		
			return new Date(timestamp * 1000);
		},
		
		
		updatePositions: function() {
		
			// set x-positions by modifying left-style
			this.options.container.startKnob.setStyle('left', this.styles.knobStartLeft);
			this.options.container.endKnob.setStyle('left', this.styles.knobEndLeft);
			
			// update the range bar
			this.updateRangeMarker();
		},
		

		updateRangeMarker: function() {
		
			// set position and width of range marker
			if ($defined(this.options.container.range)) {

				this.options.container.range.setStyle('left', this.styles.rangeLeft);
				this.options.container.range.setStyle('width', this.styles.rangeWidth);
			}
		},
				
		
		// reprint all dates
		updateDates: function(allDates) {
		
			// set only start and end date
			if ($defined(this.options.container.startDate)) { this.options.container.startDate.set('text', this.options.callbacks.formatDate(this.timestampToDate(this.options.date.start)));	}
			if ($defined(this.options.container.endDate)) { this.options.container.endDate.set('text', this.options.callbacks.formatDate(this.timestampToDate(this.options.date.end)));	}
			
			// if updating explicitly all dates set the min- and max dates, too
			if (allDates) {

				if ($defined(this.options.container.minDate)) { this.options.container.minDate.set('text', this.options.callbacks.formatDate(this.timestampToDate(this.options.date.min)));	}
				if ($defined(this.options.container.maxDate)) { this.options.container.maxDate.set('text', this.options.callbacks.formatDate(this.timestampToDate(this.options.date.max)));	}
			}
		},
		
		
		onDrag: function() {

			// grab current positions
			this.determinePositions();

			// calculate ranges
			this.calculateRange();

			// check if to swap knobs
			this.checkKnobSwap();

			// swap start and end position if necessary
			if (this.styles.knobStartLeft > this.styles.knobEndLeft) {
			
				// swap positions
				var help = this.styles.knobStartLeft;
				this.styles.knobStartLeft = this.styles.knobEndLeft;
				this.styles.knobEndLeft = help;
			}
			
			// calculate corresponding timestamps
			this.options.date.start = Math.round(this.mapRange(0, this.styles.fullWidth, this.options.date.min, this.options.date.max, this.styles.knobStartLeft));
			this.options.date.end = Math.round(this.mapRange(0, this.styles.fullWidth, this.options.date.min, this.options.date.max, this.styles.knobEndLeft));

			// update date displays
			this.updateDates(false);

			// update range
			this.updateRangeMarker();

			// trigger callback if existend
			if ($defined(this.options.callbacks.onDrag)) {
			
				// and trigger callback
				this.options.callbacks.onDrag(this);
			}
		},
		
		
		checkKnobSwap: function() {
		
			// leave out equal position
			if (this.styles.knobStartLeft == this.styles.knobEndLeft) {
			
				return;
			}
		
			// get current knob order
			var localKnobOrder = (this.styles.knobStartLeft < this.styles.knobEndLeft);
			
			// if order changed
			if (this.knobOrder != localKnobOrder) {
			
				// store the actual knob order
				this.knobOrder = localKnobOrder;
				
				// trigger callback if existend
				if ($defined(this.options.callbacks.onKnobSwap)) {
				
					// and trigger callback
					this.options.callbacks.onKnobSwap(this);
				}
			}
		},
		
		
		onComplete: function() {
		
			if ($defined(this.options.callbacks.onChange)) {
			
				this.options.callbacks.onChange(this);
			}
		},
		
		
		getStart: function() {
		
			return this.options.date.start;
		},
		
		
		getEnd: function() {
		
			return this.options.date.end;
		},


		mapRange: function(srcMin, srcMax, destMin, destMax, srcVal) {

			var destVal = (srcVal - srcMin);
			destVal *= (destMax - destMin);
			destVal /= (srcMax - srcMin);
			destVal += destMin;

			return destVal;
		}
	});

	
	
	
