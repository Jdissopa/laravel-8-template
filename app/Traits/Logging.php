<?php

namespace App\Traits;

use Illuminate\Support\Facades\Log;

trait Logging
{

    protected function getActorData($userID)
    {
        return [
            'actor_id'      => $userID,
            'ip'            => request()->ip(),
            'path'          => request()->path(),
        ];
    }
}
