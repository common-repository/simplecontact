=== simpleContact ===
Contributors: Mathias 'United20' Schmidt
Donate link: http://simplecontact.united20.de/
Tags: contact, formular, captcha, spam, sidebar, post, comments, admin, input, form, ajax, contact form, kontaktformular, kontakt, communication, kommunikation, kundenkontakt, formular
Requires at least: 2.7
Tested up to: 3.1.3
Stable tag: 1.2.2

simpleContact ist ein einfaches Kontaktformular ohne viele Spielereien mit einem effektiven Spamschutz. Bleiben 
Sie auf einfache Weise mit ihren Kunden und Besuchern in Kontakt.

== Description ==

Ohne viel Aufwand koennen Sie ihren Kunden und Besuchern ihres Webblogs ein einfaches, aber doch Recht 
umfangreiches und effektives Kontaktformular zur Verfuegung stellen. Natuerlich koennen Sie sich durch das 
integrierte Captcha effektiv gegen Spam und andere automatisierte Angriffe schuetzen. Noch nie war die 
Kommunkation einfacher als mit simpleContact.

== Installation ==

1. Laden Sie simpleContact von der Website http://simplcontact.united20.de/ herunter
2. Entpacken Sie das ZIP-Archiv mit Winzip, Winrar oder einer anderen gaengigen Packsoftware
3. Laden Sie den Ordner 'simplecontact' in das Verzeichniss '/wp-content/plugins/' ihres Wordpress-Blogs
4. Aktivieren Sie das Plugin unter dem Menuepunkt 'Plugins' in ihrem Wordpress-Blog
5. Ggfs. nehmen Sie gewünschte Einstellungen vor indem Sie in der Hauptnavigation auf simpleContact klicken
6. Erstellen Sie eine neue Seite und binden Sie das Kontaktformular. Sie haben zwei Moeglichkeiten das 
   Kontaktformular einzubinden. Sie koennen die intergrierten Buttons im Editor der Seite verwenden oder Sie 
   geben den Platzhalter [[simplecontact]] direkt in das Textfeld ein.
7. Passen Sie nach ihren Wuenschen das HTML des Kontaktformulars und der Danke-Seite an, indem Sie die Dateien 
   '/wp-content/plugins/simplecontact/Templates/done.phtml' und 
   '/wp-content/plugins/simplecontact/Templates/formular.phtml' bearbeiten

== Weitere Anwendungsfaelle ==

Es ist moeglich das Formular auch vorausgefuellt zu laden. Dazu kann man an den Link des Artikels/der Seite 
die Parameter: username (Textstring), email (Textstring/E-Mail Adresse) und subject (ID des Betreffs (die ID 
ist die Zahl hinter der # bei den Betreffs im Adminbereich von simpleContact)) anhaengen. Dies kann dann wie 
folgt genutzt werden:

 * http://example.org/blogurl/artikelurl/?email=info@example.org
 * http://example.org/blogurl/artikelurl/?email=info@example.org&username=LoremIpsum
 * http://example.org/blogurl/artikelurl/?email=info@example.org&username=LoremIpsum&subject=2

== Changelog ==

Version 1.00 vom 23.10.2008

 * Kontaktformular mit anlegbaren Betreffs
 * aktivieren/deaktivieren des Captchas
 * Mindestlaenge einer Nachricht
 * optionaler Bilderupload mit Aktivierung/Deaktivierung verschiedener Dateiformate

Version 1.01 vom 09.11.2008

 * Abfrage ob GD Libary installiert ist oder nicht

Version 1.1 vom 17.05.2009

 * Add: Autoresponder fuer neue Nachrichten
 * Add: Nachrichten als gelesen/ungelesen markieren
 * Change: Code Refacturing
 * Change: Templates an das Wordpress 2.7 Design angepasst
 * Remove: Bilderupload entfernt

Version 1.1.1 vom 19.05.2009

 * Add: Übersetzung des Plugins auf Englisch
 * Fix: Kritischer interner Fehler behoben
 * Change: kleine Aenderungen

Version 1.1.2 vom 10.06.2009

 * Change: Kleine interne Veraenderung

Version 1.2 vom 19.02.2011

 * Add: Buttons fuer den Editor der Seite wurden hinzugefuegt
 * Add: Neuer Platzhalter [[simplecontact]] wird verwendet mit Unterstuetzung der alten Platzhalter
 * Add: Reload des Captcha per JavaScript
 * Change: Die Template-Engine wurde technisch ueberarbeitet
 * Bugfix: Shorttags in den Templates entfernt

 Version 1.2.1 vom 09.03.2011

 * Bugfix: Pfadprobleme behoben, wenn Wordpress Blog in Unterverzeichnis liegt
 * Change: Readme/Doku erweitert

 Version 1.2.2 vom 22.05.2011

 * Bugfix: Captcha wurde nicht dargestellt
