<?php

declare(strict_types=1);

use Faker\Factory;
use Psn\FakerImageProvider\Image;

beforeEach(function (): void {
    $this->faker = Factory::create();
    $this->faker->addProvider(new Image($this->faker));
});

describe('imageUrl', function (): void {
    it('generates a url', function (): void {
        expect($this->faker->imageUrl())->toEqual('https://picsum.photos/640/480');
    });

    it('generates url\'s with different width\'s and height\'s', function (int $width, int $height): void {
        expect($this->faker->imageUrl($width, $height))
            ->toEqual(sprintf('https://picsum.photos/%d/%d', $width, $height));
    })->with([1920, 1280, 600], [1080, 720, 600]);

    it('throws with invalid arguments', function (int $width, int $height): void {
        expect(fn () => $this->faker->imageUrl($width, $height))->toThrow(InvalidArgumentException::class);
    })->with([-1, 0], [0, -1]);
});
