<?php
/**
 * File GoogleCredentialsConfigForm.inc.php
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

class GoogleCredentialsConfigForm extends Form {
	
	/** @var int */
	var $_journalId;
	
	/** @var object */
	var $_plugin;
	
	/**
	 * Constructor
	 * @param $plugin SocialsAuth
	 * @param $journalId int
	 */
	function __construct($plugin, $journalId) {
		$request = Application::getRequest();
		$this->_journalId = $journalId;
		$this->_plugin = $plugin;
		parent::__construct($plugin->getTemplateResource('googleAuth'  . DIRECTORY_SEPARATOR . 'settingsForm.tpl'));
		
		$this->addCheck(new FormValidator($this, 'googleAppClientID', 'required', 'plugins.generic.socialsAuth.manager.settings.googleAuth.googleAppClientIDRequired'));
		$this->addCheck(new FormValidator($this, 'googleAppClientSecret', 'required', 'plugins.generic.socialsAuth.manager.settings.googleAuth.googleAppClientSecretRequired'));
		
		$this->addCheck(new FormValidatorPost($this));
		$this->addCheck(new FormValidatorCSRF($this));
		
	}
	
	
	/**
	 * Initialize form data.
	 */
	function initData() {
		
		$googleAppClientID     = $this->_plugin->getSetting( $this->_journalId, 'googleAppClientID' );
		$googleAppClientSecret = $this->_plugin->getSetting( $this->_journalId, 'googleAppClientSecret' );
	
		$this->setData( 'googleAppClientID', $googleAppClientID );
		$this->setData( 'googleAppClientSecret', $googleAppClientSecret );
	}
	
	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('googleAppClientID', 'googleAppClientSecret'));
	}
	
	/**
	 * Fetch the form.
	 * @copydoc Form::fetch()
	 */
	function fetch($request, $template = NULL, $display = false) {
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('pluginName', $this->_plugin->getName());
		$redirectUri = $request->getDispatcher()->url($request, ROUTE_PAGE, $request->getContext()->getPath(), 'google-auth', 'redirect');
//
		$templateMgr->assign('redirectUri', $redirectUri);
		return parent::fetch($request, $template, $display);
	}
	
	/**
	 * Save settings.
	 */
	function execute(...$functionArgs) {
		$this->_plugin->updateSetting($this->_journalId, 'googleAppClientID', trim($this->getData('googleAppClientID'), "\"\';"), 'string');
		$this->_plugin->updateSetting($this->_journalId, 'googleAppClientSecret', trim($this->getData('googleAppClientSecret'), "\"\';"), 'string');
		parent::execute(...$functionArgs);
	}
}

?>
