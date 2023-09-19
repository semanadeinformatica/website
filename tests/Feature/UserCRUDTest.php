<?php

namespace Tests\Feature;

use App\Http\Controllers\UserCRUDController;
use App\Models\User;
use Tests\CRUDTestCase;

class UserCRUDTest extends CRUDTestCase
{
    protected string $controller = UserCRUDController::class;

    protected string $model = User::class;
}
