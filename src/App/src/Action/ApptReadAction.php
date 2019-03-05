<?php
/**
*   ApptReadAction.php
*   - read endpoint for Appointment applicaiton
*   - return all rows or record for specified id
*
*   Unit test:
*       ./vendor/bin/phpunit --bootstrap vendor/autoload.php test/AppTest/Action/ApptReadActionTest.php
*
*   API URLs:
*       http://localhost:8080/appt/read
*       http://localhost:8080/appt/read?id=1
*
*   Sample SQL:
*     Select patient, reason, datetime(starttime, 'unixepoch') as starttime, datetime(endtime, 'unixepoch') as endtime
*         from appts where id=1
*
*   URL:
*
*   Sample response object:
*       {
*        "api":"appt.read",
*        "msg":"1 fecord found.",
*        "data":[
*           {"id":"20","patient":"Mama Cass","reason":"weepy eyes",
*           "starttime":"2019-01-01 00:00:00","endtime":"2019-01-01 00:30:00"}
*        ],
*        "error":false
*       }
**/
namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

use App\Action\AppActionBase;
use App\DB\DB;

class ApptReadAction extends ApptActionBase implements ServerMiddlewareInterface
{
    public function __construct($debug = false)
    {
        parent::__construct($debug);
    }
    public function process(ServerRequestInterface $request, DelegateInterface $delegate):JsonResponse
    {
        $params = $request->getQueryParams();
        $this->log("ApptReadAction.process(): ".json_encode($params));

        $msg = "Ready";
        $data = null;
        $error = false;
        $id = null;

        //validate params
        if (is_array($params)) {
            if (array_key_exists('id', $params)) {
                $id = $this->filterVar('id', $params);
            }
            //ToDo: add other record query selectors
        }

        //is there a validation error?
        if ($this->validationErrors !== null) {
            $obj = ["api" => 'appt.read', "msg" => $this->validationErrors, "data" => $data, "error" => true];
            return new JsonResponse($obj);
        }

        //get records
        $sql = $this->defaultSelect;
        if ($id != null) {
            $sql .= " where id={$id}";
        }

        try {
            $this->dbInit();
            $data = $this->db->query($sql);
            if (is_array($data) == true) {
                $msg = sizeof($data).' records found';
                $error = false;
            } else {
                $error = true;
                error_log("--Error querying database: {$this->db->errorMsg}");      //stderr
                $msg = "An error occurred querying database; please contact your system administrator.";
            }
        } catch (Exception $e) {
            $error = true;
            error_log("--Exception: ".$e->getMessage(), 0);         //stderr
            $msg = "An exception occurred querying the database; please contact your system administrator.";
        }

        $obj = ["api" => 'appt.read', "msg" => $msg, "data" => $data, "error" => $error];
        return new JsonResponse($obj);
    }
}
