<?php

namespace PlasticStudio\Extensions;

use SilverStripe\ORM\DB;
use SilverStripe\ORM\DataObject;
use SilverStripe\Forms\FieldList;
use SilverStripe\Security\Member;
use SilverStripe\ORM\DataExtension;
use SilverStripe\Security\Security;
use SilverStripe\Forms\ReadonlyField;

class MemberExtension extends DataExtension
{
    // https://docs.silverstripe.org/en/5/developer_guides/extending/how_tos/track_member_logins/

    private static $db = [
        'LastVisited' => 'Datetime',
        'NumVisit' => 'Int',
    ];

    /**
     * This extension hook is called every time a member is logged in
     */
    public function afterMemberLoggedIn()
    {
        $this->logVisit();
    }

    /**
     * This extension hook is called when a member's session is restored from "remember me" cookies
     */
    public function memberAutoLoggedIn()
    {
        $this->logVisit();
    }

    public function updateCMSFields(FieldList $fields)
    {
        $fields->addFieldsToTab('Root.Main', [
            ReadonlyField::create('LastVisited', 'Last visited'),
            ReadonlyField::create('NumVisit', 'Number of visits'),
        ]);
    }

    protected function logVisit()
    {
        if (!Security::database_is_ready()) {
            return;
        }

        $lastVisitedTable = DataObject::getSchema()->tableForField(Member::class, 'LastVisited');

        DB::query(sprintf(
            'UPDATE "' . $lastVisitedTable . '" SET "LastVisited" = %s, "NumVisit" = "NumVisit" + 1 WHERE "ID" = %d',
            DB::get_conn()->now(),
            $this->owner->ID
        ));
    }
}