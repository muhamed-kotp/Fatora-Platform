<?php
namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait AuthorizeCheck {
    public function authorizCheck($id)
    {
            if (Auth::user()->id != $id) {
                throw new \Illuminate\Auth\Access\AuthorizationException(__(' Sorry, Un Authorized'));
            }
    }
}