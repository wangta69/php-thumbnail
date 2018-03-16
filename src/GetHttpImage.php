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
    
    private $FORMAT = ['gif'=>1, 'jpg'=>2, 'jpeg'=>2, 'png'=>3, 'bmp'=>6, 'wbmp'=>15];
    
    # bool imagefilledrectangle ( resource $image , int $x1 , int $y1 , int $x2 , int $y2 , int $color )
    # $image : imagecreatetruecolor() 등의 이미지 생성 함수에서 반환한 이미지 자원., 
    private $filled = ['x1'=>0, 'y1'=>0, 'x2'=>0, 'y2'=>0];
    
    # bool imagecopyresampled ( resource $dst_image , resource $src_image , int $dst_x , int $dst_y , int $src_x , int $src_y , int $dst_w , int $dst_h , int $src_w , int $src_h )
    # dst_image: 목표이미지, src_image: 원본이미지, dst_x: 목표이미지의 x 시작점, 목표이미지의 y 시작점, src_x: 소스이미지의 x시작점, src_y 소스이미지의 y 시작점, dst_w
    private $resampled = ['dst_x'=>0, 'dst_y'=>0, 'src_x'=>0, 'src_y'=>0, 'dst_w'=>0, 'dst_h'=>0, 'src_w'=>0, 'src_h'=>0];

        
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
    public function set_size($width = 100, $height = 100){
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
      
          /**
     * 이미지를 최적화 시킨후 중심부로부터 crop 한다.
     * @param $params
     * $params = array("width"=>"crop width", "height"=>"coop height", "sourcefile"=>"source filepath/filenmae", "desc"=>"desc filepath/filenmae"
     */
    public function copyimage(){
        
        $ratio_w    = $this->dest_img->width / $this->src_img->width;
        $ratio_h    = $this->dest_img->height / $this->src_img->height;

        if($ratio_h > $ratio_w) {
            $src_w = ($this->src_img->height*$this->dest_img->width)/$this->dest_img->height; 
            $src_h = $this->src_img->height; 
            $src_y = 0;  
            $src_x = ceil(($this->src_img->width - $src_h)/2); 
        }else{
            $src_w      = $this->src_img->width; 
            $src_h      = ($this->src_img->width*$this->dest_img->height)/$this->dest_img->width; 
            $src_x = 0;  
            $src_y      = ceil(($this->src_img->height-$src_h)/2); 
        }   
        
        $this->filled['x1'] = 0;
        $this->filled['y1'] = 0;
        $this->filled['x2'] = $this->dest_img->width;
        $this->filled['y2'] = $this->dest_img->height;
        
        $this->resampled['dst_x']= 0; 
        $this->resampled['dst_y']= 0;
        $this->resampled['src_x']= $src_x;
        $this->resampled['src_y']= $src_y;
        $this->resampled['dst_w']= $this->dest_img->width;
        $this->resampled['dst_h']= $this->dest_img->height;
        $this->resampled['src_w']= $src_w;
        $this->resampled['src_h']= $src_h;

         return $this->create_image();
    }  

    
    /**
     * 원본의 가로 세로 비율을 유지한체로 이미지  copy center
     */
    public function copyWithRatio(){
        $dst_w = 0;
        $dst_h = 0;
        
        if($this->src_img->width > $this->dest_img->width || $this->src_img->height > $this->dest_img->height){

            if($this->src_img->width == $this->src_img->height) 
            { 
                $dst_w  = $this->dest_img->width; 
                $dst_h = $this->dest_img->height; 
            }elseif($this->src_img->width > $this->src_img->height){ 
                $dst_w  = $this->dest_img->width; 
                $dst_h = ceil(($this->dest_img->width / $this->src_img->width) * $this->src_img->height); 
            }else{ 
                $dst_h = $this->dest_img->height; 
                $dst_w  = ceil(($this->dest_img->height / $this->src_img->height) * $this->src_img->width); 
            } 
        }else{ 
            $dst_w  = $this->src_img->width; 
            $dst_h = $this->src_img->height; 
        } 
        
        

        $this->filled['x1'] = 0;
        $this->filled['y1'] = 0;
        $this->filled['x2'] = $this->dest_img->width;
        $this->filled['y2'] = $this->dest_img->height;
        
        $this->resampled['dst_x']= $dst_w < $this->dest_img->width ? ceil(($this->dest_img->width - $dst_w)/2) : 0; 
        $this->resampled['dst_y']= $dst_h < $this->dest_img->height ? ceil(($this->dest_img->height - $dst_h)/2) : 0;
        $this->resampled['src_x']= 0;
        $this->resampled['src_y']= 0;
        $this->resampled['dst_w']= $dst_w;
        $this->resampled['dst_h']= $dst_h;
        $this->resampled['src_w']= imagesx($this->src_img->resource);
        $this->resampled['src_h']= imagesy($this->src_img->resource);

         return $this->create_image();
        
    }

    private function create_image(){

        $bgc = imagecolorallocate($this->dest_img->resource, 255, 255, 255); 
        
        ##
        imagefilledrectangle($this->dest_img->resource, $this->filled['x1'], $this->filled['y1'], $this->filled['x2'], $this->filled['y2'], $bgc); 
        ##
        imagecopyresampled($this->dest_img->resource, $this->src_img->resource, $this->resampled['dst_x'], $this->resampled['dst_y'], $this->resampled['src_x'], $this->resampled['src_y'], $this->resampled['dst_w'], $this->resampled['dst_h'], $this->resampled['src_w'],$this->resampled['src_h']);  
        
         return $this;
    }


    public function format($format){
        $this->dest_img->format = $this->FORMAT[strtolower($format)];
        $this->dest_img->extension = strtolower($format);
        return $this;
    }
    
    public function name($name){
        $this->dest_img->name = strtolower($name);
        return $this;
    }
    
    public function save($path){
       //first create 
       File::mkfolders($path);
       
       if($this->dest_img == null)
            $this->dest_img = $this->src_img;
       
        $resource = $this->dest_img->resource;
        $format = $this->dest_img->format;
        $filename   = $this->dest_img->get_filename();
        $file = $path."/".$filename;
       
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

    public function get_saved_path(){
        
    }
    
    
} 