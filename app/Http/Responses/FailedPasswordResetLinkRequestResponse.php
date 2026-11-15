<?php

namespace App\Http\Responses;

use Illuminate\Support\Facades\Password;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse as FailedPasswordResetLinkRequestResponseContract;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse as SuccessfulPasswordResetLinkRequestResponseContract;
use Laravel\Fortify\Http\Responses\FailedPasswordResetLinkRequestResponse as DefaultFailedPasswordResetLinkRequestResponse;

class FailedPasswordResetLinkRequestResponse implements FailedPasswordResetLinkRequestResponseContract
{
    public function __construct(protected string $status)
    {
        //
    }

    public function toResponse($request)
    {
        if (in_array($this->status, [Password::INVALID_USER, Password::RESET_THROTTLED], true)) {
            return app(SuccessfulPasswordResetLinkRequestResponseContract::class, [
                'status' => Password::RESET_LINK_SENT,
            ])->toResponse($request);
        }

        return app(DefaultFailedPasswordResetLinkRequestResponse::class, [
            'status' => $this->status,
        ])->toResponse($request);
    }
}
