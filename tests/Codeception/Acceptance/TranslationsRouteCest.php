<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Codeception\Acceptance;

use Codeception\Attribute\Group;
use OxidEsales\Codeception\Module\Translation\Translator;
use OxidEsales\MediaLibrary\Tests\Codeception\Step\MediaLibraryAcceptanceTester;

#[Group('ddoemedialibrary')]
final class TranslationsRouteCest
{
    private const ROUTE = '/api/oeml-admin-translations';

    public function testTranslationsRouteNeedsAdminSession(MediaLibraryAcceptanceTester $I): void
    {
        $I->wantToTest('Admin translations route is not available without admin session');

        $I->amOnPage(self::ROUTE);

        $I->see('Authentication required');
        $I->dontSee('DD_MEDIA_DIALOG');
    }

    public function testTranslationsRouteReturnsAdminTranslations(MediaLibraryAcceptanceTester $I): void
    {
        $I->wantToTest('Admin translations route returns the admin translations to a logged in admin');

        $I->loginAdmin();
        $I->openMediaLibrary();
        $stoken = $this->grabSessionToken($I);

        $I->amOnPage(self::ROUTE . '?' . http_build_query(['stoken' => $stoken]));

        $I->dontSee('Authentication required');
        $I->see('DD_MEDIA_DIALOG');
        $I->see(Translator::translate('DD_MEDIA_DIALOG'));
    }

    private function grabSessionToken(MediaLibraryAcceptanceTester $I): string
    {
        $popoutLink = $I->grabAttributeFrom('.dd-admin-popout-action', 'href');
        parse_str((string) parse_url($popoutLink, PHP_URL_QUERY), $query);

        return (string) $query['stoken'];
    }
}
