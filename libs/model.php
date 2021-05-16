<?php
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "db.php";
include_once dirname(__FILE__) . DIRECTORY_SEPARATOR . "logError.php";


class model
{

    private database $db;
    private string $name = '';
    private string $tabledefinition = '';
    private string $indexes = '';
    private array $fielddefinition = [];
    private string $engine = 'InnoDB';
    private string $charset = 'latin1';
    private string $table = '';
    private string $description = '';
    private string $definition = '';
    private string $dbPrefix = 'mod_';

    public function __construct($model = '', $fieldDefinition = []) {

        if (!empty($model)) {
            if (!empty($fieldDefinition)) {
                $this->db = new database();
                $this->name = $model;
                $this->setTableName();
                $this->fielddefinition = $fieldDefinition;
                $this->createTableDefinition();
                $this->checkModelHealth();

            } else {
                return new logError('Table Definition missing', 'model');
            }
        } else {
            return new logError('Name missing', 'model');
        }
    }


    private function createTableDefinition() {
      $this->tabledefinition = '';

      foreach ($this->fielddefinition as $value) {
          if (!$field = json_decode($value, true)) {
              return new logError('Error in JSON', 'model');
          }

         if (empty($field['name'])) {
             return new logError('Fieldname missing (mandatory)', 'model');
         }
         if (empty($field['type'])) {
             return new logError('Fieldtype missing (mandatory)', 'model');
         }
         if (!empty($this->tabledefinition)) {
             $this->tabledefinition .= ", ";
         }
         switch ($field['type']) {
             case 'string':
                 $this->tabledefinition .= "`" . $field['name'] . "` varchar(" . $field['length'] . ")";
                 if ($field['null']<>'true') {
                     $this->tabledefinition .= " NOT NULL";
                 }
                 if (!empty($field['default'])) {
                     $this->tabledefinition .= " DEFAULT '" . $field['default'] . "'";
                 }
             break;
             case 'text':
                 $this->tabledefinition .= "`" . $field['name'] . "` text";
                 if ($field['null']<>'true') {
                     $this->tabledefinition .= " NOT NULL";
                 }
                 if (!empty($field['default'])) {
                     $this->tabledefinition .= " DEFAULT '" . $field['default'] . "'";
                 }
                 break;
             case 'integer':
                 $this->tabledefinition .= "`" . $field['name'] . "` int(" . $field['length'] . ")";
                 if ($field['null']<>'true') {
                     $this->tabledefinition .= " NOT NULL";
                 }
                 if (!empty($field['default'])) {
                     $this->tabledefinition .= " DEFAULT '" . $field['default'] . "'";
                 }
                 break;
                 case 'datetime':
                 $this->tabledefinition .= "`" . $field['name'] . "` datetime";
                 if ($field['null']<>'true') {
                     $this->tabledefinition .= " NOT NULL";
                 }
                 if (!empty($field['default'])) {
                     $this->tabledefinition .= " DEFAULT " . $field['default'];
                 }
                 break;
             case 'enum':
                 if (empty($field['options'])) {
                     return new logError('Options missing for enumtype (mandatory)', 'model');
                 }
                 $this->tabledefinition .= "`" . $field['name'] . "` enum(";
                 $first = true;
                 foreach ($field['options'] as $option) {
                     if (!$first) {
                         $this->tabledefinition .= ",";
                     }
                     $this->tabledefinition .= "'" . $option . "'";
                     $first = false;
                 }
                 $this->tabledefinition .= ")";
                 if ($field['null']<>'true') {
                     $this->tabledefinition .= " NOT NULL";
                 }
                 if (!empty($field['default'])) {
                     $this->tabledefinition .= " DEFAULT '" . $field['default'] . "'";
                 }

                 break;
         }
        $this->parseIndex($field);
      }

    }

    private function parseIndex($field) {
        if ($field['index'] == 'true') {
            $this->indexes .= "INDEX `" . $field['name'] . "` (`" . $field['name'] . "`),
                     ";
        }
;
    }

