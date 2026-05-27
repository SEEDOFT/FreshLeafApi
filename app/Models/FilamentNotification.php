<?php

namespace App\Models;

use Illuminate\Notifications\DatabaseNotification;

class FilamentNotification extends DatabaseNotification
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'filament_notifications';
}
