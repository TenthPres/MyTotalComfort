<?php

namespace Tenth\MyTotalComfort\Tests;

use PHPUnit\Framework\TestCase;
use Tenth\MyTotalComfort;

class MyTotalComfortRealDataTests extends TestCase {

    protected $email = "invalid@tenth.org";
    protected $password = "invalid";

    public function test_invalidEmailThrowsException() {
        $this->expectException(MyTotalComfort\Exception::class);
        new MyTotalComfort('invalid', 'invalid');
    }

    public function test_invalidCredentialThrowsException() {
        $this->expectException(MyTotalComfort\Exception::class);
        new MyTotalComfort('invalid@tenth.org', 'badPassword');
    }
}