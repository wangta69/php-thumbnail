<?php 
namespace Pondol\Image;

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
        $get_arguments       = func_get_args();
        $number_of_arguments = func_num_args();

        if (method_exists($this, $method_name = '__construct'.$number_of_arguments)) {
            call_user_func_array(array($this, $method_name), $get_arguments);
        }
    }
    
    public function __construct1($argument1) {
        $this->createImage($argument1);
    }
    
    
    public function createImage($src){
        $img_info = getImageSize($src);

        $this->width   = $img_info[0]; 
        $this->height  = $img_info[1]; 
        $this->format  = $img_info[2]; 
        $this->bits  = $img_info['bits']; 
        $this->channels  = isset($img_info['channels']) ? $img_info['channels'] : null; 
        $this->mime  = $img_info['mime']; 
        $this->setName($src);

        switch($img_info[2]){ 
            case (1):$this->resource  = ImageCreateFromGif($src);break; 
            case (2):$this->resource  = ImageCreateFromJPEG($src);break; 
            case (3):$this->resource  = ImageCreateFromPNG($src);break; 
            case (6):$this->resource  = imagecreatefrombmp($src);break; 
            case (15):$this->resource = imagecreatefromwbmp($src);break; 
            default:$this->resource   = false;break; 
        } 
        
        return $this;
    }
    
    private function setName($src){
        $file_name          = basename($src);
        $explode            = explode(".",$file_name);
        
        
        $this->name         = $explode[0];
        $this->extension    = isset($explode[1]) ? $explode[1] : null;
    }
    
    public function get_filename()
    {
        return $this->name.".".$this->extension;
    }
} 