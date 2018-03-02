<?php 
namespace Pondol\Image;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use Response;
use Pondol\Image\Image;
use Pondol\Files\File;

class ImageService{ 
    public $imagepad    = array("x"=>20, "y"=>20);
    public $font        = array("x"=>0, "y"=>0, "size"=>15, "color"=>array("0"=>array("0","0","0"), "1"=>array("255","255","255")), "path"=>"./samples/arial.ttf");
    

    /** 
     * Create Text WaterMarking  
     * @param $params Array
     * $params = array("src"=>source/image/path/filename, "text"=>"what you want write on image", "savepath"=>"./path/saved filename", "imagedst"=>array("x"=>0, "y=>0"))
     * imagedst : text start position
     */
    public function impressWaterMark($params) {

        $src    = $this->create_image($params["src"]);

        $src_x = imagesx($src["image"]); 
        $src_y = imagesy($src["image"]); 

        $dst_x = $params["imagedst"]["x"] ? $params["imagedst"]["x"] : $src_x;  
        $dst_y = $params["imagedst"]["y"] ? $params["imagedst"]["y"] : $src_y;  
                 
        // if a string is null, quit process after making images 
        if($params["text"] === null) {  
            switch($src["format"]){ 
                case 1: imagegif($src["image"]); break; # gif 
                case 2: imagejpeg($src["image"]); break;  # jpg 
                case 3: imagepng($src["image"]); break; # png 
            }   
            imagedestroy($dst_im);  
            return; 
        }  
            
        //Create Out put images 
        $dst_im = @imagecreatetruecolor($dst_x, $dst_y);  
        if (empty($dst_im)) return false;  
        $background_color = @imagecolorallocate($dst_im, 255, 255, 255);  
        if (empty($background_color)) return false;  
         
        imagefilledrectangle($dst_im, 0, 0, $dst_x, $dst_y, $background_color); 

        //Create Font Box 
        $_font_size = imagettfbbox($this->font["size"],50,$this->font["path"],$params["text"]); 
         
        //imagettfbbox ( float $size , float $angle , string $fontfile , string $text ) 
        $dx = abs($_font_size[2]-$_font_size[0]);   
        $dy = abs($_font_size[5]-$_font_size[3]);   
         
         
        //Make Font Output position 
        $font_x = $this->font["x"] ? $this->font["x"] : (int)($this->imagepad["x"] / 2);    
        $font_y = $this->font["y"] ? $this->font["y"] : $dy+(int)($this->imagepad["y"] / 2);          
             
        //if font color is various 
        foreach($this->font["color"] as $key=>$value){ 
            $color_R = $value[0]; 
            $color_G = $value[1]; 
            $color_B = $value[2]; 
            $Fontshift = $key*(-1); 
            $fontColor = imagecolorallocate($dst_im,$color_R,$color_G,$color_B);  
            imagettftext($src["image"],$this->font["size"],0,$font_x+$Fontshift,$font_y+$Fontshift,$fontColor,$this->font["path"],$params["text"]); 
        } 

        imagecopyresampled($dst_im, $src["image"],0, 0, 0, 0, $dst_x, $dst_y, $src_x, $src_y); 


        switch($src["format"]){ 
            case 1: #gif  
                if($params["savepath"]) @imagegif($dst_im, $params["savepath"]);  
                else @imagegif($dst_im);  
            break;  
            case 2: #jpg 
                if($params["savepath"]) @imagejpeg($dst_im, $params["savepath"]); 
                else @imagejpeg($dst_im);  
            break;  
            case 3: #png 
                if($params["savepath"]) @imagepng($dst_im, $params["savepath"]); 
                else @imagepng($dst_im);  
            break;  
        }   
        
        chmod($params["savepath"], 0606);  
        imagedestroy($dst_im);  
        imagedestroy($src["image"]);    
    }  


