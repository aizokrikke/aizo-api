<?php

/**
 * Created by IntelliJ IDEA.
 * User: aizokrikke
 * Date: 2021-04-05
 * Time: 16:15
 */

include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "libs/db.php";

class Page
{
    private $db;
    private $pages = [];

    public function __construct()
    {
        $this->db = new Database();
        $this->connect();
    }

    private function connect()
    {
        $this->db->query("CREATE TABLE IF NOT EXISTS `pages` (
            `id` int(11) NOT NULL AUTO_INCREMENT,                       
            `name` varchar(150) NOT NULL DEFAULT '',
            `contentid` varchar(50) NOT NULL DEFAULT '', 
            `deleted` enum('true', 'false') NOT NULL DEFAULT 'false'
            `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            INDEX `contentid` (`contentid`),
            INDEX `name` (`name`)
            )
            ENGINE=InnoDB  DEFAULT CHARSET=latin1;
        ");

        $this->db->query("SELECT * FROM `pages`");
        if ($this->db->num_rows() <= 0) {
            // empty menu, create default menu
            $this->setDefault();
        }
    }

    private function setDefault()
    {
        $this->db->query("
          INSERT INTO `menu` ( `name`, `contentid` ) 
          VALUES 
            ('home', 'Home', )
      ");
    }

    public function get($id = 0)
    {
        if ($id < 1) {
            $this->db->query("SELECT
            `id`,
            `name`,
            FROM `pages`
            AND 'deleted' != 'y';
        ");


        } else {
            $this->db->query("SELECT
            `id`,
            `name`,
            `contentid`
            FROM `pages`
            WHERE `id` = '" . $id . "'
            AND 'deleted' != 'y';
        ");

        }

        $this->menu = $this->db->getResult();

        return $this->menu;

    }


}



