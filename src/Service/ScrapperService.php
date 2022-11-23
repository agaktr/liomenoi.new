<?php

namespace App\Service;

use App\Entity\Provider;
use DOMDocument;
use DOMXPath;
use ErrorException;
use Exception;

class ScrapperService
{

    private array $urls;
    private array $urlContent = [];
    private array $scrappedContent = [];
    private array $performance = [];
    private string $doing = 'Movie';
    private Provider $provider;

    /**
     * @return array
     */
    public function getPerformance(): array
    {
        return $this->performance;
    }
    public function setPerformance($performance): void
    {
        $this->performance = $performance;
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
     * @return Provider
     */
    public function getProvider(): Provider
    {
        return $this->provider;
    }

    /**
     * @param Provider $provider
     */
    public function setProvider(Provider $provider): void
    {
        $this->provider = $provider;
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

    public function getContent()
    {
        $this->performance = [];
        $this->urlContent = [];
        $this->get();
    }

    public function getScraps()
    {
        $this->scrappedContent = [];
        $this->scrap();
    }

    public function getProviderScraps()
    {
        $this->scrappedContent = [];
        $this->providerScrap();
    }

    /**
     * @return string
     */
    public function getDoing(): string
    {
        return $this->doing;
    }

    /**
     * @param string $doing
     */
    public function setDoing(string $doing): void
    {
        $this->doing = $doing;
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

    private function scrap()
    {

        $start = microtime(true);

        foreach ($this->urlContent as $id=>$content) {
            switch ($this->provider->getName()){

                case 'yts.do':
                    $this->YTSdoScrap($id,$content);
                    break;
                case 'ytstv.me':
                    $this->YTSTVmeScrap($id,$content);
                    break;
            }

        }

        $this->performance['scrap'] = microtime(true) - $start;
    }

    private function providerScrap()
    {

        $start = microtime(true);

        foreach ($this->urlContent as $id=>$content) {

            switch ($this->provider->getName()){

                case 'yts.do':
                    $this->YTSdoScrapProvider($content);
                    break;
                case 'ytstv.me':
                    $this->YTSTVmeScrapProvider($content);
                    break;
            }

        }

        $this->performance['scrap'] = microtime(true) - $start;
    }

    private function YTSTVmeScrap($id,$content){

        $dom = new DomDocument();
        @$dom->loadHTML($content);

        //imdb
        try {
            $element = $this->getElementByTitle($dom , 'IMDb Rating' , true);
            $this->scrappedContent[$id]['imdb'] = $element->getElementsByTagName('a')[0]->getAttribute('href');
        } catch (Exception $e) {
            $this->scrappedContent[$id]['imdb'] = null;
        }

        //type
        $this->scrappedContent[$id]['type'] = $this->doing;

        //torrents
        $torrentsWrapper = $dom->getElementById('list-dl');
        $newDom = new DomDocument();
        $newDom->appendChild($newDom->importNode($torrentsWrapper, true));
        $torrentElements = $this->getElementByClass($newDom, 'lnk-lnk');

        foreach ($torrentElements as $k=>$torrentDataElement) {
            var_dump($torrentDataElement);
            var_dump($torrentDataElement->childNodes[0]->getAttribute('href'));
            var_dump($torrentDataElement->childNodes[0]);
//            $tmpElFinder = new DomXPath($torrentElement);
//
//            $qualityClassname="modal-quality";
//            $qualityElement = $tmpElFinder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $qualityClassname ')]")->item(0);
//
//            $qualitySizeClassname="quality-size";
//            $qualitySizeElements = $tmpElFinder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $qualitySizeClassname ')]");
//
//            $magnetClassname="magnet-download";
//            $magnetElement = $tmpElFinder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $magnetClassname ')]")->item(0);

            $this->scrappedContent[$id]['magnet'][$k]['quality'] = trim($torrentDataElement->childNodes->item(2)->nodeValue);
            $this->scrappedContent[$id]['magnet'][$k]['type'] = trim($torrentDataElement->childNodes->item(2)->nodeValue);
            $this->scrappedContent[$id]['magnet'][$k]['size'] = trim('$qualitySizeElements->item(1)->nodeValue');
            $this->scrappedContent[$id]['magnet'][$k]['magnet'] = $torrentDataElement->getAttribute('href');
        }
    }

    private function YTSTVmeScrapProvider($content){

        $dom = new DomDocument();
        @$dom->loadHTML($content);

        $finder = new DomXPath($dom);
        $classname="ml-item";
        $elements = $finder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]");

        //foreach element in the node list
        foreach ($elements as $k=>$element) {

            $tmpDom = new DomDocument();
            $tmpDom->appendChild($tmpDom->importNode($element, true));
            $tmpFinder = new DomXPath($tmpDom);

            $classname="ml-mask";
            $linkElement = $tmpFinder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]")->item(0);

            $classname="mli-info";
            $titleElement = $tmpFinder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $classname ')]")->item(0);

            $year = $this->getStringBetween($titleElement->nodeValue, '(', ')');
            $title = str_replace(' ('.$year.')', '', $titleElement->nodeValue);

            if ($this->doing === 'Serie'){
                $year = str_replace('TV Series', '', $year);
            }

            $this->scrappedContent[] = [
                'title' => trim($title),
                'year' => trim($year),
                'type' => $this->doing,
                'slug' => str_replace($this->provider->getDomain(),'/',$linkElement->getAttribute('href')),
            ];
        }
    }

    private function YTSdoScrap($id,$content){

        $dom = new DomDocument();
        @$dom->loadHTML($content);

        //imdb
        try {
            $element = $this->getElementByTitle($dom , 'IMDb Rating' , true);
            $this->scrappedContent[$id]['imdb'] = $element->getElementsByTagName('a')[0]->getAttribute('href');
        } catch (Exception $e) {
            $this->scrappedContent[$id]['imdb'] = null;
        }

        //type
        $this->scrappedContent[$id]['type'] = $this->doing;

        //torrents
        $torrentElements = $this->getElementByClass($dom, 'modal-torrent');

        foreach ($torrentElements as $k=>$torrentElement) {

            $tmpElFinder = new DomXPath($torrentElement);

            $qualityClassname="modal-quality";
            $qualityElement = $tmpElFinder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $qualityClassname ')]")->item(0);

            $qualitySizeClassname="quality-size";
            $qualitySizeElements = $tmpElFinder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $qualitySizeClassname ')]");

            $magnetClassname="magnet-download";
            $magnetElement = $tmpElFinder->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' $magnetClassname ')]")->item(0);

            $this->scrappedContent[$id]['magnet'][$k]['quality'] = trim($qualityElement->nodeValue);
            $this->scrappedContent[$id]['magnet'][$k]['type'] = trim($qualitySizeElements->item(0)->nodeValue);
            $this->scrappedContent[$id]['magnet'][$k]['size'] = trim($qualitySizeElements->item(1)->nodeValue);
            $this->scrappedContent[$id]['magnet'][$k]['magnet'] = $magnetElement->getAttribute('href');
        }
    }

    private function YTSdoScrapProvider($content){

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

            $this->scrappedContent[] = [
                'title' => $titleElement->nodeValue,
                'year' => $yearElement->nodeValue,
                'type' => 'Movie',
                'slug' => $titleElement->getAttribute('href'),
            ];
        }
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

    public function getElementByTitle($dom, $titleName, $single = false)
    {
        $finder = new DomXPath($dom);
        $elements = $finder->query("//*[contains(concat(' ', normalize-space(@title), ' '), ' $titleName ')]");

        if ($single) {
            $newDom = new DomDocument();

            if (null === $elements->item(0)) {
                throw new Exception('No rating found');
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

    private function getStringBetween($string, $start, $end){
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;
        return substr($string, $ini, $len);
    }



}