    /** 
     * Create Image WaterMarking 
     * @param $params Array
     * $params = array ("src"=>Source Image,"logo"=>Logo Image(Watermarker ThumbImage) (transparent GIF or Png recommanded), "savepath"=>"./path/saved filename", "quality"=>"Image Quality") 
     * quality : level 0 to 9
     */ 
    public function imageWaterMaking($params){ ## 
        ##  
        
    //  print_r($params);
        //exit;
        $src    = $this->create_image($params["src"]);
        $logo   = $this->create_image($params["logo"]);

        # get expected position of logo
        $position_x = ($src["width"] - $logo["width"]) / 2;  
        $position_y = ($src["height"] - $logo["height"]) / 3 * 2.5;  

        #
        imagecopyresized($src["image"], $logo["image"], $position_x, $position_y, 0, 0, imagesx($logo["image"]), imagesy($logo["image"]), imagesx($logo["image"]), imagesy($logo["image"]));  

        switch($src["format"]){  
            case 1 : $result    = @imagegif($src["image"], $params["savepath"], $params["quality"]);break;  
            case 2 : $result    = @imagejpeg($src["image"], $params["savepath"], $params["quality"]);break;  
            case 3 : $result    = @imagepng($src["image"], $params["savepath"], $params["quality"]);break;  
            default : $result   = false;  
        }  
        
        $rtn["result"]  = $result;
        return $rtn;
    }  



    /** 
     * @param array $params Infomation of source image
     * $param = array("sourcefile"=>"/path/filename", "savepath"=>"./path/filename", "max_width"=>"max value of thumbnail'width", "max_height"=>"max value of thumbnail'height", resize=>false);
     * getthumbimg method check if thumb image is, if it exists return images else create thumbimage 
     * 
     */ 
    public function thumbnail($params){
        
        $params["max_width"]    = isset($params["max_width"]) ? $params["max_width"]: 100;
        $params["max_height"]   = isset($params["max_height"]) ? $params["max_height"]: 100;
        //$params["resize"]     = isset($params["resize"])?$params["resize"]:false;
        
        $src            = $this->create_image($params["sourcefile"]);
        

        ## max_width or max_height zero means as big as original image's width or hegith size
        if($params["max_width"] == 0  && $src["height"] > $params["max_height"] )
            $params["max_width"] = ceil(($params["max_height"] * $src["width"]) / $src["height"]); 
        else if($params["max_height"] == 0  && $src["width"] > $params["max_width"])
            $params["max_height"] = ceil(($params["max_width"] * $src["height"]) / $src["width"]); 
        
        //After examin whether image size is bigger than max, 
        //$largetThanThum = false;
        if($src["width"] > $params["max_width"] || $src["height"] > $params["max_height"]){
            //$largetThanThum = true; 
            if($src["width"] == $src["height"]) 
            { 
                $dst_width  = $params["max_width"]; 
                $dst_height = $params["max_height"]; 
            }elseif($src["width"] > $src["height"]){ 
                $dst_width  = $params["max_width"]; 
                $dst_height = ceil(($params["max_width"] / $src["width"]) * $src["height"]); 
            }else{ 
                $dst_height = $params["max_height"]; 
                $dst_width  = ceil(($params["max_height"] / $src["height"]) * $src["width"]); 
            } 
        }else{ 
            $dst_width  = $src["width"]; 
            $dst_height = $src["height"]; 
        } 
        
        
    
        

        ## 
        $srcx = $dst_width < $params["max_width"] ? ceil(($params["max_width"] - $dst_width)/2) : 0; 
        $srcy = $dst_height < $params["max_height"] ? ceil(($params["max_height"] - $dst_height)/2) : 0; 
        
        if($src["format"] == 1)  
        { 
            $dst_img    = imagecreate($params["max_width"], $params["max_height"]); 
        }else{ 
            $dst_img    = imagecreatetruecolor($params["max_width"], $params["max_height"]); 
        } 
        
        $bgc = imagecolorallocate($dst_img, 255, 255, 255); 
        
        ##
        imagefilledrectangle($dst_img, 0, 0, $params["max_width"], $params["max_height"], $bgc); 
        
        ## 
        imagecopyresampled($dst_img, $src["image"], $srcx, $srcy, 0, 0, $dst_width, $dst_height, imagesx($src["image"]),imagesy($src["image"]));      
        
        switch($src["format"]){ 
            case (1): 
                imageinterlace($dst_img); 
                imagegif($dst_img, $params["savepath"]); 
            break; 
            case (2): 
                imageinterlace($dst_img); 
                imagejpeg($dst_img, $params["savepath"],85);
            break; 
            case (3): 
                imagepng($dst_img, $params["savepath"]); 
            break; 
            case (6): 
                imagebmp($dst_img, $params["savepath"]); 
            break; 
            case (15): 
                imagewbmp($dst_img, $params["savepath"]); 
            break; 
        } 
        ImageDestroy($dst_img); 
        ImageDestroy($src["image"]); 
        
        //print_r($params);
        //exit;
        //return $save_filename; 
    }
     
