<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function __invoke(Request $request): JsonResource
    {
        return UserResource::make($request->user());
    }
}
