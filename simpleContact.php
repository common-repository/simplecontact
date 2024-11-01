<?php

/*
Plugin Name: simpleContact
Plugin URI: http://simplecontact.united20.de/
Description: Kontaktformular f&uuml;r Wordpress mit einer einfachen Captcha-Abfrage. Kommunkation war noch nie so einfach mit Ihren Kunden und Besuchern.
Version: v1.2.2
Author: Mathias 'United20' Schmidt
Author URI: http://united20.de/
*/

/*
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// Laden einiger Plugins
require_once (dirname(__FILE__) . '/classes/Captcha/Captcha.class.php');
require_once (dirname(__FILE__) . '/classes/RegEx/RegEx.class.php');

$sc = new simpleContact();

add_action('init', array($sc, 'init'));

// Adminpanel hinzufuegen und Aktivierungs-Hook nur im Adminbereich hinzufuegen
if (preg_match('!wp\-admin!', $_SERVER['PHP_SELF']))
{
	// Aktivierung des Plugins
	register_activation_hook(__FILE__, array($sc, 'activate'));

	add_action('admin_menu', array($sc, 'admin'));
	add_action('admin_head', array($sc, 'head'));
	add_action('init', array($sc, 'buttons'));

	add_filter('editable_extensions', array($sc, 'editableExtensions'));
}

// Filter um das Kontaktformular anzuzeigen
add_filter('the_content', array($sc, 'content'));

// Debugmodus
if (defined('WP_DEBUG') && WP_DEBUG)
{
	assert_options(ASSERT_ACTIVE, 1);
	assert_options(ASSERT_WARNING, 0);
	assert_options(ASSERT_QUIET_EVAL, 1);
	assert_options(ASSERT_CALLBACK, 'assertException');

	/**
	 * Callback fuer eine fehlgeschlagene assert() Funktion 
	 *
	 * @param string $script
	 * @param integer $line
	 * @param string $message
	 */
	function assertException($script, $line, $message)
	{
		echo '<div style="font-family:courier new,courier,sans-serif; font-size:11px;"><strong>Condition for assertation failed!</strong><br /><br /><strong>Script:</strong> ' . $script . '<br /><strong>Line:</strong> ' . $line . '<br /><strong>Condition:</strong> ' . $message . '<div>';
		exit;
	}

	if (!function_exists('vd'))
	{
		/**
		 * Vardump mit <pre>
		 * 
		 * @since Version 1.0
		 * @author Mathias 'United20' Schmidt
		 * @param mixed $var
		 */
		function vd($var)
		{
			echo '<pre>';
			var_dump($var);
			echo '</pre>';
		}
	}
}
else
{
	assert_options(ASSERT_ACTIVE, 0);
}


/**
 * Klasse des Kontaktformulars
 *
 * @since Version 1.0
 * @author Mathias 'United20' Schmidt
 * @package simplecontact
 */
class simpleContact
{

	/**
	 * Platzhalter fuer das Kontaktformular
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @var array
	 */
	var $placeholder = array(
		'<!-- simple-contact -->',
		'<!-- simplecontact -->',
		'[[simplecontact]]'
	);

	/**
	 * Einstellungen aus der Datenbank
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @var array
	 */
	var $config = null;

	/**
	 * das Datenbankobjekt von Wordpress
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @var ScDB
	 */
	var $db = null;

	/**
	 * die Request-Daten ueber die URL
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @var array
	 */
	var $request = null;

	/**
	 * Die Daten aus dem Kontaktformular
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @var array
	 */
	var $form = null;
	
	/**
	 * der aktuelle Beitrag
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @var object
	 */
	var $post = null;

	/**
	 * die Eingabefehler
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @var array
	 */
	var $errors = array();

	/**
	 * wurde das Formular ohne Fehler abgeschickt und konnten die Daten in der Datenbank gespeichert werden
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @var boolean
	 */
	var $send = false;