    public function validateInput($in, $all = false) {
        
        $fields = [];
        $mandatory = [];
        $errors = [];
        $unique = [];

        // make validation arrays
        foreach($this->fielddefinition as $field => $json) {
            $definition = json_decode($json, true);
            $mandatory[$definition['name']] = $definition['mandatory'];
            $unique[$definition['name']] = $definition['unique'];
            $fields[$definition['name']] = $definition;
        }

        // process input
        foreach ($in as $key => $value) {
            $fieldtype = $fields[$key]['type'];
            if (empty($fieldtype)) {
                // field is not in model definition
                $errors[] = "unknown field " . $key;
            }
            if (!$this->validate($value, $fieldtype)) {
                // field type incorrect
                $errors[] =  'field ' . $key . ' is not valid';
            }
            if ($unique[$key] == "true") {
                if (!$this->unique($key, $value)) {
                    $errors[] =  'field ' . $key . ' is not unique (mandatory)'; 
                }
            }
            $mandatory[$key] = "present";
        }

        // are all the required fields in place?
        foreach ($mandatory as $key => $value) {
            if ($value == "true") {
                $errors[] = "field " . $key . " is missing (mandatory)";
            }
        }
        if (empty($errors)) {
            return true;
        } else {
            return $errors;
        }
    }

    private function unique($field, $value): bool
    {
        $condition = $field . " = '". $value . "'";
        $result = $this->getOne(['id'], $condition);

        return empty($result);
    }
    
    private function validate($value, $type) {

        $valid = false;
        switch (strtolower($type))  {
            case 'string':
            case 'text':
                $valid = true;
                break;
            case 'integer':
                $valid = ctype_digit($value);
                break;
            case 'enum':
                // to do
                // for now accept
                $valid = true;
                break;
            case 'datetime':
                // to do
                // for now accept
                $valid = true;
                break;
        }
        return $valid;
    }

    public function insert($fields, $values, $verbose = 'n') {
        $fieldlist = "`" . implode("`,`", $fields) . "`";
        foreach ($values as $key=>$row) {
            $valuelist[$key] = implode("','", $row);
            $valuelist[$key] = "('" . $valuelist[$key] . "')";
        }

        $q = "INSERT INTO `$this->table` ($fieldlist) VALUES  ";
        foreach ($valuelist as $row) {
            $q .= $row;
        }

        if ($verbose == 'y') {
            echo $q;
        }
        return $this->db->query($q);
    }

    public function update($fields, $values, $conditions = '', $verbose = 'n') {

        $start = 'y';
        $q = "UPDATE `$this->table` SET ";
        foreach ($fields as $key=>$field) {
            if ($start != 'y') {
                $q .= ', ';
            }
            $q .= "`" . $field . "` = '". $values[$key]."'";
            $start = 'n';
        }
        $q .=  "WHERE deleted != 'y' ";
        if (!empty($conditions)) {
            $q .= "AND ($conditions)";
        }

        if ($verbose == 'y') {
            echo $q;
        }
        return $this->db->query($q);
    }

    public function getOne($fields, $condition='', $order='',  $verbose = 'n') {
        $limit = "0,1";
        $result = $this->get($fields, $condition, $order, $limit, $verbose);
        return $this->assoc($result);
    }

    public function get($fields, $condition='', $order='', $limit='', $verbose = 'n') {
        if (is_array($fields)) {
            $fieldlist = implode("`, `", $fields);
            $fieldlist = "`" . $fieldlist . "`";
        } else {
            $fieldlist = $fields;
        }
        $q = "SELECT $fieldlist FROM `$this->table` WHERE deleted != 'y' ";
        if (!empty($condition)) {
            $q .= "AND ($condition) ";
        }
        if (!empty($order)) {
            $q .= "ORDER BY $order ";
        }
        if (!empty($limit)) {
            $q .= "LIMIT $limit ";
        }
        if ($verbose == 'y') {
            echo $q;
        }
        $results = $this->db->query($q);

        return $results;
    }

    public function list($condition = '', $fields = array('id'), $order = '', $start = -1, $end = -1): array {

        $limit = '';
        if ($start >= 0 && $end >= 0 && $end > $start) {
            $delta = $end - $start;
            $limit = "$start , $delta";
        }
        $result = $this->get($fields, $condition, $order, $limit);
        $out = array();
        while ($row = $this->assoc($result)) {
            $out[] = $row;
        }
        return $out;
    }


    public function row($in) {
        return $this->db->row($in);
    }

    public function assoc($in) {
        return $this->db->assoc($in);
    }

    public function delete($condition) {
        $q = "DELETE FROM `$this->table` WHERE $condition";
        $this->db->query($q);
    }

    public function esc($in) {
        return $this->db->esc($in);
    }

    public function num_rows($in) {
        return $this->db->num_rows($in);
    }

    public function insert_id() {

        return $this->db->insert_id();
    }

    public function query($in) {
        return $this->db->query($in);
    }

    public function setDescription() {
        //update the decription in the class and db
    }

