<?php
/**
 * @author Marcus Ullrich
 * @version 0.0.1
 * @package Joomla.Plugin
 * @subpackage gu471s.keywordLinks
 * @copyright (C) 2005 - 2012 Open Source Matters. All rights reserved.
 * @license GNU/GPL
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.plugin.plugin');

class plgContentkeywordLinks extends JPlugin
{
	var $patternPrefix = "{kwl";
	var $patternSuffix = "}";
	var $finalpattern;

	public function __plgContentkeywordLinks( &$subject, $params )
	{
		parent::__construct( $subject, $params );
	}

	private function isKeyValid ($key)
	{
		$valid = false;
		strpos($key,"'")||strpos($key,'"')||
		strpos($key,"\\")|| $valid = true;
		return $valid;
	}

	private function getSqlFromKey ($key)
	{
		$key = " ".$key." ";
		// &amp => &
		$pattern[0] = "/&amp;/";
		$replace[0] = " &&& ";
		// Leerzeichen vor und nach Operatoren setzen
		$pattern[1] = "~([!()|])~";
		$replace[1] = " $1 ";
		// unnötige Leerzeichen entfernen
		$pattern[2] = "/\s+/";
		$replace[2] = " ";
		// ! Key => !Key
		$pattern[3] = "~([!])[\s]([\w])~";
		$replace[3] = "$1$2";
		// Key => SQL
		$pattern[4] = "~[\s]([^!^(^)^&^| ]+)~";
		$replace[4] = " metakey LIKE '%$1%'";
		// !Key => SQL
		$pattern[5] = "~[!]([^!^(^)^&^| ]+)~";
		$replace[5] = "metakey NOT LIKE '%$1%'";
		// ! => NOT
		$pattern[6] = "/[!]/";
		$replace[6] = "NOT";
		// & => SQL
		$pattern[7] = "/&&&/";
		$replace[7] = "AND";
		// | => SQL
		$pattern[8] = "/\|/";
		$replace[8] = "OR";

		$sql = preg_replace($pattern, $replace, $key);
		return "(".$sql.")";

	}

	//input:	$key - Schlagwort
	//return: 	J-Links für alle Artikel eines Schlagwortes $key
	private function getLinksForOne($key)
	{
		$output = "";

		if (!$this->isKeyValid($key))
		return "<i>".$this->patternPrefix." ".$key.$this->patternSuffix.JText::_('PLG_CONTENT_KEYWORDLINKS_NOTVALID')."</i>";

		// betroffene Artikel holen
		$sql = $this->getSqlFromKey($key);
		$db = JFactory::getDbo();
		$query = "SELECT id, title FROM #__content WHERE $sql ORDER BY title ASC";
		$db->setQuery( $query, 0, 0 );
		$rows = $db->loadObjectList();
		
		// Menu-ID holen 
		$itemid = JRequest::getVar('Itemid');
		// Artikel verlinken
		if ($rows){
			foreach($rows as $rows){
				$output .= "<a class=\"keywordLink\" href='".JRoute::_("?option=com_content&amp;view=article&amp;id=$rows->id&amp;Itemid=$itemid")."'>".$rows->title."</a> ";
			}
		}
		else
		{
			return "<i>".JText::_('PLG_CONTENT_KEYWORDLINKS_NOENTRIES').$this->patternPrefix." ".$key.$this->patternSuffix."</i>";
		}
		return $output;
	}

	// Finde alle Vorkommen von {kwl } und ersetze diese durch Links
	private function getKeywordsByPattern ($text)
	{
		preg_match_all($this->finalpattern,$text,$matches);
		foreach ($matches[0] as &$match)
		{
			$key = preg_replace($this->finalpattern,'$2',$match);
			$text = str_replace($match,$this->getLinksForOne($key),$text);
		}

		return $text;
	}

	function onContentPrepare( $context, &$row, &$params, $limitstart )
	{
		// CSS laden
		$document = JFactory::getDocument();
		$document->addStyleSheet(JRoute::_('plugins/content/keywordLinks/css/keywordLink.css'));
		//Sprachdateien laden
		$lang = JFactory::getLanguage();
		$lang->load('plg_content_keywordLinks', JPATH_ADMINISTRATOR);

		$this->finalpattern = "~($this->patternPrefix) (.*?)($this->patternSuffix)~";

		$row->text = $this->getKeywordsByPattern($row->text);
		return true;
	}
}
?>