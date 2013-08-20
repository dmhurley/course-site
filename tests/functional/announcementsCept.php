<?php
$I = new TestGuy($scenario);
$I->wantTo('make a visible announcement');
$I->amOnPage('/login');
$I->submitForm('form', array(
		'_username' => 'admin',
		'_password' => 'adminpass'
	));
$I->click('Announcements');
$I->see('Manage Announcements');
$I->click('form[add]');
$I->see('Invalid form.', '.failure');

$I->fillField('Announcement:', 'This should appear here!');
$I->click('form[add]');
$I->see('Announcement added.', '.success');

$I->click('#course a');
$I->see('This should appear here!');
$I->click('Announcements');

$I->click('delete');
$I->see('Announcement deleted.', '.success');
