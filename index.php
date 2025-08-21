<?php
namespace Wonder_Link\Yomi_Search_PHP;
/*-------------------------------------------------------------------------
 Yomi-Search PHP
 Main Index Page
--------------------------------------------------------------------------*/

use Sora_Kanata\Common_Library\Autoload\Project_Loader;
use Wonder_Link\Yomi_Search_PHP\System\Yomi_Search;

// # Call - Project Loader
require dirname(__FILE__) . "/system/library/autoload/project_loader.php";
$Project_Loader = new Project_Loader(__FILE__, __NAMESPACE__);

// # Call Yomi - Search System - Top
$Yomi_Search =  new Yomi_Search();
$Yomi_Search->StartUp();

exit;