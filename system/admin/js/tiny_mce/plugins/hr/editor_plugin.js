/**
 * editor_plugin_src.js
 *
 * Copyright 2009, Moxiecode Systems AB
 * Released under LGPL License.
 *
 * License: http://tinymce.moxiecode.com/license
 * Contributing: http://tinymce.moxiecode.com/contributing
 */

(function() {
	tinymce.create('tinymce.plugins.HRPlugin', {
		init : function(ed, url) {
			// Register commands
			ed.addCommand('mceHr', function() {
				ed.execCommand("mceInsertContent", false, '<hr/>');
			});

			// Register buttons
			ed.addButton('hr', {
				title : 'hr.hr_desc',
				cmd : 'mceHr'
			});

			ed.onNodeChange.add(function(ed, cm, n) {
				cm.setActive('hr', n.nodeName == 'HR');
			});

			ed.onClick.add(function(ed, e) {
				e = e.target;

				if (e.nodeName === 'HR')
					ed.selection.select(e);
			});
		},

		getInfo : function() {
			return {
				longname : 'HR',
				author : 'Moxiecode Systems AB',
				authorurl : 'http://tinymce.moxiecode.com',
				infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/hr',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('hr', tinymce.plugins.HRPlugin);
})();