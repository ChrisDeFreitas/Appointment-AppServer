<?php
/**
*
*   DBTest.php
*   tests against src/App/src/DB/DB.pbp
*
*/
namespace AppTest\Data;

use App\DB\DB;
use PHPUnit\Framework\TestCase;

$debug = false;     //write messages to stderr


class DBTest extends TestCase
{
    protected $db = null;

    protected function dbInit()
    {
        global $debug;
        if($this->db !== null) {
            return true;
        }
        $this->db = new DB($debug);
        return $this->db->init('./data/appts.sqlite');
    }

    public function testInit()
    {
        $this->assertFileExists('./data/appts.sqlite', 'SQLite database file not found.');

        $result = $this->dbInit();
        $this->assertTrue($result);
    }
    public function testQuery()
    {
        $result = $this->dbInit();
        $this->assertTrue($result);

        $result = $this->db->query();
        //assertion fails with:  Error: Call to undefined method assertIsArray()
        //$this->assertIsArray($result);
        $this->assertGreaterThan(0, sizeof($result) );
        //print_r($result);
        if($this->db->errorFound() !== false){
            //assertion fails with:  Error: Call to undefined method assertIsString()
            //$this->assertIsString( $this->db->errorFound() );
            print("\nDB Error: ".$this->db->errorFound());
        }
    }
    public function testInsert()
    {
        $result = $this->dbInit();
        $this->assertTrue($result);

        $result = $this->db->exec("Insert into appts (patient, reason) Values ('Al Bundy', 'Hemorrhoids')");
        //assertion fails with:  Error: Call to undefined method assertIsInt()
        //$this->assertIsInt($result);
        $this->assertEquals(1, $result);
        if($this->db->errorFound() !== false){
            //assertion fails with:  Error: Call to undefined method assertIsString()
            //$this->assertIsString( $this->db->errorFound() );
            print("\nDB Error: ".$this->db->errorFound());
        }
    }
    public function testUpdate()
    {
        $result = $this->dbInit();
        $this->assertTrue($result);

        $result = $this->db->exec("Update appts set reason ='Extreme Hemorrhoids' where patient = 'Al Bundy'");
        //assertion fails with:  Error: Call to undefined method assertIsInt()
        //$this->assertIsInt($result);
        $this->assertGreaterThan(0, $result);
        if($this->db->errorFound() !== false){
            //assertion fails with:  Error: Call to undefined method assertIsString()
            //$this->assertIsString( $this->db->errorFound() );
            print("\nDB Error: ".$this->db->errorFound());
        }
    }
    public function testDelete()
    {
        $result = $this->dbInit();
        $this->assertTrue($result);

        $result = $this->db->exec("Delete from appts where patient = 'Al Bundy'");
        //assertion fails with:  Error: Call to undefined method assertIsInt()
        //$this->assertIsInt($result);
        $this->assertGreaterThan(0, $result);
        if($this->db->errorFound() !== false){
            //assertion fails with:  Error: Call to undefined method assertIsString()
            //$this->assertIsString( $this->db->errorFound() );
            print("\nDB Error: ".$this->db->errorFound());
        }
    }

}
