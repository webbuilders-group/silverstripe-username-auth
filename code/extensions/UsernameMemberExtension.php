<?php

class UsernameMemberExtension extends DataExtension {

    private static $db = array(
                                'Username' => 'Varchar'
                            );

    public function updateCMSFields(FieldList $fields) {
        //main tab
        $fields->addFieldToTab('Root.Main', TextField::create('Username', 'Username'), 'Email');
    }

}

class Member_ForgotUsernameEmail extends Email {

    protected $from = '';   // setting a blank from address uses the site's default administrator email
    protected $subject = '';
    protected $ss_template = 'ForgotUsernameEmail';

    public function __construct() {
        parent::__construct();

        $this->subject = _t('Member.SUBJECTYOURUSERNAME', "Your Username for {domain}", 'Email subject', array('domain'=> SiteConfig::current_site_config()->Title));
    }

}
