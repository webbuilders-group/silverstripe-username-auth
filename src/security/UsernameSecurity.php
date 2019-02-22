<?php

use SilverStripe\Security\Security;
use SilverStripe\Control\Director;
use SilverStripe\Control\Controller;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\FormAction;
use SilverStripe\Core\Convert;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Security\Authenticator;
use SilverStripe\Core\Injector\Injector;

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
     * Show the "lost password" page
     *
     * @return string Returns the "lost password" page as HTML code.
     */
    public function lostusername()
    {
        $handlers = [];
		//$authenticators = Injector::inst()->get('UsernameMemberAuthenticator');
        /** @var Authenticator $authenticator */
        //foreach ($authenticators as $authenticator) {
            $handlers[] = Injector::inst()->get('UsernameMemberAuthenticator')->getLostUsernameHandler(
                Controller::join_links($this->Link(), 'lostusername')
            );
        //}

        return $this->delegateToMultipleHandlers(
            $handlers,
            _t('SilverStripe\\Security\\Security.LOSTUSERNAMEHEADER', 'Lost Username'),
            $this->getTemplatesFor('lostusername'),
            [$this, 'aggregateAuthenticatorResponses']
        );
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