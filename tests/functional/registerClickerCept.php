<?php
$I = new TestGuy($scenario);
$I->wantTo('try to register a clicker');
$I->amOnPage('/clicker/register');
$I->click('Register');
$I->see('Invalid form.', '.failure');
$I->seeElement('div[data-tip="This value should not be blank."] input[name="form[cid]"]');
$I->seeElement('div[data-tip="This value should not be blank."] input[name="form[sid]"]');
$I->seeElement('div[data-tip="This value should not be blank."] input[name="form[lName]"]');

// not a real person
$I->fillField('Clicker ID:', '01FA91');
$I->fillField('Student ID:', '0087103');
$I->fillField('Last Name:', 'Clawson');
$I->click('Register');
$I->see('Could not find anyone with that last name and ID.', '.failure');

// register successfully
$I->fillField('Clicker ID:', '012345');
$I->fillField('Student ID:', '1126819');
$I->fillField('Last Name:', 'Clawson');
$I->click('Register');
$I->seeElement('.success');

// try to register someone else to the same clicker
$I->fillField('Clicker ID:', '012345');
$I->fillField('Student ID:', '1234567');
$I->fillField('Last Name:', 'Hurley');
$I->click('Register');
$I->see('Someone else is already registered to that clicker.' ,'.failure');

// register from one clicker to another
$I->fillField('Clicker ID:', '012346');
$I->fillField('Student ID:', '1234567');
$I->fillField('Last Name:', 'Hurley');
$I->click('Register');
$I->fillField('Clicker ID:', '012347');
$I->fillField('Student ID:', '1126819');
$I->fillField('Last Name:', 'Clawson');
$I->click('Register');
$I->fillField('Clicker ID:', '012345');
$I->fillField('Student ID:', '1234567');
$I->fillField('Last Name:', 'Hurley');
$I->click('Register');
$I->seeElement('.success');


// register to out of the way clickers
$I->fillField('Clicker ID:', '111111');
$I->fillField('Student ID:', '1234567');
$I->fillField('Last Name:', 'Hurley');
$I->click('Register');
$I->fillField('Clicker ID:', '000000');
$I->fillField('Student ID:', '1126819');
$I->fillField('Last Name:', 'Clawson');
$I->click('Register');