<?php
$I = new TestGuy($scenario);
$I->wantTo('create and delete a link');
$I->amOnPage('/login');
$I->submitForm('form', array(
		'_username' => 'admin',
		'_password' => 'adminpass'
	));
$I->click('Links');
$I->seeInCurrentUrl('/admin/link/');
$I->see('Manage Links');

$I->click('form[add]');
$I->see('Invalid form.', '.failure');

$I->fillField('Title:', 'Test link');
$I->fillField('URL:', 'lasfjlsafjks');
$I->click('form[add]');
$I->see('Invalid form.', '.failure');
$I->seeElement('[data-tip="This value is not a valid URL."]');

$I->fillField('Title:', 'Test link');
$I->fillField('URL:', 'http://www.google.com/');
$I->click('form[add]');
$I->see('Link added.', '.success');

$I->click('table tr:last-of-type td:nth-child(4) a');
$I->seeInCurrentUrl('/admin/link/edit/');
$I->see('http://www.google.com/');
$I->fillField('URL:', 'sfsfsaffasffa');
$I->click('form[edit]');
$I->see('Invalid form.', '.failure');

$I->fillField('Title:', 'Edit link');
$I->seeElement('[name="form[edit]"]');
$I->submitForm('form', array(
		'form[title]' => 'Edited link.',
		'form[address]' => 'http://www.google.com/'
	));
$I->see('Link edited.', '.success');
$I->seeCurrentUrlEquals('/admin/link/');

$I->click('table tr:last-of-type td:nth-child(5) a');
$I->see('Link deleted.', '.success');

