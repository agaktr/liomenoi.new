<?php

namespace App\Service;

use DOMDocument;
use DOMXPath;
use ErrorException;

class ScrapperService
{

    private array $urls;
    private array $urlContent = [];
    private array $scrappedContent = [];
    private array $performance = [];

    /**
     * @return array
     */
    public function getPerformance(): array
    {
        return $this->performance;
    }

    /**
     * @return array
     */
    public function getUrls(): array
    {
        return $this->urls;
    }

    /**
     */
    public function setUrls($urls): void
    {
        $this->urls = $urls;
    }

    /**
     * @return array
     */
    public function getUrlContent(): array
    {
        return $this->urlContent;
    }

    /**
     * @return array
     */
    public function getScrappedContent(): array
    {
        return $this->scrappedContent;
    }

    public function initSlugs()
    {
        unset($this->urlContent);
        unset($this->scrappedContent);
        $this->get();
        $this->scrapSlugs();
    }

    public function initObjects()
    {
        unset($this->urlContent);
        unset($this->scrappedContent);
        $this->get();
        $this->scrapObjects();
    }

    private function get()
    {

        $start = microtime(true);

        // array of curl handles
        $multiCurl = array();

        // multi handle
        $mh = curl_multi_init();

        $config['useragent'] = 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0';

        foreach ($this->urls as $id => $url) {
            $multiCurl[$id] = curl_init();
            curl_setopt($multiCurl[$id], CURLOPT_URL, $url);
            curl_setopt($multiCurl[$id], CURLOPT_HEADER, 0);
            curl_setopt($multiCurl[$id], CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($multiCurl[$id], CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($multiCurl[$id], CURLOPT_USERAGENT, $config['useragent']);
            curl_multi_add_handle($mh, $multiCurl[$id]);
        }

        $index=null;
        do {
            curl_multi_exec($mh,$index);
        } while($index > 0);

        foreach($multiCurl as $id => $ch) {
            $this->urlContent[$id] = curl_multi_getcontent($ch);
            curl_multi_remove_handle($mh, $ch);
        }

        // close
        curl_multi_close($mh);

        $this->performance['curl'] = microtime(true) - $start;
    }

    private function scrapSlugs()
    {

        $start = microtime(true);

        $index = 0;

        foreach ($this->urlContent as $id=>$content) {

            $dom = new DomDocument();
            @$dom->loadHTML($content);

            $finder = new DomXPath($dom);
            $classname="browse-movie-bottom";
            $elements = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");

            //foreach element in the node list
            foreach ($elements as $k=>$element) {

                $tmpDom = new DomDocument();
                $tmpDom->appendChild($tmpDom->importNode($element, true));
                $tmpFinder = new DomXPath($tmpDom);

                $titleClassname="browse-movie-title";
                $titleElement = $tmpFinder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $titleClassname ')]")->item(0);

                $yearClassname="browse-movie-year";
                $yearElement = $tmpFinder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $yearClassname ')]")->item(0);

                $this->scrappedContent[$index]['title'] = $titleElement->nodeValue;
                $this->scrappedContent[$index]['slug'] = $titleElement->getAttribute('href');
                $this->scrappedContent[$index]['year'] = $yearElement->nodeValue;

                $index++;
            }
        }

        $this->performance['scrap'] = microtime(true) - $start;
    }

    private function scrapObjects()
    {

        $start = microtime(true);

//
//die();
        foreach ($this->urlContent as $id=>$content) {

            var_dump($id);
            var_dump($this->urls[$id]);

            if ($content === '') {
               throw new ErrorException($id);
            }

            $dom = new DomDocument();
            @$dom->loadHTML($content);

            //imdb
//            $element = $this->getElementByClass($dom, 'rating-row',true);
            $element = $this->getRating($dom, 'IMDb Rating',true);
            $this->scrappedContent[$id]['imdb'] = $element->getElementsByTagName('a')[0]->getAttribute('href');

            //torrents
            $torrentElements = $this->getElementByClass($dom, 'modal-torrent');

            foreach ($torrentElements as $k=>$torrentElement) {

//                $tmpElDom = new DomDocument();
//                $tmpElDom->appendChild($tmpElDom->importNode($torrentElement, true));
                $tmpElFinder = new DomXPath($torrentElement);

                $qualityClassname="modal-quality";
                $qualityElement = $tmpElFinder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $qualityClassname ')]")->item(0);

                $qualitySizeClassname="quality-size";
                $qualitySizeElements = $tmpElFinder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $qualitySizeClassname ')]");

                $magnetClassname="magnet-download";
                $magnetElement = $tmpElFinder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $magnetClassname ')]")->item(0);
var_dump($magnetElement);
                $this->scrappedContent[$id]['magnet'][$k]['quality'] = trim($qualityElement->nodeValue);
                $this->scrappedContent[$id]['magnet'][$k]['type'] = trim($qualitySizeElements->item(0)->nodeValue);
                $this->scrappedContent[$id]['magnet'][$k]['size'] = trim($qualitySizeElements->item(1)->nodeValue);
                $this->scrappedContent[$id]['magnet'][$k]['magnet'] = $magnetElement->getAttribute('href');

            }
        }

        $this->performance['scrap'] = microtime(true) - $start;
    }

    public function getElementByClass($dom, $classname, $single = false)
    {
        $finder = new DomXPath($dom);
        $elements = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");
        if ($single) {
            $newDom = new DomDocument();
            $newDom->appendChild($newDom->importNode($elements->item(0), true));
            return $newDom;
        }

        $return = [];
        foreach ($elements as $k=>$element) {
            $newDom = new DomDocument();
            $newDom->appendChild($newDom->importNode($element, true));
            $return[$k] = $newDom;
        }

        return $return;
    }

    public function getRating($dom, $titleName, $single = false)
    {
        $finder = new DomXPath($dom);
        $elements = $finder->query("//*[contains(concat(' ', normalize-space(@title), ' '), ' $titleName ')]");

        if ($single) {
            $newDom = new DomDocument();

            if (null === $elements->item(0)) {
                throw new \Exception('No rating found');
            }

            $newDom->appendChild($newDom->importNode($elements->item(0), true));
            return $newDom;
        }

        $return = [];
        foreach ($elements as $k=>$element) {
            $newDom = new DomDocument();
            $newDom->appendChild($newDom->importNode($element, true));
            $return[$k] = $newDom;
        }

        return $return;
    }
}