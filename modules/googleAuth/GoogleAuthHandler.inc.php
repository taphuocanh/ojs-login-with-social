<?php
/**
 * File GoogleAuthHandler.inc.php
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

import('classes.handler.Handler');

class GoogleAuthHandler extends Handler {
	public function index($args, $request): string {
		return 'abc';
	}
	public function redirect($args, $request): void {
		$requestQueries = $request->getQueryArray();
		$this->setupTemplate($request);
		if (Validation::isLoggedIn()) $this->sendHome($request);
		
		if (Config::getVar('security', 'force_login_ssl') && $request->getProtocol() != 'https') {
			// Force SSL connections for login
			$request->redirectSSL();
		}
		
		if (isset($requestQueries['code']) && !empty($requestQueries['code'])) {
			$authCode = $requestQueries['code'];
			$googleUser = $this->verifyGoogleAuth($request, $authCode);
			if ( $googleUser && isset( $googleUser['email'] ) ) {
				$email = $googleUser['email'];
				$userDao = DAORegistry::getDAO('UserDAO');
				$userByEmail = $userDao->getUserByEmail($email, true);
				if (isset($userByEmail) && is_a($userByEmail, 'User') && !$userByEmail->getDisabled()) {
					$reason = null;
					$remember = false;
					session_id($_COOKIE[session_name()]);
					$user = Validation::registerUserSession($userByEmail, $reason, $remember);
					if ($user !== false) {
						$stateEncoded = $request->getUserVar('state');
						$state = json_decode($stateEncoded, true);
						$source = ( isset( $state['source'] ) ) ? $state['source'] : '' ;
						$redirectNonSsl = Config::getVar('security', 'force_login_ssl') && !Config::getVar('security', 'force_ssl');
						if (isset($source) && !empty($source)) {
							$request->redirectUrl($source);
						} elseif ($redirectNonSsl) {
							$request->redirectNonSSL();
						} else {
							$this->_redirectAfterLogin($request);
						}
						
					}
				}
			}
			// now you can use this profile info to create account in your website and make user logged in.
		}
		Validation::redirectLogin();
	}
	
	function verifyGoogleAuth($request, $authCode) {
		$plugin = PluginRegistry::getPlugin('generic', 'socialsauth');
		$context = $request->getContext();
		$contextId = ($context == null) ? 0 : $context->getId();
		$googleAppClientID     = $plugin->getSetting( $contextId, 'googleAppClientID' );
		$googleAppClientSecret = $plugin->getSetting( $contextId, 'googleAppClientSecret' );
		$redirectUri = $request->getRouter()->url($request, null, 'google-auth', 'redirect');
		$client = new Google_Client();
		$client->setPrompt('select_account');
		$client->setClientId($googleAppClientID);
		$client->setClientSecret($googleAppClientSecret);
		$client->setRedirectUri($redirectUri);
		$client->addScope("email");
		$client->addScope("profile");
		$token = $client->fetchAccessTokenWithAuthCode($authCode);
		if ( isset($token['access_token']) ) {
			$client->setAccessToken( $token['access_token'] );
			// get profile info
			$google_oauth        = new Google_Service_Oauth2( $client );
			$google_account_info = $google_oauth->userinfo->get();
			return $google_account_info;
		}
		return false;
	}
	
	/**
	 * After a login has completed, direct the user somewhere.
	 * @param $request Request
	 */
	function _redirectAfterLogin(Request $request): void {
		$context = $this->getTargetContext($request);
		$stateEncoded = $request->getUserVar('state');
		$state = json_decode($stateEncoded, true);
		$source = ( isset( $state['source'] ) ) ? $state['source'] : '' ;
		// If there's a context, send them to the dashboard after login.
		
		if ($context && $source == '' && array_intersect(
				array(ROLE_ID_SITE_ADMIN, ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR, ROLE_ID_AUTHOR, ROLE_ID_REVIEWER, ROLE_ID_ASSISTANT),
				(array) $this->getAuthorizedContextObject(ASSOC_TYPE_USER_ROLES)
			)) {
			$request->redirect($context->getPath(), 'submissions');
		}
		
		$request->redirectHome();
	}
	
	/**
	 * Send the user "home" (typically to the dashboard, but that may not
	 * always be available).
	 * @param $request PKPRequest
	 */
	protected function sendHome($request) {
		if ($request->getContext()) $request->redirect(null, 'submissions');
		else $request->redirect(null, 'user');
	}
	
	/**
	 * Configure the template for display.
	 */
	function setupTemplate($request) {
		AppLocale::requireComponents(LOCALE_COMPONENT_APP_MANAGER, LOCALE_COMPONENT_PKP_MANAGER);
		parent::setupTemplate($request);
	}
}