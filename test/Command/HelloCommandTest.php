<?php

namespace Colfej\LeKioskCLI\Test\Command;

use Colfej\LeKioskCLI\Test\AppTestCase;

class HelloCommandTest extends AppTestCase {

    /**
     * @test
     */
    public function it_should_say_hello_world()
    {
        $output = $this->runCommand("hello");
        $this->assertEquals("Hello World", trim($output));
    }

}
