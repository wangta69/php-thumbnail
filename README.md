# php-thumbnail

## Installation
```
composer require wangta69/php-thumbnail

composer require "wangta69/php-thumbnail @dev"

```
## How to Use
#### save remote image to local
```
use Pondol\Image\GetHttpImage;
$img_url = 'http://www.shop-wiz.com/myphoto.jpg';
$save_path = '/home/photos';
$image->read($img_url)->save($save_path);
```
#### read(source image url)
=== read image from url;
#### set_size(width, height)
=== create blank image
#### copyimage()
=== copy source image to destination image with croping from center 
#### copyWithRatio()
=== copy source image to destination image 
#### save(destinaition image url)
=== save or create image
#### format(image format)
=== change image format
#### name('image name')
=== change image name, image name shoud be without extention, extention will be created according to image format 



#### save remote image to local (image resizing With croping sourceimage from center )
```
use Pondol\Image\GetHttpImage;
$img_url = 'http://www.shop-wiz.com/myphoto.jpg';
$save_path = '/home/photos';

$image = new GetHttpImage();
$image->read($img_url)->->set_size(100, 100)->copyimage()->save($save_path);
```


#### save remote image to local (image resizing With maintaining the source ratio  )
```
use Pondol\Image\GetHttpImage;
$img_url = 'http://www.shop-wiz.com/myphoto.jpg';
$save_path = '/home/photos';

$image = new GetHttpImage();
$image->read($img_url)->->set_size(100, 100)->copyWithRatio()->save($save_path);
```



#### save remote image to local (image resizing With the ratio maintained and change file format )
```
use Pondol\Image\GetHttpImage;
$img_url = 'http://www.shop-wiz.com/myphoto.jpg';
$save_path = '/home/photos';

$image = new GetHttpImage();
$image->read($img_url)->set_size(100, 100)->copyWithRatio()->format('png')->save($save_path);
```


#### save remote image to local (change file name )
```
use Pondol\Image\GetHttpImage;
$img_url = 'http://www.shop-wiz.com/myphoto.jpg';
$save_path = '/home/photos';

$image = new GetHttpImage();

$image->read($img_url)->set_size(100, 100)->copyWithRatio()->name('myphoto')->save($save_path);

or 

$image->read($img_url)->set_size(100, 100)->copyWithRatio()->name('myphoto')->format('png')->save($save_path);
```