	/**
	 * der Bilderpfad
	 * 
	 * @since Version 1.1
	 * @author Mathias 'United20' Schmidt
	 * @var string
	 */
	var $images = null;

	/**
	 * aktuelle Kontaktnachricht
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @var $array
	 */
	var $contact = null;

	/**
	 * Konstruktor der Klasse und holt die ueber das Formular verschickten Daten
	 * 
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @return simpleContact
	 */
	function simpleContact()
	{
		session_start();

		$this->initDB();
		$this->settings();
		$this->request();

		if (isset($_POST['simple_contact']) && is_array($_POST['simple_contact']) && count($_POST['simple_contact']))
		{
			$this->form = $_POST['simple_contact'];
			if ($this->validate())
			{
				$this->send = $this->save() !== false;
			}
		}
	}

	/**
	 * Hook fuer das Aktivieren des Plugins wo alle notwendigen Einstellungen geladen werden
	 *
	 * @since Version 1.1
	 * @author Mathias 'United20' Schmidt
	 */
	function activate()
	{
		// Einstellungen setzen
		add_option('simplecontact_email', 'on', 'Soll eine E-Mail verschickt werden, wenn eine neue Kontaktnachricht eingegangen ist.', 'yes');
		add_option('simplecontact_captcha', 'on', 'Soll ein Captcha fuer verwendet werden, um SPAM ab zu verhindern.', 'yes');
		add_option('simplecontact_message_min_lenght', '3', 'Mindestlaenge fuer eine Nachricht.', 'yes');

		// Tabellen erstellen
		if (! is_int($this->db->query('SELECT * FROM #simple_contact_subjects')))
		{
			$this->db->query('
				CREATE TABLE #simple_contact_subjects (
					subject_id int(10) unsigned not null auto_increment,
					subject_name varchar(100) not null,
					subject_order int(10) unsigned not null default "0",
					subject_active tinyint(1) unsigned not null default "1",
					PRIMARY KEY(subject_id)
				) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=' . DB_CHARSET . ';
			');
			$this->db->query('INSERT INTO #simple_contact_subjects (subject_id, subject_name, subject_order, subject_active) VALUES (NULL, "Allgemeine Frage", 1, 1);');
			$this->db->query('INSERT INTO #simple_contact_subjects (subject_id, subject_name, subject_order, subject_active) VALUES (NULL, "Verbesserungsvorschlag", 2, 1);');
			$this->db->query('INSERT INTO #simple_contact_subjects (subject_id, subject_name, subject_order, subject_active) VALUES (NULL, "Fehler melden", 3, 1);');
			$this->db->query('INSERT INTO #simple_contact_subjects (subject_id, subject_name, subject_order, subject_active) VALUES (NULL, "Werbeanfrage", 4, 1);');
		}
		if (! is_int($this->db->query('SELECT * FROM #simple_contact_messages')))
		{
			$this->db->query('
				CREATE TABLE #simple_contact_messages (
					message_id int(10) unsigned not null auto_increment,
					subject_id int(10) unsigned not null,
					message_username varchar(255) not null,
					message_email varchar(255) not null,
					message_date int(10) unsigned not null,
					message_ip varchar(40) not null default "127.0.0.1",
					message_message text not null,
					message_attachment varchar(255) not null,
					message_read tinyint(1) unsigned not null default 0,
					PRIMARY KEY (message_id)
				) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=' . DB_CHARSET . ';
			');
		}
	}

	/**
	 * laedt die Einstellungen fuer das Plugin
	 * 
	 * @since Version 1.1
	 * @author Mathias 'United20' Schmidt
	 * @param bool $force
	 */
	function settings($force = false)
	{
		assert('is_bool($force); // $force nicht vom Typ boolean');
		if (empty($this->config) || $force)
		{
			$this->config = array(
				'captcha' => $this->installedGDLibary() && get_option('simplecontact_captcha') == 'on',
				'message_min_length' => get_option('simplecontact_message_min_lenght'),
				'email' => get_option('simplecontact_email') == 'on'
			);
		}
		return $this->config;
	}

