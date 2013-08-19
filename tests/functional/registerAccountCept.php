<?php


// no data
$I = new TestGuy($scenario);
$I->wantTo('want to register an account');
$I->amOnPage('/login');
$I->click('register');
$I->seeInCurrentUrl('/register');
$I->fillField('Username:', '');
$I->fillField('Password:', '');
$I->fillField('Repeat:', '');
$I->click('Register');
$I->seeInCurrentUrl('/register');
$I->see('Invalid form.');

// mismatched passwords
$I->fillField('Username:', 'test');
$I->fillField('Password:', 'foo');
$I->fillField('Repeat:', 'bar');
$I->click('Register');
$I->seeInCurrentUrl('/register');
$I->see('You typed in two different passwords.');

// matched user
$I->fillField('Username:', 'admin');
$I->fillField('Password:', 'abc');
$I->fillField('Repeat:', 'abc');
$I->click('Register');
$I->seeInCurrentUrl('/register');
$I->see('This value is already used.');

$I->fillField('Username:', 'test');
$I->fillField('Password:', 'foobar');
$I->fillField('Repeat:', 'foobar');
$I->click('Register');
$I->seeElement('.success');
$I->seeInCurrentUrl('/login');

// quickly delete them
$I->fillField('Username:', 'admin');
$I->fillField('Password:', 'adminpass');
$I->click('Login');
$I->seeInCurrentUrl('/admin/');
$I->click('Users');
$I->seeInCurrentUrl('/admin/user');
$I->click('delete');
$I->see('test deleted.');