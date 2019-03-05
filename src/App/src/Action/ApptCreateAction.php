<?php
/**
*   ApptCreateAction.php
*   - create endpoint for Appointment applicaiton
*   - create an appt record
*   - return new id in id property of json response object
*   - return new record in data property of json response object

*   Unit test:
*       ./vendor/bin/phpunit --bootstrap vendor/autoload.php test/AppTest/Action/ApptCreateActionTest.php
*
*   URL:
*      http://localhost:8080/appt/create?patient=Clem Frio&reason=Flu
*         &starttime='2019-03-04T22:19:24'&endtime="2019-03-04T22:49:24"
*
*   Sample SQL:
*       Insert Into appts (patient, reason, starttime, endtime)
*           Values ('Mama Cass', 'weepy eyes', 1546300800 , 1546302600)
*
*   Sample response object:
*       {
*        "api":"appt.create",
*        "error":false,
*        "id":"20",
*        "msg":"Record created",
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

class ApptCreateAction extends ApptActionBase implements ServerMiddlewareInterface
{
    public function __construct($debug = false)
    {
        parent::__construct($debug);
    }
    public function process(ServerRequestInterface $request, DelegateInterface $delegate):JsonResponse
    {
        $params = $request->getQueryParams();
        $this->log("ApptCreateAction.process(): ".json_encode($params));

        $msg = "Ready!";
        $data = null;
        $error = false;
        $id = null;
        $sql = 'Insert Into appts (patient, reason, starttime, endtime) Values (';

        //get and validate params
        $result = $this->filterVar('patient', $params);
        if ($result != null) {
            $sql .= "'$result'";
        }
        $result = $this->filterVar('reason', $params);
        if ($result != null) {
            $sql .= ", '$result'";
        }
        $result = $this->filterVar('starttime', $params);
        if ($result != null) {
            $sql .= ", $result ";
        }
        $result = $this->filterVar('endtime', $params);
        if ($result != null) {
            $sql .= ", $result)";
        }

        //is there a validation error?
        if ($this->validationErrors !== null) {
            $obj = ["api" => 'appt.create', "msg" => $this->validationErrors,
                "data" => $data, "error" => true, 'id' => null];
            return new JsonResponse($obj);
        }

        try {
            $this->dbInit();
            $result = $this->db->exec($sql);
            if ($result === 1) {                //one record created
                $msg = 'Record created';
                $id = $this->db->lastIdCreated();
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
            "api" => 'appt.create',
            "error" => $error,
            "id" => $id,
            "msg" => $msg,
            "data" => $data
        ];
        return new JsonResponse($obj);
    }
}
