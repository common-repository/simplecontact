<?php

/**
 * Regulaere Ausdruecke
 * 
 * @since Version 1.0
 * @author Mathias 'United20' Schmidt
 * @package simplecontact
 * @subpackage regex
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
class RegEx
{

	/**
	 * RegEx fuer eine E-Mail Adresse
	 *
 	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @return string
	 */
	function email()
	{
		return '/^[a-z0-9_\-]+(\.[_a-z0-9\-]+)*@([_a-z0-9\-]+\.)+([a-z]{2}|aero|arpa|biz|com|coop|edu|gov|info|int|jobs|mil|museum|name|nato|net|org|pro|travel|lan)$/';
	}

	/**
	 * RegEx fuer URL
	 *
 	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @return string
	 */
	function url()
	{
		return '/^((https?|ftp|news):\/\/)?([a-z]([a-z0-9\-]*\.)+([a-z]{2}|aero|arpa|biz|com|coop|edu|gov|info|int|jobs|mil|museum|name|nato|net|org|pro|travel|lan)|(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]))(\/[a-zA-Z0-9_\-\.~]+)*(\/([a-zA-Z0-9_\-\.]*)(\?[a-zA-Z0-9+_\-\.%=&amp;]*)?)?(#[a-zA-Z][a-zA-Z0-9_]*)?$/';
	}

	/**
	 * RegEx fuer einen Text mit regul�ren ASCII Zeichen
	 *
 	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @return string
	 */
	function ascii()
	{
		return '/^[0-9a-zA-Z- \x80-\xFF]+$/';
	}

}
