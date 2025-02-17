<?php

namespace App\Traits;

trait HasTokenGeneration
{
    protected $token;

    protected function createToken()
    {
        return \Illuminate\Support\Str::random(64);
    }

    public function toArray($notifiable): array
    {
        return [
            'token' => $this->token
        ];
    }
}