    private function setTableName() {
        $this->table = $this->dbPrefix . $this->name;
    }

    private function checkModelHealth() {
        $q = "SELECT * FROM models WHERE name = '$this->name' LIMIT 0,1";
        $currentModel = $this->db->assoc($q);

        if (empty($currentModel)) {
            $this->create();
        } else {
            // check of de database bestaat
            $q = "SHOW TABLES LIKE '$this->table'";
            $result = $this->db->row($q);

            if (!empty($result)) {
                // check health of model
                if ($currentModel['definition'] <> $this->tabledefinition) {
                    // model has changed
                    $newParts = $this->getDefinitionLines($this->tabledefinition);

                    // database velden aanpassen en/of toevoegen
                      foreach($newParts as $newLine) {
                        $name = $this->getLineFieldName($newLine);
                        echo $name . " ";
                        if (!empty($name)) {
                            if (preg_match($name, $currentModel['definition'])) {
                                $this->updateField($newLine);
                            } else {
                                $this->addField($newLine);
                            }
                        }
                    }
                    // velden verwijderen indien nodig
                    $oldParts = $this->getDefinitionLines($currentModel['definition']);
                    foreach($oldParts as $oldLine) {
                        $name = $this->getLineFieldName($oldLine);
                        if (!empty($name)) {
                            if (!preg_match($name, $this->definition)) {
                                // veld is removed
                                $this->removeField($name);
                            }
                        }
                    }

                    // update model in lijst in database
                    $this->definition = $this->db->esc($this->tabledefinition);
                    $q = "UPDATE `models` set `definition` = '$this->definition' where `name`='$this->name'";
                    $this->db->query($q);
                }
            } else {
                $this->create();
            }

        }
    }

    private function tableHealthy() {
        $parts = $this->getDefinitionLines($this->tabledefinition);
        $q = "SHOW TABLES LIKE '$this->table'";
        $result = $this->db->row($q);

       $q = "SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = '" . $this->db->getDbName() . "' AND TABLE_NAME = '" . $result[0] . "'";
       echo $q;
        $fields = array();
        $result = $this->db->query($q);
        while ($field = $this->db->row($result)) {
            $fields[] = $field[0];
        };
        print_r($fields);

    }

    private function getLineFieldName($line) {
        $parts = explode(' ', trim($line));
        if ($parts[0] == 'PRIMARY' || $parts[0] == 'INDEX') {
            return '';
        }
        return trim($parts[0]);
    }

    private function create() {
        if (empty($this->name) || empty($this->tabledefinition)) {
            return new logError('Cannot create model, input missing', 'model');
        } else {
            $q = "CREATE TABLE `$this->table` (
                `id` bigint(20) NOT NULL AUTO_INCREMENT,
                $this->tabledefinition,
                `created` timestamp NOT NULL DEFAULT current_timestamp(),
                `modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                `deleted` enum('y','n') DEFAULT 'n'
                ";
            if (!empty($this->indexes)) {
                $q .= ", " . $this->indexes;
            }
            $q .= "
                PRIMARY KEY (`id`))
                ENGINE=$this->engine 
                DEFAULT CHARSET=$this->charset 
                AUTO_INCREMENT=1";

            $this->db->query($q);

            // update list of models
            $definition = $this->db->esc($this->tabledefinition);
            // eerst verwijderen, just to be  sure
            $q = "DELETE FROM `models` WHERE `name` = '$this->name'";
            $this->db->query($q);
            $q = "INSERT INTO `models` (`name`, `description`, `table`, `definition`) 
                    VALUES ('$this->name', '$this->description', '$this->table', '$definition')";
            $this->db->query($q);
        }
    }



    private function getDefinitionLines($parts) {
        preg_match_all("/enum\([\'\w\s,]{0,}\)/", $parts, $results);

        foreach ($results[0] as $snippet) {
            $escaped = str_replace(',',':comma:', $snippet);
            $parts = str_replace($snippet, $escaped, $parts);
        }
        $definitionParts = explode(',', trim($parts));
        foreach ($definitionParts as $key=>$part) {
            $definitionParts[$key] = str_replace(':comma:',',', $part);
        }

        return $definitionParts;;
    }

    private function updateField($field) {
        $q = "ALTER TABLE $this->table MODIFY $field";
        $this->db->query($q);
    }

    private function addField($field) {
        $q = "ALTER TABLE $this->table ADD $field";
        $this->db->query($q);
    }

    private function removeField($field) {
        $q = "ALTER TABLE $this->table DROP COLUMN $field";
        $this->db->query($q);
    }
}
