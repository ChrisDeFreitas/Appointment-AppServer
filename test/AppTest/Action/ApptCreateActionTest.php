<?php
/**
*   ApptCreateActionTest.php
*   - test create endpoint for Appointment applicaiton
*
*/

namespace AppTest\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;

use App\Action\ApptCreateAction;
use App\DB\DB;

$debug = false;     //write messages to stderr

class ApptCreateActionTest extends TestCase
{
    protected function makeRequestObj($queryParams = [])
    {
        $obj = new ServerRequest(
            [],         //array $serverParams =
            [],         //array $uploadedFiles =
            null,       //$uri =
            null,       //$method =
            'php://input',  //$body =
            [],         //array $headers =
            [],         //array $cookies =
            $queryParams,         //array $queryParams =
            null,       //$parsedBody =
            '1.1'       //$protocol =
        );
        //error_log( 'Request object query params: '.json_encode($obj->getQueryParams()), 0);
        return $obj;
    }
    public function testCreate()
    {
        global $debug;

        //create request object
        $obj = $this->makeRequestObj([
            "patient" => "Mama Cass",
            "reason" => "weepy eyes",
            "starttime" => "20190101T00:00",
            "endtime" =>   "20190101T00:30"
        ]);

        $action = new ApptCreateAction($debug);
        $response = $action->process(
            //$this->prophesize(ServerRequestInterface::class)->reveal(),
            $obj,
            $this->prophesize(DelegateInterface::class)->reveal()
        );

        $this->assertInstanceOf(JsonResponse::class, $response);
        //error_log($response->getBody(), 0);

        $obj = json_decode((string) $response->getBody());
        $this->assertTrue( is_object($obj), "Response decodes to object." );

        $this->assertTrue( property_exists($obj, 'error'), "Response object has error property." );
        $this->assertTrue( $obj->error === false, "Verify no errors reported." );

        $this->assertTrue( property_exists($obj, 'id'), "Response object has id property." );
        $this->assertTrue( $obj->data[0]->id == $obj->id, "id values match." );

        $this->assertTrue( property_exists($obj, 'data'), "Response object has data property." );
        $this->assertTrue( 1 === sizeof($obj->data), "One record returned." );

        //cleanup record created
        $db = new DB(false);
        $db->init('./data/appts.sqlite');
        $db->exec("Delete from appts where patient = 'Mama Cass'");
    }
}
