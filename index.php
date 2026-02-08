<?php
    /******
     * GET Articles
     * Tools: Omicrom.Cloud Global NEWS API
     * @author: Technologie Omicrom Sendas Inc. & LLC
     * Date creation: 2026-01-12
     * Licence: Private
     * Version: 1.0
     */
    ob_start();
    header("Cache-Control: no-cache, no-store, must-revalidate");
    header("Pragma: no-cache");
    header("Expires: 0");

    // Optional: enable error reporting in dev only
    // ini_set('display_errors', 0);
    // error_reporting(E_ALL);

    // Determine the project root reliably
    $project_root = "/var/www/html/mini-projects-omicrom.cloud/news-webapp/"; //Replace with your real project path
    $config_dir = $project_root . '/configs/';

    if (!is_dir($config_dir)) {
        die("Error: Config directory not found at $config_dir\n");
    }
    
    include $config_dir . 'array_categories.php';
    include $config_dir . 'db_redis_config.php';
    include $config_dir . 'src-config.php';
    include $config_dir . 'apis.php';

    /// API Omicrom.Cloud Functions
    include $config_dir . 'omicrom.cloud-functions.php';
    
    /****
     * GET - requests by categories
     * Get current date articles with interval of of 15 minutes.
     * @author: Technologie Omicrom Sendas Inc. & LLC
     * Date created: 2026-01-13
     * Version 3.0
     */

    /// Omicrom.Cloud  API key
    $api_key = $array_API['omicrom.cloud'];
    
    $category = "technology";
    $country = "canada";
    $date = date("Y-m-d"); ///  "2026-01-14"; // date("Y-m-d");
    /// echo "DATE: ".$date."\n";
    /// echo "==================\n";
    
    $request_result = getOmicromNews($category, $country, $date, $api_key);
    pp($request_result);
    $_metadata = $request_result['_metadata'];
    pp($_metadata);
    $dict_article = $request_result['articles'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>News Web App</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <h1>
        REQUEST RESULT:
    </h1>
    <br>
    <?php
        foreach ($dict_article as $key => $str_object_details) {
            pp($str_object_details);
            echo "--------------------------------------------------------<br>";
        }
    ?>

</body>
</html>