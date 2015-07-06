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
	tinymce.create('tinymce.plugins.FWImagePlugin', {
		init : function(ed, url) {
			// Register commands
			ed.addCommand('mceFWImage', function() {
				// Internal image object like a flash placeholder
				if (ed.dom.getAttrib(ed.selection.getNode(), 'class', '').indexOf('mceItem') != -1)
					return;

				ed.windowManager.open({
					file : '/admin/_imagepopup',
					width : 600,
					height : 800,
					inline : 1,
					scrollbars : true
				}, {
					plugin_url : url
				});
			});

			// Register buttons
			ed.addButton('fwimage', {
				title : 'fwimage.image_desc',
				cmd : 'mceFWImage'
			});
		},

		getInfo : function() {
			return {
				longname : 'Advanced image',
				author : 'Moxiecode Systems AB',
				authorurl : 'http://tinymce.moxiecode.com',
				infourl : 'http://wiki.moxiecode.com/index.php/TinyMCE:Plugins/advimage',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
	});

	// Register plugin
	tinymce.PluginManager.add('fwimage', tinymce.plugins.FWImagePlugin);
})();