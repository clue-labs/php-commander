<?php

use Clue\Commander\Router;

class RouterTest extends PHPUnit_Framework_TestCase
{
    public function testEmptyRouterHasNoRoutes()
    {
        $router = new Router();

        $this->assertEquals(array(), $router->getRoutes());
    }

    public function testAddRouteReturnRoute()
    {
        $router = new Router();

        $route = $router->add('hello', function () { });

        $this->assertInstanceOf('Clue\Commander\Route', $route);
        $this->assertEquals(array($route), $router->getRoutes());
    }

    public function testAddRouteCanBeRemoved()
    {
        $router = new Router();

        $route = $router->add('hello', function () { });

        $router->remove($route);

        $this->assertEquals(array(), $router->getRoutes());
    }

    /**
     * @expectedException UnderflowException
     */
    public function testCanNotRemoveRouteWhichHasNotBeenAdded()
    {
        $router = new Router();
        $route = $router->add('hello', function () { });

        $router2 = new Router();
        $router2->remove($route);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddRouteThrowsForInvalidHandler()
    {
        $router = new Router();

        $router->add('hello', 'invalid');
    }

    public function testHandleAddedRouteEmpty()
    {
        $router = new Router();

        $invoked = null;
        $router->add('hello', function ($args) use (&$invoked) {
            $invoked = $args;
        });

        $this->assertNull($invoked);

        $router->handleArgs(array('hello'));

        $this->assertEquals(array(), $invoked);
    }

    public function testHandleArgvAddedRouteEmpty()
    {
        $router = new Router();

        $invoked = null;
        $router->add('hello', function ($args) use (&$invoked) {
            $invoked = $args;
        });

        $this->assertNull($invoked);

        $router->handleArgv(array('ignored', 'hello'));

        $this->assertEquals(array(), $invoked);
    }

    public function testHandleArgvFromGlobalsAddedRouteEmpty()
    {
        $router = new Router();

        $invoked = null;
        $router->add('hello', function ($args) use (&$invoked) {
            $invoked = $args;
        });

        $this->assertNull($invoked);

        $orig = $_SERVER['argv'];
        $_SERVER['argv'] = array('ignored', 'hello');
        $router->handleArgv();
        $_SERVER['argv'] = $orig;

        $this->assertEquals(array(), $invoked);
    }

    public function testExecArgvAddedRouteEmpty()
    {
        $router = new Router();

        $invoked = null;
        $router->add('hello', function ($args) use (&$invoked) {
            $invoked = $args;
            return 2;
        });

        $this->assertNull($invoked);

        $ret = $router->execArgv(array('ignored', 'hello'));

        $this->assertEquals(array(), $invoked);

        $this->assertNull($ret);
    }

    public function testHandleAddedRouteWithArgument()
    {
        $router = new Router();

        $invoked = null;
        $router->add('hello <name>', function ($args) use (&$invoked) {
            $invoked = $args;
        });

        $this->assertNull($invoked);

        $router->handleArgs(array('hello', 'clue'));

        $this->assertEquals(array('name' => 'clue'), $invoked);
    }

    public function testHandleAddedRouteWithOptionalArgument()
    {
        $router = new Router();

        $invoked = null;
        $router->add('hello [<name>]', function ($args) use (&$invoked) {
            $invoked = $args;
        });

        $this->assertNull($invoked);

        $router->handleArgs(array('hello', 'clue'));

        $this->assertEquals(array('name' => 'clue'), $invoked);
    }

    public function testHandleAddedRouteWithoutOptionalArgument()
    {
        $router = new Router();

        $invoked = null;
        $router->add('hello [<name>]', function ($args) use (&$invoked) {
            $invoked = $args;
        });

        $this->assertNull($invoked);

        $router->handleArgs(array('hello'));

        $this->assertEquals(array(), $invoked);
    }

    public function testHandleAddedRouteWitEllipseArguments()
    {
        $router = new Router();

        $invoked = null;
        $router->add('hello <names>...', function ($args) use (&$invoked) {
            $invoked = $args;
        });

        $this->assertNull($invoked);

        $router->handleArgs(array('hello', 'first', 'second'));

        $this->assertEquals(array('names' => array('first', 'second')), $invoked);
    }

    public function testHandleAddedRouteWithoutOptionalEllipseArguments()
    {
        $router = new Router();

        $invoked = null;
        $router->add('hello [<names>...]', function ($args) use (&$invoked) {
            $invoked = $args;
        });

        $this->assertNull($invoked);

        $router->handleArgs(array('hello'));

        $this->assertEquals(array(), $invoked);
    }

    /**
     * @expectedException Clue\Commander\NoRouteFoundException
     */
    public function testHandleEmptyRouterThrowsUnderflowException()
    {
        $router = new Router();

        $router->handleArgs(array());
    }

    /**
     * @expectedException Clue\Commander\NoRouteFoundException
     */
    public function testHandleRouteDoesNotMatch()
    {
        $router = new Router();
        $router->add('hello', 'var_dump');

        $router->handleArgs(array('test'));
    }

    /**
     * @expectedException Clue\Commander\NoRouteFoundException
     */
    public function testHandleRouteSentenceDoesNotMatch()
    {
        $router = new Router();
        $router->add('hello world', 'var_dump');

        $router->handleArgs(array('hello'));
    }

    /**
     * @expectedException Clue\Commander\NoRouteFoundException
     */
    public function testHandleRouteArgumentDoesNotMatch()
    {
        $router = new Router();
        $router->add('hello <name>', 'var_dump');

        $router->handleArgs(array('hello'));
    }

    /**
     * @expectedException Clue\Commander\NoRouteFoundException
     */
    public function testHandleAddedRouteWithoutEllipseArgumentsDoesNotMatch()
    {
        $router = new Router();
        $router->add('hello <names>...', 'var_dump');

        $router->handleArgs(array('hello'));
    }
}
