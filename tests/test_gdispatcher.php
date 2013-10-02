<?php
require './vendor/autoload.php';
require './vendor/funkatron/funit/FUnit.php';

use Exception;

use \FUnit\fu;
use goliatone\events\Event;
use goliatone\events\Dispatcher;
use goliatone\events\core\CoreEvent;

class TEvent extends CoreEvent{};

fu::setup(function() use($config) {
    // set a fixture to use in tests
});
fu::teardown(function() {
    // this resets the fu::$fixtures array. May not provide clean shutdown
});

/////////////////////////////////////////////
// CoreEvent -> abstract class
/////////////////////////////////////////////
fu::test("CoreEvent: constructor", function(){
   // class TEvent extends CoreEvent{};
   $event = new TEvent('event_type');    
   fu::ok($event);
});

fu::test("CoreEvent: constructor with arguments", function(){
   // class TEvent extends CoreEvent{};
   $event = new TEvent('event_type', array('name'=>'goliatone', 'age'=>32));    
   fu::ok($event);
   fu::equal($event->age, 32);
   fu::equal($event->name, 'goliatone');
});
fu::test("CoreEvent: setters", function(){
   // class TEvent extends CoreEvent{};
   $e = new TEvent('event_type');    
   $e->set('name', 'goliatone');
   fu::equal($e->name, 'goliatone');
});

fu::test("CoreEvent: magic setters", function(){
   // class TEvent extends CoreEvent{};
   $e = new TEvent('event_type');    
   $e->magic =  'abracadabra';
   fu::equal($e->magic, 'abracadabra');
});
/*
fu::test("CoreEvent: toString with arguments", function(){
   // class TEvent extends CoreEvent{};
   $event = new TEvent('event_type', array('name'=>'goliatone', 'age'=>32));    
   print $event;
   fu::equal((string) $event, "[Event: type: 'event_type', data( 'name'=>'goliatone', 'age'=>32 ) ]");
});
*/
// fu::test("CoreEvent", function(){
   // class TEvent extends CoreEvent{};
//    
   // $thrower = function(){
        // $event = new TEvent(/*NEEDED*/);    
   // }; 
   // fu::throws($thrower, NULL, Exception);
// });
/////////////////////////////////////////////
// Dispatcher
/////////////////////////////////////////////
fu::test("Dispatch event", function() {
    $result = 2;
    $expected;
    $handle_render_body = function($e) {
        print "hola";
        $e->expected = 2;
    };    
    Dispatcher::instance()->add_listener('render', $handle_render_body);
    $event = new Event('render');
    $event->bind("expected",$expected);
    $event->dispatch();
    fu::equal($result, $expected, "");
});

#############################################
# RUN IT!
# $ php -f tests/test_gdispatcher.php 
#############################################
$exit = fu::run();
exit($exit);