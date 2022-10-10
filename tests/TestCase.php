<?php

use Laravel\Lumen\Testing\TestCase as BaseTestCase;
use PHPUnit\Framework\MockObject\MockObject;

abstract class TestCase extends BaseTestCase
{
    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    /**
     * Gets a mock for the specified model class
     *
     * @param string $modelClassName
     * @return MockObject
     */
    protected function getModelMock(string $modelClassName): MockObject
    {
        return $this->getMockBuilder($modelClassName)
            ->onlyMethods(['getKey'])
            ->getMock();
    }
}
