// JavaScript Document
(function(){
	tinymce.create('tinymce.plugins.W4PL', {
		init : function(ed, url) {
			// Register commands
			ed.addCommand('mceW4PL', function() {
				ed.windowManager.open({
					title : 'W4 Post List',
					file : url + '/index.php',
					width : 840 + parseInt(ed.getLang('w4pl.delta_width', 0)),
					height : 550 + parseInt(ed.getLang('w4pl.delta_height', 0)),
					inline : 1
				},{
					plugin_url : url
				});
			});
			// Register buttons
			ed.addButton('w4pl', {title : 'W4 Post List', cmd : 'mceW4PL', image: url + '/w4pl.png' });
		},

		getInfo : function() {
			return {
				longname : 'W4 Post List',
				author : 'Shazzad Hossain Khan',
				authorurl : 'http://w4dev.com/about',
				infourl : 'http://w4dev.com/plugins/w4-post-list',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('w4pl', tinymce.plugins.W4PL);
})();