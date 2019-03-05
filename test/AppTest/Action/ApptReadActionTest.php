<?php
/**
*   ApptReadActionTest.php
*   - read endpoint for Appointment applicaiton
*
*/

namespace AppTest\Action;

use App\Action\ApptReadAction;
use Interop\Http\ServerMiddleware\DelegateInterface;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\ServerRequest;

class ApptReadActionTest extends TestCase
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

    public function testReadAll()
    {
        $action = new ApptReadAction();
        $response = $action->process(
            $this->prophesize(ServerRequestInterface::class)->reveal(),
            $this->prophesize(DelegateInterface::class)->reveal()
        );
        //error_log( $response->getBody(), 0);

        $this->assertInstanceOf(JsonResponse::class, $response);

        $obj = json_decode((string) $response->getBody());
        $this->assertTrue( is_object($obj), "Response decodes to object" );

        $this->assertTrue( property_exists($obj, 'error'), "Response object has error property." );
        $this->assertTrue( $obj->error === false, "Verify no errors reported." );

        $this->assertTrue( property_exists($obj, 'msg'), "Response object has msg property" );

        $this->assertTrue( property_exists($obj, 'data'), "Response object has data property" );
        $this->assertTrue( is_array($obj->data), "data property is an array" );
        $this->assertGreaterThan( 0, sizeof($obj->data), "data property has at least one record" );
    }
    public function testReadId()
    {
        $obj = $this->makeRequestObj(["id" => "1"]);

        $action = new ApptReadAction();
        $response = $action->process(
            //$this->prophesize(ServerRequestInterface::class)->reveal(),
            $obj,
            $this->prophesize(DelegateInterface::class)->reveal()
        );
        //error_log( $response->getBody(), 0);

        $this->assertInstanceOf(JsonResponse::class, $response);

        $obj = json_decode((string) $response->getBody());
        $this->assertTrue( is_object($obj), "Response decodes to object" );
        $this->assertEquals( 1, sizeof($obj->data), "data property has one record" );
        $this->assertTrue( $obj->data[0]->id == '1', "id property has correct value" );
    }
}
