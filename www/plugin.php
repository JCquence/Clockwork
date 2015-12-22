<?php
    //
    define('CW_CRON', true);
    include_once 'index.php';
    
    extract($_GET);
    
    //
    if(preg_match('/(css|js|image)/', $type))
    {
        if(Clockwork::getInstance()->isPluginLoaded($plugin))
        {
            if($type != 'image' || preg_match('/jpg|jpeg|gif|png/i', $ext))
            {
                $file = Plugin::getInstance($plugin)->dir().'asset/'.$type.'/'.$file.'.'.$ext;

                if(file_exists($file))
                {
                    if($type == 'image')
                        header('Content-Type: image/'.str_replace('jpg', 'jpeg', $ext));
                    else if($type == 'css')
                        header('Content-Type: text/css');
                    else  if($type == 'js')
                        header('Content-Type: application/javascript');
                    
                    readfile($file);
                    exit;
                }
            }
        }
    }
    
    // --- 404
    new Template(['view' => '404']);
?>