<?php
/**
 * Groups configuration for default Minify implementation
 * @package Minify
 */

/** 
 * You may wish to use the Minify URI Builder app to suggest
 * changes. http://yourdomain/min/builder/
 *
 * See http://code.google.com/p/minify/wiki/CustomSource for other ideas
 **/

return array(
    // 'js' => array('//js/file1.js', '//js/file2.js'),
    // 'css' => array('//css/file1.css', '//css/file2.css'),
    'js' => array('//js/jquery-2.2.4.min.js', '//js/bootstrap.js', '//js/bootstrap-slider.js', '//js/jquery.dataTables.min.js', '//js/dataTables.bootstrap.min.js', '//js/chosen.jquery.min.js', '//js/navbar.js', '//js/moment-with-locales.js', '//js/moment-timezone-with-data-2010-2020.js', '//js/jquery.ddslick.js', '//js/jsrender.min.js', '//scripts/sha512.js', '//scripts/forms.js', '//scripts/core.js'),
    'css' => array('//css/bootstrap.slate.min.css', '//css/bootstrap-slider.css', '//css/dataTables.bootstrap.min.css', '//js/chosen.min.css', '//css/navbar.css', '//styles/main.css'),
    'jsdev' => array('//js/jquery-2.2.4.min.js', '//js/bootstrap.js', '//js/bootstrap-slider.js', '//js/jquery.dataTables.min.js', '//js/dataTables.bootstrap.min.js', '//js/chosen.jquery.min.js', '//js/navbar.js', '//js/moment-with-locales.js', '//js/moment-timezone-with-data-2010-2020.js', '//js/jquery.ddslick.js', '//js/jsrender.min.js'),
    'cssdev' => array('//css/bootstrap.slate.min.css', '//css/bootstrap-slider.css', '//css/dataTables.bootstrap.min.css', '//js/chosen.min.css', '//css/navbar.css')
);