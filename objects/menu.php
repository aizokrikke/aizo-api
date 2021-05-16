<?php
/**
 * Created by IntelliJ IDEA.
 * User: aizokrikke
 * Date: 2021-04-05
 * Time: 16:15
 */

include_once dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . "libs/model.php";

class Menu {
    private $model;
    private $menu = [];
    private $fieldsDef = [
        '{ 
            "name": "name",
            "type": "string",
            "length": "32",
            "index": "false",
            "null": "false",
            "mandatory": "true",
            "default": ""
        }',
        '{ 
            "name": "display",
            "type": "string",
            "length": "100",
            "index": "false",
            "null": "false",
            "mandatory": "true",
            "default": ""
        }',
        '{ 
            "name": "parent",
            "type": "integer",
            "length": "11",
            "index": "true",
            "null": "true",
            "mandatory": "false",
            "default": "0"
        }',
        '{ 
            "name": "order",
            "type": "integer",
            "length": "11",
            "index": "true",
            "null": "false",
            "mandatory": "false",
            "default": "0"
        }',
        '{ 
            "name": "action",
            "type": "string",
            "length": "150",
            "index": "false",
            "null": "false",
            "mandatory": "false",
            "default": ""
        }',
        '{ 
            "name": "content",
            "type": "string",
            "length": "150",
            "index": "false",
            "null": "false",
            "mandatory": "false",
            "default": ""
        }',
        '{ 
            "name": "show",
            "type": "enum",
            "options": ["true", "false"],
            "index": "false",
            "null": "false",
            "mandatory": "false",
            "default": "true"
        }',
        '{ 
            "name": "external",
            "type": "enum",
            "options": ["true", "false"],
            "index": "false",
            "null": "false",
            "mandatory": "false",
            "default": "false"
        }'
        ];

    public function __construct() {
        $this->model = new model('menu', $this->fieldsDef);
    }

    public function get($parent = -1) {
        if ($parent <= 0) {
            $condition = "parent < 1";
        } else {
            $condition = "parent = '" . $parent . "'";
        }
        $order = "`order` ASC";

        $this->menu = $this->model->get('*', $condition, $order);

        return $this->menu;
    }

    public function getTree($parent = -1) {
        $menu = [];
        $result = $this->get($parent);

        WHILE ($line = $this->model->assoc($result)) {
            if (!empty($line)) {
                $line["submenu"] = $this->getTree($line["id"]);
            } else {
                $line["submenu"] = [];
            }
            array_push( $menu, $line);
        }

        return $menu;
    }

    public function add($name, $display = '', $parent = 0, $order = 0, $action = 'show', $content = '',
        $show = true, $external = false) {

        if (!empty($name)) {
            if (empty($display)) {
                $display = $name;
            }
            if (empty($content)) {
                $content = $name;
            }
            if (empty($order)) {
                $order = 0;
            }
            if (empty($show)) {
                $show = 'true';
            }
            if (empty($external)) {
                $external = 'false';
            }
            return $this->model->insert(['name','display','parent','order','action', 'content', 'show','external'],
                [[$name, $display, $parent, $order, $action, $content, $show, $external]]);
        } else {
            return false;
        }
    }

    public function update($id, $name, $display = '', $parent = 0, $order = 0, $action = 'show', $content = '',
                        $show = true, $external = false) {

        if (!empty($name) && !empty($id)) {
            if (empty($display)) {
                $display = $name;
            }
            if (empty($content)) {
                $content = $name;
            }
            if (empty($order)) {
                $order = 0;
            }
            if (empty($show)) {
                $show = 'true';
            }
            if (empty($external)) {
                $external = 'false';
            }
            return $this->model->update(['name','display','parent','order','action', 'content', 'show','external'],
                [$name, $display, $parent, $order, $action, $content, $show, $external],
                "`id` = '". $id ."'");
        } else {
            return false;
        }
    }

    public function updateOrAdd($name, $display = '', $parent = 0, $order = 0, $action = 'show', $content = '',
        $show = true, $external = false) {
        if (!empty($name)) {
            $result = $this->model->get(['id'],"`name` = '". $name . "'");
            $row = $this->model->assoc($result);
            if (!empty($row['id'])) {
                return $this->update($row['id'], $name, $display, $parent, $order, $action, $content, $show, $external);
            } else {
                return $this->add($name, $display, $parent, $order, $action, $content, $show, $external);
            }
        } else {
            return false;
      }
    }



}


?>
