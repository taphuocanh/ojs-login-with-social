<?php
/**
 * File index.php
 *
 * Description
 *
 * PHP version 7.2.12
 *
 * @package    OJSPluginsAuth
 * @subpackage SocialsAuth
 * @author     Tạ Phước Ánh <taphuocanh@huaf.edu.vn>
 * @license    http://www.php.net/license/3_01.txt  PHP License 3.01
 * @version    1.0.0
 * @link       https://taphuocanh.com/ojs/socials-auth
 * @since      File available since Release 1.0.0
 */

require_once 'vendor/autoload.php';

require_once 'SocialsAuth.inc.php';
//			echo '<pre>';
//			echo 'Line ' . __LINE__ . ' - ' .__FILE__ . PHP_EOL;
//			var_dump('aaaaa');
//			echo '</pre>';
return new SocialsAuth();