console.log('user');
require(['element/databasejoin/databasejoin'], function () {
	console.log('user - required db');
	FbUser = new Class({
		Extends: FbDatabasejoin
	});	
});
