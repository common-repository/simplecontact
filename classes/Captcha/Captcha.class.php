<?php

/**
 * Klasse zum generieren einer Captcha
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
class Captcha
{

	/**
	 * Captcha Breite
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @var integer
	 */
	var $imgWidth = 120;

	/**
	 * Captcha Hoehe
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @var integer
	 */
	var $imgHeight = 40;

	/**
	 * Abstaende zwischen den einzelnen Buchstaben in der Reihenfolge: Top, Right, Bottom, Left
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @var array
	 */
	var $textPadding = array(5, 6, 8, 11);

	/**
	 * Wie stark die Verschmutzung auf dem Captcha ist
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @var integer
	 */
	var $noise = 40;

	/**
	 * Verhaeltnis Kratzer / Schmutz
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @var integer
	 */
	var $DustVsScratches = 90;

	/**
	 * Anzahl der Zeichen auf dem Captcha
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @var integer
	 */
	var $numChars = 4;

	/**
	 * Die verwendeten Zeichen
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @var string
	 */
	var $letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

	/**
	 * Faktor fuer die Drehung eines Zeichens
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @var integer
	 */
	var $letterPrecession = 20;

	/**
	 * Schriftgroessen
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @var array
	 */
	var $fontSizes = array(22, 24, 26);

	/**
	 * die verwendeten Schriftarten
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @var array
	 */
	var $fonts = array('liberation-serif.ttf');

	/**
	 * 1. Hintergrundfarbe
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @var array
	 */
	var $bgColor1 = array(255, 255, 255);

	/**
	 * 2. Hintergrundfarbe
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @var array
	 */
	var $bgColor2 = array(180, 239, 171);

	/**
	 * 1. Vordergrundfarbe
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @var array
	 */
	var $fgColor1 = array(225, 8, 8);

	/**
	 * 2. Vordergrundfarbe
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @var array
	 */
	var $fgColor2 = array(225, 8, 166);

	/**
	 * 3. Vordergrundfarbe
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @var array
	 */
	var $fgColor3 = array(60, 174, 44);

	/**
	 * 4. Vordergrundfarbe
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @var array
	 */
	var $fgColor4 = array(39, 79, 209);

	/**
	 * das Objekt des Bildes
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @var ressource
	 */
	var $image = null;

	/**
	 * Konstruktor
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @return Captcha
	 */
	function Captcha()
	{
		if (!defined('COLOR_VALUE_R'))
		{
			define('COLOR_VALUE_R', 0);
		}
		if (!defined('COLOR_VALUE_G'))
		{
			define('COLOR_VALUE_G', 1);
		}
		if (!defined('COLOR_VALUE_B'))
		{
			define('COLOR_VALUE_B', 2);
		}
		$this->fonts = $this->getFonts();
	}

	/**
	 * Setzt Schriftarten auf Session-Ebene, so dass diese vom Captcha genutzt werden koennen
	 *
	 * @since Version 1.2
	 * @author Mathias 'United20' Schmidt
	 * @param mixed $fonts
	 */
	function setFonts($fonts)
	{
		if (!isset($_SESSION['plugins']['Fonts']))
		{
			$_SESSION['plugins']['Fonts'] = array();
		}
		if (is_array($fonts))
		{
			foreach ($fonts as $font)
			{
				if (file_exists(dirname(__FILE__) . '/fonts/' . $font))
				{
					$_SESSION['plugins']['Fonts'][] = $font;
				}
			}
		}
		elseif (is_string($fonts) && file_exists(dirname(__FILE__) . '/fonts/' . $fonts))
		{
			$_SESSION['plugins']['Fonts'][] = $fonts;
		}
	}
	
	/**
	 * Gibt alle Schriftarten aus der Session fuer das Captcha zurueck (mit Fallback)
	 *
	 * @since Version 1.2
	 * @author Mathias 'United20' Schmidt
	 * @return array
	 */
	function getFonts()
	{
		if (!isset($_SESSION['plugins']['Fonts']) || !count($_SESSION['plugins']['Fonts']) || !is_array($_SESSION['plugins']['Fonts']))
		{
			return $this->fonts;
		}
		return $_SESSION['plugins']['Fonts'];
	}

	/**
	 * Loescht alle Fonts aus der Session
	 *
	 * @since Version 1.2
	 * @author Mathias 'United20' Schmidt
	 */
	function cleanFonts()
	{
		$_SESSION['plugins']['Fonts'] = array();
	}

	/**
	 * Zeigt das bild On-the-Fly an
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @param integer $code CaptchaId
	 */
	function showImage($code)
	{
		$this->image = $this->createImageBase();

		$this->addDustAndScratches($this->bgColor1);
		$this->printCaptchaString($code);
		$this->addDustAndScratches($this->bgColor2);

		header("Content-type: image/jpeg");
		imagejpeg($this->image);
		imagedestroy($this->image);
	}

	/**
	 * Erstellt das Bildobjekt
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @return ressource
	 */
	function createImageBase()
	{
		$image = imagecreate($this->imgWidth, $this->imgHeight);
		imagecolorallocate($image, $this->bgColor1[COLOR_VALUE_R], $this->bgColor1[COLOR_VALUE_G], $this->bgColor1[COLOR_VALUE_B]);
		return $image;
	}

	/**
	 * fuegt zum Bild Kratzer und Schmutz
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @param string $color Farbe
	 */
	function addDustAndScratches($color)
	{
		$x = $this->imgWidth - 1;
		$y = $this->imgHeight - 1;
		$color = imagecolorallocate($this->image, $color[COLOR_VALUE_R], $color[COLOR_VALUE_G], $color[COLOR_VALUE_B]);
		for($i = 0; $i < $this->noise; ++ $i)
		{
			if (rand(1, 100) > $this->DustVsScratches)
			{
				imageline($this->image, rand(0, $x), rand(0, $y), rand(0, $x), rand(0, $y), $color);
			}
			else
			{
				imagesetpixel($this->image, rand(0, $x), rand(0, $y), $color);
			}
		}
	}

	/**
	 * Generiert das Captcha-Bild
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @param integer $code CaptchaId
	 */
	function printCaptchaString($code)
	{
		list($pTop, $pRight, $pBottom, $pLeft) = $this->textPadding;
		$width = ($this->imgWidth - ($pLeft + $pRight)) / $this->numChars;
		$height = $this->imgHeight - ($pTop + $pBottom);
		$fontsCount = (count($this->fonts) - 1);
		$fontSizeCount = (count($this->fontSizes) - 1);
		$fgColorsCount = 3;
		$fgColors = array($this->fgColor1, $this->fgColor2, $this->fgColor3, $this->fgColor4);

		for ($i = 0; $i < $this->numChars; ++$i)
		{
			$index = rand(0, $fontSizeCount);
			$size = $this->fontSizes[$index];
			$angle = ((rand(0, ($this->letterPrecession * 2)) - $this->letterPrecession) + 360) % 360;
			$x = $pLeft + ($width * $i);
			$y = $pTop + $size + (($height - $size) / 2);

			$colorIndex = (rand(0, $fgColorsCount));
			$color = $fgColors[$colorIndex];
			$color = imagecolorallocate($this->image, $color[0], $color[1], $color[2]);

			$index = rand(0, $fontsCount);
			imagettftext($this->image, $size, $angle, $x, $y, $color, dirname(__FILE__) . '/fonts/' . $this->fonts[$index], $_SESSION['plugins']['Captcha'][$code][$i]);
		}
	}

	/**
	 * generiert die CaptchaId
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @return integer
	 */
	function getCaptchaId()
	{
		$random = array();
		$max = strlen($this->letters) - 1;

		for ($i = 0; $i < $this->numChars; ++ $i)
		{
			$index = rand(0, $max);
			$random[] = $this->letters[$index];
		}
		if (! isset($_SESSION['plugins']['Captcha']))
		{
			$_SESSION['plugins']['Captcha'] = array();
		}
		$microtime = str_replace('.', '', microtime(true));
		$_SESSION['plugins']['Captcha'][$microtime] = $random;

		return $microtime;
	}

	/**
	 * Ueberpruefen ob Code korrekt eingeben ist
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @param string $user Code aus Formular
	 * @param string $code CaptchaId
	 * @return boolean
	 */
	function validateCaptchaString($user, $code)
	{
		if (! isset($_SESSION['plugins']['Captcha'][$code]))
		{
			return false;
		}
		$old = $_SESSION['plugins']['Captcha'][$code];
		unset($_SESSION['plugins']['Captcha'][$code]);
		return ((strtolower(implode($old))) == (strtolower($user)));
	}

}
