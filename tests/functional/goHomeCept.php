<?php
$I = new TestGuy($scenario);
$I->wantTo('go to the login page then back home');
$I->amOnPage('/');
$I->click('Admin');
$I->seeInCurrentUrl('/login');
$I->click('Home');
$I->seeCurrentUrlEquals('/');
