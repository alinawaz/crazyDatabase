<?php

/*
 * Database Helper Class - CrazyDatabase
 * Copyright(c)2015 - DemiXsoft(R) 2015
 * Author. Ali Nawaz Hiraj
 * Version. 1.0.0 {12 March,2012} ---Beta
 * Version. 1.1.0 {25 May, 2013} ---Stable
 * Version. 2.0.0 {30 April, 2014} ---Beta
 * Version. 2.1.0 {10 June, 2015} ---Beta
 * Version. 2.1.1 {23 June, 2015} ---Stable
 * Version. 2.2.0 {28 July, 2016} ---Major Revision
 */

class Database {
    /* Static Variables */

    public static $db_name = "test";
    public static $server = "localhost";
    public static $DBuser = "root";
    public static $DBpass = '';
    private static $connected = false;
    public $conn = FALSE; // connection reference

    /*
     * Function: Connect
     * @Params [n/a]
     * @Returns [boolean]
     */

    public static function Connect() {
        Database::close();
        $this->conn = mysqli_connect(Database::$server, Database::$DBuser, Database::$DBpass, Database::$db_name);
        if (mysqli_connect_errno()) {
            echo "Crazy Database MSQLI Error: " . mysqli_connect_error();
        }
        Database::$connected = true;
        return true;
    }

    /*
     * Function: Close
     * @Params [n/a]
     * @Returns [boolean]
     */

    public static function Close() {
        if (Database::$connected == true) {
            mysqli_close($this->conn);
            Database::$connected = false;
            return true;
        }
        return false;
    }

    /*
     * Function: Config
     * @Params [serverName(String),databaseName(String),username(String),password(String)]
     * @Returns [boolean]
     */

    public static function Config($serverName, $db, $username, $password) {
        Database::$db_name = $db;
        Database::$DBpass = $password;
        Database::$DBuser = $username;
        Database::$server = $serverName;
        return true;
    }

    /*
     * Function: Query
     * @Params [queryText(String)]
     * @Returns [result(dbObject)]
     */

    public static function Query($QueryText) {
        Database::connect();
        $result = mysqli_query($this->conn,$QueryText);
        return $result;
    }

    /*
     * Function: objectQuery
     * @Params [queryText(String)]
     * @Returns [rows(ObjectClass)]
     */

    public static function objectQuery($QueryText) {
        Database::connect();
        $result = mysqli_query($this->conn,$QueryText);
        return new rows($result);
    }

}

/* New Feature : rows Class
 * Version 2.0.0 
 */

class rows {
    /*
     * Static Variables
     */

    private static $currentPosition = 0;
    private static $dataArray = array();

    /*
     * Constructor Function
     * @Params [queryResult(Object)]
     * @Returns [nil]
     */

    function __construct($queryResult) {
        while ($tempData = mysqli_fetch_assoc($queryResult)) {
            rows::$dataArray[] = $tempData;
        }
    }

    /*
     * Function: get
     * @Params [nil]
     * @Returns [row(Object)]
     */

    public static function get() {
        return (object) rows::$dataArray[rows::$currentPosition];
    }

    /*
     * Function: getAndNext
     * @Params [nil]
     * @Returns [row(Object)]
     */

    public static function getAndNext() {
        if (rows::$currentPosition < count(rows::$dataArray)) {
            $ret = (object) rows::$dataArray[rows::$currentPosition];
            rows::$currentPosition++;
            return $ret;
        } else {
            return null;
        }
    }

    /*
     * Function: total
     * @Params [nil]
     * @Returns [int(rowsCount)]
     */

    public static function total() {
        return count(rows::$dataArray);
    }

    /*
     * Function: hasNext
     * @Params [nil]
     * @Returns [bool]
     */

    public static function hasNext() {
        if (rows::$currentPosition < count(rows::$dataArray)) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * Function: hasBack
     * @Params [nil]
     * @Returns [bool]
     */

    public static function hasBack() {
        if (rows::$currentPosition > 0) {
            return true;
        } else {
            return false;
        }
    }

    /*
     * Function: next
     * @Params [nil]
     * @Returns [bool]
     */

    public static function next() {
        if (rows::$currentPosition >= count(rows::$dataArray)) {
            return false;
        } else {
            rows::$currentPosition++;
            return true;
        }
    }

    /*
     * Function: back
     * @Params [nil]
     * @Returns [bool]
     */

    public static function back() {
        if (rows::$currentPosition == 0) {
            return false;
        } else {
            rows::$currentPosition--;
            return true;
        }
    }

}

/* New Feature : Table Class
 * Version 3.0.0 
 */

class Table {
    /* Static Variables */

    private static $tableName = '';

    /*
     * Function: constructor
     * @Params [string(tableName)]
     * @Returns [nil]
     */

    function __construct($tableNameString) {
        Table::$tableName = $tableNameString;
    }

    /*
     * Function: __toString
     * @Params [nil]
     * @Returns [string(tableName)]
     */

    function __toString() {
        return Table::$tableName;
    }

    /*
     * Function: exists
     * @Params [array(queryWhereClause)]
     * @Returns [bool]
     */

