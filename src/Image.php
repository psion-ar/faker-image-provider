<?php

declare(strict_types=1);

namespace Psn\FakerImageProvider;

use Faker\Provider\Base;
use Imagick;
use InvalidArgumentException;
use RuntimeException;

class Image extends Base
{
    private const URL = 'https://picsum.photos';

    private null|string $dirname = null;
    private null|string $filename = null;
    private null|string $extension = null;


    /**
     * Generates a URL for an image with the specified width and height.
     *
     * @param int $width The width of the image in pixels. Default is 640.
     * @param int $height The height of the image in pixels. Default is 480.
     *
     * @throws InvalidArgumentException If the width or height is less than or equal to 0.
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
     * Generates an image file with the specified dimensions and saves it to the specified directory.
     *
     * @param int $width The width of the image in pixels. Default is 640.
     * @param int $height The height of the image in pixels. Default is 480.
     * @param null|string $dirname The directory where the image file will be saved. If not provided, the system's temporary directory will be used.
     * @param null|string $filename The name of the image file. If not provided, a unique name will be generated.
     * @param null|string $extension The extension of the image file. If not provided, 'jpg' will be used.
     *
     * @throws InvalidArgumentException If the directory is not writable or the extension is not supported.
     *
     * @return string The path of the saved image file.
     */
    public function image(
        int $width = 640,
        int $height = 480,
        null|string $dirname = null,
        null|string $filename = null,
        null|string $extension = null
    ): string {
        $this->dirname = $dirname ?? sys_get_temp_dir();
        if ( ! is_dir($this->dirname) || ! is_writable($this->dirname)) {
            throw new InvalidArgumentException(sprintf('Directory "%s" is not writable.', $this->dirname));
        }

        $this->filename = $filename ?? uniqid('faker_img_');

        $this->extension = $extension ?? 'jpg';
        if ( ! in_array(mb_strtoupper($this->extension), Imagick::queryFormats())) {
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
        $path = sprintf('%s/%s.%s', $this->dirname, $this->filename, 'jpg');

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

        $newPath = sprintf('%s/%s.%s', $this->dirname, $this->filename, $this->extension);

        $image = new Imagick($path);
        $image->setImageFormat($this->extension);
        $image->writeImage($newPath);

        $image->destroy();

        unlink($path);

        return $newPath;
    }
}
