<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

final class BrokenModel extends Model
{
    protected $table = 'table_that_does_not_exist';

    protected $fillable = ['name', 'email'];

    public $timestamps = false;
}
