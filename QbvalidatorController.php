<?php
/**
 * This file is part of the {@link http://ontowiki.net OntoWiki} project.
 *
 * @copyright Copyright (c) 2011, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @package Extensions
 */

/**
 * The cube helper class file which class is used by the controller to operate
 * on the data cube vocabulary
 */
require_once 'CubeHelper.php';

/**
 * Basic controller for the component
 *
 * @copyright Copyright (c) 2013, {@link http://aksw.org AKSW}
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License (GPL)
 * @category OntoWiki
 * @package Extensions
 * @subpackage QBValidator
 * @author Michael Martin <martin@informatik.uni-leipzig.de>
 */
class QbvalidatorController extends OntoWiki_Controller_Component
{

    /**
     * Holds the uris from the configuration file to access the graph
     * @var array contains all uris: elementId => uri
     */
    private $_uris = array();

    /**
     * Holds the uri patterns from the configuration file for creating new elements
     * @var array contains all uri pattertns: elementType => pattern
     */
    private $_uriPattern = array();

    /**
     * Holds the uri elements from the configuration file as the components of
     * uri patterns
     * @var array contains all enabled uri elements for uri patterns: uriElement => true
     */
    private $_uriElements = array();

    /**
     * Holds all configurable options from the configuration file, i.e. the
     * pagination limit
     * @var array contains all options: optionKey => optionValue
     */
    private $_options = array();

    /**
     * Holds all registered and thereby enabled parser classes
     * @var array contains all parser files: parserClass => parserName
     */
    private $_parser = array();

    /**
     * Holds all registered and thereby enabled addon classes for the parsers
     * @var array contains all addon files: parserClass => { addonClass => true }
     */
    private $_addon = array();

    private $_resources;
    private $_time;

    public function init() {
        parent::init();

#var_dump($this->_privateConfig->urielement);die;

        //prepare URI-array for use by loading the ini configuration
        foreach($this->_privateConfig->uris as $entity => $uri) {
            $this->_uris[$entity] = $uri;
        }

        //add the qb-namespace to all uris
        foreach($this->_uris as $entity => $uri) {
            if($entity != "qb" && $entity != "rdfType" && $entity != "rdfsLabel")
                $this->_uris[$entity] = $this->_uris['qb'].$this->_uris[$entity];
        }

        //prepare the uri creation rules
        foreach($this->_privateConfig->uripattern as $spec => $pattern)
            $this->_uriPattern[$spec] = $pattern;

        //load the uri pattern elements
        foreach($this->_privateConfig->urielement->placeholder as $uriElement)
            $this->_uriElements[$uriElement] = true;

#        //load all other options
#        foreach($this->_privateConfig->options as $key => $value)
#            $this->_options[$key] = $value;

        //if a manual adaption of uri patterns was made, take these values
        if(isset($_POST['isUriPatternSet'])) {
                $newPattern = array();
                foreach($this->_uriPattern as $spec => $pattern) {
                    $newPattern[$spec] = $_POST[$spec];
                }
                $this->_uriPattern = $newPattern;
        }

#        //prepare parser-array with the given parser class names
#        foreach($this->_privateConfig->parser as $parser => $name)
#            $this->_parser[$parser] = $name;

        //prepare the parser-specific addons
#        foreach($this->_parser as $parser => $name) {
#            foreach($this->_privateConfig->parseraddon as $addon => $parserKey) {
#                if($parserKey == $parser) {
#                    $this->_addon[$parser][$addon] = true;
#                }
#            }
#        }

	}

    public function analyzeAction() {
		$this->view->placeholder('main.window.title')->set($this->_owApp->translate->_('DataCube Quality Assurance Dashboard'));
        $this->addModuleContext('main.window.qbvalidator');
		$translate = $this->_owApp->translate;
		$titleHelper = new OntoWiki_Model_TitleHelper($this->_owApp->selectedModel);
		$cubeHelper = new CubeHelper($this->_uris, $this->_owApp->selectedModel,
				$this->_uriPattern, $this->_uriElements);

        if($this->_owApp->selectedModel) {
			//step 1: analyze the model and decide whether cube elements have to
            //be constructed or can be read out; to increase the performance only
            //the analysis is done by default
            $analysisResult = $cubeHelper->analyzeModel();
            $this->view->analysis = $analysisResult;
            if(isset($_POST['isCreateElementsSet']) && $isCreationAllowed) {
                //elements have to be created

                //the following steps have high performance costs
                $observationPartition = $cubeHelper->
                        analyzeNeededObservationPartition($analysisResult);

                $creationTable = array_merge($analysisResult,
                        $observationPartition);

                //evaluate the user input on names and uris if given
                if(isset($_POST['creationList'])) {
                    $creationTable = $this->_evaluateCreationForm($creationTable);
                }

                //create the elements and reset the analysis
                $cubeHelper->createStructureElements($creationTable);
                $analysisResult = $cubeHelper->analyzeModel();
                $this->view->analysis = $analysisResult;
            }
		}
	}
}
