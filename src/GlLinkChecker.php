<?php
/**
 * Main Class
 *
 * PHP version 5.4
 *
 * @category  GLICER
 * @package   GlLinkChecker
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
     * @var array|null $internalurls
     */
    private $internalurls;

    /**
     *
     */
    public function __construct($rooturl = null, array $internalurls = null)
    {
        $this->client = new Client([
            'base_uri' => $rooturl,
            'verify'   => false,
            'defaults' => [
                'headers' => [
                    'User-Agent'      => 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:36.0) Gecko/20100101 Firefox/36.0',
                    'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'fr,fr-FR;q=0.8,en-US;q=0.5,en;q=0.3',
                    'Accept-Encoding' => 'gzip, deflate'
                ]
            ]
        ]);
        $this->internalurls = $internalurls;
    }

    /**
     * get all links in an object
     *
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
     * get all links in a json
     *
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
     * check links in a sitemap
     *
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
     * check http error status code
     *
     * @param array $result
     * @param array $urls
     * @param int   $statuscode
     */
    private function checkStatus(array &$result, array $urls, $statuscode) {
        foreach ($urls as $url) {
            $response = $this->client->get($url, ['exceptions' => false]);
            if ($response->getStatusCode() != $statuscode) {
                $result[$statuscode]["error"][] = $url;
            } else {
                $result[$statuscode]["ok"][] = $url;
            }
        }
    }
    
    /**
     * check 403 and 404 errors
     *
     * @param array $urlerrors
     * @param array $urlforbiddens
     *
     * @return string
     */
    public function checkErrors(array $urlerrors, array $urlforbiddens)
    {
        $result = [];

        $this->checkStatus($result,$urlerrors,404);
        $this->checkStatus($result,$urlforbiddens, 403);

        return $result;
    }

    /**
     * check links in robots.txt and sitemap
     *
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

        return $result;
    }
    
    /**
     * check links in any text file
     *
     * @return array
     * @throws \Exception
     */
    public function getLinksFromMarkdown($markdownContent)
    {
        $pattern = '/\[.+\]\((https?:\/\/\S+)\)/';
        
        if($num_found = preg_match_all($pattern, $markdownContent, $out)) return $out[1];
        else return [];
    }


    /**
     * check links in html and json files
     *
     * @param Finder   $files
     * @param callable $checkstart
     * @param callable $checking
     * @param callable $checkend
     *
     * @throws \Exception
     * @return GlLinkCheckerError[]
     */
    public function checkFiles(Finder $files, callable $checkstart, callable $checking, callable $checkend, Array $criterias = ['lowercase', 'endslash', 'absolute'])
    {
        $linksByFile = [];
        /**
         * @var SplFileInfo $file
         */
        foreach ($files as $file) {
            $inner   = file_get_contents($file->getRealPath());
            $keyname = $file->getRelativePathname();
            $extension = $file->getExtension();
            switch($extension){
                case "html":
                    $html = new GlHtml($inner);
                    $linksByFile[$keyname] = $html->getLinks();
                break;
                case "json":
                    $linksByFile[$keyname] = $this->getJsonLinks($inner);
                break;
                case "md":
                    $linksByFile[$keyname] = $this->getLinksFromMarkdown($inner);
                break;
                default:
                    throw new \Exception("Extension unknown : " . $keyname);
                break;
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
                $gllink->check($criterias);
            }

            $gllink->check(['exist']);
            $result[] = $gllink;
        }
        $checkend();

        return $result;
    }
}
