<?php
$I = new TestGuy($scenario);
$I->wantTo('login as an admin');
$I->amOnPage('/login');
$I->fillField('Username:', 'admin');
$I->fillField('Password:', 'adminpass');
$I->click('Login');
$I->seeInCurrentUrl('/admin/');
