<?php

class UsernameMemberAuthenticator extends MemberAuthenticator {
    
    /**
	 * Method that creates the login form for this authentication method
	 *
	 * @param Controller The parent controller, necessary to create the
	 *                   appropriate form action tag
	 * @return Form Returns the login form to use with this authentication
	 *              method
	 */
	public static function get_login_form(Controller $controller) {        
		return UsernameMemberLoginForm::create($controller, "LoginForm");
	}

}