<?php
$I = new AcceptanceTester($scenario);
$config = new Config();
$admin_pass = $config->get('admin_pass');
$I->wantTo('管理ログインのテスト');

$I->amOnPage('/index.php?mode=admin');
$I->see('P-BBS');
$I->see('管理モード', 'font');
$I->fillField('apass', $admin_pass);
$I->click('body > div.center > form > input[type="submit"]:nth-child(4)');
$I->see('管理モード', 'font');
$I->seeInCurrentUrl('/index.php');

$I->wantTo('管理ログインエラーのテスト');
$I->amOnPage("/index.php?mode=admin");
$I->see('管理モード', 'font');
$I->fillField('apass', $admin_pass . 'fail');
$I->click('body > div.center > form > input[type="submit"]:nth-child(4)');
$I->seeInCurrentUrl('/index.php');
$I->dontSee('管理モード', 'font');
