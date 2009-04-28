<?php
/**
 * Authed Component
 * 
 * Custom scope rules for AuthComponent
 * 
 * The purpose of this extension is to add a flexible rule setup to 
 * the login process. You could compare their setup to model validation.
 * The rules are applied just before the usual auth is issued. 
 * 
 * Note: This extension overwrites Auth::login()
 * 
 * Example: 
 * 
 *	$this->Authed->loginError = __("Wrong password / username. Please try again.", true);
 *	$this->Authed->authError = __("Sorry, you are not authorized. Please log in first.", true);
 * 
 *	$this->Authed->userScopeRules = array(
 *		'is_banned' => array(
 *			'expected' => 0, 
 *			'message' => __("You are banned from this service. Sorry.", true)
 *		),
 *		'is_validated' => array(
 *			'expected' => 1,
 *			'message' => __("Your account is not active yet. Click the Link in our Mail.", true)
 *		)
 *	);
 *
 * PHP versions 4 and 5
 *
 * @version 0.1
 * @author Kjell Bublitz <m3nt0r.de@gmail.com>
 * @copyright 2008-2009 (c) Kjell Bublitz
 * @link http://cakealot.com Authors Weblog
 * @link http://github.com/m3nt0r/cake-bits Components Repository
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @package cake-bits
 * @subpackage components
 */
App::import('component', 'Auth');

/**
 * Authed Component 
 * 
 * @package cake-bits
 * @subpackage components
 */
class AuthedComponent extends AuthComponent
{ 
	/**
	 * Rules
	 *
	 * @var array
	 */
	var $userScopeRules = array();
	
	/**
	 * Check variable
	 *
	 * @var boolean
	 */
	var $_scopeRuleError = false;
	
	/**
	 * Walk through all available rules and compare with row data.
	 * Break on mismatch and reset loginError to rule.message
	 *
	 * @param array $data UserModel row
	 * @return boolean True on login success, false on failure
	 * @access public
	 */
	function hasScopeRuleMismatch($user) {
		foreach ($this->userScopeRules as $field => $rule) {
			if ($user[$field] != $rule['expected']) {
				$this->loginError = $rule['message'];
				$this->_scopeRuleError = true;
				break;
			}
		}
		return $this->_scopeRuleError;
	}
	
	/**
	 * Overwrites Auth::login()
	 *
	 * Basicly the same method, but after identify() was successful call
	 * the above hasScopeRuleMismatch passing $user.
	 * 
	 * Only if this method returns false we will continue the login process.
	 * 
	 * @param mixed $data
	 * @return boolean True on login success, false on failure
	 * @access public
	 */
	function login($data = null) { 
		$this->__setDefaults();
		$this->_loggedIn = false;

		if (empty($data)) {
			$data = $this->data;
		}

		if ($user = $this->identify($data)) {
			if (!$this->hasScopeRuleMismatch($user)) {
				$this->Session->write($this->sessionKey, $user);
				$this->_loggedIn = true;
			}
		}
		return $this->_loggedIn;
	}
		
	/**
	 * Returns true if the login error was scope rules related.
	 * Maybe someone needs this to go on with.
	 * 
	 * @return boolean
	 */
	function wasScopeRuleError() {
		return $this->_scopeRuleError;
	}
	
	/**
	 * Returns the user() data, only not nested.
	 * I found it to verbose for view assignment.
	 * 
	 * @example: $this->set('authed', $this->Authed->userdata());
	 * @return array
	 */
	function userdata() {
		$authed_user = $this->user();
		return ife(!empty($authed_user), $authed_user[$this->userModel], array());
	}
	
}
?>