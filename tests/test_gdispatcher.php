<?php
require './vendor/autoload.php';
require './vendor/funkatron/funit/FUnit.php';

use \FUnit\fu;
use goliatone\events\Event;
use goliatone\events\Dispatcher;

fu::setup(function() use($config) {
    // set a fixture to use in tests
});
fu::teardown(function() {
    // this resets the fu::$fixtures array. May not provide clean shutdown
});

/////////////////////////////////////////////
// FLATG HELPER METHODS
/////////////////////////////////////////////
fu::test("scriptURL returns the current url", function() {
    $result = 2;
    $expected = 2;
    fu::equal($result, $expected, "");
    
});

#############################################
# RUN IT!
# $ php -f tests/test_gdispatcher.php 
#############################################
$exit = fu::run();
exit($exit);