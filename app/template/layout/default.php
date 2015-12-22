<?php if($part == 'header'){ ?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        
        <link href="<?php echo assetpath('css/default.css'); ?>" rel="stylesheet" type="text/css" />
        
        <script>var ROOT_PATH = '<?php echo path(); ?>';</script>
        <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
        <script src="<?php echo assetpath('js/functions.js'); ?>"></script>

        <title>Clockwork Framework</title>
    </head>
    <body>
    
        <div id="wrapper">
<?php } else if($part == 'footer'){ ?>
        </div>
    
    </body>
</html>
<?php } ?>