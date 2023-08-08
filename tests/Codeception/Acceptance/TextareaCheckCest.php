<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\MediaLibrary\Tests\Codeception\Acceptance;

use OxidEsales\MediaLibrary\Tests\Codeception\AcceptanceTester;

/**
 * @group ddoe_wysiwyg
 */
final class TextareaCheckCest
{
    public function testCmsTextAreaModified(AcceptanceTester $I): void
    {
        $I->wantToTest('Module improves the cms pages textarea');

        $adminPanel = $I->loginAdmin();
        $adminPanel->openCMSPages();
        $I->selectEditFrame();

        $I->seeElementInDOM("#ddoew #editor_oxcontents__oxcontent");
    }
}
