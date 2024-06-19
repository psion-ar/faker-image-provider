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

describe('image', function (): void {
    it('can download an image', function (): void {
        $file = $this->faker->image();

        expect($file)
            ->toBeFile()
            ->toHaveMime('image/jpeg');

        unlink($file);
    });

    it('can download an image with an arbitrary width and height', function (): void {
        $file = $this->faker->image(320, 240);

        expect($file)->toEqualDimension(320, 240);

        unlink($file);
    });

    it('can download an image to an arbitrary directory', function (): void {
        $tempDir = $this->tempDir->create();
        $file = $this->faker->image(directory: $tempDir->path());

        expect($file)->toBeFile()->toHaveMime('image/jpeg');
    });

    it('can convert an image to an arbitrary extension', function (string $extension): void {
        $file = $this->faker->image(directory: $this->tempDir->path(), extension: $extension);

        expect($file)->toBeFile()->toHaveMime("image/{$extension}");
    })->with(['jpeg',  'png', 'gif', 'webp', 'avif', 'heic', 'tiff']);
});
