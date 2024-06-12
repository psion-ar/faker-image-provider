<?php

declare(strict_types=1);

namespace Psn\FakerImageProvider;

use Faker\Provider\Base;
use InvalidArgumentException;

class Image extends Base
{
    private const URL = 'https://picsum.photos';

    /**
     * Generates a URL for a random image with the specified width and height.
     *
     * @param int $witdth The width of the image. Defaults to 640.
     * @param int $height The height of the image. Defaults to 480.
     * @throws InvalidArgumentException If the width or height is less than or equal to zero.
     * @return string The URL of the image.
     */
    public static function imageUrl(int $witdth = 640, int $height = 480): string
    {
        if ($witdth <= 0 || $height <= 0) {
            throw new InvalidArgumentException(sprintf('Invalid image size: "%d x %d".', $witdth, $height));
        }

        return sprintf('%s/%d/%d', static::URL, $witdth, $height);
    }
}
