<?php

namespace WebbuildersGroup\UsernameAuth\Security;

use SilverStripe\Security\Member;

class LostPasswordHandler extends \SilverStripe\Security\MemberAuthenticator\LostPasswordHandler {
    
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

}