	/**
	 * Testen, ob die GD Library installiert ist
	 *
	 * @since Version 1.01
	 * @author Mathias 'United20' Schmidt
	 * @return boolean
	 */
	function installedGDLibary()
	{
		return @extension_loaded('gd');
	}

	/**
	 * initialisieren des Datenbankobjektes
	 * 
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 */
	function initDB()
	{
		require_once (dirname(__FILE__) . '/classes/ScDB/ScDB.class.php');
		$this->db = new ScDB();
	}

	/**
	 * holen der Request-Daten ueber die URL
	 * 
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 */
	function request()
	{
		$this->request = array(
			'username' => $this->getRegEx('username', RegEx::ascii(), ''),
			'email' => $this->getRegEx('email', RegEx::email(), ''),
			'subject' => $this->getRegEx('subject', '/^[0-9]{0,10}$/', 0)
		);
	}

	/**
	 * Holt aus dem $_GET oder $_POST Array das spezifizierte Feld
	 *
	 * @since Version 1.1
	 * @author Mathias 'United20' Schmidt
	 * @param string $var
	 * @param mixed $default
	 * @return mixed
	 */
	function get($var, $default = null)
	{
		assert('is_string($var); // $var ist kein String');
		if (isset($_GET[$var]))
		{
			return $_GET[$var];
		}
		elseif (isset($_POST[$var]))
		{
			return $_POST[$var];
		}
		return $default;
	}

	/**
	 * Holt aus dem $_GET oder $_POST Array das spezifizierte Feld und prueft es anhand eines RegEx
	 *
	 * @since Version 1.1
	 * @author Mathias 'United20' Schmidt
	 * @param string $var
	 * @param string $regex
	 * @param mixed $default
	 * @return integer
	 */
	function getRegEx($var, $regex, $default = null)
	{
		assert('is_string($var); // $var ist kein String');
		assert('is_string($regex); // $regex ist kein String');

		$get = $this->get($var, $default);
		if (is_string($get) && preg_match($regex, $get))
		{
			return $get;
		}
		return $default;
	}

	/**
	 * ersetzen des Platzhalters im Content der Seite
	 *
	 * @since Version 1.1
	 * @author Mathias 'United20' Schmidt
	 * @param string $content
	 * @return string
	 */
	function content($content)
	{
		return str_replace($this->placeholder, $this->formular(), $content);
	}

	/**
	 * anzeigen des Kontaktformulars bzw. der Danke-Seite
	 *
	 * @since Version 1.1
	 * @author Mathias 'United20' Schmidt
	 * @return string
	 */
	function formular()
	{
		if ($this->send == true)
		{
			$file = dirname(__FILE__) . '/templates/done.phtml';
		}
		else
		{
			$subjects = $this->getSubjects();

			// Captcha laden
			$captcha = new Captcha();
			// $captcha->cleanFonts();
			// $captcha->setFonts('basicdots.ttf');

			$captchaId = $captcha->getCaptchaId();
			$request = $this->request;
			$file = dirname(__FILE__) . '/templates/formular.phtml';
			$path = WP_PLUGIN_URL . '/' . plugin_basename(dirname(__FILE__)) . '/';
		}

		// Template laden
		ob_start();
		require_once ($file);
		$tpl = ob_get_contents();
		ob_end_clean();

		return $tpl;
	}

	/**
	 * Captcha-Validierung
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @param integer $captchaId ID fuer das Captcha
	 * @param string $code eingebener Code
	 * @return boolean
	 */
	function captcha($captchaId, $code)
	{
		$captcha = new Captcha();
		return $captcha->validateCaptchaString($code, $captchaId);
	}

	/**
	 * holen der Betreffs fuer das Kontaktformular
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @return array
	 */
	function getSubjects()
	{
		return $this->db->fetchPairs('SELECT subject_id, subject_name FROM #simple_contact_subjects WHERE subject_active=1 ORDER BY subject_order ASC, subject_name ASC');
	}

