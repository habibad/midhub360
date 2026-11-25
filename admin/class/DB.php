<?php
class DB
{
    //varible create for connection

    /**
     * Author Rotun Ahmed
     * This class for database handle
     * create by Rotun Ahmed
     * create Date 8-13-2016
     *
     **/

    private static $_instance = null;
    private $_pdo;
    public $_query;
    private $_error = false;
    private $_results;
    private $_count = 0;
    private $insertId;

    //decleare private function
    private function __construct()
    {
        try {
            $this->_pdo = new PDO('mysql:host=' . Config::get('mysql/host') . ';charset=utf8;dbname=' . Config::get('mysql/db'), Config::get('mysql/user'), Config::get('mysql/password'));

        } catch (PDOException $e) {
            die($e->getMessage());
        }
    }
    //decleare static function get database action
    public static function getInstance()
    {
        if (!isset($_instance)) {
            self::$_instance = new DB();
        }
        return self::$_instance;
    }

    /**
     * @param $sql
     * @param array $params
     * @return $this
     */
    public function get($sql, $params = array())
    {
        $this->_error = false; // was misspelled "_erorr", so the error flag never reset
        if ($this->_query = $this->_pdo->prepare($sql)) {
            $x = 1;
            if (count($params)) {
                foreach ($params as $param) {
                    $this->_query->bindValue($x, $param);
                    $x++;
                }
            }
            //echo $sql.'<br>';
            if ($this->_query->execute()) {
                  $this->_results = $this->_query->fetchAll(PDO::FETCH_OBJ);
                  $this->_count   = $this->_query->rowCount();
            } else {
                $this->_error = true;
            }
        }

        return $this;
    }

    /**
     * @param $action
     * @param $table
     * @param array $where
     *
     */
    public function action($action, $table, $where = array())
    {
        if (count($where) === 3) {
            $oparetors = array('=', '>', '<', '>=', '<=');
            $field     = $where[0];
            $oparetor  = $where[1];
            $value     = $where[2];

            if (in_array($oparetor, $oparetors)) {
                $sql = "{$action} FROM {$table} WHERE {$field} {$oparetor} ?";
                //echo $sql;
                if (!$this->get($sql, array($value))->error()) {
                    return $this;
                }
            }
        }
        return false;
    } //end function

    //select with condition query
    public function getWhere($table, $where)
    {
        return $this->action('SELECT *', $table, $where);
    } //end function

    //function delete data
    public function delete($table, $where)
    {
        return $this->action('DELETE', $table, $where);
    } //end fucntion

    //function for return result
    public function insert($table, $fields = array())
    {
        if (count($fields)) {
            $keys   = array_keys($fields);
            $values = '';
            $x      = 1;
            foreach ($fields as $field) {
                $values .= '?';
                if ($x < count($fields)) {
                    $values .= ', ';
                }
                $x++;
            }

            $sql = "INSERT INTO {$table} (" . implode(',', $keys) . ") VALUES ({$values})";
            //echo $sql;
            if (!$this->get($sql, $fields)->error()) {
                  return $this->_pdo->lastInsertId();
            }

        }
        return false;
    } //end function

    //function for table  query
    public function getTable($table, $limit = null, $offset = null)
    {
        $this->_error = false;
        $sql          = "SELECT * FROM {$table}";
        if (!empty($limit)) {
            $sql .= " LIMIT " . $limit . ' ,' . $offset;
        }
        if ($this->_query = $this->_pdo->prepare($sql)) {

            if ($this->_query->execute()) {
                $this->_results = $this->_query->fetchAll(PDO::FETCH_OBJ);
                $this->_count   = $this->_query->rowCount();
            } else {
                $this->_error = true;
            }
        }
        return $this;
    } //end get table
    ##  update method
    public function update($table, $id, $fields)
    {
        $set = '';
        $x   = 1;

        foreach ($fields as $name => $value) {
            $set .= "{$name} = ?";
            if ($x < count($fields)) {
                $set .= ', ';
            }
            $x++;
        }

        $sql = "UPDATE {$table} SET {$set} where id={$id}";
        //echo $sql;
        if (!$this->get($sql, $fields)->error()) {
            return true;
        }

        return false;
    }

    //return results
    public function result()
    {
        return $this->_results;
    } //end fucntion

    //return table rowcount
    public function Count()
    {
        return $this->_count;
    }
    //function for show erorr
    public function error()
    {
        return $this->_error;
    }

    public function firstId()
    {
        return $this->insertId;
    }

    public function sql()
    {
        return $this->_query;
    }

}
