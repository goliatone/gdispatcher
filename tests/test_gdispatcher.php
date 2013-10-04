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

fu::test("CoreEvent: magic getters for type and arguments",function(){
    // fu::ok(property_exists(TEvent, 'type') === FALSE);
    // fu::ok(property_exists(TEvent, 'arguments') === FALSE);
    $e = new TEvent('event_type', array('a'=>1));  
    fu::ok($e->type);
    fu::ok($e->arguments);
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
fu::test("Dispatcher: Singleton", function(){
   $handler = function($e) {};
   $a = Dispatcher::instance();
   $a->add_listener('render', $handler);
   $b = Dispatcher::instance();
   fu::equal($a, $b);
   fu::strict_equal($a, $b);
   fu::ok($b->will_trigger('render'));
   
   $c = new Dispatcher();
   fu::not_equal($a, $c);
   fu::not_strict_equal($a, $c);
   
   $d = Dispatcher::factory();
   fu::not_strict_equal($c, $d);
});

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

fu::test("CoreDispatch::will_trigger", function() {
   $handler = function($e) {};
   $a = Dispatcher::instance();
   $a->add_listener('render', $handler);
   fu::ok($a->will_trigger('render'));
   fu::not_equal($a->will_trigger('render'), $a->will_trigger('BULLCRAP'));
});

fu::test("CoreDispatch::dispatch_event will fire if event is registered.", function(){
    $result = 2;
    $expected = 0;
    $handle = function($e) use (&$expected) { $expected += 2; };
    $event = new Event('render');
    Dispatcher::instance()->add_listener('render',$handle);
    Dispatcher::instance()->dispatch_event($event);
    fu::equal($result, $expected);
});

fu::test("CoreDispatch::dispatch_event will fire if event is registered.", function(){
    $result = 2;
    $expected = 0;
    $handle = function($e){ $e->expected = 2; /*NOTE this fails: $e->expected = 2;*/ };
    $event = new Event('render');
    $event->bind('expected', $expected);
    Dispatcher::instance()->add_listener('render',$handle);
    Dispatcher::instance()->dispatch_event($event);
    fu::equal($result, $expected);
});

fu::test("CoreDispatch::dispatch_event wont fire if no event is registered.", function(){
    $result = 2;
    $expected = 0;
    $handle = function($e) use ($expected) { $expected += 2; };
    $e = new Event('render');
    Dispatcher::instance()->dispatch_event($e);
    fu::not_equal($result, $expected);
});

fu::test("CoreDispatch::dispatch_event can fire the same event multiple times.", function(){
    $result = 2;
    $expected = 0;
    $handle = function($e) use (&$expected) { $expected += 2; };
    $e = new Event('render');
    Dispatcher::instance()->add_listener('render',$handle);
    Dispatcher::instance()->add_listener('render',$handle);
    Dispatcher::instance()->dispatch_event($e);
    fu::equal($result + $result, $expected);
});
fu::test("CoreDispatch::dispatch_event you can stop propagation.", function(){
    $result = 2;
    $expected = 0;
    $handle = function($e) use (&$expected) { $expected += 2; };
    $handle_stop = function($e) use (&$expected) { $expected += 2; $e->stop_propagation = TRUE; };
    $e = new Event('render');
    Dispatcher::instance()->add_listener('render',$handle);
    Dispatcher::instance()->add_listener('render',$handle_stop);
    Dispatcher::instance()->add_listener('render',$handle);
    Dispatcher::instance()->add_listener('render',$handle);
    Dispatcher::instance()->dispatch_event($e, TRUE);
    fu::equal($result + $result, $expected);
});

fu::test("CoreDispatch::add_listener requires a listener.", function(){
    $callback = function(){
        Dispatcher::instance()->add_listener('render'/*,$handle*/);
    };
    fu::throws($callback, 'Exception');
});

fu::test("CoreDispatch::dispatch_event events should be executed as the order in which were attached.", function(){
    $result = "";
    $expected = "THIS IS THE RESULT";    
    $handle0 = function($e) use (&$result) { $result .= "THIS"; };
    $handle1 = function($e) use (&$result) { $result .= " IS"; };
    $handle2 = function($e) use (&$result) { $result .= " THE"; };
    $handle3 = function($e) use (&$result) { $result .= " RESULT"; };
    
    Dispatcher::instance()->add_listener('render',$handle0);
    Dispatcher::instance()->add_listener('render',$handle1);
    Dispatcher::instance()->add_listener('render',$handle2);
    Dispatcher::instance()->add_listener('render',$handle3);
    
    $e = new Event('render');
    Dispatcher::instance()->dispatch_event($e);
    
    fu::equal($expected, $result, "We actually got:\n$result");
});

fu::test("CoreDispatch::dispatch_event events can be attached with priority.", function(){
    $result = "";
    $expected = "RESULT THE IS THIS";    
    $handle0 = function($e) use (&$result) { $result .= " THIS"; };
    $handle1 = function($e) use (&$result) { $result .= " IS"; };
    $handle2 = function($e) use (&$result) { $result .= " THE"; };
    $handle3 = function($e) use (&$result) { $result .= "RESULT"; };
    
    Dispatcher::instance()->add_listener('render',$handle0, 0);
    Dispatcher::instance()->add_listener('render',$handle1, 1);
    Dispatcher::instance()->add_listener('render',$handle2, 2);
    Dispatcher::instance()->add_listener('render',$handle3, 3);
    
    $e = new Event('render');
    Dispatcher::instance()->dispatch_event($e);
    
    fu::equal($expected, $result, "We actually got:\n$result");
});
#############################################
# CoreListener:
#
#############################################
fu::test("CoreListener::TBD.", function(){
    // fu::expect_fail('CORELISTENER: TODO');
});

#############################################
# RUN IT!
# $ php -f tests/test_gdispatcher.php 
#############################################
$exit = fu::run();
exit($exit);