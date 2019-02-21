<?php

class UsernameSecurity extends Security {

    /**
	 * The default lost username URL
	 *
	 * @config
	 *
	 * @var string
	 */
	private static $lost_username_url = "Security/lostusername";

    private static $allowed_actions = array(
                                            'lostusername',
                                            'LostUsernameForm',
                                            'usernamesent'
                                        );

	/**
	 * Get a link to a security action
	 *
	 * @param string $action Name of the action
	 * @return string Returns the link to the given action
	 */
	public function Link($action = null) {
		return Controller::join_links(Director::baseURL(), "UsernameSecurity", $action);
	}

    /**
     * show the forgot username page
     * @return type
     */
    public function lostusername(){
        $controller = $this->getResponseController(_t('UsernameSecurity.FORGOTUSERNAME', '_Forgot Username'));

		// if the controller calls Director::redirect(), this will break early
		if(($response = $controller->getResponse()) && $response->isFinished()) return $response;

		$customisedController = $controller->customise(array(
			'Content' =>
				'<p>' .
				_t(
					'UsernameSecurity.NOTEFORGOTUSERNAME',
					'_Enter your e-mail address and we will send you a email containing your username'
				) .
				'</p>',
			'Form' => $this->LostUsernameForm(),
		));
		
		return $customisedController->renderWith($this->getTemplatesFor('lostusername'));
    }

	/**
	 * Factory method for the forgot username form
	 *
	 * @return Form Returns the forgot username form
	 */
	public function LostUsernameForm() {
		return UsernameMemberLoginForm::create(
			$this,
			'LostUsernameForm',
			new FieldList(
				new EmailField('Email', _t('Member.EMAIL', 'Email'))
			),
			new FieldList(
				new FormAction(
					'forgotUsername',
					_t('UsernameSecurity.FORGOTUSERNAMEBUTTONSEND', '_Send me my username')
				)
			),
			false
		);
	}

    /**
	 * Show the "username sent" page, after a user has requested
	 * to send an email with their username	 
	 * @param SS_HTTPRequest $request The SS_HTTPRequest for this action.
	 * @return string Returns the "password sent" page as HTML code.
	 */
    public function usernamesent($request){
        $controller = $this->getResponseController(_t('UsernameSecurity.LOSTUSERNAMEHEADER', 'Lost Username'));

		// if the controller calls Director::redirect(), this will break early
		if(($response = $controller->getResponse()) && $response->isFinished()) return $response;

		$email = Convert::raw2xml(rawurldecode($request->param('ID')) . '.' . $request->getExtension());

		$customisedController = $controller->customise(array(
			'Title' => _t('UsernameSecurity.USERNAMESENTHEADER', "Username sent to '{email}'",
				array('email' => $email)),
			'Content' =>
				"<p>"
				. _t('UsernameSecurity.USERNAMESENTTEXT',
					"Thank you! the username has been sent to '{email}', provided an account exists for this email"
					. " address.",
					array('email' => $email))
				. "</p>",
			'Email' => $email
		));
        
		return $customisedController->renderWith($this->getTemplatesFor('usernamesent'));
    }

	/**
	 * Get the URL of the lost username page.
	 *
	 * To update the lost username url use the "Security.lost_username_url" config setting.
	 *
	 * @return string
	 */
	public static function lost_username_url() {
		return Controller::join_links(Director::baseURL(), self::config()->lost_username_url);
	}

}