	/**
	 * Validieren der Eingaben und gibt zurueck, ob die eingabe valide ist
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @return boolean
	 */
	function validate()
	{
		if (strlen(trim($this->form['username'])) <= 0 || strlen(trim($this->form['username'])) > 100 || ! preg_match(RegEx::ascii(), $this->form['username']))
		{
			$this->errors[] = 'error username';
		}
		if (! preg_match(RegEx::email(), $this->form['email']))
		{
			$this->errors[] = 'error email';
		}
		if (! $this->form['subject'])
		{
			$this->errors[] = 'error subject';
		}
		if (strlen(trim($this->form['message'])) < $this->config['message_min_length'])
		{
			$this->errors[] = 'error message';
		}
		if ($this->config['captcha'] && ! $this->captcha($this->form['captcha_id'], $this->form['code']))
		{
			$this->errors[] = 'error captcha';
		}
		return ! count($this->errors);
	}

	/**
	 * prueft ob ein Fehler vorhanden ist oder nicht
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @param string $type
	 * @return boolean
	 */
	function hasError($type)
	{
		return in_array($type, $this->errors);
	}

	/**
	 * speichern der Daten des Kontaktformulars
	 *
	 * @since Version 1.1
	 * @author Mathias 'United20' Schmidt
	 * @return integer
	 */
	function save()
	{
		$insert = array(
			'subject_id' => $this->form['subject'], 
			'message_username' => $this->convertToUtf8($this->form['username']), 
			'message_email' => $this->form['email'], 
			'message_date' => time(), 
			'message_ip' => getenv('REMOTE_ADDR'), 
			'message_message' => $this->convertToUtf8($this->form['message'])
		);
		if ($this->config['email'])
		{
			$this->sendMail($insert);
		}
		return $this->db->insert('#simple_contact_messages', $insert, array('ignore' => true));
	}

	/**
	 * Codiert einen String nach UTF-8 wenn noetig
	 * 
	 * @since Version 1.1
	 * @author Mathias 'United20' Schmidt
	 * @param string $string
	 * @param string $type
	 * @return string
	 */
	function convertToUtf8($string, $type = 'UTF-8')
	{
		if (mb_detect_encoding($string) == 'ASCII' || $type == 'ISO-8859-1' || $type == '')
		{
			$string = utf8_encode($string);
		}
		return $string;
	}

	/**
	 * Initialisiert die Multisprachfaehigkeit
	 * 
	 * @since Version 1.1
	 * @author Mathias 'United20' Schmidt
	 */
	function init()
	{
		load_plugin_textdomain('simplecontact', false, dirname(plugin_basename(__FILE__)) . '/language/');
	}

	/**
	 * verknuepft die Einstellungsseite von simpleContact mit dem Adminmenu
	 * 
	 * @since Version 1.1
	 * @author Mathias 'United20' Schmidt
	 */
	function admin()
	{
		$this->imageDir();
		add_menu_page(__('simpleContact Kontaktformular', 'simplecontact'), __('simpleContact', 'simplecontact'), 10, __FILE__, array($this, 'options'), $this->images . ($this->hasNewMessages() ? 'simplecontact-new.png' : 'simplecontact.png'));
	}
	
	function head()
	{
		echo ' <script type="text/javascript">
	<!--
		edButtons[edButtons.length]=new edButton("simplecontact", "simplecontact", "[[simplecontact]]", false, "SimpleContact");
	//-->
	</script>';
	}

	/**
	 * Filter fuer zusaetzlichen Button definieren
	 * 
	 * @since Version 1.2
	 * @author Mathias 'United20' Schmidt
	 */
	function buttons()
	{
		if (!current_user_can('edit_posts') && !current_user_can('edit_pages'))
		{
			return ;
		}
	 
		if (get_user_option('rich_editing'))
		{
			add_filter('mce_external_plugins', array($this, 'tinymcePlugin'));
			add_filter('mce_buttons', array($this, 'tinymceButtons'));
		}
	}

