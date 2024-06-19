<?php

declare(strict_types=1);

use Pest\Expectation;
use Spatie\TemporaryDirectory\TemporaryDirectory;

// TESTCASES
uses()
    ->beforeAll(function (): void {
        (new TemporaryDirectory(getTempPath()))->delete();
    })
    ->beforeEach(function (): void {
        $this->tempDir = (new TemporaryDirectory(getTestSupportPath()))->name('temp');
    })
    ->in('.');

// EXPECTATIONS
expect()->extend('toHaveMime', function (string $expectedMime): Expectation {
    $file = finfo_open(FILEINFO_MIME_TYPE);
    $actualMime = finfo_file($file, $this->value);
    finfo_close($file);

    expect($actualMime)->toEqual($expectedMime);

    return $this;
});

expect()->extend('toEqualDimension', function (int $expectedWidth, int $expectedHeight): Expectation {
    $actualWidth = getimagesize($this->value)[0];
    $actualHeight = getimagesize($this->value)[1];

    expect([$actualWidth, $actualHeight])->toEqual([$expectedWidth, $expectedHeight]);

    return $this;
});

// HELPERS
function getTempPath($suffix = ''): string
{
    return getTestSupportPath('temp/'.$suffix);
}

function getTestSupportPath($suffix = ''): string
{
    return __DIR__."/TestSupport/{$suffix}";
}
