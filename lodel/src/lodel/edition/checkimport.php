<?php
/**
 * V�rification d'un import
 *
 * PHP version 4
 *
 * LODEL - Logiciel d'Edition ELectronique.
 *
 * Copyright (c) 2001-2002, Ghislain Picard, Marin Dacos
 * Copyright (c) 2003, Ghislain Picard, Marin Dacos, Luc Santeramo, Nicolas Nutten, Anne Gentil-Beccot
 * Copyright (c) 2004, Ghislain Picard, Marin Dacos, Luc Santeramo, Anne Gentil-Beccot, Bruno C�nou
 * Copyright (c) 2005, Ghislain Picard, Marin Dacos, Luc Santeramo, Gautier Poupeau, Bruno C�nou, Jean Lamy
 *
 * Home page: http://www.lodel.org
 *
 * E-Mail: lodel@lodel.org
 *
 * All Rights Reserved
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 *
 * @author Ghislain Picard
 * @author Jean Lamy
 * @copyright 2005, Ghislain Picard, Marin Dacos, Luc Santeramo, Gautier Poupeau, Bruno C�nou, Jean Lamy
 * @licence http://www.gnu.org/copyleft/gpl.html
 * @version CVS:$Id:
 * @package lodel/source/lodel/edition
 */

require "siteconfig.php";
require "auth.php";
authenticate(LEVEL_REDACTOR);
require "func.php";

require "taskfunc.php";
$task              = gettask($idtask);
$context['idtask'] = $idtask;
gettypeandclassfromtask($task, $context);

$textorig = $text = file_get_contents($task['fichier']);

require_once("xmlimport.php");
$handler = new XMLImportHandler;
$parser  = new XMLImportParser();
$parser->init($context['class']);
$parser->parse($text, $handler);

$context['tablecontents'] = $handler->contents();
$context['multidoc']      = $handler->multidoc;

require_once "view.php";
$view = &View::getView();
$view->render($context, "checkimport");

//--------------------------------------------------//
// definition of the handler to proceduce the table
//--------------------------------------------------//

/**
 * This class define the handler used during the first part of an entity import : 
 * the construction of a table containing all the field, the entries and the persons to check that
 * the document was correctly parsed
 */
class XmlImportHandler {

	var $_contents; /** the table content */

	function contents ()
	{
		return $this->_contents;
	}

	/**
	 * process the data
	 * @param string $data the data encountered
	 */
	function processData ($data) 
	{
		return $data;
	}
	
	/**
	 * process the differents tablefieds of the entity, constructing the table and adding information
	 * on the field.
	 * @param object $obj the corresponding field object
	 * @param string $data the data of this field
	 */
	function processTableFields ($obj, $data) 
	{
		if ($obj->style[0]==".") {
			return "<span style=\"background-color: violet;\">".$data."</span>";
		}
		$title = $obj->title;
		if ($obj->lang) {
			$title.= "<br />(".$obj->lang.")";
		}
		$this->_contents.= "<tr><td>". $title. "</td><td>". $data. "</td></tr>";
		return $data;
	}
	/**
	 * process the differents entry fields of the entity, constructing the table and 
	 * adding information on the entry.
	 * @param object $obj the corresponding entry object
	 * @param string $data the data of this field
	 */
	function processEntryTypes ($obj, $data) 
	{
		$this->_contents.= '<tr><td class="processentrytypes">'. $obj->style. "</td><td>". $data. "</td></tr>";
	}
	
	/**
	 * this function is used when  an object like an entry is encountered during the XML parsing.
	 * @param array $class an array with the name and the title of the class
	 * @param object $obj by default null, the object corresponding
	 */
	function openClass ($class, $obj = null) 
	{
	$this->_contents.= "<tr><td colspan=\"2\" class=\"openclass". $class[1]. "\">". $class[0]." &darr;</td></tr>";
	}

	function closeClass ($class, $multidoc=false) 
	{
		if ($multidoc) {
			$this->multidoc=true;
		}
		$this->_contents.= '<tr><td colspan="2" class="closeclass'. $class[1]. '">'. $class[0].' &uarr;</td></tr>';
  }
	
	/**
	 * process the differents persons fields of the entity, constructing the table and 
	 * adding information on the person.
	 * @param object $obj the corresponding person object
	 * @param string $data the data of this field
	 */
	function processPersonTypes ($obj, $data) 
	{
		$this->_contents.= '<tr><td class="processpersontypes">'. $obj->style. '</td><td>'. $data. '</td></tr>';
	}
	/**
	 * process the encountered character styles.
	 * @param object $obj the corresponding to the character style
	 * @param string $data the data which use this character style
	 */
	function processCharacterStyles ($obj, $data) 
	{
		return "<span style=\"background-color: gray;\">". $data. "</span>";
	}
	/**
	 * process the encountered internal styles.
	 * @param object $obj the corresponding to the internal style
	 * @param string $data the data which use this internal style
	 */
	function processInternalStyles ($obj, $data) 
	{
		if (strpos ($obj->conversion, "<li>") !== false) {
			$conversion = str_replace("<li>", "", $obj->conversion);
			$data = $conversion. preg_replace(array("/(<p\b)/", "/(<\/p>)/"), array("<li>\\1", "\\1</li>"), $data). closetags($conversion);
		}
		return '<div class="internalstyleblock"><span class="internalstyle">'. $obj->style. '</span>'. $data. "</div>";
	}

	/**
	 * process when an unknow paragraph style is encountered
	 * @param string $style the name of the unknown style
	 * @param string $data the data contained in this style
	 */
	function unknownParagraphStyle ($style, $data) 
	{
		$this->_contents.= "<tr><td>Style inconnu: ". $style. "</td><td>". $data. "</td></tr>";
	}
	/**
	 * process when an unknow character style is encountered
	 * @param string $style the name of the unknown style
	 * @param string $data the data contained in this style
	 */
	function unknownCharacterStyle($style,$data) 
	{
		return "<span style=\"background-color: #ff8080;\" title=\"". $style. "\">". $data. "</span>";
	}
}// end of Handler class

exit;
?>