<?php

use \Kasha\Profiler\Profiler;

class ProfilerTest extends PHPUnit_Framework_TestCase
{
	public function testDummy()
	{
		$this->assertEquals(1, 1);
	}

    public function testCreateProfiler()
    {
        try {
            $profiler = new Profiler();
            $this->assertInstanceOf('Profiler', get_class($profiler));
        } catch (Exception $ex) {
            print 'failed to instantiate Profiler class' . PHP_EOL;
        }
    }
}

