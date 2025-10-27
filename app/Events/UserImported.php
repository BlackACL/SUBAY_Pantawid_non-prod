<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserImported
{
    use Dispatchable, SerializesModels;

    public $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
