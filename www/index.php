<?php
    /* DEFINITIONS */
    // --- environment:
    //     Determine what config file should be loaded
    //cron: $host = gethostname();
    define('ENVIRONMENT', 'development');

    // --- app path: 
    //     Name of the application folder, this is where you application files should be located.
    define('APP_FOLDER', 'app');

    // --- asset path: 
    //     Name of the asset folder, typically the folder to include your stylesheets, javascripts and images.
    define('ASSET_FOLDER', 'www/asset');

    // --- system path: 
    //     Name of the system folder, which includes the core system.
    define('SYS_FOLDER', 'system');


    /* LOAD  */
    // --- include
    include_once dirname(__FILE__).'/../'.SYS_FOLDER.'/load.php';
?>