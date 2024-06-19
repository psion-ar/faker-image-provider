<?php

declare(strict_types=1);

namespace Psn\FakerImageProvider;

use Exception;
use Faker\Provider\Base;
use Imagick;
use InvalidArgumentException;
use RuntimeException;

class Image extends Base
{
    private const URL = 'https://picsum.photos';

    private null|string $directory = null;
    private null|string $extension = null;

    /**
     * Generates a URL for a random image with the specified width and height.
     *
     * @param int $witdth The width of the image. Defaults to 640.
     * @param int $height The height of the image. Defaults to 480.
     *
     * @throws InvalidArgumentException If the width or height is less than or equal to zero.
     *
     * @return string The URL of the image.
     */
    public static function imageUrl(int $witdth = 640, int $height = 480): string
    {
        if ($witdth <= 0 || $height <= 0) {
            throw new InvalidArgumentException(sprintf('Invalid image size: "%d x %d".', $witdth, $height));
        }

        return sprintf('%s/%d/%d', static::URL, $witdth, $height);
    }


    /**
     * Generates an image with the specified width, height, directory, and extension.
     *
     * @param int $width The width of the image. Defaults to 640.
     * @param int $height The height of the image. Defaults to 480.
     * @param null|string $directory The directory to save the image. Defaults to the system's temporary directory.
     * @param null|string $extension The extension of the image. Defaults to 'jpg'.
     *
     * @throws InvalidArgumentException If the directory is not writable or the extension is not supported.
     *
     * @return string The path to the downloaded and converted image.
     */
    public function image(
        int $width = 640,
        int $height = 480,
        null|string $directory = null,
        null|string $extension = null
    ): string {
        $this->directory = $directory ?? sys_get_temp_dir();
        if ( ! is_dir($this->directory) && ! is_writable($this->directory)) {
            throw new InvalidArgumentException(sprintf('Directory "%s" is not writable.', $this->directory));
        }

        $this->extension = $extension ?? 'jpg';
        if ( ! in_array(mb_strtoupper($this->extension), imagick::queryFormats())) {
            throw new InvalidArgumentException(sprintf('Extension "%s" is not supported.', $this->extension));
        }

        return $this->convert($this->download(static::imageUrl($width, $height)));
    }


    /**
     * Downloads an image from the given URL and saves it to a file in the specified directory.
     *
     * @param string $url The URL of the image to download.
     *
     * @throws RuntimeException If the image fails to download.
     *
     * @return string The path to the downloaded image file.
     */
    protected function download(string $url): string
    {
        $path = sprintf('%s/%s.%s', $this->directory, uniqid('faker_img_'), 'jpg');

        $fh = fopen($path, 'w');
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_FILE, $fh);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        $success = curl_exec($ch) && curl_getinfo($ch, CURLINFO_RESPONSE_CODE) === 200;
        curl_close($ch);
        fclose($fh);

        if ( ! $success) {
            unlink($path);
            throw new RuntimeException(sprintf('Failed to download image from "%s".', $url));
        }

        return $path;
    }

    /**
     * Converts an image file to the specified format.
     *
     * @param string $path The path of the image file to convert.
     *
     * @throws ImagickException If the conversion fails.
     *
     * @return string The path of the converted image file.
     */
    protected function convert(string $path): string
    {
        if ($this->extension === 'jpg') {
            return $path;
        }

        $dir = pathinfo($path, PATHINFO_DIRNAME);
        $file = pathinfo($path, PATHINFO_FILENAME);
        $newPath = sprintf('%s/%s.%s', $dir, $file, $this->extension);

        $image = new Imagick($path);
        $image->setImageFormat($this->extension);
        $image->writeImage($newPath);

        $image->destroy();

        unlink($path);

        return $newPath;
    }
}
