<?php
/**
 * File GoogleCredentialsForm.inc.php
 *
 * Description
 *
 * PHP version 7.2.12
 *
 * @package    OJSPluginsGeneric
 * @subpackage SocialsAuth
 * @author     Tạ Phước Ánh <taphuocanh@huaf.edu.vn>
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    1.0.0
 * @link       https://taphuocanh.com/ojs/socials-auth
 * @since      File available since Release 1.0.0
 */


import('lib.pkp.classes.form.Form');

class GoogleCredentialsForm extends Form {
	/** @var int Context (press / journal) ID */
	var $contextId;
	
	/** @var SocialsAuth Socials Auth plugin */
	var $plugin;
	
	/**
	 * Constructor
	 *
	 * @param $template string the path to the form template file
	 * @param $contextId int
	 * @param $plugin SocialsAuth
	 */
	function __construct( $template, $contextId, $plugin = null ) {
		parent::__construct( $template );
		
		$this->contextId = $contextId;
		$this->plugin    = $plugin;
		
		// Add form checks
		$this->addCheck( new FormValidatorPost( $this ) );
		$this->addCheck( new FormValidatorCSRF( $this ) );
		$this->addCheck( new FormValidator( $this, 'googleAppClientID', 'required', 'plugins.generic.socicalsAuth.form.googleAppClientID' ) );
		$this->addCheck( new FormValidator( $this, 'googleAppClientSecret', 'required', 'plugins.generic.socicalsAuth.form.googleAppClientSecret' ) );
//		$this->addCheck(new FormValidator($this, 'blockName', 'required', 'plugins.generic.socicalsAuth.nameRequired'));
//		$this->addCheck(new FormValidatorRegExp($this, 'blockName', 'required', 'plugins.generic.socicalsAuth.nameRegEx', '/^[a-zA-Z0-9_-]+$/'));
	}
	
	/**
	 * Khởi tạo dữ liệu nếu đã có trong CSDL
	 */
	function initData() {
		$contextId = $this->contextId;
		$plugin    = $this->plugin;
		
		$templateMgr = TemplateManager::getManager();
		
		$blockName    = null;
		$blockContent = null;
		if ( $plugin ) {
			$blockName             = $plugin->getName();
			$googleAppClientID     = $plugin->getSetting( $contextId, 'googleAppClientID' );
			$googleAppClientSecret = $plugin->getSetting( $contextId, 'googleAppClientSecret' );
		}
//		$this->setData('blockName', $blockName);
		$this->setData( 'googleAppClientID', $googleAppClientID );
		$this->setData( 'googleAppClientSecret', $googleAppClientSecret );
	}
	
	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars( array( 'googleAppClientID', 'googleAppClientSecret' ) );
	}
	
	/**
	 * Save form values into the database
	 */
	function execute() {
		$plugin    = $this->plugin;
		$contextId = $this->contextId;
	}
	
	
	/**
	 * Hiển thị form
	 */
	public function fetch($request, $template = null, $display = false) {
		
		$templateMgr = TemplateManager::getManager($request);
		
		// By default the template defined in the constructor
		// will be loaded
		return parent::fetch($request, $template, $display);
	}
}
//		if (!$plugin) {
//			// Create a new custom block plugin
//			import('plugins.generic.customBlockManager.CustomBlockPlugin');
//			$plugin = new CustomBlockPlugin($this->getData('blockName'), CUSTOMBLOCKMANAGER_PLUGIN_NAME);
//			// Default the block to being enabled
//			$plugin->setEnabled(true);
//
//			// Default the block to the left sidebar
//			$plugin->setBlockContext(BLOCK_CONTEXT_SIDEBAR);
//
//			// Add the custom block to the list of the custom block plugins in the
//			// custom block manager plugin
//			$customBlockManagerPlugin = $plugin->getManagerPlugin();
//			$blocks = $customBlockManagerPlugin->getSetting($contextId, 'blocks');
//			if (!isset($blocks)) $blocks = array();
//
//			array_push($blocks, $this->getData('blockName'));
//			$customBlockManagerPlugin->updateSetting($contextId, 'blocks', $blocks);
//		}
//
//		// update custom block plugin content
//		$plugin->updat