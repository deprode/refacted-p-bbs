<?php

class AuthDeleteCest
{
    private $file;

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
        $I->click('body > form > tt > input[type="submit"]:nth-child(6)');
    }

    protected function authLogin(\AcceptanceTester $I)
    {
        $config = new Config();
        $admin_pass = $config->get('admin_pass');
        $I->amOnPage('/index.php?mode=admin');
        $I->fillField('apass', $admin_pass);
        $I->click('body > div.center > form > input[type="submit"]:nth-child(4)');
    }

    /**
     * @before backupFile
     * @before userWrite
     * @before authLogin
     * @after  restoreFile
     */
    public function DeleteTest(\AcceptanceTester $I)
    {
        $I->wantTo('管理者による削除');

        $I->see('管理モード', 'font');

        $I->checkOption('input[type="checkbox"][name="del[]"]');
        $I->click('body > div.center > form > p:nth-child(6) > input[type="submit"]:nth-child(1)');

        $I->dontSeeElement('body > div.center > form > table > tbody > tr:nth-child(2)');
    }
}
