/**
 * Captcha JS zum neuladen des Captchas
 * 
 * @since Version 1.2
 * @author Mathias 'United20' Schmidt
 * @package simplecontact
 * @subpackage captcha
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
!window.jQuery && document.write(unescape('%3Cscript src="//ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.js"%3E%3C/script%3E'));

/**
 * Captch neu laden
 * 
 * @since Version 1.2
 * @author Mathias 'United20' Schmidt
 * @param string img ID des Bildes
 * @param string id Hidden Field mit der CaptchaID
 * @param string url Url an den der Request gehen soll
 */
function reloadCaptcha(img, id, url)
{
    if ($('#'+img).length > 0 && $('#'+id).length)
    {
        $.ajax({
            url: url,
            data: { 'reload': true },
            dataType: 'json',
            success: function(json) {
        		$('#'+img).attr('src', url+'?code='+json.id);
        		$('#'+id).attr('value', json.id);
            }
        });
    }
}