<?php

declare(strict_types=1);

namespace Ntoufoudis\Hopper\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

final class ExplodingCustomer extends Model
{
    protected $table = 'customers';

    protected $fillable = ['name', 'email'];

    public $timestamps = false;

    protected static function booted(): void
    {
        ExplodingCustomer::creating(function (ExplodingCustomer $model): void {
            if ($model->name === 'BOOM') {
                throw new RuntimeException('boom');
            }
        });
    }
}