	/**
	 * Neuen zusaetzlichen Button fuer TinyMCE definieren
	 * 
	 * @since Version 1.2
	 * @author Mathias 'United20' Schmidt
	 * @param array $buttons
	 * @return array
	 */
	function tinymceButtons($buttons)
	{
		array_push($buttons, 'separator', 'simplecontact');
		return $buttons;
	}

	/**
	 * Setzt den Pfad fuer die TinyMCE JS Datei des Plugins
	 * 
	 * @since Version 1.2
	 * @author Mathias 'United20' Schmidt
	 * @param array $plugins
	 * @return array
	 */
	function tinymcePlugin($plugins)
	{
		$plugins['simplecontact'] = WP_PLUGIN_URL . '/' . plugin_basename(dirname(__FILE__)) . '/js/tinymce.js';
		return $plugins;
	}

	/**
	 * holt den aktuellen Pfad zu den Bildern und Icons
	 * 
	 * @since Version 1.1
	 * @author Mathias 'United20' Schmidt
	 */
	function imageDir()
	{
		$this->images = WP_PLUGIN_URL . '/' . plugin_basename(dirname(__FILE__)) . '/images/';
	}

	/**
	 * Die Seite mit den Optionen
	 * 
	 * @since Version 1.1
	 * @author Mathias 'United20' Schmidt
	 */
	function options()
	{
		// Formulare entgegen nehmen
		if (isset($_POST['simplecontact']))
		{
			$message = $this->saveSettings($_POST['simplecontact']);
		}
		elseif (isset($_POST['newsubject']))
		{
			$message = $this->addNewSubject($_POST['newsubject']);
			if ($message != 'newsubject')
			{
				$newsubject = $_POST['newsubject'];
			}
		}
		elseif (isset($_POST['subject']))
		{
			$errors = $this->updateOrder($_POST['subject']);
			$message = count($errors) ? 'error order' : 'order';
		}
		else
		{
			// Status setzen
			if (isset($_GET['deactivate']) && preg_match('/^[0-9]+$/', $_GET['deactivate']))
			{
				$status = $this->setStatus((int)$_GET['deactivate'], 'deactivate');
			}
			if (isset($_GET['activate']) && preg_match('/^[0-9]+$/', $_GET['activate']))
			{
				$status = $this->setStatus((int)$_GET['activate'], 'activate');
			}
			if (isset($status))
			{
				$message = $status ? 'status' : null;
				$status = isset($_GET['activate']) ? $_GET['activate'] : $_GET['deactivate'];
			}
			// gelesen/ungelesen
			if (isset($_GET['read']) && preg_match('/^[0-9]+$/', $_GET['read']))
			{
				$status = $this->setRead((int)$_GET['read'], 'read');
			}
			if (isset($_GET['unread']) && preg_match('/^[0-9]+$/', $_GET['unread']))
			{
				$status = $this->setRead((int)$_GET['unread'], 'unread');
			}
			// Betreff loeschen
			if (isset($_GET['delete']) && preg_match('/^[0-9]+$/', $_GET['delete']))
			{
				$delete = $this->deleteSubject((int)$_GET['delete']);
				$message = $delete ? 'delete' : null;
				$delete = $_GET['delete'];
			}
			// Nachricht loeschen
			if (isset($_GET['m_delete']) && preg_match('/^[0-9]+$/', $_GET['m_delete']))
			{
				$contact = $this->getContact((int)$_GET['m_delete']);
				$delete = $this->deleteMessage((int)$_GET['m_delete']);
				$message = $delete ? 'message delete' : null;
				$name = $contact->message_username;
				$date = $contact->message_date;
			}
		}

		$data = $this->settings(true);
		$list = $this->getList();
		$gd = $this->installedGDLibary();

		require_once (dirname(__FILE__) . '/templates/options.phtml');
	}

