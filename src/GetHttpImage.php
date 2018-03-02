<?php 
namespace Pondol\Image;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Response;

use Pondol\Image\Image;
use Pondol\Image\ImageService;
use Pondol\Files\File;

class GetHttpImage{

    private $imgSvc;
    private $src_img    = null;//sorceimg  [width] => 288 [height] => 512 [format] => 2 [bits] => 8 [channels] => 3 [mime] => image/jpeg  [resource ] => Resource id #22
    private $dest_img   = null;
    
    public function __construct() {
        $this->imgSvc = new ImageService();
    }
    
    /**
     * readImage from Url
     * @param String $httpUrl : image url (/path/filename or http(s)://imageUrl)
     * Set to Array ([width] => 288 [height] => 512 [format] => 2 [image] => Resource id #22)
     */
    public function read($httpUrl){
        $this->src_img = $this->imgSvc->create_image($httpUrl);//
        return $this;
    }
    
    /**
     * blank image create
     */
    public function create($width = 100, $height = 100){
        $this->dest_img = new Image();//$this->src_img;
        
        $this->dest_img->name = $this->src_img->name;
        $this->dest_img->format = $this->src_img->format;
        
        if($width == 0  && $this->src_img->height > $height )
            $width = ceil(($height * $this->src_img->width) / $this->src_img->height); 
        else if($height == 0  && $this->src_img->width > $width)
            $height = ceil(($width * $this->src_img->height) / $this->src_img->width); 
        
        
        $this->dest_img->width = $width;
        $this->dest_img->height = $height;
        
         if($this->src_img->format == 1)  
        { 
            $this->dest_img->resource    = imagecreate($width, $height); 
        }else{ 
            $this->dest_img->resource    = imagecreatetruecolor($width, $height); 
        } 
        
        return $this;
    }
    
    
    /**
     * 원본의 가로 세로 비율을 유지한체로 이미지  copy center
     */
    public function copyWithRatio(){
        $dst_width = 0;
        $dst_height = 0;
        
        if($this->src_img->width > $this->dest_img->width || $this->src_img->height > $this->dest_img->height){
            //$largetThanThum = true; 
            
            
            if($this->src_img->width == $this->src_img->height) 
            { 
                $dst_width  = $this->dest_img->width; 
                $dst_height = $this->dest_img->height; 
            }elseif($this->src_img->width > $this->src_img->height){ 
                $dst_width  = $this->dest_img->width; 
                $dst_height = ceil(($this->dest_img->width / $this->src_img->width) * $this->src_img->height); 
            }else{ 
                $dst_height = $this->dest_img->height; 
                $dst_width  = ceil(($this->dest_img->height / $this->src_img->height) * $this->src_img->width); 
            } 
        }else{ 
            $dst_width  = $this->src_img->width; 
            $dst_height = $this->src_img->height; 
        } 
        
        
        
        $dst_x = $dst_width < $this->dest_img->width ? ceil(($this->dest_img->width - $dst_width)/2) : 0; 
        $dst_y = $dst_height < $this->dest_img->height ? ceil(($this->dest_img->height - $dst_height)/2) : 0;
        
        
        $bgc = imagecolorallocate($this->dest_img->resource, 255, 255, 255); 
        
        ##
        imagefilledrectangle($this->dest_img->resource, 0, 0, $this->dest_img->width, $this->dest_img->height, $bgc); 
        
        ## bool imagecopyresampled ( resource $dst_image , resource $src_image , int $dst_x , int $dst_y , int $src_x , int $src_y , int $dst_w , int $dst_h , int $src_w , int $src_h )
        //imagecopyresampled($this->dest_img->resource, $this->src_img->resource, $dst_x, $dst_y, 0, 0, $dst_width, $dst_height, imagesx($src["image"]),imagesy($src["image"]));  
        //원본의 비율을 유지하는 상태이므로 상기에서 계산된 $dst_width, $dst_height 을 사용한다.
        
        imagecopyresampled($this->dest_img->resource, $this->src_img->resource, $dst_x, $dst_y, 0, 0, $dst_width, $dst_height, imagesx($this->src_img->resource),imagesy($this->src_img->resource));  
        
        
         return $this;
        
    }

    
    public function form($form){
        echo "save start..";
    }
    
    public function save($path){
       //first create 
       File::mkfolders($path);
       
       if($this->dest_img != null){
            $resource = $this->dest_img->resource;
            $format = $this->dest_img->format;
            $name   = $this->dest_img->name;
       }else{
            $resource = $this->src_img->resource;
            $format = $this->src_img->format;
            $name   = $this->src_img->name;
       }
    
       
       $file = $path."/".$name;
       
        switch($format){ 
            case (1): 
                imageinterlace($resource); 
                imagegif($resource, $file); 
            break; 
            case (2): 
                imageinterlace($resource); 
                imagejpeg($resource, $file,85);
            break; 
            case (3): 
                imagepng($resource, $file); 
            break; 
            case (6): 
                imagebmp($resource, $file); 
            break; 
            case (15): 
                imagewbmp($resource, $file); 
            break; 
        } 
        
        if($this->dest_img != null) ImageDestroy($this->dest_img->resource); 
        ImageDestroy($this->src_img->resource); 
        
    }
    
    
} 