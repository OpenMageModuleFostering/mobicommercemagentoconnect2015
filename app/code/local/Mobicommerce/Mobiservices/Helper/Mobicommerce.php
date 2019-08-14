<?php
class Mobicommerce_Mobiservices_Helper_Mobicommerce extends Mage_Core_Helper_Abstract {

    /* function to remove entire directory with all files in it */
    public function rrmdir($dir, $include_basedir = true)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir."/".$object) == "dir") $this->rrmdir($dir."/".$object); else unlink($dir."/".$object);
                } 
            }
            reset($objects); 
            if($include_basedir)
              rmdir($dir); 
        } 
    }
}