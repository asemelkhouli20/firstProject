<?php

namespace Tests;

use Illuminate\Contracts\Auth\Authenticatable as UserContract;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Sanctum\Sanctum;

abstract class TestCase extends BaseTestCase
{
    //

    public function actingSanctumAs(UserContract $user, $abilities = ['*'])
    {
        Sanctum::actingAs($user, $abilities);

        return $this;
    }
}
