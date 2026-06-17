<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Maps onto the customers table but casts the match field to a non-scalar, so a
 * primed DatabaseResolver lookup encounters a non-scalar attribute value and
 * exercises its is_scalar() guard.
 */
final class ArrayEmailCustomer extends Model
{
    protected $table = 'customers';

    public $timestamps = false;

    protected $guarded = [];

    /** @var array<string, string> */
    protected $casts = ['email' => 'array'];
}
