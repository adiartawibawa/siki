<?php

namespace App\Http\Responses;

use Illuminate\Support\Facades\Password;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse;

class QuietFailedPasswordResetLinkRequestResponse implements FailedPasswordResetLinkRequestResponse
{
    public function __construct(protected string $status) {}

    public function toResponse($request)
    {
        return back()->with('status', trans(Password::RESET_LINK_SENT));
    }
}
