<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller
{
    // Gives every controller the authorize() helper used with Policies.
    use AuthorizesRequests;
}
