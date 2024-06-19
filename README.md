# FakerPHP Image Provider
A custom image provider for the [FakerPHP](https://github.com/FakerPHP) library.  
The package uses [LOREM PICSUM](http://picsum.photos) to generate url's and images.  

## Usage
- add provider  

    ```php
    $faker = Faker\Factory::create(); 
    $faker->addProvider(new Psn\FakerImageProvider\Image($faker));
    ```
- generate url's  

    ```
    Image::imageUrl(int $witdth = 640, int $height = 480): string
    ```
    ```php
    // https://picsum.photos/640/480
    $url = $faker->imageUrl(); 

    // https://picsum.photos/1280/720
    $url = $faker->imageUrl(width: 1280, height: 720);
    ```
- generate images  

    ```
    Image::image(
        int $width = 640, 
        int $height = 480, 
        ?string $directory = null, 
        ?string $extension = null
    ): string
    ```  
    ```php
    // /tmp/faker_img_6672b731a5760.jpg 
    $path = $faker->image(); 

    // /home/user/test/faker_img_6672b731a5760.jpg
    $path = $faker->image(directory: '~/test'); 

    // /tmp/faker_img_6672b731a5760.heic
    $path = $faker->image(extension: 'heic');
    ```