<?php
$I = new TestGuy($scenario);
$I->wantTo('test out the student bundle');
// sign in
$I->amOnPage('/login');
$I->fillField('Username:', 'admin');
$I->fillField('Password:', 'adminpass');
$I->click('Login');
$I->seeInCurrentUrl('/admin/');

// add student invalid form
$I->click('Add');
$I->seeInCurrentUrl('/admin/student/add');
$I->click('Submit');
$I->see('Invalid form.', '.failure');

// add valid student
$I->fillField('Student ID:', '0000000');
$I->fillField('First Name:', 'Foo');
$I->fillField('Last Name:', 'Bar');
$I->fillField('Section:', 'AZ');
$I->fillField('Email:', 'foobar@bangbing.com');
$I->click('Submit');

// find student
$I->click('Find');
$I->seeInCurrentUrl('/admin/student/find');
$I->submitForm('#content form', array(
		'form[sid]:' => '0000000',
		'form[fName]:' => 'Foo',
		'form[lName]:' => 'Bar',
		'form[section]' => 'AZ',
		'form[email]', 'foobar@bangbing.com'
	));
$I->seeInCurrentUrl('/admin/student/display');
$I->click('edit');
$I->seeInCurrentUrl('/admin/student/edit/');
$I->submitForm('#content form', array(
		'form[email]:' => 'foobar.com'
	));
$I->seeElement('.failure');
$I->submitForm('#content form', array(
		'form[email]' => 'foobar@baz.com'
	));

$I->click('Find');
$I->seeInCurrentUrl('/admin/student/find');
$I->submitForm('#content form', array(
		'form[sid]:' => '0000000',
		'form[fName]:' => 'Foo',
		'form[lName]:' => 'Bar',
		'form[section]' => 'AZ',
		'form[email]' => 'foobar@bangbing.com'
	));
$I->seeInCurrentUrl('/admin/student/find');
$I->see('No entries found.', '.failure');
$I->submitForm('#content form', array(
		'form[sid]:' => '0000000',
		'form[fName]:' => 'Foo',
		'form[lName]:' => 'Bar',
		'form[section]' => 'AZ',
		'form[email]'=> 'foobar@baz.com'
	));
$I->click('delete');
$I->seeElement('.success');