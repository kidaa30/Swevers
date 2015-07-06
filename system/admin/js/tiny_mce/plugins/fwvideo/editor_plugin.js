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
	tinymce.create('tinymce.plugins.FWVideoPlugin', {
		init : function(ed, url) {
			// Register commands
			ed.addCommand('mceFWVideo', function() {
				if (ed.dom.getAttrib(ed.selection.getNode(), 'class', '').indexOf('mceItem') != -1)
					return;

				ed.windowManager.open({
					file : '/admin/_youtubepopup',
					width : 600,
					height : 500,
					inline : 1,
					scrollbars : true
				}, {
					plugin_url : url
				});
			});

			// Register buttons
			ed.addButton('fwvideo', {
				title : 'fwvideo.video_desc',
				cmd : 'mceFWVideo'
			});
		}
	});

	// Register plugin
	tinymce.PluginManager.add('fwvideo', tinymce.plugins.FWVideoPlugin);
})();