<?php
/**
*   ApptUpdateAction.php
*   - update endpoint for Appointment application
*   - id field required, but may not be updated
*   - any other combintaion of fields may be updated in one call
*   - return new id in id property of json response object
*   - return updated fields in fields property of json response object
*   - return new record in data property of json response object
*
*   Unit test:
*       ./vendor/bin/phpunit --bootstrap vendor/autoload.php test/AppTest/Action/ApptUpdateActionTest.php
*
*   URL:
*      http://localhost:8080/appt/update?id=1&reason=Test <script>alert(1)</script> reason
*          &starttime='2019-03-04T22:19:24'&endtime="2019-03-04T22:49:24"
*
*   Sample SQL:
*       Update appts Set starttime = 1551737360, endtime = 1551739160 Where id=1
*
*   Sample response object:
*       {
*        "api":"appt.update",
*        "error":false,
*        "fields":"starttime,endttime",
*        "id":"20",
*        "msg":"Record updated",
*        "data":[
*           {"id":"20","patient":"Mama Cass","reason":"weepy eyes",
*           "starttime":"2019-01-01 00:00:00","endtime":"2019-01-01 00:30:00"}
*        ]
*       }
*/
namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

use App\Action\AppActionBase;
use App\DB\DB;

class ApptUpdateAction extends ApptActionBase implements ServerMiddlewareInterface
{
    public function __construct($debug = false)
    {
        parent::__construct($debug);
    }
    public function process(ServerRequestInterface $request, DelegateInterface $delegate):JsonResponse
    {
        $params = $request->getQueryParams();
        $this->log("ApptUpdateAction.process(): ".json_encode($params));

        $msg = "Ready!";
        $data = null;
        $error = false;
        $id = null;
        $sql = 'Update appts Set ';
        $flds = '';

        //get and validate params
        $val = $this->filterVar('id', $params);
        if ($val == null) {
            $this->addValidationError('id field is required to upate records.');
        } else {
            $id = $val;
        }
        $val = $this->filterVar('patient', $params);
        if ($val != null) {
            $sql .= "patient = '$val'";
            $flds = 'patient';
        }
        $val = $this->filterVar('reason', $params);
        if ($val != null) {
            if ($flds != '') {
                $sql .= ', ';
                $flds .= ',';
            }
            $sql .= "reason = '$val'";
            $flds .= 'reason';
        }
        $val = $this->filterVar('starttime', $params);
        if ($val != null) {
            if ($flds != '') {
                $sql .= ', ';
                $flds .= ',';
            }
            $sql .= "starttime = $val";
            $flds .= 'starttime';
        }
        $val = $this->filterVar('endtime', $params);
        if ($val != null) {
            if ($flds != '') {
                $sql .= ', ';
                $flds .= ',';
            }
            $sql .= "endtime = $val";
            $flds .= 'endtime';
        }
        if ($flds === '') {
            $this->addValidationError('No fields found to update.');
        }

        //is there a validation error?
        if ($this->validationErrors !== null) {
            $obj = ["api" => 'appt.update', "msg" => $this->validationErrors,
                "data" => null, "error" => true, 'id' => null];
            return new JsonResponse($obj);
        }
        $sql .= " Where id={$id}";

        try {
            $this->dbInit(false);
            $result = $this->db->exec($sql);
            if ($result === 1) {        //one record updated
                $msg = "Record updated";
                $sql = "{$this->defaultSelect} where id={$id}";
                $data = $this->db->query($sql);
                $error = false;
            } else {
                $error = true;
                error_log("--Error querying database: {$this->db->errorMsg}", 0);       //stderr
                $msg = "An error occurred querying database; please contact your system administrator.";
            }
        } catch (Exception $e) {
            $error = true;
            error_log("--Exception: ".$e->getMessage(), 0);        //stderr
            $msg = "An exception occurred querying the database; please contact your system administrator.";
        }

        $obj = [
            "api" => 'appt.update',
            "error" => $error,
            "fields" => $flds,
            "id" => $id,
            "msg" => $msg,
            "data" => $data
        ];
        return new JsonResponse($obj);
    }
}
