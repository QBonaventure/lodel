<?php
/**	
 * Logique des utilisateurs
 *
 * PHP version 4
 *
 * LODEL - Logiciel d'Edition ELectronique.
 *
 * Home page: http://www.lodel.org
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
 * @package lodel/logic
 * @author Ghislain Picard
 * @author Jean Lamy
 * @copyright 2001-2002, Ghislain Picard, Marin Dacos
 * @copyright 2003, Ghislain Picard, Marin Dacos, Luc Santeramo, Nicolas Nutten, Anne Gentil-Beccot
 * @copyright 2004, Ghislain Picard, Marin Dacos, Luc Santeramo, Anne Gentil-Beccot, Bruno C�nou
 * @copyright 2005, Ghislain Picard, Marin Dacos, Luc Santeramo, Gautier Poupeau, Jean Lamy
 * @licence http://www.gnu.org/copyleft/gpl.html
 * @since Fichier ajout� depuis la version 0.8
 * @version CVS:$Id$
 */


/**
 * Classe de logique des utilisateurs
 * 
 * @package lodel/logic
 * @author Ghislain Picard
 * @author Jean Lamy
 * @copyright 2001-2002, Ghislain Picard, Marin Dacos
 * @copyright 2003, Ghislain Picard, Marin Dacos, Luc Santeramo, Nicolas Nutten, Anne Gentil-Beccot
 * @copyright 2004, Ghislain Picard, Marin Dacos, Luc Santeramo, Anne Gentil-Beccot, Bruno C�nou
 * @copyright 2005, Ghislain Picard, Marin Dacos, Luc Santeramo, Gautier Poupeau, Jean Lamy
 * @licence http://www.gnu.org/copyleft/gpl.html
 * @since Classe ajout� depuis la version 0.8
 * @see logic.php
 */
class UsersLogic extends Logic {

	/** Constructor
	*/
	function UsersLogic() {
		$this->Logic("users");
	}

	/**
	*  Indique si un objet est prot�g� en suppression
	*
	* Cette m�thode indique si un objet, identifi� par son identifiant num�rique et
	* �ventuellement son status, ne peut pas �tre supprim�. Dans le cas o� un objet ne serait
	* pas supprimable un message est retourn� indiquant la cause. Sinon la m�thode renvoit le
	* booleen false.
	*
	* @param integer $id identifiant de l'objet
	* @param integer $status status de l'objet
	* @return false si l'objet n'est pas prot�g� en suppression, un message sinon
	*/
	function isdeletelocked($id,$status=0) 

	{
		global $lodeluser;
		if ($lodeluser['id']==$id && 
	( ($GLOBALS['site'] && $lodeluser['rights']<LEVEL_ADMINLODEL) ||
		(!$GLOBALS['site'] && $lodeluser['rights']==LEVEL_ADMINLODEL))) {
			return getlodeltextcontents("cannot_delete_current_user","common");
		} else {
			return false;
		}
		//) { $error["error_has_entities"]=$count; return "_back"; }
	}


	/**
		* make the select for this logic
		*/

	function makeSelect(&$context,$var)

	{
		switch($var) {
		case "usergroups" :       
			$dao=&getDAO("usergroups");
			$list=$dao->findMany("status>0","rank,name","id,name");
			$arr=array();
			foreach($list as $group) {
	$arr[$group->id]=$group->name;
			}
			if (!$arr) $arr[1]="--";
			renderOptions($arr,$context['usergroups']);
			break;
		case "userrights":
			require_once("commonselect.php");
			makeSelectUserRights($context['userrights'],!$GLOBALS['site'] || SINGLESITE);
			break;
		case "lang" :
			// get the language available in the interface
			
			$dao=&getDAO("translations");
			$list=$dao->findMany("status>0 AND textgroups='interface'","rank,lang","lang,title");
			$arr=array();
			foreach($list as $lang) {
	$arr[$lang->lang]=$lang->title;
			}
			if (!$arr) $arr['fr']="Francais";
			renderOptions($arr,$context['lang']);
		}
	}

