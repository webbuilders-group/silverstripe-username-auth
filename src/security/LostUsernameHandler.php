<?php

use SilverStripe\Control\RequestHandler;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\Control\Controller;
use SilverStripe\Security\Authenticator;
use SilverStripe\Security\Member;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Control\Email\Email;
use SilverStripe\Core\Convert;

class LostUsernameHandler extends RequestHandler
{   

    /**
     * Authentication class to use
     * @var string
     */
    protected $authenticatorClass = UsernameMemberAuthenticator::class;

    /**
     * @var array
     */
    private static $url_handlers = [
        'usernamesent' => 'usernamesent',
        '' => 'lostusername',
    ];

    /**
     * Since the logout and dologin actions may be conditionally removed, it's necessary to ensure these
     * remain valid actions regardless of the member login state.
     *
     * @var array
     * @config
     */
    private static $allowed_actions = [
        'lostusername',
        'LostUsernameForm',
        'usernamesent',
    ];

    /**
     * Link to this handler
     *
     * @var string
     */
    protected $link = null;

    /**
     * @param string $link The URL to recreate this request handler
     */
    public function __construct($link)
    {
        $this->link = $link;
        parent::__construct();
    }

    /**
     * Return a link to this request handler.
     * The link returned is supplied in the constructor
     *
     * @param string|null $action
     * @return string
     */
    public function Link($action = null)
    {
        $link = Controller::join_links($this->link, $action);
        $this->extend('updateLink', $link, $action);
        return $link;
    }

    /**
     * show the forgot username page
     * @return type
     */
    public function lostusername(){        
        $message = _t(
            'UsernameSecurity.NOTEFORGOTUSERNAME',
            '_Enter your e-mail address and we will send you a email containing your username'
        );

        return [
            'Content' => DBField::create_field('HTMLFragment', "<p>$message</p>"),
            'Form'    => $this->LostUsernameForm(),
        ];
    }


	/**
	 * Factory method for the forgot username form
	 *
	 * @return Form Returns the forgot username form
	 */
	public function LostUsernameForm() {
		return LostUsernameForm::create(
			$this,
			$this->authenticatorClass,
			'LostUsernameForm',
			null,
			null,
			false
		);
    }
    
    /**
     * Forgot password form handler method.
     * Called when the user clicks on "I've lost my password".
     * Extensions can use the 'forgotPassword' method to veto executing
     * the logic, by returning FALSE. In this case, the user will be redirected back
     * to the form without further action. It is recommended to set a message
     * in the form detailing why the action was denied.
     *
     * @skipUpgrade
     * @param array $data Submitted data
     * @param LostPasswordForm $form
     * @return HTTPResponse
     */
    public function forgotUsername($data, $form)
    {
        // Run a first pass validation check on the data
        $dataValidation = $this->validateForgotUsernameData($data, $form);
        if ($dataValidation instanceof HTTPResponse) {
            return $dataValidation;
        }

        /** @var Member $member */
        $member = $this->getMemberFromData($data);

        

        // Allow vetoing forgot password requests
        $results = $this->extend('forgotUsername', $member);
        if ($results && is_array($results) && in_array(false, $results, true)) {
            return $this->redirectToLostPassword();
        }

        if ($member) {
            $token = $member->generateAutologinTokenAndStoreHash();
            $this->sendEmail($member, $token);
        }

        return $this->redirectToSuccess($data);
    }

    /**
     * Ensure that the user has provided an email address. Note that the "Email" key is specific to this
     * implementation, but child classes can override this method to use another unique identifier field
     * for validation.
     *
     * @param  array $data
     * @param  LostPasswordForm $form
     * @return HTTPResponse|null
     */
    protected function validateForgotUsernameData(array $data, LostUsernameForm $form)
    {
        if (empty($data['Email'])) {
            $form->sessionMessage(
                _t(
                    'SilverStripe\\Security\\Member.ENTEREMAIL',
                    'Please enter an email address to get a password reset link.'
                ),
                'bad'
            );

            return $this->redirectToLostPassword();
        }
    }

    /**
     * Load an existing Member from the provided data
     *
     * @param  array $data
     * @return Member|null
     */
    protected function getMemberFromData(array $data)
    {
        if (!empty($data['Email'])) {
            return Member::get()->filter(['Email' => $data['Email']])->first();
        }
    }

    /**
     * Avoid information disclosure by displaying the same status, regardless wether the email address actually exists
     *
     * @param array $data
     * @return HTTPResponse
     */
    protected function redirectToSuccess(array $data)
    {
        $link = $this->link('usernamesent?email='.$data['Email']);

        return $this->redirect($this->addBackURLParam($link));
    }

    /**
	 * Show the "username sent" page, after a user has requested
	 * to send an email with their username	 
	 * @param SS_HTTPRequest $request The SS_HTTPRequest for this action.
	 * @return string Returns the "password sent" page as HTML code.
	 */
    public function usernamesent($request){
        $email = Convert::raw2xml(rawurldecode($request->getVar('email')) . '.' . $request->getExtension());
        $message = _t(
            'UsernameSecurity.USERNAMESENTHEADER', "Username sent to '{email}'",
            ['email' => $email]);

        return [
            'Title' => _t(
                'UsernameSecurity.USERNAMESENTTEXT',
                "Thank you! the username has been sent to '{email}', provided an account exists for this email". " address.",
                array('email' => $email)
            ),
            'Content' => DBField::create_field('HTMLFragment', "<p>$message</p>"),
        ];
    }

    /**
     * Send the email to the member that requested a reset link
     * @param Member $member
     * @param string $token
     * @return bool
     */
    protected function sendEmail($member, $token)
    {
        /** @var Email $email */
        $email = Email::create()
            ->setHTMLTemplate('ForgotUsernameEmail')
            ->setData($member)
            ->setSubject(_t(
                'Member.SUBJECTYOURUSERNAME',
                "Your Username for {domain}",
                'Email subject'
            ))
            ->setTo($member->Email);

        return $email->send();
    }

}