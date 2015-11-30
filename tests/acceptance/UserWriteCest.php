<?php

class UserWriteCest
{
    private $file;

    public function _before(\AcceptanceTester $I)
    {
        $LOG_FILE_PATH = __DIR__.'/../../bbs.log';
        $this->file = file_get_contents($LOG_FILE_PATH);
    }

    public function _after(\AcceptanceTester $I)
    {
        $LOG_FILE_PATH = __DIR__.'/../../bbs.log';
        file_put_contents($LOG_FILE_PATH, $this->file);
    }

    public function writeTest(\AcceptanceTester $I)
    {
        $I->wantTo('ユーザが通常の書き込み');
        $I->amOnPage('/');
        $I->see('P-BBS');
        $I->fillField('name', 'テスト');
        $I->fillField('email', 'example@example.com');
        $I->fillField('sub', 'テストタイトル');
        $I->fillField('com', 'テスト
        これはテストです。');
        $I->fillField('url', 'http://example.com');
        $I->click('body > form > tt > input[type="submit"]:nth-child(6)');

        $I->see('P-BBS');
        $I->see('テスト', 'html/body/span[2]/b/a/text()');
        $I->see('example@example.com', 'html/body/span[2]/b/a/@href');
        $I->see('テストタイトル', 'html/body/span[1]/b');
        $I->see('テスト
        これはテストです。', 'html/body/blockquote[1]/tt');
        $I->see('http://example.com', 'html/body/blockquote[1]/p/a/@href');
        $I->see('http://example.com', 'html/body/blockquote[1]/p/a/text()');
    }

    public function writeEmptyTest(\AcceptanceTester $I)
    {
        $I->wantTo('一部の入力がないまま書き込み');

        $I->amOnPage('/');
        $I->see('P-BBS');
        $I->fillField('name', '');
        $I->fillField('sub', 'テスト');
        $I->fillField('com', 'テスト');
        $I->click('body > form > tt > input[type="submit"]:nth-child(6)');

        $I->see('エラー');


        $I->amOnPage('/');
        $I->see('P-BBS');
        $I->fillField('name', 'テスト');
        $I->fillField('sub', 'テスト');
        $I->fillField('com', '');
        $I->click('body > form > tt > input[type="submit"]:nth-child(6)');

        $I->see('エラー');


        $I->amOnPage('/');
        $I->see('P-BBS');
        $I->fillField('name', 'テスト');
        $I->fillField('sub', '');
        $I->fillField('com', 'テスト');
        $I->click('body > form > tt > input[type="submit"]:nth-child(6)');

        $I->see('P-BBS');
        $I->see('無題', 'html/body/span[1]/b');
    }

    public function writeDuplicateTest(\AcceptanceTester $I)
    {
        $I->wantTo('内容が重複した書き込みでエラー');

        $I->amOnPage('/');
        $I->see('P-BBS');
        $I->fillField('name', 'テスト');
        $I->fillField('com', 'テスト');
        $I->click('body > form > tt > input[type="submit"]:nth-child(6)');

        $I->amOnPage('/');
        $I->see('P-BBS');
        $I->fillField('name', 'テスト');
        $I->fillField('com', 'テスト');
        $I->click('body > form > tt > input[type="submit"]:nth-child(6)');

        $I->see('エラー');
        $I->see('二重投稿は禁止です', 'html/body/div/div/p/text()');
    }

    public function writeConsecutiveTest (\AcceptanceTester $I)
    {
        $I->wantTo('連続した書き込みでエラー');

        $I->amOnPage('/');
        $I->see('P-BBS');
        $I->fillField('name', 'テスト');
        $I->fillField('com', 'テスト');
        $I->click('body > form > tt > input[type="submit"]:nth-child(6)');

        $I->amOnPage('/');
        $I->see('P-BBS');
        $I->fillField('name', 'テスト');
        $I->fillField('com', 'テスト2');
        $I->click('body > form > tt > input[type="submit"]:nth-child(6)');

        $I->see('エラー');
        $I->see('連続投稿はもうしばらく時間を置いてからお願い致します', 'html/body/div/div/p/text()');
    }

    protected function makeMaxLine($max)
    {
        $str = 'test';
        for ($i=0; $i < $max; $i++) {
            $str .= 'a' . PHP_EOL;
        }
        return $str;
    }

    public function writeMaxLineTest (\AcceptanceTester $I)
    {
        $I->wantTo('最大行数を超えた書き込みでエラー');

        $config = new Config();
        $str = $this->makeMaxLine($config->maxline + 1);

        $I->amOnPage('/');
        $I->see('P-BBS');
        $I->fillField('name', 'テスト');
        $I->fillField('com', $str);
        $I->click('body > form > tt > input[type="submit"]:nth-child(6)');

        $I->see('エラー');
        $I->see('行数が長すぎますっ！', 'html/body/div/div/p/text()');
    }
}

