<?php
/**
 * Main Class
 *
 * PHP version 5.4
 *
 * @category  GLICER
 * @package   GlHtml
 * @author    Emmanuel ROECKER
 * @author    Rym BOUCHAGOUR
 * @copyright 2015 GLICER
 * @license   MIT
 * @link      http://dev.glicer.com/
 *
 * Created : 10/03/15
 * File : GlLinkChecker.php
 *
 */
namespace GlLinkChecker;

use GlHtml\GlHtml;
use GuzzleHttp\Client;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Console\Output\StreamOutput;

/**
 * Class GlLinkChecker
 * @package GLLinkChecker
 */
class GlLinkChecker
{
    /**
     * @var \GuzzleHttp\Client
     */
    private $client;

    /**
     * @var array $internalurls
     */
    private $internalurls;

    /**
     *
     */
    public function __construct($rooturl = null, array $internalurls = null)
    {
        $this->client = new Client([
            'base_url' => $rooturl,
            'defaults' => [
                'headers' => [
                    'User-Agent'      => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:36.0) Gecko/20100101 Firefox/36.0',
                    'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'fr,fr-FR;q=0.8,en-US;q=0.5,en;q=0.3',
                    'Accept-Encoding' => 'gzip, deflate'
                ]
            ]
        ]);
        $this->client->setDefaultOption('verify', false);
        $this->internalurls = $internalurls;
    }


    /**
     * @param string $text
     * @param array  $links
     *
     * @return array
     */
    private function getLinks($text, &$links)
    {
        $regexUrl = '/[">\s]+((http|https|ftp|ftps)\:\/\/(.*?))["<\s]+/';
        $urls     = null;
        if (preg_match_all($regexUrl, $text, $urls) > 0) {
            $matches = $urls[1];
            foreach ($matches as $url) {
                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    $links[$url] = $url;
                }
            }
        }
    }


    /**
     * @param       $obj
     * @param array $links
     */
    private function searchInArray($obj, array &$links)
    {
        foreach ($obj as $key => $elem) {
            if (is_string($elem)) {
                if (preg_match("/^(http|https|ftp|ftps).*$/", $elem)) {
                    if (filter_var($elem, FILTER_VALIDATE_URL)) {
                        $links[$elem] = $elem;
                    }
                }
            } else {
                if (is_array($elem)) {
                    $this->searchInArray($elem, $links);
                }
            }
        }
    }

    /**
     * @param string $json
     *
     * @return array
     */
    private function getJsonLinks($json)
    {
        $obj   = json_decode($json, true);
        $links = [];
        $this->searchInArray($obj, $links);

        return $links;
    }


    /**
     * @param string $sitemap
     *
     * @return array
     * @throws \Exception
     */
    private function checkSitemap($sitemap)
    {
        $xml     = new GlHtml($sitemap);
        $listloc = $xml->get("loc");
        $result  = [];
        foreach ($listloc as $loc) {
            $response = $this->client->get($loc->getText(), ['exceptions' => false]);
            if ($response->getStatusCode() != 200) {
                $result['error'][] = $loc->getText();
            } else {
                $result['ok'][] = $loc->getText();
            }
        }

        return $result;
    }

    /**
     * @param array $urlforbiddens
     *
     * @return string
     */
    public function checkErrors(array $urlforbiddens)
    {
        $result   = [];
        $test404  = "/" . uniqid() . ".html";
        $response = $this->client->get($test404, ['exceptions' => false]);
        if ($response->getStatusCode() != 404) {
            $result["404"]["error"][] = $test404;
        } else {
            $result["404"]["ok"][] = $test404;
        }

        foreach ($urlforbiddens as $urlforbidden) {
            $response = $this->client->get($urlforbidden, ['exceptions' => false]);
            if ($response->getStatusCode() != 403) {
                $result["403"]["error"][] = $urlforbidden;
            } else {
                $result["403"]["ok"][] = $urlforbidden;
            }
        }

        $resultfile   = sys_get_temp_dir() . "/" . uniqid("errorcheck") . ".txt";
        $resultoutput = new StreamOutput(fopen($resultfile, 'a', false));
        $resultoutput->write("\xEF\xBB\xBF"); //add ut8 bom to txt file
        $resultoutput->write(print_r($result, true));

        return $resultfile;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function checkRobotsSitemap()
    {
        $response = $this->client->get("/robots.txt");
        if ($response->getStatusCode() != 200) {
            throw new \Exception("Cannot find robots.txt");
        }

        $robotstxt = $response->getBody()->getContents();
        $robotstxt = explode("\n", $robotstxt);
        $result    = [];
        foreach ($robotstxt as $line) {
            if (preg_match('/^\s*Sitemap:(.*)/i', $line, $match)) {
                $urlsitemap = trim($match[1]);
                $response   = $this->client->get($urlsitemap, ['exceptions' => false]);
                if ($response->getStatusCode() != 200) {
                    $result['sitemap']['error'][] = $urlsitemap;
                } else {

                    $result['sitemap']['ok'][$urlsitemap] = $this->checkSitemap($response->getBody()->getContents());
                }
            }

            if (preg_match('/^\s*Disallow:(.*)/i', $line, $match)) {
                $urldisallow = trim($match[1]);
                $response    = $this->client->get($urldisallow, ['exceptions' => false]);
                if (($response->getStatusCode() != 200) && ($response->getStatusCode() != 403)) {
                    $result['disallow']['error'][] = $urldisallow;
                } else {
                    $result['disallow']['ok'][] = $urldisallow;
                }
            }
        }

        $resultfile   = sys_get_temp_dir() . "/" . uniqid("robotcheck") . ".txt";
        $resultoutput = new StreamOutput(fopen($resultfile, 'a', false));
        $resultoutput->write("\xEF\xBB\xBF"); //add ut8 bom to txt file
        $resultoutput->write(print_r($result, true));

        return $resultfile;
    }


    /**
     * @param string   $title
     * @param Finder   $files
     * @param callable $checkstart
     * @param callable $checking
     * @param callable $checkend
     *
     * @throws \Exception
     * @return GlLinkCheckerError[]
     */
    public function checkFiles($title, Finder $files, callable $checkstart, callable $checking, callable $checkend)
    {
        $linksByFile = [];
        /**
         * @var SplFileInfo $file
         */
        foreach ($files as $file) {
            $inner   = file_get_contents($file->getRealPath());
            $keyname = $file->getRelativePathname();
            if ($file->getExtension() == 'html') {
                $html                  = new GlHtml($inner);
                $linksByFile[$keyname] = $html->getLinks();
            } else {
                if ($file->getExtension() == 'json') {
                    $linksByFile[$keyname] = $this->getJsonLinks($inner);
                } else {
                    throw new \Exception("Extension unknown : " . $keyname);
                }
            }
        }

        //reverse $linksByFile
        $links = [];
        foreach ($linksByFile as $filename => $filelinks) {
            foreach ($filelinks as $filelink) {
                $links[$filelink][] = $filename;
            }
        }

        $checkstart(count($links));
        $result = [];
        foreach ($links as $link => $files) {
            $checking($link, $files);

            $gllink = new GlLinkCheckerError($this->client, $link, $files);

            if ($gllink->isInternal($this->internalurls)) {
                $gllink->check(['lowercase', 'endslash', 'absolute']);
            }

            $gllink->check(['exist']);
            $result[] = $gllink;
        }

        $resultfile = sys_get_temp_dir() . "/" . uniqid("linkcheck") . ".html";
        $html       = $this->exportToHtml($title, $result);
        file_put_contents($resultfile, $html);
        $checkend();

        return $resultfile;
    }

    /**
     * @param string               $title
     * @param GlLinkCheckerError[] $links
     *
     * @return string
     */
    private function exportToHtml($title, $links)
    {
        $html = '<!DOCTYPE HTML>';
        $html .= '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
        $html .= '<title>' . $title . '</title>';
        $html .= '<style>';
        $html .= '.error {  color: red  }';

        $html .= '.tooltip
                    {
                        display: inline;
                        position: relative;
                        text-decoration: none;
                        top: 0px;
                        left: 0px;
                    }';

        $html .= '.tooltip:hover:after
                    {
                        background: #333;
                        background: rgba(0,0,0,.8);
                        border-radius: 5px;
                        top: -5px;
                        color: #fff;
                        content: attr(data-tooltip);
                        left: 160px;
                        padding: 5px 15px;
                        position: absolute;
                        z-index: 98;
                        width: 150px;
                    }';
        $html .= '</style>';
        $html .= '</head><body>';

        /**
         * @var GlLinkCheckerError $link
         */
        foreach ($links as $link) {
            $html .= '<div class="link">';
            $url    = $link->getLink();
            $files  = " -> " . implode(" ", $link->getFiles());
            $errors = $link->getErrors();

            if (count($errors) <= 0) {
                $html .= '<a href="' . $url . '">' . $url . '</a>' . $files;
                $html .= '</div>';
                continue;
            }

            $tooltip = implode(' ', $errors);
            $html .= '<a href="' . $url . '" class="error tooltip" data-tooltip="' . $tooltip . '">' . $url . '</a>' . $link->getStatusCode(
                ) . $files;
            $html .= '</div>';
        }
        $html .= '<br><br><br></body></html>';

        return $html;
    }
} 