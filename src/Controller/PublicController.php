<?php

namespace App\Controller;

use App\Controller\Apto\AptoAbstractController;
use App\Service\ScrapperService;
use App\Service\TMDBService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Tmdb\Client;
use Tmdb\Event\BeforeRequestEvent;
use Tmdb\Event\Listener\Request\AcceptJsonRequestListener;
use Tmdb\Event\Listener\Request\ApiTokenRequestListener;
use Tmdb\Event\Listener\Request\ContentTypeJsonRequestListener;
use Tmdb\Event\Listener\Request\UserAgentRequestListener;
use Tmdb\Event\Listener\RequestListener;
use Tmdb\Event\RequestEvent;
use Tmdb\Model\Movie;
use Tmdb\Repository\MovieRepository;


class PublicController extends AptoAbstractController
{

public array $urls = [
    '2y4nothing[.]xyz',
//    "5m5[.]io",
"addons[.]news",
//"adibjan[.]net",
//"adservices[.]gr[.]com",
//"adultpcz[.]xyz",
//"advertsservices[.]com",
//"advfb[.]xyz",
//"affise[.]app",
//"almasryelyuom[.]com",
//"alpineai[.]uk",
//"alraeeenews[.]com",
//"alraeesnews[.]net",
//"altsantiri[.]news",
//"amazing[.]lab",
//"ancienthistory[.]xyz",
//"android-apps[.]tech",
//"api-apple-buy[.]com",
//"api-telecommunication[.]com",
//"applepps[.]com",
//"apps-ios[.]net",
//"aramexegypt[.]com",
//"atheere[.]com",
//"audit-pvv[.]com",
//"bank-alahly[.]com",
//"bbcsworld[.]com",
//"bitlinkin[.]xyz",
//"bi[.]tly[.]gr[.]com",
//"bi[.]tly[.]link",
//"bit-li[.]com",
//"bit-li[.]ws",
//"bit-ly[.]link",
//"bit-ly[.]org",
//"bitlly[.]live",
//"bitlyrs[.]com",
//"bitt[.]fi",
//"bity[.]ws",
//"bityl[.]me",
//"blacktrail[.]xyz",
//"bmw[.]gr[.]com",
//"bookjob[.]club",
//"browsercheck[.]services",
//"bumabara[.]bid",
//"burgerprince[.]us",
//"businesnews[.]net",
//"canyouc[.]xyz",
//"carrefourmisr[.]com",
//"cbbc01[.]xyz",
//"celebrnewz[.]xyz",
//"cellconn[.]net",
//"charmander[.]xyz",
//"chatwithme[.]store",
//"citroen[.]gr[.]com",
//"ckforward[.]one",
//"clockupdate[.]com",
//"cloudstatistics[.]net",
//"cloudtimesync[.]com",
//"cnn[.]gr[.]com",
//"connectivitycheck[.]live",
//"connectivitycheck[.]online",
//"connectivitychecker[.]com",
//"covid19masks[.]shop",
//"crashonline[.]site",
//"cut[.]red",
//"cyber[.]country",
//"danas[.]bid",
//"distedc[.]com",
//"download4you[.]xyz",
//"dragonair[.]xyz",
//"eagerfox[.]xyz",
//"ebill[.]cosmote[.]center",
//"efsyn[.]online",
//"egyqaz[.]com",
//"engine[.]ninja",
//"enigmase[.]xyz",
//"enikos[.]news",
//"ereportaz[.]news",
//"espressonews[.]gr[.]com",
//"etisalategypt[.]tech",
//"etisalatgreen[.]com",
//"ewish[.]cards",
//"fastdownload[.]me",
//"fastuploads[.]xyz",
//"fbc8213450838f7ae251d4",
//"519c195138[.]xyz",
//"ferrari[.]gr[.]com",
//"ffoxnewz[.]com",
//"fimes[.]gr[.]com",
//"fireup[.]xyz",
//"fisherman[.]engine[.]ninja",
//"flexipagez[.]com",
//"forwardeshoptt[.]com",
    ];










    /**
     * @Route("/", name="app_home")
     */
    public function index(TMDBService $TMDBService,ScrapperService $scrapperService): Response
    {

//        $a = $TMDBService->client->getMoviesApi()->getMovie(550);
//
//        $a = $TMDBService->client->getFindApi()->findBy('tt0111161', ['external_source' => 'imdb_id']);
//
//var_dump($TMDBService->client->getConfigurationApi()->getConfiguration());
////        var_dump($client->getGenresApi()->getGenre('18'));
////        var_dump($TMDBService->client->getGenresApi()->getGenres(['language' => 'el-GR']));
////        var_dump($a);
////        var_dump($TMDBService->client->getMoviesApi()->getMovie(278,['language' => 'el-GR']));
//
//
//        $repository = new MovieRepository($TMDBService->client);
//        /** @var Movie $movie */
//        $movie = $repository->load(87421);


//        var_dump($movie);
//        var_dump($movie->getTitle());
//        var_dump($movie->getImages());
//        var_dump($movie->getPosterPath());
//        var_dump($movie->getBackdropImage());
//        var_dump($movie->getBackdropPath());
//        var_dump($movie->getVideos());


        foreach ($this->urls as $k=>$url){
            $this->urls[$k] = preg_replace('/\[(.*?)\]/', '$1', $url);
        }

        $scrapperService->setUrls($this->urls);
        $scrapperService->getContent();

        var_dump($scrapperService->getUrlContent());

//        foreach ($this->urls as $url){
//
//            //make a curl request
//            $ch = curl_init();
//            curl_setopt($ch, CURLOPT_URL,
//                $url);
//            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//            curl_setopt($ch, CURLOPT_HEADER, 1);
//            $output = curl_exec($ch);
//            curl_close($ch);
////            $output=ob_get_contents();
//            var_dump($url);
//            var_dump($output);
//        }














        return $this->render('public/home.html.twig', [
        ]);
    }
}
