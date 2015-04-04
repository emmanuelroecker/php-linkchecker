<?php
/**
 * PHP version 5.4
 *
 * Output link checker report in txt or html format
 *
 * @category  GLICER
 * @package   GlLinkChecker
 * @author    Emmanuel ROECKER
 * @author    Rym BOUCHAGOUR
 * @copyright 2015 GLICER
 * @license   MIT
 * @link      http://dev.glicer.com/
 *
 * Created : 04/04/15
 * File : GlLinkCheckerReport.php
 *
 */

namespace GlLinkChecker;

use Symfony\Component\Console\Output\StreamOutput;

class GlLinkCheckerReport
{

    /**
     * write array result in a temp txt file
     *
     * @param string $name
     * @param array  $result
     *
     * @return string
     */
    public static function toTmpTxt($name, $result)
    {
        $resultfile   = sys_get_temp_dir() . "/" . uniqid($name) . ".txt";
        $resultoutput = new StreamOutput(fopen($resultfile, 'a', false));
        $resultoutput->write("\xEF\xBB\xBF"); //add ut8 bom to txt file
        $resultoutput->write(print_r($result, true));

        return $resultfile;
    }

    /**
     * write links test result in a temp html file
     *
     * @param string               $name
     * @param GlLinkCheckerError[] $result
     *
     * @return string
     */
    public static function toTmpHtml($name, $result)
    {
        $resultfile = sys_get_temp_dir() . "/" . uniqid($name) . ".html";
        $html       = self::toHtml($name, $result);
        file_put_contents($resultfile, $html);

        return $resultfile;
    }

    /**
     * render report in html format
     *
     * @param string               $title
     * @param GlLinkCheckerError[] $links
     *
     * @return string
     */
    private static function toHtml($title,array $links)
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