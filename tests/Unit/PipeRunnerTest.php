<?php

declare(strict_types=1);

use Ntoufoudis\Hopper\Exceptions\RowRejected;
use Ntoufoudis\Hopper\Pipeline\PipeRunner;
use Ntoufoudis\Hopper\Tests\Fixtures\Pipes\LowercaseEmail;
use Ntoufoudis\Hopper\Tests\Fixtures\Pipes\RejectBlankName;

it('returns the row unchanged when there are no pipes', function () {
    $row = ['name' => 'Alice', 'email' => 'ALICE@X.COM'];

    expect(app(PipeRunner::class)->process($row, []))->toBe($row);
});

it('runs pipes in order and returns the transformed row', function () {
    $result = app(PipeRunner::class)->process(
        ['name' => 'Alice', 'email' => 'ALICE@X.COM'],
        [new LowercaseEmail],
    );

    expect($result['email'])->toBe('alice@x.com');
});

it('lets a RowRejected thrown inside a pipe propagate', function () {
    app(PipeRunner::class)->process(
        ['name' => '', 'email' => 'x@x.com'],
        [new RejectBlankName],
    );
})->throws(RowRejected::class, 'name is required');
