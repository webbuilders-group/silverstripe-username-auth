<?php

use SilverStripe\Forms\EmailField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\FormAction;
use SilverStripe\Security\MemberAuthenticator\MemberLoginForm;

/**
 * Class LostPasswordForm handles the requests for lost password form generation
 *
 * We need the MemberLoginForm for the getFormFields logic.
 */
class LostUsernameForm extends MemberLoginForm
{

    /**
     * Create a single EmailField form that has the capability
     * of using the MemberLoginForm Authenticator
     *
     * @skipUpgrade
     * @return FieldList
     */
    public function getFormFields()
    {
        return FieldList::create(
            EmailField::create('Email', _t('SilverStripe\\Security\\Member.EMAIL', 'Email'))
        );
    }

    /**
     * Give the member a friendly button to push
     *
     * @return FieldList
     */
    public function getFormActions()
    {
        return FieldList::create(
            FormAction::create(
                'forgotUsername',
                _t('UsernameSecurity.FORGOTUSERNAMEBUTTONSEND', '_Send me my username')
            )
        );
    }
}