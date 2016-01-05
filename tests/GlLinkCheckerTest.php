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

use GlLinkChecker\GlLinkChecker;
use Symfony\Component\Finder\Finder;

/**
 * @covers        \GlLinkChecker\GlLinkChecker
 * @backupGlobals disabled
 */
class GlLinkCheckerTest extends \PHPUnit_Framework_TestCase
{
    public function testRobotsSitemap()
    {
        $linkChecker = new GlLinkChecker('http://' . WEB_SERVER_HOST . ':' . WEB_SERVER_PORT);
        $result      = $linkChecker->checkRobotsSitemap();
        $expected    = [
            'disallow' =>
                ['error' => ['/img/', '/download/']],
            'sitemap'  =>
                [
                    'ok' => [
                        '/sitemap.xml' =>
                            [
                                'ok' =>
                                    [
                                        '/index.html',
                                        '/section/probleme-solution/compresser-css-html-js.html'
                                    ]
                            ]
                    ]
                ]
        ];
        $this->assertEquals($expected, $result);
    }

    public function testErrors()
    {
        $linkChecker = new GlLinkChecker('http://' . WEB_SERVER_HOST . ':' . WEB_SERVER_PORT);
        $result      = $linkChecker->checkErrors(['/nothing.html'], ['/test.html']);

        $expected = [
            '404' =>
                ['ok' => ['/nothing.html']],
            '403' =>
                ['error' => ['/test.html']]
        ];
        $this->assertEquals($expected, $result);
    }

    public function testLinks()
    {
        $finder = new Finder();
        $files  = $finder->files()->in('./tests/site1')->name("*.html");

        $linkChecker = new GlLinkChecker('http://' . WEB_SERVER_HOST . ':' . WEB_SERVER_PORT);
        $result      = $linkChecker->checkFiles(
                                   $files,
                                       function () {
                                       },
                                       function () {
                                       },
                                       function () {
                                       }
        );

        $this->assertEquals(6, count($result));
        $this->assertEquals($result[0]->getLink(), "/section/probleme-solution/compresser-css-html-js.html");
        $this->assertEquals(
             $result[0]->getErrorArray(),
                 ['absolute' => true, 'lowercase' => true, 'exist' => true, 'notendslash' => true]
        );

        $this->assertEquals($result[1]->getLink(), "http://dev.glicer.com/");
        $this->assertEquals(
             $result[1]->getErrorArray(),
                 ['absolute' => true, 'lowercase' => true, 'exist' => true, 'notendslash' => true]
        );

        $this->assertEquals($result[2]->getLink(), "http://stop.glicer.com/no-exist.html");
        $this->assertEquals(
             $result[2]->getErrorArray(),
                 ['absolute' => true, 'lowercase' => true, 'exist' => false, 'notendslash' => true]
        );

        $this->assertEquals($result[3]->getLink(), "/index.html");
        $this->assertEquals(
             $result[3]->getErrorArray(),
                 ['absolute' => true, 'lowercase' => true, 'exist' => true, 'notendslash' => true]
        );

        $this->assertEquals($result[4]->getLink(), "http://lyon.glicer.com/");
        $this->assertEquals(
             $result[4]->getErrorArray(),
                 ['absolute' => true, 'lowercase' => true, 'exist' => true, 'notendslash' => true]
        );

        $this->assertEquals($result[5]->getLink(), "http://dev.glicer.com/section/probleme-solution/prefixer-automatiquement-css.html");
        $this->assertEquals(
             $result[5]->getErrorArray(),
                 ['absolute' => true, 'lowercase' => true, 'exist' => true, 'notendslash' => true]
        );
    }
}