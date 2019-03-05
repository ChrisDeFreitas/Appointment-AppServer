<?php
/**
*   ApptActionBase.php
*   - base class for Appointment Actions
*
*/
namespace App\Action;

use PDO;
use App\DB\DB;

class ApptActionBase
{
    protected $debug = true;
    protected $db = null;
    protected $datafile = './data/appts.sqlite';
    protected $validationErrors = null;
    protected $defaultSelect = "Select id, patient, reason, "
        ."datetime(starttime, 'unixepoch') as starttime, "
        ."datetime(endtime, 'unixepoch') as endtime from appts";

    public function __construct($debug = false)
    {
        $this->debug = $debug;
        $this->validationErrors = null;
    }
    protected function addValidationError($msg)
    {
        if ($this->validationErrors === null) {  //record seperator = ".  "
            $this->validationErrors = $msg;
        } else {
            $this->validationErrors .= '  '.$msg;
        }
    }
    /**
    * apply PHP filter and sanitization to incoming data
    * - populate $this->validationErrors with error message for each bad param received
    * - return (FilteredValue || null)
    */
    public function filterVar($nm, $params)
    {
        if (!isset($params[$nm])) {     //field not found
            return null;
        }

        $var = $params[$nm];
        $val = null;

        if ($nm == 'id') {
            //id is integer
            $var = trim($var, '"\' ');   //remove quoutes and spaces from begin and end
            $options = ["options" => ['default' => null, "min_range"=>1]];
            $val = filter_var($var, FILTER_VALIDATE_INT, $options);
            if (!is_integer($val)) {
                $val = null;
                $this->log("--Error converting [$nm] value: [$var].");
            }
        } elseif ($nm == 'patient' || $nm == 'reason') {
            //FILTER_SANITIZE_STRING strips tags and converts qoutes
            $val = filter_var($var, FILTER_SANITIZE_STRING);
            if ($val === null || $val == '') {
                $val = null;
                $this->log("--Error converting [$nm] value: [$var].");
            }
        } elseif ($nm == 'starttime' || $nm == 'endtime') {
            //datetime values stored as Unix timestamps
            //and passed to client as mysql date string
            $var = trim($var, '"\' ');   //remove quoutes and spaces from begin and end
            $val = filter_var($var, FILTER_SANITIZE_STRING);
            if ($val === false) {
                $val = null;
                $this->log("--Error converting [$nm] value: [$var].");
            }
            $timestamp = strtotime($val);
            if ($timestamp === false) {
                $val = null;
                $this->log("--Error converting [$nm] value: [$var].");
            } else {
                $val = $timestamp;
            }
        }

        if ($val === null) {
            $this->addValidationError("Invalid value for [$nm] received.");
        }
        return $val;
    }
    /**
    * init sqlite database file
    */
    protected function dbInit($debug = false)
    {
        if ($this->db !== null) {
            return true;
        }
        $this->db = new DB($debug);
        return $this->db->init($this->datafile);
    }
    /**
    * write msg to stderr
    */
    protected function log($msg)
    {
        if ($this->debug===true) {
            error_log($msg, 0);
        }
    }
}