	/**
	 * holt alle Betreffs und Kontaktanfragen
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @return array
	 */
	function getList()
	{
		$subject = null;
		$list = array();
		$res = $this->db->fetchResults('SELECT sub.subject_id AS id, sub.*, m.* FROM #simple_contact_subjects AS sub LEFT JOIN #simple_contact_messages AS m ON sub.subject_id=m.subject_id ORDER BY subject_order ASC, subject_name ASC');
		foreach ($res as $data)
		{
			if (empty($subject) || $data->subject_id != $subject)
			{
				$subject = $data->id;
				$list[$subject] = array(
					'subject_id' => $subject, 
					'subject_name' => $data->subject_name, 
					'subject_order' => $data->subject_order, 
					'subject_active' => $data->subject_active, 
					'subject_messages' => array()
				);
			}
			if (! empty($data->message_id))
			{
				$list[$subject]['subject_messages'][] = array(
					'message_id' => $data->message_id, 
					'message_username' => $data->message_username, 
					'message_email' => $data->message_email, 
					'message_date' => $data->message_date, 
					'message_ip' => $data->message_ip, 
					'message_message' => $data->message_message, 
					'message_read' => $data->message_read
				);
			}
		}
		return $list;
	}


	/**
	 * Fuegt einen neuen Betreff hinzu. Validiert diesen vorher und gibt ggfs. eine Fehlermeldung aus.
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @param array $subject
	 * @return string
	 */
	function addNewSubject($subject)
	{
		if (strlen(trim($subject['name'])) && preg_match('/^[0-9]+$/', $subject['order']))
		{
			$this->db->insert('#simple_contact_subjects', array('subject_name' => $subject['name'], 'subject_order' => $subject['order'], 'subject_active' => 1), array('ignore' => true));
			return 'newsubject';
		}
		return 'error newsubject';
	}

	/**
	 * Setzen des Status fuer die Betreffs
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @param integer $id
	 * @param string $type
	 * @return integer
	 */
	function setStatus($id, $type)
	{
		assert('is_int($id); // $id nicht vom Typ integer');
		assert('in_array($type, array("activate", "deactivate")); // $type besitzt nicht den Wert "activate" oder "deactivate"');

		$type = ($type == 'activate' ? 1 : 0);
		return $this->db->update('#simple_contact_subjects', array('subject_active' => $type), 'WHERE subject_id=' . addslashes($id));
	}

	/**
	 * Loeschen eines Betreffs
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @param integer $id
	 * @return string
	 */
	function deleteSubject($id)
	{
		assert('is_int($id); // $id nicht vom Typ integer');

		$this->db->delete('#simple_contact_messages', 'subject_id=' . addslashes($id));
		return $this->db->delete('#simple_contact_subjects', 'subject_id=' . addslashes($id));
	}

	/**
	 * holen der Daten einer Kontaktnachricht
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @param integer $ids
	 * @param boolean $force
	 * @return object/array
	 */
	function getContact($ids, $force = false)
	{
		assert('is_bool($force); // $force ist nicht vom Typ boolean');
		if (! is_array($ids))
		{
			$ids = array($ids);
		}
		if (empty($this->contact) || $force)
		{
			$this->contact = $this->db->fetchResults('SELECT * FROM #simple_contact_messages WHERE message_id IN ("' . implode('","', $ids) . '")');
		}
		if (count($this->contact) > 1)
		{
			return $this->contact;
		}
		elseif (count($this->contact) == 1)
		{
			return $this->contact[0];
		}
		return false;
	}

	/**
	 * Loeschen einer Kontaktnachricht
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @param integer $id
	 * @return string
	 */
	function deleteMessage($id)
	{
		assert('is_int($id); // $id nicht vom Typ integer');
		return $this->db->delete('#simple_contact_messages', 'message_id=' . addslashes($id));
	}

