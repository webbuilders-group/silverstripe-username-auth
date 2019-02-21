<?php

use SilverStripe\Control\Email\Email;
use SilverStripe\SiteConfig\SiteConfig;

class Member_ForgotUsernameEmail extends Email {

protected $from = '';   // setting a blank from address uses the site's default administrator email
protected $subject = '';
protected $ss_template = 'ForgotUsernameEmail';

public function __construct() {
    parent::__construct();

    $this->subject = _t('Member.SUBJECTYOURUSERNAME', "Your Username for {domain}", 'Email subject', array('domain'=> SiteConfig::current_site_config()->Title));
}

}