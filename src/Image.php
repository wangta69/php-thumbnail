<?php
namespace Pondol\Image;
use Illuminate\Support\Facades\Log;
class Image{
  public $width;
  public $height;
  public $format;
  public $bits;
  public $channels;
  public $mime;
  public $resource;
  public $name;
  public $extension;

  public function __construct() {
    $get_arguments = func_get_args();
    $number_of_arguments = func_num_args();

    if (method_exists($this, $method_name = '__construct'.$number_of_arguments)) {
      call_user_func_array(array($this, $method_name), $get_arguments);
    }
  }

  public function __construct1($argument1) {
    $this->createImage($argument1);
  }


  public function createImage($src){
    try {
      $img_info = getImageSize($src);
    // img_info ( [0] => width [1] => height [2] => format [3] => width="" height="" [bits] => 8 [mime] => image/png )
      $this->width = $img_info[0];
      $this->height = $img_info[1];
      $this->format = $img_info[2];
      $this->bits = isset($img_info['bits']) ? $img_info['bits'] : '';
      $this->channels = isset($img_info['channels']) ? $img_info['channels'] : '';
      $this->mime = isset($img_info['mime']) ? $img_info['mime'] : '';
      $this->setName($src);

      // $imagetype = exif_imagetype ( $src );
      try {
        switch($img_info[2]){
          case (1):$this->resource = ImageCreateFromGif($src);break;
          case (2):$this->resource = ImageCreateFromJPEG($src);break;
          case (3):$this->resource = ImageCreateFromPNG($src);break;
          case (6):$this->resource = imagecreatefrombmp($src);break;
          case (15):$this->resource = imagecreatefromwbmp($src);break;
          default:$this->resource = false;break;
        }
        return $this;
      } catch (\Exception $e) {
        return $this->exceptonErr($e);
      }
    } catch (\Exception $e) {
      return $this->exceptonErr($e);
    }
  }

  private function exceptonErr($e) {
    Log::info($e);
    $src = dirname(__FILE__) . "/./resource/prohibition.png";
    $img_info = getImageSize($src);

    $this->width = $img_info[0];
    $this->height = $img_info[1];
    $this->format = $img_info[2];
    $this->bits = isset($img_info['bits'])? $img_info['bits']:null; //animate gif 일경우 이 부분에서 에러 발생
    $this->channels = isset($img_info['channels']) ? $img_info['channels'] : null;
    $this->mime = $img_info['mime'];
    $this->setName($src);
    $this->resource = ImageCreateFromPNG($src);
    //$this->resource   = false;
    return $this;
  }

  private function setName($src){
    $file_name = basename($src);
    $explode = explode(".",$file_name);
    $this->name = $explode[0];
    $this->extension = isset($explode[1]) ? $explode[1] : null;
  }

  public function get_filename()
  {
    return $this->name.".".$this->extension;
  }
}
