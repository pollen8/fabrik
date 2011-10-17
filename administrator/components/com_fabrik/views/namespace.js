(function () {
	Fabrik = {};
//various Joomla element plugins used to control JForm elements
	Fabrik.model = {'fields': {'fabriktable': {}, 'element': {}}};
	Fabrik.events = {};
	Fabrik.addEvent = function (type, fn) {
		if (!Fabrik.events[type]) {
			Fabrik.events[type] = [];
		}
		if (!Fabrik.events[type].contains(fn)) {
			Fabrik.events[type].push(fn);
		}
	};

	Fabrik.addEvents = function (events) {
		for (var event in events) {
			Fabrik.addEvent(event, events[event]);
		}
		return this;
	};

	Fabrik.fireEvent = function (type, args, delay) {
		var events = Fabrik.events;
		if (!events || !events[type]) {
			return this;
		}
		args = Array.from(args);

		events[type].each(function (fn) {
			if (delay) {
				fn.delay(delay, this, args);
			} else {
				fn.apply(this, args);
			}
		}, this);
		return this;
	};
}());