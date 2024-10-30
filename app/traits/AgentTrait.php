<?php

namespace App\traits;

use App\Models\Agent;

trait AgentTrait
{
    public function createAgent($payload)
    {
        $agent = new Agent();
        $agent->fill($payload);
        $agent->save();
        return $agent;
    }
}
