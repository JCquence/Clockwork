<?php if(!defined('ACCESS')) exit('Access denied');
    /**
     * Image
     * @author Jelle van der Coelen
     * @package Clockwork/Module
     */
    class Image
    {
        /**
         * Extension
         * @var string
         */
        private $extension;
        
        /**
         * Height
         * @var int
         */
        private $height;
        
        /**
         * Image
         * @var string 
         */
        private $image;
        
        /**
         * Image type
         * @var int
         */
        private $type;
        
        /**
         * Width
         * @var int
         */
        private $width;
        
        /**
         * Constructor
         *
         * @param string $image (optional, default = null)
         *
         * @return void
         */
        function __construct($image = null)
        {
            if($image)
                $this->setImage($image);
        }

        /**
         * Calculate ratio and new dimensions with bleeding
         *
         * @param int $w Width
         * @param int $h Hheight
         *
         * @return array
         */
        private function calculateBleedingDimensions($w, $h)
        {
            $oldRatio = ($this->getWidth() / $this->getHeight());
            $newRatio = ($w / $h);
            
            $nh = 0;
            $nw = 0;
            
            if($oldRatio > $newRatio)
            {
                $nh = $h;
                $nw = intval(($this->getWidth() * $h) / $this->getHeight());
            }
            else
            {
                $nw = $w;
                $nh = intval(($w * $this->getHeight()) / $this->getWidth());
            }
            
            return array($nw, $nh);
        }
        
        /**
         * Calculate ratio and new dimensions
         *
         * @param int $w Width
         * @param int $h Height
         *
         * @return array
         */
        private function calculateDimensions($w, $h)
        {
            $ratio = ($this->getWidth() / $this->getHeight());
            
            if(($w / $h) > $ratio)
                $w = ceil($h * $ratio);
            else
                $h = ceil($w / $ratio);

            return array($w, $h);
        }
        
        /**
         * Cut image
         *
         * @param string $dest    Destination to save file
         * @param int    $w       Width
         * @param int    $h       Height
         * @param int    $x       (optional, default = 0) Offset X
         * @param int    $y       (optional, default = 0) Offset Y
         * @param int    $quality (optional, default = 100) Only applies to JPEG
         *
         * @return void
         */
        public function cut($dest, $w, $h, $x = 0, $y = 0, $quality = 100)
        {
            if($w > ($this->getWidth() - $x) || $h > ($this->getHeight() - $y))
                $this->throwError('not enough pixels');
            
            $new     = imagecreatetruecolor($w, $h);
            $orignal = $this->imagecreatefrom($this->image);

            $this->filters($new, $w, $h);
            
            imagecopyresampled($new, $orignal, 0, 0, $x, $y, $w, $h, $w, $h);
            
            $this->imageoutput($new, $dest, $quality);
        }
        
        /**
         * Apply filters before saving image
         * 
         * @param resource $new    Image to apply filters to
         * @param int      $w      Width
         * @param int      $h      Height
         * 
         * @return void
         */
        private function filters(&$new, $w, $h)
        {
            //alpha for png
            if($this->type == 3)
            {
                imagealphablending($new, false);
                imagesavealpha($new, true);
                
                $transparent = imagecolorallocatealpha($new, 255, 255, 255, 127);
                imagefilledrectangle($new, 0, 0, $w, $h, $transparent);
            }
        }

        /**
         * Get height
         *
         * @return int
         */
        public function getHeight()
        {
            if(!$this->height)
                list($w, $this->height) = getimagesize($this->image);
            
            return $this->height;
        }
        
        /**
         * Get width
         *
         * @return int
         */
        public function getWidth()
        {
            if(!$this->width)
                list($this->width) = getimagesize($this->image);
            
            return $this->width;
        }

        /**
         * Create image according to extension
         * 
         * @return resource
         */
        private function imagecreatefrom()
        {
            switch($this->type)
            {
                case 1: return imagecreatefromgif($this->image);  break;
                case 2: return imagecreatefromjpeg($this->image); break;
                case 3: return imagecreatefrompng($this->image);  break;
            }
        }
        
        /**
         * Output image according to extension
         * 
         * @param resource $new     Image to output
         * @param string   $dest    Destination to save file
         * @param int      $quality (optional, default = 100) Only applies to JPEG
         *
         * @return resource
         */
        private function imageoutput($new, $dest, $quality = 100)
        {
            switch($this->type)
            {
                case 1: return imagegif($new, $dest);  break;
                case 2: return imagejpeg($new, $dest, $quality); break;
                case 3: return imagepng($new, $dest, 0);  break;
            }
        }
        
        /**
         * Rescale image
         *
         * @param string $dest    Destination to save file
         * @param int    $w       Width
         * @param int    $h       Height
         * @param int    $quality (optional, default = 100) Only applies to JPEG
         * 
         * @return void
         */
        public function rescale($dest, $w, $h, $quality = 100)
        {
            list($w, $h) = $this->calculateDimensions($w, $h);
            
            $new     = imagecreatetruecolor($w, $h);
            $orignal = $this->imagecreatefrom($this->image);
            
            $this->filters($new, $w, $h);
            
            imagecopyresampled($new, $orignal, 0, 0, 0, 0, $w, $h, $this->getWidth(), $this->getHeight());
            
            $this->imageoutput($new, $dest, $quality);
        }
        
        /**
         * Rescale and cut image
         * 
         * @param string $dest    Destination to save file
         * @param int    $w       Width
         * @param int    $h       Height
         * @param int    $x       (optional, default = 0) Offset X
         * @param int    $y       (optional, default = 0) Offset Y
         * @param int    $quality (optional, default = 100) Only applies to JPEG
         * 
         * @return void
         */
        public function rescaleAndCut($dest, $w, $h, $x = 0, $y = 0, $quality = 100)
        {
            list($nw, $nh) = $this->calculateBleedingDimensions($w, $h);
            
            $this->rescale($dest, $nw, $nh, $quality);
            $this->setImage($dest);
            $this->cut($dest, $w, $h, $x, $y, $quality);
        }
        
        /**
         * Set image
         *
         * @param string $image
         *
         * @return void
         */
        public function setImage($image)
        {
            if(file_exists($image))
            {
                $this->extension = pathinfo($image, PATHINFO_EXTENSION);
                
                if(preg_match('/jpg|jpeg|gif|png/i', $this->extension))
                {
                    $info = getimagesize($image);
                    
                    $this->type  = $info[2];
                    $this->image = $image;
                }
                else
                    $this->throwError('unsupported extension');
            }
            else
                $this->throwError('image does not exist');
        }
        
        /**
         * Throw custom fatal error
         *
         * @param string $error
         * @param int    $lvl
         *
         * @return void;
         */
        private function throwError($error, $lvl = E_USER_ERROR)
        {
            $caller = next(debug_backtrace());
            
            die("<strong>Fatal image error:</strong> ".$error." in ".$caller['file']." on line ".$caller['line']."<br />\r\n<br />\r\n");
        }
    }
?>
