<?php
/**
*       DB.php
*       provide access to SQLite database file: appts.sqlite
*
*/
namespace App\DB;

use PDO;
use PDOStatement;
use Exception;

class DB
{
    protected $debug = false;
    protected $dbh = null;       //pdo handle
    public $errorMsg = null;     //errorInfo() associated with last operation

    public function __construct($debug = false)
    {
        $this->debug = $debug;
    }
    public function __destruct()
    {
        $this->dbh = null;      //release resource
    }
    protected function log($msg)
    {
        if ($this->debug===true) {
            error_log($msg, 0);
        }
    }
    public function inited()
    {
        return ($this->dbh !== null);
    }
    public function errorFound()
    {
        if ($this->errorMsg === null) {
            return false;
        }
        return $this->errorMsg;
    }
    public function init($dbfile)
    {
        $this->log("DB.init($dbfile)");
        if ($this->inited()) {
            return true;
        }
        if (file_exists($dbfile) == false) {
            $this->log("DB.init() error file not found: [$dbfile].");
            return false;
        }
        try {
            $this->dbh = new PDO('sqlite:'.$dbfile);
        } catch (Exception $e) {
            $this->dbh = null;
            $this->log('DB.init() exception: '.$e->getMessage());
        }
        if ($this->dbh == null) {
            $this->log("DB.init() error: data/appts.sqlite not opened.");
            return false;
        }
        return true;
    }
    public function query($sql = '')
    {
        if ($sql == '') {
            $sql = 'Select * from appts';
        }
        $result = null;

        $this->log("DB.query($sql)");

        $result = $this->dbh->query($sql);
        $this->handleErrors();
        if ($result != false) {
            $result = $result->fetchAll(PDO::FETCH_ASSOC);
        }
        return $result;
    }
    public function exec($sql)
    {
        if ($sql == '') {
            throw new Exception("DB.run() error: sql arg is empty.");
        }
        $this->log("DB.exec($sql)");
        $result = $this->dbh->exec($sql);
        $this->handleErrors();
        return $result;     // numer of rows modified
    }
    public function lastIdCreated()
    {
        $this->log("DB.lastIdCreated()");
        $result = $this->dbh->lastInsertId();
        return $result;
    }
    protected function handleErrors()
    {
        //see http://php.net/manual/en/pdo.errorcode.php
        $this->errorMsg = null;

        $errorCode = $this->dbh->errorCode();
        if ($errorCode == null) {
            return;
        }

        $errorCode = substr($errorCode, 0, 2);
        if ($errorCode != '00' && $errorCode != '01') {
            $this->errorMsg = '['
                            .implode('],[', $this->dbh->errorInfo())
                            .']';
            $this->log("DB.handleErrors(): $this->errorMsg");
        }
    }
}
