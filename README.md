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

#### save remote image to local (image resizing With the ratio maintained )
```
use Pondol\Image\GetHttpImage;
$img_url = 'http://www.shop-wiz.com/myphoto.jpg';
$save_path = '/home/photos';
$image->read($img_url)->->create(100, 100)->copyWithRatio()->save($save_path);
```
### Request Json
```

```