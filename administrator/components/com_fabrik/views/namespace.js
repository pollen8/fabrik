(function () {
	if (typeOf(window.FabrikAdmin) === 'object') {
		return;
	}
	FabrikAdmin = {};
	//various Joomla element plugins used to control JForm elements
	FabrikAdmin.model = {'fields': {'fabriktable': {}, 'element': {}}};
}());