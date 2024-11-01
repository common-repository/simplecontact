<?php

/**
 * Datenbankklasse fuer simpleContact
 *
 * @since Version 1.1
 * @author Mathias 'United20' Schmidt
 * @package simplecontact
 * @subpackage db
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
class ScDB
{

	/**
	 * Datenbankobjekt von Wordpress
	 * 
	 * @since Version 1.1
	 * @author Mathias 'United20' Schmidt
	 * @var object
	 */
	var $db = null;

	/**
	 * Klassenkonstruktor
	 * 
	 * @since Version 1.1
	 * @author Mathias 'United20' Schmidt
	 * @return ScDB
	 */
	function ScDB()
	{
		global $wpdb;
		$this->db = $wpdb;
		if (! defined('DB_PREFIX'))
		{
			define('DB_PREFIX', $this->db->prefix);
		}
	}

	/**
	 * fuehrt einen MySQL-Query aus
	 *
	 * @since Version 1.1
	 * @author Mathias 'United20' Schmidt
	 * @param string $query
	 * @return ressource
	 */
	function query($query)
	{
		assert('is_string($query); // $query ist kein String');
		return $this->db->query($this->replacePrefix($query));
	}

	/**
	 * Loeschen eines oder mehrerer Datensaetze aus der Datenbank
	 *
	 * @since Version 1.1
	 * @author Mathias 'United20' Schmidt
	 * @param string $table
	 * @param string $where
	 * @return integer
	 */
	function delete($table, $where)
	{
		assert('is_string($table); // $table ist kein String');
		assert('is_string($where); // $where ist kein String');
		return $this->query('DELETE FROM ' . $table . ' WHERE ' . $where);
	}

	/**
	 * Leeren einer Datenbanktabelle
	 *
	 * @since Version 1.1
	 * @author Mathias 'United20' Schmidt
	 * @param string $table
	 * @return integer
	 */
	function truncate($table)
	{
		assert('is_string($table); // $table ist kein String');
		return $this->query('TRUNCATE ' . $table);
	}

	/**
	 * Update eines Datensatzes
	 *
	 * @since Version 1.1
	 * @author Mathias 'United20' Schmidt
	 * @param string $table
	 * @param string/array $fields
	 * @param string $where
	 * @return integer
	 */
	function update($table, $fields, $where)
	{
		assert('is_string($table); // $table ist kein String');
		assert('is_string($fields) || is_array($fields); // $fields ist kein String oder Array');
		assert('is_string($where); // $where ist kein String');

		$query = 'UPDATE ' . $table . ' SET ';
		if (is_array($fields))
		{
			$values = array();
			foreach ($fields as $field => $value)
			{
				$values[] = $field . '="' . addslashes($value) . '"';
			}
			$query .= implode(',', $values);
		}
		else
		{
			$query .= $fields . ' ';
		}
		$query .= $where;
		return $this->query($query);
	}

	/**
	 * Fuegt einen Datensatz in eine Datenbanktabelle ein
	 *
	 * @since Version 1.1
	 * @author Mathias 'United20' Schmidt
	 * @param string $table
	 * @param array $fields
	 * @param array $options
	 * @return integer
	 */
	function insert($table, $fields, $options = array())
	{
		assert('is_string($table); // $table ist kein String');
		assert('is_string($fields) || is_array($fields); // $fields ist kein String oder Array');
		assert('is_array($options); // $options ist kein Array');

		$query = 'INSERT ';
		if (isset($options['ignore']) && $options['ignore'] == true)
		{
			$query .= 'IGNORE ';
		}
		$query .= 'INTO ' . $table . ' SET ';
		$queryFields = array();
		foreach ($fields as $key => $value)
		{
			if (preg_match('/^[0-9]+$/', $key))
			{
				$queryFields[] = addslashes($value);
			}
			else
			{
				$queryFields[] = $key . '="' . addslashes($value) . '"';
			}
		}
		$this->query($query . implode(', ', $queryFields));
		return mysql_insert_id();
	}

	/**
	 * gibt die Resultate eines Queries zurueck
	 *
	 * @since Version 1.1
	 * @author Mathias 'United20' Schmidt
	 * @param string $query
	 * @return array
	 */
	function fetchResults($query)
	{
		assert('is_string($query); // $query ist kein String');
		return $this->db->get_results($this->replacePrefix($query));
	}

	/**
	 * gibt einen Wert eines Queries zurueck
	 *
	 * @since Version 1.1
	 * @author Mathias 'United20' Schmidt
	 * @param string $query
	 * @return mixed
	 */
	function fetchOne($query)
	{
		assert('is_string($query); // $query ist kein String');
		return $this->db->get_var($this->replacePrefix($query));
	}

	/**
	 * gibt eine assoziatives Array eines Datensatzes zurueck
	 *
	 * @since Version 1.1
	 * @author Mathias 'United20' Schmidt
	 * @param string $query
	 * @return array
	 */
	function fetchAssoc($query)
	{
		assert('is_string($query); // $query ist kein String');
		return $this->db->get_row($this->replacePrefix($query), ARRAY_A);
	}

	/**
	 * gibt ein assoziatives Array zurueck, welches zusammengehoerige Paare zurueck gibt
	 *
	 * @since Version 1.1
	 * @author Mathias 'United20' Schmidt
	 * @param string $query
	 * @return array
	 */
	function fetchPairs($query)
	{
		assert('is_string($query); // $query ist kein String');

		$return = array();
		$data = $this->fetchResults($query);
		foreach ($data as $object)
		{
			$return[$object->subject_id] = $object->subject_name;
		}
		return $return;
	}

	/**
	 * ersetzen des Platzhalters # in einem Query
	 *
	 * @since Version 1.1
	 * @author Mathias 'United20' Schmidt
	 * @param string $query
	 * @return string
	 */
	function replacePrefix($query)
	{
		assert('is_string($query); // $query ist kein String');
		return str_replace('#', DB_PREFIX, $query);
	}

}
