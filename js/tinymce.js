/**
 * Hook fuer den Tinymce Editor
 * 
 * @since Version 1.2
 * @author Mathias 'United20' Schmidt
 * @package simplecontact
 * @subpackage tinymce
 */

/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

(function() {
	tinymce.create('tinymce.plugins.simplecontact', {
		init: function(ed, url) {
			ed.addCommand('mceSimpleContact', function() {
				if(window.tinyMCE) {
					window.tinyMCE.execInstanceCommand('content', 'mceInsertContent', false, '[[simplecontact]]');
					window.tinyMCE.editor.execCommand('mceRepaint');
				}
			});

			ed.addButton('simplecontact', {
				title : 'SimpleContact',
				cmd : 'mceSimpleContact',
				image : url + '/../images/simplecontact-tinymce.png'
			});

			ed.onNodeChange.add(function(ed, cm, n) {
				cm.setActive('simplecontact', n.nodeName == 'IMG');
			});
		},

		createControl: function(n, cm) {
			return null;
		},

		getInfo: function() {
			return {
				longname: 'simplecontact',
				author: 'Mathias \'United20\' Schmidt',
				authorurl: 'http://united20.de/',
				infourl: 'http://simplecontact.united20.de/',
				version: '1.2'
			};
		}
	});

	tinymce.PluginManager.add('simplecontact', tinymce.plugins.simplecontact);
})();