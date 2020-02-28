<?php

namespace Tenth\MyTotalComfort\Tests;

use PHPUnit\Framework\TestCase;
use Tenth\MyTotalComfort;

class MyTotalComfortBadDataTests extends TestCase {

    public function test_invalidEmailThrowsException()
    {
        $this->expectException(MyTotalComfort\Exception::class);
        new MyTotalComfort('invalid', 'invalid');
    }

    public function test_invalidCredentialThrowsException()
    {
        $this->expectException(MyTotalComfort\Exception::class);
        (new MyTotalComfort('invalid@tenth.org', 'badPassword'))->getLocations();
    }

}
