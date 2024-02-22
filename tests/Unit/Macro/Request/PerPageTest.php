<?php

namespace Tests\Unit\Macro\Request;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PerPageTest extends TestCase
{
    /**     *
     * @test
     */
    public function perPageReturnsPassedValueWhenNoRequestParameters()
    {
        $val = request()->perPage(105);

        $this->assertEquals($val, 105);
    }

    /**
     * @test
     */
    public function returnTheValueOfParameterPerPage()
    {
        request()->merge(['per_page' => 105]);
        $val = request()->perPage();
        
        $this->assertEquals($val, 105);
    }

    /**
     * @test
     */
    public function returnTheValueOfParameterLimit()
    {
        request()->merge(['limit' => 105]);
        $val = request()->perPage();
        
        $this->assertEquals($val, 105);
    }
}
