<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2012, {@link http://aksw.org AKSW}
 * @license   http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 */

require_once 'OntoWiki/Component/Helper.php';
require_once 'OntoWiki/Menu/Registry.php';

/**
 * @category   OntoWiki
 * @package    Extensions_Qbvalidator
 */
class QbvalidatorHelper extends OntoWiki_Component_Helper
{
    public function __construct()
    {
		$owApp      = OntoWiki::getInstance();
		$translate  = $owApp->translate;
		$url        = new OntoWiki_Url(array('controller' => 'qbvalidator', 'action' => 'analyze'), array());
		$extrasMenu = OntoWiki_Menu_Registry::getInstance()->getMenu('application')->getSubMenu('Extras');
		$extrasMenu->setEntry($translate->_('DataCube Dashboard'), (string)$url);
		
		$owApp->getNavigation()->disableNavigation();
		
    }
}
