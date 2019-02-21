<?php

use SilverStripe\ORM\DataExtension;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\TextField;

class UsernameMemberExtension extends DataExtension {

    private static $db = array(
                                'Username' => 'Varchar'
                            );

    public function updateCMSFields(FieldList $fields) {
        //main tab
        $fields->addFieldToTab('Root.Main', TextField::create('Username', 'Username'), 'Email');
    }

}