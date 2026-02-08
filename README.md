# api-omicrom.cloud-v3
Global News API Omicrom.Cloud V3
# Desc.:
This project gets articles from Omicrom.Cloud using the V3 algorithm, and displays them on the screen.
Use your preferred database to store your Articles. On this mini project, we use Redis.

/// Determine the project root reliably
# Replace with your real project path
$project_root = "/var/www/html/mini-projects-omicrom.cloud/news-webapp/"; 
$config_dir = $project_root . '/configs/';

# Your API KEY HERE
include $config_dir . 'apis.php';
<?php
    /******
     * API key
     * Tools: Omicrom.Cloud Global NEWS API
     * @author: Technologie Omicrom Sendas Inc. & LLC
     * Date creation: 2026-01-12
     * Licence: Private
     * Version: 3.0
     */
    $array_API = [
        "omicrom.cloud"=>"YOU_API_KEY_HERE",
    ];
?>
