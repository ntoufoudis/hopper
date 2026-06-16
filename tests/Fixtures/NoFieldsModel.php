<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;

final class NoFieldsModel extends Model
{
    protected $table = 'customers';

    public $timestamps = false;
}
