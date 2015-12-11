<?php

class UserDeleteCest
{
    private $file;
    private $password = '1234';

    protected function backupFile(\AcceptanceTester $I)
    {
        $LOG_FILE_PATH = __DIR__.'/../../bbs.log';
        $this->file = file_get_contents($LOG_FILE_PATH);
    }

    protected function restoreFile(\AcceptanceTester $I)
    {
        $LOG_FILE_PATH = __DIR__.'/../../bbs.log';
        file_put_contents($LOG_FILE_PATH, $this->file);
    }

    protected function userWrite(\AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->fillField('name', 'テスト');
        $I->fillField('email', 'example@example.com');
        $I->fillField('sub', 'テストタイトル');
        $I->fillField('com', 'テスト
        これはテストです。');
        $I->fillField('url', 'http://example.com');
        $I->fillField('password', $this->password);
        $I->click('body > form > tt > input[type="submit"]:nth-child(6)');
    }

    public function tryToTest(AcceptanceTester $I)
    {
        $I->wantTo('ユーザによる削除');
        $I->amOnPage('/');
        $I->fillField('no', '1');
        $I->fillField('pwd', $this->password);
        $I->click('body > div.right.cf > form > input[type="submit"]:nth-child(5)');

        $I->see('P-BBS');
        $I->dontSeeElement('/html/body/span[1]');
    }
}
