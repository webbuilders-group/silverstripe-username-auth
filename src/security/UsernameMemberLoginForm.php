<?php

use SilverStripe\Control\Director;
use SilverStripe\View\Requirements;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Security\Security;
use SilverStripe\Security\Member;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\FormAction;
use SilverStripe\Forms\TextField;
use SilverStripe\Forms\PasswordField;
use SilverStripe\Forms\CheckboxField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\RequiredFields;
use SilverStripe\Forms\Form;
use SilverStripe\Security\RememberLoginHash;

class UsernameMemberLoginForm extends Form {

	/**
	 * This field is used in the "You are logged in as %s" message
	 * @var string
	 */
	public $loggedInAsField = 'FirstName';

	/**
     * Required fields for validation
     *
     * @config
     * @var array
     */
    private static $required_fields = [
        'Email',
        'Password',
    ];

	protected $authenticator_class = 'UsernameMemberAuthenticator';

	/**
	 * Constructor
	 *
	 * @param Controller $controller The parent controller, necessary to
	 *                               create the appropriate form action tag.
	 * @param string $name The method on the controller that will return this
	 *                     form object.
	 * @param FieldList|FormField $fields All of the fields in the form - a
	 *                                   {@link FieldList} of {@link FormField}
	 *                                   objects.
	 * @param FieldList|FormAction $actions All of the action buttons in the
	 *                                     form - a {@link FieldList} of
	 *                                     {@link FormAction} objects
	 * @param bool $checkCurrentUser If set to TRUE, it will be checked if a
	 *                               the user is currently logged in, and if
	 *                               so, only a logout button will be rendered
	 * @param string $authenticatorClassName Name of the authenticator class that this form uses.
	 */
	public function __construct(
        $controller,
        $authenticatorClass,
        $name,
        $fields = null,
        $actions = null,
        $checkCurrentUser = true
    ) {
        $this->setController($controller);
        $this->authenticator_class = $authenticatorClass;

        $customCSS = project() . '/css/member_login.css';
        if (Director::fileExists($customCSS)) {
            Requirements::css($customCSS);
        }

        if ($checkCurrentUser && Security::getCurrentUser()) {
            // @todo find a more elegant way to handle this
            $logoutAction = Security::logout_url();
            $fields = FieldList::create(
                HiddenField::create('AuthenticationMethod', null, $this->authenticator_class, $this)
            );
            $actions = FieldList::create(
                FormAction::create('logout', _t(
                    'SilverStripe\\Security\\Member.BUTTONLOGINOTHER',
                    'Log in as someone else'
                ))
            );
        } else {
            if (!$fields) {
				$fields = $this->getFormFields();
            }
            if (!$actions) {
				$actions = $this->getFormActions();
				$actions->push(LiteralField::create(
					'forgotUsername',
					'<p id="ForgotUsername"><a href="' . UsernameSecurity::lost_username_url() . '">'
					. _t('Member.BUTTONLOSTUSERNAME', "_I've lost my username") . '</a></p>'
				));
            }
        }

        // Reduce attack surface by enforcing POST requests
        $this->setFormMethod('POST', true);

        parent::__construct($controller, $name, $fields, $actions);

        if (isset($logoutAction)) {
            $this->setFormAction($logoutAction);
        }
        $this->setValidator(RequiredFields::create(self::config()->get('required_fields')));
	}
	
	/**
     * Build the FieldList for the login form
     *
     * @skipUpgrade
     * @return FieldList
     */
    protected function getFormFields()
    {
        $request = $this->getRequest();
        if ($request->getVar('BackURL')) {
            $backURL = $request->getVar('BackURL');
        } else {
            $backURL = $request->getSession()->get('BackURL');
        }
        
        $label = Member::singleton()->fieldLabel(Member::config()->get('unique_identifier_field'));
        $fields = FieldList::create(
            HiddenField::create("AuthenticationMethod", null, $this->authenticator_class, $this),
            // Regardless of what the unique identifer field is (usually 'Email'), it will be held in the
            // 'Email' value, below:
            // @todo Rename the field to a more generic covering name
            $emailField = TextField::create("Email", $label, null, null, $this),
            PasswordField::create("Password", _t('SilverStripe\\Security\\Member.PASSWORD', 'Password'))
        );
        $emailField->setAttribute('autofocus', 'true');

        if (Security::config()->get('remember_username')) {
            $emailField->setValue($this->getSession()->get('SessionForms.MemberLoginForm.Email'));
        } else {
            // Some browsers won't respect this attribute unless it's added to the form
            $this->setAttribute('autocomplete', 'off');
            $emailField->setAttribute('autocomplete', 'off');
        }
        if (Security::config()->get('autologin_enabled')) {
            $fields->push(
                CheckboxField::create(
                    "Remember",
                    _t('SilverStripe\\Security\\Member.KEEPMESIGNEDIN', "Keep me signed in")
                )->setAttribute(
                    'title',
                    _t(
                        'SilverStripe\\Security\\Member.REMEMBERME',
                        "Remember me next time? (for {count} days on this device)",
                        [ 'count' => RememberLoginHash::config()->uninherited('token_expiry_days') ]
                    )
                )
            );
        }

        if (isset($backURL)) {
            $fields->push(HiddenField::create('BackURL', 'BackURL', $backURL));
        }

        return $fields;
    }

	/**
     * Build default login form action FieldList
     *
     * @return FieldList
     */
    protected function getFormActions()
    {
        $actions = FieldList::create(
            FormAction::create('doLogin', _t('SilverStripe\\Security\\Member.BUTTONLOGIN', "Log in")),
            LiteralField::create(
                'forgotPassword',
                '<p id="ForgotPassword"><a href="' . Security::lost_password_url() . '">'
                . _t('SilverStripe\\Security\\Member.BUTTONLOSTPASSWORD', "I've lost my password") . '</a></p>'
            )
        );

        return $actions;
    }

	/**
	 * Get message from session
	 */
	protected function getMessageFromSession() {

		//get the session
		$request = Injector::inst()->get(HTTPRequest::class);
		$session = $request->getSession();

		$forceMessage = $session->get('MemberLoginForm.force_message');
		if(($member = Security::getCurrentUser()) && !$forceMessage) {
			$this->message = _t(
				'Member.LOGGEDINAS',
				"You're logged in as {name}.",
				array('name' => $member->{$this->loggedInAsField})
			);
		}

		// Reset forced message
		if($forceMessage) {
			$session->set('MemberLoginForm.force_message', false);
		}

		return parent::getMessageFromSession();
	}

	/**
     * The name of this login form, to display in the frontend
     * Replaces Authenticator::get_name()
     *
     * @return string
     */
    public function getAuthenticatorName()
    {
        return _t(self::class . '.AUTHENTICATORNAME', "E-mail & Password");
    }

	/**
	 * Log out form handler method
	 *
	 * This method is called when the user clicks on "logout" on the form
	 * created when the parameter <i>$checkCurrentUser</i> of the
	 * {@link __construct constructor} was set to TRUE and the user was
	 * currently logged in.
	 */
	public function logout() {
		$s = new Security();
		$s->logout();
	}
	

}
