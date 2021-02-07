<?php
/**
 * Created by IntelliJ IDEA.
 * User: aizokrikke
 * Date: 2019-03-23
 * Time: 16:15
 */

include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "libs/db.php";

class Menu {
    private $db;
    private $menu = [];

    public function __construct() {
        $this->db = new Database();
        $this->connect();
    }

    private function connect() {
        $this->db->query("CREATE TABLE IF NOT EXISTS `menu` (
            `id` int(11) NOT NULL AUTO_INCREMENT,                       
            `name` varchar(32) NOT NULL DEFAULT '',
            `display` varchar(100) NOT NULL DEFAULT '', 
            `parent` int DEFAULT NULL,                 
            `order` int NOT NULL DEFAULT 0,
            `page` varchar(150) NOT NULL DEFAULT '',
            `content` varchar(150) NOT NULL DEFAULT '',                   
            `show` enum('true','false') NOT NULL DEFAULT 'true',           
            `external` enum('true', 'false') NOT NULL DEFAULT 'true',
            `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `order` (`order`),
            INDEX `parent` (`parent`)
            )
            ENGINE=InnoDB  DEFAULT CHARSET=latin1;
        ");

        $this->db->query("SELECT * FROM `menu`");
        if ($this->db->num_rows()<=0) {
            // empty menu, create default menu
            $this->setDefault();
        }
    }

    private function setDefault() {
        $this->db->query("
          INSERT INTO `menu` ( `name`, `display`, `page`, `content`, `show`, `external`, `order`, `parent`) 
          VALUES 
            ('home', 'Home', 'show', 'home', 'true', 'false', 1, NULL),
            ('about', 'About', 'show', 'about', 'true', 'false', 2, NULL),
            ('contact', 'Contact', 'show', 'contact', 'true', 'false', 1, 2),
            ('route', 'Routebeschrijving', 'route', 'route', 'true', 'false', 1, 3);
      ");
    }

    public function get($parent = -1) {
        if ($parent < 0) {
            $this->db->query("SELECT
            `id`,
            `name`,
            `display`,
            `page`,
            `content`,
            `show`,
            `external`,
            `order`
            FROM `menu`
            WHERE `parent` IS NULL
            ORDER BY `order` DESC;
        ");


        } ELSE {
            $this->db->query("SELECT
            `id`,
            `name`,
            `display`,
            `page`,
            `content`,
            `show`,
            `external`,
            `order`
            FROM `menu`
            WHERE `parent` = '" . $parent . "'
            ORDER BY `order` DESC;
        ");

        }

        $this->menu = $this->db->getResult();

       return $this->menu;

    }

    public function getTree($parent = -1) {
        $menu = [];
        $result = $this->get($parent);

        WHILE ($line = $this->db->assoc($result)) {

            if (!empty($line)) {
                $line["submenu"] = $this->getTree($line["id"]);
            } else {
                $line["submenu"] = [];
            }
            array_push( $menu, $line);

        }

        return $menu;
    }



}


?>
