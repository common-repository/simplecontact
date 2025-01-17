<?php

/**
 * zeigt das Captcha an
 * 
 * @since Version 1.0
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

session_start();

require_once (dirname(__FILE__) . '/classes/Captcha/Captcha.class.php');
$captcha = new Captcha();

if (isset($_GET['reload']) && $_GET['reload'] == "true")
{
	echo json_encode(array('id' => $captcha->getCaptchaId()));
	exit;
}
elseif (isset($_GET['code']) && is_numeric($_GET['code']))
{
	$captcha->showImage(trim($_GET['code']));
}
else
{
	header('HTTP/1.1 404 Not Found');
	exit;
}
