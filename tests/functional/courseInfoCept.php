<?php
$I = new TestGuy($scenario);
$I->wantTo('try to edit the course info');
$I->amOnPage('/login');
$I->submitForm('form', array(
		'_username' => 'admin',
		'_password' => 'adminpass'
	));
$I->click('Edit Info');
$I->seeInCurrentUrl('/admin/course/edit');
// $I->see('buildings.txt');
// $I->click('form[edit]');
// $I->see('Course information updated.', '.success');

