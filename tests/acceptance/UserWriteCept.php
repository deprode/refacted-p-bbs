<?php
$I = new AcceptanceTester($scenario);
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