	/*---------------------------------------------------------------*/
	//! Private or protected from this point
	/**
		* @private
		*/
	/**
	* Pr�paration de l'action Edit
	*
	* @access private
	* @param object $dao la DAO utilis�e
	* @param array &$context le context pass� par r�f�rence
	*/
	function _prepareEdit($dao,&$context) 

	{
		// encode the password
		if ($context['passwd']) $context['passwd']=md5($context['passwd'].$context['username']);
	}



	function _populateContextRelatedTables(&$vo,&$context)

	{
		if ($vo->userrights<=LEVEL_EDITOR) {
			$dao=&getDAO("users_usergroups");
			$list=$dao->findMany("iduser='".$vo->id."'","","idgroup");
			$context['usergroups']=array();
			foreach($list as $relationobj) {
	$context['usergroups'][]=$relationobj->idgroup;
			}
		}
	}
	/**
	* Sauve des donn�es dans des tables li�es �ventuellement
	*
	* Appel� par editAction pour effectuer des op�rations suppl�mentaires de sauvegarde.
	*
	* @param object $vo l'objet qui a �t� cr��
	* @param array $context le contexte
	*/
	function _saveRelatedTables($vo,$context) 

	{
		global $db;
		if ($vo->userrights<=LEVEL_EDITOR) {
			if (!$context['usergroups']) $context['usergroups']=array(1);

			// change the usergroups     
			// first delete the group
			$this->_deleteRelatedTables($vo->id);
			// now add the usergroups
			foreach ($context['usergroups'] as $usergroup) {
	$usergroup=intval($usergroup);
	$db->execute(lq("INSERT INTO #_TP_users_usergroups (idgroup, iduser) VALUES  ('$usergroup','$id')")) or dberror();
			}
		}
	}

	function _deleteRelatedTables($id) {
		global $db;
		if ($GLOBALS['site']) { // only in the site table
			$db->execute(lq("DELETE FROM #_TP_users_usergroups WHERE iduser='$id'")) or dberror();
		}
	}


	function validateFields(&$context,&$error) {
		global $db,$lodeluser;

		if (!Logic::validateFields($context,$error)) return false;

		// check the user has the right equal or higher to the new user
		if ($lodeluser['rights']<$context['userrights']) die("ERROR: You don't have the right to create a user with rights higher than yours");

		// Check the user is not duplicated in the main table...
		if (!usemaindb()) return true; // use the main db, return if it is the same as the current one.

		$ret=$db->getOne("SELECT 1 FROM ".lq("#_TP_".$this->maintable)." WHERE status>-64 AND id!='".$context['id']."' AND username='".$context['username']."'");
		if ($db->errorno()) die($this->errormsg());
		usecurrentdb();

		// check the passwd is given for new user.
		if (!$context['id'] && !trim($context['passwd'])) {
			$error['passwd']=1;
			return false;
		}

		if ($ret) {
			$error['username']="1"; // report the error on the first field
			return false;
		} else {
			return true;
		}
	}


	// begin{publicfields} automatic generation  //

	/**
	 * Retourne la liste des champs publics
	 * @access private
	 */
	function _publicfields() 
	{
		return array('username' => array('username', '+'),
									'passwd' => array('passwd', ''),
									'name' => array('text', ''),
									'email' => array('email', ''),
									'userrights' => array('select', '+'),
									'lang' => array('lang', '+'));
	}
	// end{publicfields} automatic generation  //

	// begin{uniquefields} automatic generation  //

	/**
	 * Retourne la liste des champs uniques
	 * @access private
	 */
	function _uniqueFields() 
	{ 
		return array(array('username'), );
	}
	// end{uniquefields} automatic generation  //

} // class 


/*-----------------------------------*/
/* loops                             */

?>