    public static function exists($matchArray) {
        $db = new Database();
        $db->connect();
        $whereString = '';
        if ($matchArray != null) {
            foreach ($matchArray as $key => $value) {
                if ($whereString == "") {
                    $whereString = $whereString . " " . $key . "='" . $value . "' ";
                } else {
                    $whereString = $whereString . " and " . $key . "='" . $value . "' ";
                }
            }
            if ($whereString != '')
                $whereString = ' where ' . $whereString;
        }
        $sql = $db->query("select * from " . Table::$tableName . $whereString);
        if ($res = mysql_fetch_array($sql)) {
            $db->close();
            return true;
        } else {
            return false;
        }
        return false;
    }

    /*
     * Function: select
     * @Params [string(querySelectClause), array(queryWhereClause), string(queryAfterWhereClause)]
     * @Returns [array(result)]
     */

    public static function select($selectString, $whereArray = null, $afterWhere = '') {
        $db = new Database();
        $db->connect();
        $whereString = '';
        if ($whereArray != null) {
            foreach ($whereArray as $key => $value) {
                if ($whereString == "") {
                    $whereString = $whereString . " " . $key . "='" . $value . "' ";
                } else {
                    $whereString = $whereString . " and " . $key . "='" . $value . "' ";
                }
            }
            if ($whereString != '')
                $whereString = ' where ' . $whereString;
        }
        $queryString = "select $selectString from " . Table::$tableName . $whereString . " " . $afterWhere;
        $sql = $db->query($queryString);
        $record = array();
        while ($data = mysqli_fetch_assoc($sql)) {
            $record[] = $data;
        }
        return $record;
    }

    /*
     * Function: selectObject
     * @Params [string(querySelectClause), array(queryWhereClause), string(queryAfterWhereClause)]
     * @Returns [object(result)]
     */

    public static function selectObject($selectString, $whereArray = null, $afterWhere = '') {
        $db = new Database();
        $db->connect();
        $whereString = '';
        if ($whereArray != null) {
            foreach ($whereArray as $key => $value) {
                if ($whereString == "") {
                    $whereString = $whereString . " " . $key . "='" . $value . "' ";
                } else {
                    $whereString = $whereString . " and " . $key . "='" . $value . "' ";
                }
            }
            if ($whereString != '')
                $whereString = ' where ' . $whereString;
        }
        $queryString = "select $selectString from " . Table::$tableName . $whereString . " " . $afterWhere;
        $sql = $db->objectQuery($queryString);
        return $sql;
    }

    /*
     * Function: insert
     * @Params [array(columnAndValues)]
     * @Returns [bool]
     */

    public static function insert($dataArray) {
        $columns = '';
        $values = '';
        if ($dataArray) {
            foreach ($dataArray as $key => $value) {
                if ($columns == '') {
                    $columns = $columns . $key;
                } else {
                    $columns = $columns . "," . $key;
                }
                if ($values == '') {
                    $values = $values . "'" . $value . "'";
                } else {
                    $values = $values . ",'" . $value . "'";
                }
            }
            $db = new Database();
            $db->connect();
            $db->query("insert into " . Table::$tableName . " (" . $columns . ") values (" . $values . ");");
            $id = mysql_insert_id();
            $db->close();
            return $id;
        } else {
            return false;
        }
        return false;
    }

    /*
     * Function: insert
     * @Params [array(columnAndValues), array(queryWhereClause)]
     * @Returns [bool]
     */

    public static function update($dataArray, $matchArray) {
        $updates = '';
        $matches = '';
        if ($dataArray && $matchArray) {
            foreach ($dataArray as $key => $value) {
                if ($updates == '') {
                    $updates = $updates . $key . "='" . $value . "'";
                } else {
                    $updates = $updates . "," . $key . "='" . $value . "'";
                }
            }
            foreach ($matchArray as $key => $value) {
                if ($matches == '') {
                    $matches = $matches . $key . "='" . $value . "'";
                } else {
                    $matches = $matches . " and " . $key . "='" . $value . "'";
                }
            }
            $db = new Database();
            $db->connect();
            $tempQuery = "update " . Table::$tableName . " set " . $updates . " where " . $matches;
            //var_dump($tempQuery);
            $response = $db->query($tempQuery);
            $db->close();
            return $response;
        } else {
            return false;
        }
        return false;
    }

    /*
     * Function: delete
     * @Params [array(queryWhereClause)]
     * @Returns [bool]
     */

    public static function delete($matchArray) {
        $matches = '';
        if ($matchArray) {
            foreach ($matchArray as $key => $value) {
                if ($matches == '') {
                    $matches = $matches . $key . "='" . $value . "'";
                } else {
                    $matches = $matches . " and " . $key . "='" . $value . "'";
                }
            }
            $db = new Database();
            $db->connect();
            $response = $db->query("delete from " . Table::$tableName . " where " . $matches);
            $db->close();
            return $response;
        } else {
            return false;
        }
        return false;
    }

    /*
     * Function: truncate
     * @Params [nil]
     * @Returns [bool]
     */

    public static function truncate() {
        $db = new Database();
        $db->connect();
        $db->query("truncate table " . Table::$tableName);
        $db->close();
        return true;
    }

}
