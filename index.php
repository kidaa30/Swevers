<?php

/* ----------
FW4 FRAMEWORK
-------------

Usage:

1) Set up database access in config.php
2) Define pages in content directory
3) Give write access to /files, /files/thumbnails and /cache

Make sure that you don't forget to upload .htaccess file.

No configuration required below */


// Define folder names
$system_folder = "system";
$content_folder = "content";
$views_folder = "views";
$uploads_folder = "files";

// Define subdirectory for framework admin page
$admin_directory = "admin";

// Start session handling
session_start();

// Define paths for system usage
define('BASEPATH', $_SERVER['DOCUMENT_ROOT'].'/'.$system_folder.'/');
define('CONTENTPATH', $_SERVER['DOCUMENT_ROOT'].'/'.$content_folder.'/');
define('VIEWSPATH', $_SERVER['DOCUMENT_ROOT'].'/'.$views_folder.'/');
define('FILESPATH', $_SERVER['DOCUMENT_ROOT'].'/'.$uploads_folder.'/');

define('ADMINDIR', $admin_directory);
define('ADMINRESOURCES', $system_folder.'/admin/');
define('UPLOADSDIR', $uploads_folder);

// Nucleus server timezones are misconfigured. We have to use GMT for GMT+2
date_default_timezone_set('Europe/Brussels');
ini_set('date.timezone', 'Europe/Brussels');

// Router takes care of the rest
require($system_folder.'/router.php');
if (!Router::go()) error(404);