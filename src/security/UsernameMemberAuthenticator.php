<?php

namespace WebbuildersGroup\UsernameAuth\Security;

use SilverStripe\Security\MemberAuthenticator\MemberAuthenticator;
use SilverStripe\Control\Controller;
use WebbuildersGroup\UsernameAuth\Security\LostPasswordHandler;
use WebbuildersGroup\UsernameAuth\Security\LostUsernameHandler;
use WebbuildersGroup\UsernameAuth\Security\UsernameLoginHandler;


class UsernameMemberAuthenticator extends MemberAuthenticator {
    
    /**
	 * Method that creates the login form for this authentication method
	 *
	 * @param Controller The parent controller, necessary to create the
	 *                   appropriate form action tag
	 * @return Form Returns the login form to use with this authentication
	 *              method
	 */
	public function getLoginHandler($link) {    
		return UsernameLoginHandler::create($link, $this);
	}

	/**
     * @param string $link
     * @return LostUsernameHandler
     */
    public function getLostUsernameHandler($link)
    {
        return LostUsernameHandler::create($link, $this);
	}
	
	/**
     * @param string $link
     * @return LostPasswordHandler
     */
    public function getLostPasswordHandler($link)
    {
        return LostPasswordHandler::create($link, $this);
    }
}