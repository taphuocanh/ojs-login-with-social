<?php
/**
 * File SocialsAuth.inc.php
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

import('lib.pkp.classes.plugins.GenericPlugin');

class SocialsAuth extends GenericPlugin {
	
	/**
	 * Called as a plugin is registered to the registry
	 * @param $category String Name of category plugin was registered to
	 * @return boolean True iff plugin initialized successfully; if false,
	 * 	the plugin will not be registered.
	 */
	function register($category, $path, $mainContextId = NULL): bool {
		$success = parent::register($category, $path, $mainContextId);
		if (!Config::getVar('general', 'installed') || defined('RUNNING_UPGRADE')) return true;
		if ($success && $this->getEnabled()) {
			// Register callback for Smarty filters; add CSS
			HookRegistry::register('TemplateManager::display', array($this, 'handleTemplateDisplay'));
			
			// Insert SocialsAuthHandler callback
			HookRegistry::register('LoadHandler', array($this, 'setupCallbackHandler'));
	
		}
		return $success;
	}
	
	function handleTemplateDisplay($hookName, $params) {
		$request = $this->getRequest();
		$context = $request->getContext();
		
		if (!$context) return false;
		$router = $request->getRouter();
		if (!is_a($router, 'PKPPageRouter')) return false;
		if ( $router->getRequestedPage($request) === 'login' ) {
			$templateMgr =& $params[0];
			$templateMgr->registerFilter("output", array($this, 'loginTemplateFilter'));
		}
		
	}
	
	function loginTemplateFilter($output, &$templateMgr) {
		if (preg_match('/<form[^>]+id="login"[^>]+>/', $output, $matches, PREG_OFFSET_CAPTURE)) {

			$request = $this->getRequest();
			$match = $matches[0][0];
			$offset = $matches[0][1];
			$context = $request->getContext();
			$contextId = ($context == null) ? 0 : $context->getId();
			$googleAppClientID     = $this->getSetting( $contextId, 'googleAppClientID' );
			$googleAppClientSecret = $this->getSetting( $contextId, 'googleAppClientSecret' );
			$redirectUri = $request->getRouter()->url($request, null, 'google-auth', 'redirect');
			$client = new Google_Client();
			$client->setPrompt('select_account');
			$client->setClientId($googleAppClientID);
			$client->setClientSecret($googleAppClientSecret);
			$client->setRedirectUri($redirectUri);
			$client->addScope("email");
			$client->addScope("profile");
			if ( !is_null($request->getUserVar('source'))) {
				$client->setState( json_encode( array(
					'source' => $request->getUserVar('source')
				)));
			}
			$templateMgr->assign(array(
				'AuthUrl' => $client->createAuthUrl(),
			));
			$newOutput = substr($output, 0, $offset+strlen($match));
			$newOutput .= $templateMgr->fetch($this->getTemplateResource('googleAuth' .  DIRECTORY_SEPARATOR . 'googleAuthButton.tpl'));
			$newOutput .= substr($output, $offset+strlen($match));
			$output = $newOutput;
			$templateMgr->unregisterFilter('output', array($this, 'loginTemplateFilter'));
		}
		
		return $output;
	}
	
	function setupCallbackHandler($hookName, $params): bool {
		$page = $params[0];
		$op   = $params[1];
		
		if ( $this->getEnabled() && $page == 'google-auth' ) {
			$this->import( 'modules.googleAuth.GoogleAuthHandler' );
			define( 'HANDLER_CLASS', 'GoogleAuthHandler' );
			
			return true;
		}
		
		return false;
		
	}
	
	/**
	 *
	 * Get a list of link actions for plugin management.
	 * @param $request PKPRequest
	 * @param $actionArgs array The list of action args to be included in request URLs.
	 * @return array
	 * @see Plugin::getActions()
	 */
	function getActions($request, $actionArgs): array {
		$router = $request->getRouter();
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		return array_merge(
			$this->getEnabled()?array(
				new LinkAction(
					'google-credentials-config',
					new AjaxModal(
						$router->url(
							$request,
							null,
							null,
							'manage',
							null,
							array(
								'verb' => 'credentials-config',
								'plugin' => $this->getName(),
								'category' => 'generic',
								'type' => 'google'
							)
						),
						$this->getDisplayName()
					),
					__('plugins.generic.socialsAuth.manager.settings.googleAuth.googleCredentialsConfigs'),
					null
				),
			):array(),
			parent::getActions($request, $actionArgs)
		);
	}
	
	/**
	 * @see Plugin::manage()
	 */
	function manage($args, $request) {
		$context = $request->getContext();
		AppLocale::requireComponents(LOCALE_COMPONENT_APP_COMMON,  LOCALE_COMPONENT_PKP_MANAGER);
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->register_function('plugin_url', array($this, 'smartyPluginUrl'));
		
		if ( $request->getUserVar('verb') == 'credentials-config' ) {
			switch ($request->getUserVar('type')) {
				case 'google':
					$this->import('modules.googleAuth.GoogleCredentialsConfigForm');
					$form = new GoogleCredentialsConfigForm($this, $context->getId());
					break;
				default:
					$form = null;
			}
			
			if ( $form instanceof Form ) {
				
				if ($request->getUserVar('save')) {
					$form->readInputData();
					if ($form->validate()) {
						$form->execute();
						return new JSONMessage(true);
					}
				} else {
					$form->initData();
				}
				
				return new JSONMessage(true, $form->fetch($request));
			}
		}
		
		return parent::manage($args, $request);
	}
	
	function getDisplayName(): String {
		// TODO: Implement getDisplayName() method.
		return __('plugins.generic.socialsAuth.displayName');
	}
	
	function getDescription(): String {
		// TODO: Implement getDescription() method.
		return __('plugins.generic.socialsAuth.description');
	}
}