<?php
$I = new TestGuy($scenario);
$I->wantTo('try to login as ROLE_USER');
$I->amOnPage("/login");
$I->fillField('Username:', 'user');
$I->fillField('Password:', 'userpass');
$I->click('Login');
$I->seeInCurrentUrl('/login');