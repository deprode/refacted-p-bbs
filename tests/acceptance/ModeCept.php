<?php
$I = new AcceptanceTester($scenario);
$I->wantTo('ルーティングのテスト');

$I->amOnPage('/');
$I->see('P-BBS');

$I->amOnPage('/index.php?mode=regist');
$I->see('エラー');

$I->amOnPage('/index.php?mode=past');
$I->see('■ 過去ログ 1 ■');

$I->amOnPage('/index.php?mode=admin');
$I->see('P-BBS');
$I->see('管理モード', 'font');
$I->fillField('apass', '0123');
$I->click('body > center > form > input[type="submit"]:nth-child(3)');
$I->see('管理モード', 'font');
$I->seeInCurrentUrl('/index.php');

$I->amOnPage('/index.php?mode=userdel');
$I->see('P-BBS');
