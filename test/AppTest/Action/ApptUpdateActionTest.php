<?php
/**
*   ApptUpdateActionTest.php
*   - test update endpoint for Appointment applicaiton
*
*/

namespace AppTest\Action;

use Interop\Http\ServerMiddleware\DelegateInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;

use DateTime;
use DateInterval;

use App\Action\ApptUpdateAction;
use App\DB\DB;

$debug = false;     //write messages to stderr

class ApptUpdateActionTest extends TestCase
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
    public function testUpdate()
    {
        global $debug;

        //create request object
        $id = 1;
        $dt = new DateTime();
        $starttime = $dt->format('c');
        $dt->add(new DateInterval('PT30M'));    //add 30 minutes
        $endtime = $dt->format('c');
        $obj = $this->makeRequestObj([
            "id" => $id,
            "starttime" => $starttime,
            "endtime" =>   $endtime,
            "reason" => "Test \" <script>alert(1)</script> Test"
        ]);

        $action = new ApptUpdateAction($debug);
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
        $this->assertTrue( $id == $obj->id, "id values match, test1." );
        $this->assertTrue( $obj->id == $obj->data[0]->id, "id values match, test2." );

        $this->assertTrue( property_exists($obj, 'data'), "Response object has data property." );
        $this->assertTrue( 1 === sizeof($obj->data), "One record returned." );

        $t1 = date_create($starttime)->format('U');
        $t2 = date_create($obj->data[0]->starttime)->format('U');
        $this->assertTrue( $t1 == $t2, "startime values match." );

        $t1 = date_create($endtime)->format('U');
        $t2 = date_create($obj->data[0]->endtime)->format('U');
        $this->assertTrue( $t1 == $t2, "endtime values match." );

        $ss = $obj->data[0]->reason;
        $this->assertTrue( strpos($ss, 'script') === false, "Does reason contain the script tag?" );
    }
}
