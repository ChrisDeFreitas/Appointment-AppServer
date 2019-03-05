<?php
/**
*   ApptDeleteActionTest.php
*   - test delete endpoint for Appointment applicaiton
*
*/

namespace AppTest\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;

use App\Action\ApptDeleteAction;
use App\DB\DB;

$debug = true;     //write messages to stderr

class ApptDeleteActionTest extends TestCase
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
    public function testDelete()
    {
        global $debug;

        //create record to delete
        $db = new DB(false);
        $db->init('./data/appts.sqlite');
        $result = $db->exec("Insert Into appts (patient, reason, starttime, endtime) "
                    ."Values ('Mama Cass', 'weepy eyes', 1546300800 , 1546302600)");
        $id = $db->lastIdCreated();

        //create request object
        $obj = $this->makeRequestObj([
            "id" => $id
        ]);

        $action = new ApptDeleteAction($debug);
        $response = $action->process(
            $obj,
            $this->prophesize(DelegateInterface::class)->reveal()
        );

        $this->assertInstanceOf(JsonResponse::class, $response);
        error_log($response->getBody(), 0);

        $obj = json_decode((string) $response->getBody());
        $this->assertTrue( is_object($obj), "Response decodes to object." );

        $this->assertTrue( property_exists($obj, 'error'), "Response object has error property." );
        $this->assertTrue( $obj->error === false, "Verify no errors reported." );

        $this->assertTrue( property_exists($obj, 'id'), "Response object has id property." );
        $this->assertTrue( $id == $obj->id, "id values match." );

        //verify record deleted
        $recs = $db->query("Select * from appts where patient = 'Mama Cass'");
        $this->assertTrue( 0 == sizeof($recs), "Verify record deleted." );
    }
}
