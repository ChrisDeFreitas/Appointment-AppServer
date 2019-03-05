<?php
/**
*   ApptDeleteAction.php
*   - delete endpoint for Appointment applicaiton
*   - id field required
*   - return deleted id in id property of json response object
*
*   Unit test:
*       ./vendor/bin/phpunit --bootstrap vendor/autoload.php test/AppTest/Action/ApptDeleteActionTest.php
*
*   URL:
*      http://localhost:8080/appt/delete?id=4
*
*   Sample SQL:
*       Delete from appts Where id=999
*
*   Sample response object:
*       {
*        "api":"appt.update",
*        "error":false,
*        "id":"20",
*        "msg":"Record deleted",
*        "data":null
*       }
*/
namespace App\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

use App\Action\AppActionBase;
use App\DB\DB;

class ApptDeleteAction extends ApptActionBase implements ServerMiddlewareInterface
{
    public function __construct($debug = false)
    {
        // parent::__construct($debug);
        parent::__construct(true);
    }
    public function process(ServerRequestInterface $request, DelegateInterface $delegate):JsonResponse
    {
        $params = $request->getQueryParams();
        $this->log("ApptDeleteAction.process(): ".json_encode($params), 0);

        $msg = "Ready!";
        $data = null;
        $error = false;
        $id = null;
        $sql = 'Delete from appts ';

        //get and validate params
        $id = $this->filterVar('id', $params);
        if ($id == null) {
            $this->addValidationError('id field is required to delete records.');
        }

        //is there a validation error?
        if ($this->validationErrors !== null) {
            $obj = ["api" => 'appt.delete', "msg" => $this->validationErrors,
                "data" => null, "error" => true, 'id' => null];
            return new JsonResponse($obj);
        }
        $sql .= " Where id={$id}";

        try {
            $this->dbInit(false);
            $result = $this->db->exec($sql);
            if ($result === 1) {        //one record deleted
                $msg = "Record deleted";
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
            "api" => 'appt.delete',
            "error" => $error,
            "id" => $id,
            "msg" => $msg,
            "data" => $data
        ];
        return new JsonResponse($obj);
    }
}
