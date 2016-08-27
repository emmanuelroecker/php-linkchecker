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
use GlLinkChecker\GlLinkCheckerError;
use GlLinkChecker\GlLinkCheckerReport;
use Symfony\Component\Finder\Finder;

/**
 * @covers        \GlLinkChecker\GlLinkChecker
 * @covers        \GlLinkChecker\GlLinkCheckerError
 * @covers        \GlLinkChecker\GlLinkCheckerReport
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
                ['ok' => ['/img/', '/download/']],
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

    private function validatelink($link, $links, $result, array $errorarray)
    {
        $key = array_search($link, $links);
        if ($key === false) {
            $this->fail($link . " - " . var_export($links, TRUE));
        }
        $this->assertEquals(
             $errorarray,$result[$key]->getErrorArray(),$link
        );
    }

    public function testJson() 
    {
        $finder = new Finder();
        $files = $finder->files()->in('./tests/json')->name("*.json");
        
        $linkChecker = new GlLinkChecker();
        $result      = $linkChecker->checkFiles(
                                   $files,
                                       function () {
                                       },
                                       function () {
                                       },
                                       function () {
                                       }
        );
        $this->assertEquals(3, count($result));

        $links = [];
        foreach ($result as $link) {
            $links[] = $link->getLink();
        }

        $this->validatelink("http://dev.glicer.com/", $links,$result, ['absolute' => true, 'lowercase' => true, 'exist' => true, 'notendslash' => true]);
        $this->validatelink("http://lyon.glicer.com/", $links, $result, ['absolute' => true, 'lowercase' => true, 'exist' => true, 'notendslash' => true]);
        $this->validatelink("http://dev.glicer.com/section/probleme-solution/prefixer-automatiquement-css.html", $links, $result, ['absolute' => true, 'lowercase' => true, 'exist' => true, 'notendslash' => true]);
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

        $links = [];
        foreach ($result as $link) {
            $links[] = $link->getLink();
        }

        $this->validatelink("/section/probleme-solution/compresser-css-html-js.html", $links, $result,['absolute' => true, 'lowercase' => true, 'exist' => true, 'notendslash' => true]);
        $this->validatelink("http://dev.glicer.com/", $links,$result, ['absolute' => true, 'lowercase' => true, 'exist' => true, 'notendslash' => true]);
        $this->validatelink("http://stop.glicer.com/no-exist.html", $links, $result, ['absolute' => true, 'lowercase' => true, 'exist' => false, 'notendslash' => true]);
        $this->validatelink("/index.html", $links, $result, ['absolute' => true, 'lowercase' => true, 'exist' => true, 'notendslash' => true]);
        $this->validatelink("http://lyon.glicer.com/", $links, $result, ['absolute' => true, 'lowercase' => true, 'exist' => true, 'notendslash' => true]);
        $this->validatelink("http://dev.glicer.com/section/probleme-solution/prefixer-automatiquement-css.html", $links, $result, ['absolute' => true, 'lowercase' => true, 'exist' => true, 'notendslash' => true]);
    }
    
    public function testReport()
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

        //sort link by name
        usort($result,function(GlLinkCheckerError $linkA,GlLinkCheckerError $linkB) {
                return strcmp($linkA->getLink(), $linkB->getlink());
            });
        
        $filereport = GlLinkCheckerReport::toTmpHtml('testReport',$result);
        
        $report = file_get_contents($filereport);
        $reportexpected = file_get_contents(__DIR__ . '/expectedReport.html');
        
        $this->assertEquals($reportexpected,$report);
    }
}