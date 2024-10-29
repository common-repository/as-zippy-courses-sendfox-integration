<?php
/*
    Plugin Name: AS Sendfox Opt-In Integration with Zippy Courses 
    Description: Integrate Zippy Courses with Sendfox Opt-In
    Version: 1.0.0
    Author: aksharsoftsolutions
    Author URI: http://aksharsoftsolutions.com/
    Text Domain: as-sizc
    Domain Path: /languages
    License: GPL2
 */

function zippy_courses_sendfox_init()
{
    if (class_exists('Zippy')) {
        include dirname(__FILE__) . '/integration.php';
        new ZippyCourses_Sendfox_EmailListIntegration;
    }
    
}
add_action('plugins_loaded', 'zippy_courses_sendfox_init', 11);


