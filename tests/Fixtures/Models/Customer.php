<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;

final class Customer extends Model
{
    protected $fillable = ['name', 'email'];

    public $timestamps = false;
}
