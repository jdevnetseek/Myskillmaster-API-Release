<?php

namespace Tests\Unit\Support\Boolean;

use Tests\TestCase;
use App\Support\Boolean;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CastTest extends TestCase
{
    /** @test */
    public function itShouldReturnProperBooleanValue()
    {
        $possibleTrue = [true, 1, '1', 'TRUE', 'true', 'anyValueShouldBeTruthy'];

        foreach ($possibleTrue as $value) {
            $res = Boolean::cast($value);

            $this->assertIsBool($res);
            $this->assertSame($res, true);
            $this->assertTrue($res);
        }

        $possibleFalse = [false, 0, '0', 'FALSE', 'false', '', null];

        foreach ($possibleFalse as $value) {
            $res = Boolean::cast($value);

            $this->assertIsBool($res);
            $this->assertSame($res, false);
            $this->assertFalse($res);
        }
    }
}