	/**
	 * Aktualisieren der Reihenfolge fuer die Betreffs
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @param array $subjects
	 * @return array
	 */
	function updateOrder($subject)
	{
		assert('is_array($subject["order"]); // $subject["order"] nicht vom Typ array');

		$errors = array();
		foreach ($subject['order'] as $id => $value)
		{
			if (preg_match('/^[0-9]+$/', $value))
			{
				$this->db->update('#simple_contact_subjects', array('subject_order' => addslashes($value)), 'WHERE subject_id=' . addslashes($id));
			}
			else
			{
				$errors[] = '#' . $id;
			}
		}
		return $errors;
	}

	/**
	 * speichert die Einstellungen fuer das Kontaktformular
	 *
	 * @since Version 1.0
	 * @author Mathias 'United20' Schmidt
	 * @param array $settings
	 * @return string
	 */
	function saveSettings($settings)
	{
		if (! empty($settings) && is_array($settings) && count($settings))
		{
			$settings['simplecontact_captcha'] = isset($settings['simplecontact_captcha']) && $settings['simplecontact_captcha'] == 'on' ? 'on' : 'off';
			$settings['simplecontact_email'] = isset($settings['simplecontact_email']) && $settings['simplecontact_email'] == 'on' ? 'on' : 'off';
			foreach ($settings as $key => $value)
			{
				if (strlen(trim($value)))
				{
					update_option($key, trim($value));
				}
			}
			if (function_exists('wp_cache_flush'))
			{
				wp_cache_flush();
			}
			return 'settings';
		}
		return false;
	}

	/**
	 * sind neue Nachrichten vorhanden
	 *
	 * @since Version 1.1
	 * @author Mathias 'United20' Schmidt
	 * @return boolean
	 */
	function hasNewMessages()
	{
		return $this->db->fetchOne('SELECT COUNT(message_id) FROM #simple_contact_messages WHERE message_read=0') > 0;
	}

	/**
	 * verschickt eine Info-Mail an den Admin
	 *
	 * @since Version 1.1
	 * @author Mathias 'United20' Schmidt
	 * @param array $message
	 * @return boolean
	 */
	function sendMail($message)
	{
		assert('is_array($message); // $message nicht vom Typ array');

		$to = get_option('admin_email');
		$subject =sprintf(__('Neue Kontaktnachricht von %s vorhanden auf dem Blog %s', 'simplecontact'), $message['message_username'], get_option('blogname', $_SERVER['HTTP_HOST']));
		$message = sprintf(__("Hallo Administrator,\n\nes liegt eine neue Kontaktnachricht von %s vor. Klicken Sie hier, um zur Kontaktnachricht zu gelangen:\n\n%s", 'simplecontact'), $message['message_username'], trim(get_option('siteurl'), '/') . '/wp-admin/admin.php?page=simplecontact/simpleContact.php');
		$headers = 'FROM: ' . __('Wordpress Plugin simpleContact', 'simplecontact');
		if (RegEx::email($to))
		{
			return @mail($to, $subject, $message, $headers);
		}
		return false;
	}

	/**
	 * Setzt eine Nachricht auf gelesen/ungelesen
	 *
	 * @since Version 1.1
	 * @author Mathias 'United20' Schmidt
	 * @param integer $id
	 * @param string $type
	 * @return integer
	 */
	function setRead($id, $type)
	{
		assert('is_int($id); // $id nicht vom Typ integer');
		assert('in_array($type, array("read", "unread")); // $type besitzt nicht den Wert "read" oder "unread"');

		$type = ($type == 'read' ? 1 : 0);
		return $this->db->update('#simple_contact_messages', array('message_read' => $type), 'WHERE message_id=' . addslashes($id));
	}

	/**
	 * Erweitert den Plugin-Editor um die Templates
	 *
	 * @since Version 1.2.2
	 * @param array $ext
	 * @return array
	 */
	public function editableExtensions($ext)
	{
		$ext[] = 'tpl';
		$ext[] = 'phtml';
		return $ext;
	}

}