    /**
     *  Crop Images From Center
     * @param $params
     * $params = array("width"=>"crop width", "height"=>"coop height", "sourcefile"=>"source filepath/filenmae", "desc"=>"desc filepath/filenmae")
     * $params = array("width"=>"crop width", "height"=>"coop height", "sourcefile"=>"source filepath/filenmae", "desc"=>"desc filepath/filenmae", "resize"=>true, "resize_w"=>"", "resize_h"=>"")
     */
    public function cropcenter($params){
        
        $src    = $this->create_image($params["sourcefile"]);
        $params["resize"]   = isset($params["resize"]) ? $params["resize"]:false;
        
        //if params["resize"] is true,  crop image after resizing source image
        if($params["resize"]==true){
            $dst_width  = $resize_w;
            $dst_height = $resize_h;
        }else{
            if($src["width"] > $params["width"]) { 
                $sc_x = ceil(($src["width"]-$params["width"])/2); 
                $dst_width = $params["width"]; 
            }else{ 
                $sc_x = 0; 
                $dst_width = $src["width"]; 
            }   

            if($src["height"] > $params["height"]) { 
                $sc_y       = ceil(($src["height"]-$params["height"])/2); 
                $dst_height = $params["height"]; 
            }else{ 
                $sc_y = 0; 
                $dst_height = $src["height"]; 
            }  
        }
 
        if($src["format"] == 1)  
        { 
            $dst_img = imagecreate($dst_width, $dst_height); 
        }else{ 
            $dst_img = imagecreatetruecolor($dst_width, $dst_height); 
        } 
     
        $bgc = imagecolorallocate($dst_img, 255, 255, 255); 
        imagefilledrectangle($dst_img, 0, 0, $dst_width, $dst_height, $bgc);  
        
        //bool imagecopyresampled ( resource $dst_image , resource $src_image , int $dst_x , int $dst_y , int $src_x , int $src_y , int $dst_w , int $dst_h , int $src_w , int $src_h )
        imagecopyresampled($dst_img, $src["image"], 0, 0, $sc_x, $sc_y, $dst_width, $dst_height, $dst_width, $dst_height);   
        imagepng($dst_img, $params["desc"]); 

        ImageDestroy($dst_img); 
        ImageDestroy($src["image"]); 
        return true; 
    }  

    /**
     * Crop Images From Center After resizing
     * @param $params
     * $params = array("width"=>"crop width", "height"=>"coop height", "sourcefile"=>"source filepath/filenmae", "desc"=>"desc filepath/filenmae"
     */
    public function resizeCropCenter($params){
        
        $src    = $this->create_image($params["sourcefile"]);
        $ratio_w    = $params["width"]/ $src["width"];
        $ratio_h    = $params["height"]/ $src["height"];

        if($ratio_h > $ratio_w) {
            $src_w = ($src["height"]*$params["width"])/$params["height"]; 
            $src_h = $src["height"]; 
            $src_y = 0;  
            $src_x = ceil(($src["width"]-$src_h)/2); 
        }else{
            //echo "2".PHP_EOL;
            
            
            $src_w      = $src["width"]; 
            $src_h      = ($src["width"]*$params["height"])/$params["width"]; 
            $src_x = 0;  
            $src_y      = ceil(($src["height"]-$src_h)/2); 
        }   

        //새로운 빈 이미지를 먼저 생성 시작
        if($src["format"] == 1)  
        { 
            $dst_img = imagecreate($params["width"], $params["height"]); 
        }else{ 
            $dst_img = imagecreatetruecolor($params["width"], $params["height"]); 
        } 
    
        $bgc = imagecolorallocate($dst_img, 255, 255, 255); 
        imagefilledrectangle($dst_img, 0, 0, $params["width"], $params["height"], $bgc);  
        //새로운 빈 이미지를 먼저 생성 끝
            
        imagecopyresampled ( $dst_img, $src["image"], 0, 0, $src_x, $src_y, $params["width"], $params["height"] , $src_w , $src_h); 
        imagepng($dst_img, $params["desc"]); 

        ImageDestroy($dst_img); 
        ImageDestroy($src["image"]); 
        return true; 
    }  

