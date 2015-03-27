<?php
/**
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
 * File : GlLinkCheckerError.php
 *
 */

namespace GlLinkChecker;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Ring\Exception\RingException;

/**
 * Class GlLinkCheckerUrl
 * @package GLLinkChecker
 */
class GlLinkCheckerError
{
    /**
     * @var \GuzzleHttp\Client $client
     */
    private $client;

    /**
     * @var string
     */
    private $link;

    /**
     * @var array
     */
    private $url;

    /**
     * @var array
     */
    private $files;

    /**
     * @var int
     */
    private $statuscode = 0;

    /**
     * @var bool
     */
    private $isAbsolute = true;

    /**
     * @var bool
     */
    private $isExist = true;


    /**
     * @var bool
     */
    private $isLowerCase = true;

    /**
     * @var bool
     */
    private $isNotEndSlash = true;

    /**
     * Client $client
     * string $link
     * array $files
     */
    public function __construct(Client $client, $link, $files)
    {
        $url = parse_url($link);
        if ($url === false) {
            throw new \Exception("Cannot parse link : " . $link);
        }

        $this->client = $client;
        $this->url    = $url;
        $this->link   = $link;
        $this->files  = $files;
    }

    /**
     * @return bool
     */
    private function checkexisthead()
    {
        try {
            $response         = $this->client->head($this->link);
            $this->statuscode = $response->getStatusCode();
            $this->isExist    = (($this->statuscode == 200) || ($this->statuscode == 204));

            return $this->isExist;
        } catch (ClientException $e) {
            $this->statuscode = $e->getCode();
        } catch (RequestException $e) {

        } catch (RingException $e) {

        }

        $this->isExist = false;

        return false;
    }

    /**
     * @return bool
     */
    private function checkexistget()
    {
        try {
            $response         = $this->client->get($this->link);
            $this->statuscode = $response->getStatusCode();
            $this->isExist    = (($this->statuscode == 200) || ($this->statuscode == 204));

            return $this->isExist;
        } catch (ClientException $e) {
            $this->statuscode = $e->getCode();
        } catch (RequestException $e) {
        } catch (RingException $e) {
        }

        $this->isExist = false;

        return false;
    }

    /**
     *
     */
    private function checkexist()
    {
        if ($this->checkexisthead()) {
            return true;
        }
        if ($this->checkexistget()) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    private function checkendslash()
    {
        if (substr($this->link, -1) == '/') {
            $this->isNotEndSlash = true;

            return true;
        }

        if (isset($this->url['path']) && (strlen($this->url['path']) > 0)) {
            $extension = pathinfo($this->url['path'], PATHINFO_EXTENSION);
            if (isset($extension) && (strlen($extension) > 0)) {
                $this->isNotEndSlash = true;

                return true;
            }
        }

        $this->isNotEndSlash = false;

        return false;
    }

    /**
     * @return bool
     */
    private function checkabsolute()
    {
        if (isset($this->url['host']) && (strlen($this->url['host']) > 0)) {
            $this->isAbsolute = true;

            return true;
        }
        if (isset($this->url['path']) && (strpos($this->url['path'], "/") === 0)) {
            $this->isAbsolute = true;

            return true;
        }
        $this->isAbsolute = false;

        return false;
    }

    /**
     * @return bool
     */
    private function checklowercase()
    {
        $this->isLowerCase = ($this->link === strtolower($this->link));

        return $this->isLowerCase;
    }


    /**
     * @param array|null $internalurls
     *
     * @return bool
     */
    public function isInternal($internalurls)
    {
        if (!isset($internalurls)) {
            return true;
        }

        if (!isset($this->url['host']) || (strlen($this->url['host']) <= 0)) {
            return true;
        }

        foreach ($internalurls as $internalurl) {
            if (strpos($this->link, $internalurl) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $list
     *
     * @return bool
     */
    public function check(array $list)
    {
        $result = true;
        foreach ($list as $element) {
            $element = "check" . trim(strtolower($element));
            $result &= $this->$element();
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statuscode;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        $message = [];

        if (!($this->isAbsolute)) {
            $message[] = "Must be absolute (Sample : /article/index.html)";
        }

        if (!($this->isLowerCase)) {
            $message[] = "Must be in lowercase (Sample : http://www.example.com/index.html)";
        }

        if (!($this->isExist)) {
            $message[] = "Must exist (Http get error)";
        }

        if (!($this->isNotEndSlash)) {
            $message[] = "Must have a slash at the end (Sample : http://www.example.com/)";
        }

        return $message;
    }
}
