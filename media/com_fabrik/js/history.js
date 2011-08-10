History = new Class({
	initialize : function(undobutton, redobutton) {
		this.recording = true;
		this.pointer = -1;
		if($(undobutton)){
		$(undobutton).addEvent('click', this.undo.bindWithEvent(this));
		}
		if($(redobutton)){
		$(redobutton).addEvent('click', this.redo.bindWithEvent(this));
		}
		window.addEvent('fabrik.history.on', this.on.bindWithEvent(this));
		window.addEvent('fabrik.history.off', this.off.bindWithEvent(this));
		window.addEvent('fabrik.history.add', this.add.bindWithEvent(this));
		this.history = $A([]);
	},

	undo : function() {
		if (this.pointer > -1) {
			this.off();
			var h = this.history[this.pointer];
			var f = h.undofunc;
			var p = h.undoparams;
			var res = f.attempt(p, h.object);
			this.on();
			this.pointer--;
		}

	},

	redo : function() {
		if (this.pointer < this.history.length - 1) {
			this.pointer++;
			this.off();
			var h = this.history[this.pointer];
			var f = h.redofunc;
			var p = h.redoparams;
			var res = f.attempt(p, h.object);
			this.on();
		}
	},

	add : function(obj, undofunc, undoparams, redofunc, redoparams) {
		if (this.recording) {
			// remove history which is newer than current pointer location
			newh = this.history.filter(function(h, x) {
				return x <= this.pointer;
			}.bind(this));
			this.history = newh;
			this.history.push({
				'object' : obj,
				'undofunc' : undofunc,
				'undoparams' : undoparams,
				'redofunc' : redofunc,
				'redoparams' : redoparams
			});
			this.pointer++;
		}
	},

	on : function() {
		this.recording = true;
	},

	off : function() {
		this.recording = false;
	}
});
