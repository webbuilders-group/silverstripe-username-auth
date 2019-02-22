<?php

use SilverStripe\Control\HTTPRequest;
use SilverStripe\Security\MemberAuthenticator\LoginHandler;
use SilverStripe\Security\MemberAuthenticator\MemberLoginForm;
use SilverStripe\Control\RequestHandler;
use SilverStripe\Control\Controller;

class UsernameLoginHandler extends RequestHandler {


    /**
     * @var Authenticator
     */
    protected $authenticator;

    /**
     * @var array
     */
    private static $url_handlers = [
        '' => 'login',
    ];

    /**
     * @var array
     * @config
     */
    private static $allowed_actions = [
        'login',
        'LoginForm',
        'logout',
    ];

    /**
     * Link to this handler
     *
     * @var string
     */
    protected $link = null;


    /**
     * Return a link to this request handler.
     * The link returned is supplied in the constructor
     *
     * @param null|string $action
     * @return string
     */
    public function Link($action = null)
    {
        $link = Controller::join_links($this->link, $action);
        $this->extend('updateLink', $link, $action);
        return $link;
    }

    /**
     * @param string $link The URL to recreate this request handler
     * @param MemberAuthenticator $authenticator The authenticator to use
     */
    public function __construct($link, UsernameMemberAuthenticator $authenticator)
    {
        $this->link = $link;
        $this->authenticator = $authenticator;
        parent::__construct();
    }

    /**
     * URL handler for the log-in screen
     *
     * @return array
     */
    public function login()
    {
        return [
            'Form' => $this->loginForm(),
        ];
    }

    /**
     * Return the MemberLoginForm form
     *
     * @skipUpgrade
     * @return MemberLoginForm
     */
    public function loginForm()
    {
        return UsernameMemberLoginForm::create(
            $this,
            get_class($this->authenticator),
            'LoginForm'
        );
    }

    // /**
	//  * Login form handler method
	//  *
	//  * This method is called when the user clicks on "Log in"
	//  *
	//  * @param array $data Submitted data
	//  */
	// public function dologin($data, UsernameMemberLoginForm $form, HTTPRequest $request) {
    //     var_dump('test'); exit;
	// 	$failureMessage = null;

    //     $this->extend('beforeLogin');
    //     // Successful login
    //     /** @var ValidationResult $result */
    //     if ($member = $this->checkLogin($data, $request, $result)) {
    //         $this->performLogin($member, $data, $request);
    //         // Allow operations on the member after successful login
    //         $this->extend('afterLogin', $member);

    //         return $this->redirectAfterSuccessfulLogin();
    //     }

    //     $this->extend('failedLogin');

    //     $message = implode("; ", array_map(
    //         function ($message) {
    //             return $message['message'];
    //         },
    //         $result->getMessages()
    //     ));

    //     $form->sessionMessage($message, 'bad');

    //     // Failed login

    //     /** @skipUpgrade */
    //     if (array_key_exists('Email', $data)) {
    //         $rememberMe = (isset($data['Remember']) && Security::config()->get('autologin_enabled') === true);
    //         $this
    //             ->getRequest()
    //             ->getSession()
    //             ->set('SessionForms.MemberLoginForm.Email', $data['Email'])
    //             ->set('SessionForms.MemberLoginForm.Remember', $rememberMe);
    //     }

    //     // Fail to login redirects back to form
    //     return $form->getRequestHandler()->redirectBackToForm();
    // }
    
    public function getReturnReferer()
    {
        return $this->Link();
    }

    /**
     * Login in the user and figure out where to redirect the browser.
     *
     * The $data has this format
     * array(
     *   'AuthenticationMethod' => 'MemberAuthenticator',
     *   'Email' => 'sam@silverstripe.com',
     *   'Password' => '1nitialPassword',
     *   'BackURL' => 'test/link',
     *   [Optional: 'Remember' => 1 ]
     * )
     *
     * @return HTTPResponse
     */
    protected function redirectAfterSuccessfulLogin()
    {
        $this
            ->getRequest()
            ->getSession()
            ->clear('SessionForms.MemberLoginForm.Email')
            ->clear('SessionForms.MemberLoginForm.Remember');

        $member = Security::getCurrentUser();
        if ($member->isPasswordExpired()) {
            return $this->redirectToChangePassword();
        }

        // Absolute redirection URLs may cause spoofing
        $backURL = $this->getBackURL();
        if ($backURL) {
            return $this->redirect($backURL);
        }

        // If a default login dest has been set, redirect to that.
        $defaultLoginDest = Security::config()->get('default_login_dest');
        if ($defaultLoginDest) {
            return $this->redirect($defaultLoginDest);
        }

        // Redirect the user to the page where they came from
        if ($member) {
            // Welcome message
            $message = _t(
                'SilverStripe\\Security\\Member.WELCOMEBACK',
                'Welcome Back, {firstname}',
                ['firstname' => $member->FirstName]
            );
            Security::singleton()->setSessionMessage($message, ValidationResult::TYPE_GOOD);
        }

        // Redirect back
        return $this->redirectBack();
    }

    

}