    /** 
     * Conver Format 
     *  @param $params Array
     * $params = array("type" => "format chaning to(gif, jpeg)", "src"=>"path/from/sourcefile", "desc"=>"path/to/desc file");
     */ 
    public function convertformat($params){      

        $src = $this->create_image($params["src"]);
 
        if($src["format"] == 1)  
        { 
              $dst_img = imagecreate($src["width"], $src["height"]); 
        }else{ 
              $dst_img = imagecreatetruecolor($src["width"], $src["height"]); 
        } 
 
 

        $bgc = imagecolorallocate($dst_img, 255, 255, 255); 
        imagefilledrectangle($dst_img, 0, 0, $src["width"], $src["width"], $bgc);  
        
        imagecopyresampled($dst_img, $src["image"], 0, 0, 0, 0, $src["width"], $src["height"], $src["width"], $src["height"]);   


        switch($params["type"]){ 
            case ("gif"): 
              imageinterlace($dst_img); 
              imagegif($dst_img, $params["desc"]); 
            break; 
            case ("jpeg"): 
              imageinterlace($dst_img); 
              imagejpeg($dst_img, $params["desc"]); //ImageJPEG($dst_img, $params["savepath"].$save_filename); 
            break; 
            case ("png"): 
                imagepng($dst_img, $params["desc"]); 
            break; 
            case ("bmp"): 
                imagebmp($dst_img, $params["desc"]); 
            break; 
            case ("wbmp"): 
                imagewbmp($dst_img, $params["desc"]); 
            break; 
        } 
        
        ImageDestroy($dst_img); 
        ImageDestroy($src["image"]); 
        return true; 
    }


    /** 
     * @param Array $params Infomation of source image
     * $param = array(path=>"/path/", filename=>"filename", width=>"thnumnail width", height=>"thumbnail height", default=>"/path/default_filename");
     * getthumbimg method check if thumb image is, if it exists return images else create thumbimage 
     * 
     */ 
    public function getthumbimg($params){    
        $source = isset($params["source"]) ? $params["source"] : $params["path"].$params["filename"];
        
        if(is_file($source)){ 
            $thumbnailpath      = $params["path"]."thumb/".$params["width"]."_".$params["height"]; 
            $thumfullpath   = $params["path"]."thumb/".$params["width"]."_".$params["height"]."/".$params["filename"]; 
            if(!is_file($thumfullpath)){
                $thumfilename   = $params["filename"].".thumb"; 
            //Create File Save Path : Modify this part depend on your program 
                File::mkfolder($params["path"]."thumb"); 
                File::mkfolder($params["path"]."thumb/".$params["width"]."_".$params["height"]); 
                
                //전체 이미지를 유지한채 축소
                //$args = array("sourcefile"=>$source, "savepath"=>$thumfullpath, "max_width"=>$params["width"], "max_height"=>$params["height"]);
                //$this->thumbnail($args); 
                
                //전체이미지를 crop 사이즈에 맞게 축소후 crop
                $args = array("sourcefile"=>$source, "desc"=>$thumfullpath, "width"=>$params["width"], "height"=>$params["height"]);
                $this->resizeCropCenter($args); 

        
                
                
            }   
            return $thumfullpath; 
        }else{ 
            return $params["default"];//default images return; 
        } 
    } 

    
    /**
     * get image type and create image to each each image type
     * @param String Source Image path and name
     *//*
    private function set_image($src, $img_info){
        
        $image = new Image();
        $image->init();
        $image->width   = $img_info[0]; 
        $image->height  = $img_info[1]; 
        $image->format  = $img_info[2]; 
        $image->bits  = $img_info['bits']; 
        $image->channels  = $img_info['channels']; 
        $image->mime  = $img_info['mime']; 
        $image->setName($src)  = basename();


        switch($img_info[2]){ 
            case (1):$image->resource  = ImageCreateFromGif($src);break; 
            case (2):$image->resource  = ImageCreateFromJPEG($src);break; 
            case (3):$image->resource  = ImageCreateFromPNG($src);break; 
            case (6):$image->resource  = imagecreatefrombmp($src);break; 
            case (15):$image->resource = imagecreatefromwbmp($src);break; 
            default:$image->resource   = false;break; 
        } 
        
        return $image;
    }
    */
    
