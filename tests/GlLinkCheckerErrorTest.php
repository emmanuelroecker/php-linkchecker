<?php
/**
 * Test GlHtml
 *
 * PHP version 5.4
 *
 * @category  GLICER
 * @package   GlLinkChecker\Tests
 * @author    Emmanuel ROECKER
 * @author    Rym BOUCHAGOUR
 * @copyright 2015 GLICER
 * @license   MIT
 * @link      http://dev.glicer.com/
 *
 * Created : 04/04/15
 * File : GlLinkChecker.php
 *
 */
namespace GlLinkChecker\Tests;

use GlLinkChecker\GlLinkCheckerError;
use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;

/**
 * @covers        \GlLinkChecker\GlLinkCheckerError
 * @backupGlobals disabled
 */
class GlLinkCheckerErrorTest extends TestCase
{
    public function testConstruct()
    {
        $client = new Client();
        $linkerror = new GlLinkCheckerError($client,'http://dev.glicer.com',['file1','file2']);

        $this->assertEquals('http://dev.glicer.com', $linkerror->getLink());
        $this->assertEquals(['file1','file2'], $linkerror->getFiles());
    }

    public function testCheck()
    {
        $client = new Client();
        $linkerror = new GlLinkCheckerError($client, 'http://dev.glicer.com',['index.html']);

        $linkerror->check(['exist','endslash','absolute','lowercase']);
    }
}
