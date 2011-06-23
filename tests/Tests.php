<?php
 
class Tests
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('PHPUnit Framework');
 
        $suite->addTestFile('CMDTest.php');
        $suite->addTestFile('PIOTest.php');
        $suite->addTestFile('PRSTest.php');
        $suite->addTestFile('PSOMapTest.php');
        $suite->addTestFile('PSOStreamTest.php');
        $suite->addTestFile('PSOTest.php');
        $suite->addTestFile('PSOVectorTest.php');
//        $suite->addTestFile('DATTest.php');
//        $suite->addTestFile('TPLTest.php');

        return $suite;
    }
}
