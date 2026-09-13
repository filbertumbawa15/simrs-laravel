<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Disable activity log — auth()->user() null di CLI test,
        // dan migration bikin causer NOT NULL. Sama polanya dengan UserSeeder.
        activity()->disableLogging();
    }
}