        /**
     * get image type and create image to each each image type
     * @param String Source Image path and name
     */
    public function create_image($src){
        return new Image($src);
    }
     /**
     * get image type and create image to each each image type
     * @param String Source Image path and name
     */
    //public function create_image_from_steam($data){
    //    return $this->set_image($src, getimagesizefromstring($src)); 
   // }
} 


/** 
 * Creates function imagecreatefrombmp, since PHP doesn't have one 
 * @return resource An image identifier, similar to imagecreatefrompng 
 * @param string $filename Path to the BMP image 
 * @see imagecreatefrompng 
 * @author Glen Solsberry <glens@networldalliance.com> 
 */ 
if (!function_exists("imagecreatefrombmp")) { 
    function imagecreatefrombmp( $p_sFile ) { 
     $file  =   fopen($p_sFile,"rb"); 
    $read   =   fread($file,10); 
    while(!feof($file)&&($read<>"")) 
        $read   .=  fread($file,1024); 
    $temp   =   unpack("H*",$read); 
    $hex    =   $temp[1]; 
    $header =   substr($hex,0,108); 
    if (substr($header,0,4)=="424d") 
    { 
        $header_parts   =   str_split($header,2); 
        $width          =   hexdec($header_parts[19].$header_parts[18]); 
        $height         =   hexdec($header_parts[23].$header_parts[22]); 
        unset($header_parts); 
    } 
    $x              =   0; 
    $y              =   1; 
    $image          =   imagecreatetruecolor($width,$height); 
    $body           =   substr($hex,108); 
    $body_size      =   (strlen($body)/2); 
    $header_size    =   ($width*$height); 
    $usePadding     =   ($body_size>($header_size*3)+4); 
    for ($i=0;$i<$body_size;$i+=3) 
    { 
        if ($x>=$width) 
        { 
            if ($usePadding) 
                $i  +=  $width%4; 
            $x  =   0; 
            $y++; 
            if ($y>$height) 
                break; 
        } 
        $i_pos  =   $i*2; 
        $r      =   hexdec($body[$i_pos+4].$body[$i_pos+5]); 
        $g      =   hexdec($body[$i_pos+2].$body[$i_pos+3]); 
        $b      =   hexdec($body[$i_pos].$body[$i_pos+1]); 
        $color  =   imagecolorallocate($image,$r,$g,$b); 
        imagesetpixel($image,$x,$height-$y,$color); 
        $x++; 
    } 
    unset($body); 
    return $image; 
    } 
} 

if (!function_exists("imagebmp")) { 
    function imagebmp ($im, $fn = false) 
    { 
        if (!$im) return false; 
                 
        if ($fn === false) $fn = 'php://output'; 
        $f = fopen ($fn, "w"); 
        if (!$f) return false; 
                 
        //Image dimensions 
        $biWidth = imagesx ($im); 
        $biHeight = imagesy ($im); 
        $biBPLine = $biWidth * 3; 
        $biStride = ($biBPLine + 3) & ~3; 
        $biSizeImage = $biStride * $biHeight; 
        $bfOffBits = 54; 
        $bfSize = $bfOffBits + $biSizeImage; 
                 
        //BITMAPFILEHEADER 
        fwrite ($f, 'BM', 2); 
        fwrite ($f, pack ('VvvV', $bfSize, 0, 0, $bfOffBits)); 
                 
        //BITMAPINFO (BITMAPINFOHEADER) 
        fwrite ($f, pack ('VVVvvVVVVVV', 40, $biWidth, $biHeight, 1, 24, 0, $biSizeImage, 0, 0, 0, 0)); 
                 
        $numpad = $biStride - $biBPLine; 
        for ($y = $biHeight - 1; $y >= 0; --$y) 
        { 
            for ($x = 0; $x < $biWidth; ++$x) 
            { 
                $col = imagecolorat ($im, $x, $y); 
                fwrite ($f, pack ('V', $col), 3); 
            } 
            for ($i = 0; $i < $numpad; ++$i) 
                fwrite ($f, pack ('C', 0)); 
        } 
        fclose ($f); 
        return true; 
    } 
}