<?php
$I = new TestGuy($scenario);
$I->wantTo('try to log in with the wrong password');
$I->amOnPage('/login');
$I->fillField('Username:', 'admin');
$I->fillField('Password:', 'alsdfhja;l');
$I->click('Login');
$I->seeInCurrentUrl('/login');
$I->see('Incorrect username or password.');