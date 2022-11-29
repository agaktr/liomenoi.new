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
//    '2y4nothing[.]xyz',
//    "5m5[.]io",
//"addons[.]news",
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

//"getsignalapps[.]com",
//"getsignalapps[.]live",
//"getupdatesnow[.]xyz",
//"goldenscent[.]net",
//"goldenscint[.]com",
//"goldescent[.]com",
//"gosokm[.]com",
//"guardian-tt[.]me",
//"guardnews[.]live",
//"heaven[.]army",
//"heiiasjournai[.]com",
//"hellasjournal[.]company",
//"hellasjournal[.]website",
//"hellottec[.]art",
//"hempower[.]shop",
//"hopnope[.]xyz",
//"icloudeu[.]com",
//"icloudflair[.]com",
//"iibt[.]xyz",
//"ikea-egypt[.]net",
//"ilnk[.]xyz",
//"in-politics[.]com",
//"infosms-a[.]site",
//"inservices[.]digital",
//"insider[.]gr[.]com",
//"instagam[.]click",
//"instagam[.]in",
//"instagam[.]photos",
//"instegram[.]co",
//"invoker[.]icu",
//"ios-apps[.]store",
//"iosmnbg[.]com",
//"itcgr[.]live",
//"itly[.]link",
//"itter[.]me",
//"jquery-updater[.]xyz",
//"kathimerini[.]news",
//"kinder[.]engine[.]ninja",
//"koenigseggg[.]com",
//"kohaicorp[.]com",
//"koora-egypt[.]com",
//"kormoran[.]bid",
//"kranos[.]gr[.]com",
//"lamborghini-s[.]shop",
//"landingpg[.]xyz",
//"landingpge[.]xyz",
//"leanwithme[.]xyz",
//"lexpress[.]me",
//"lifestyleshops[.]net",
//"limk[.]one",
//"linkit[.]digital",
//"linktothisa[.]xyz",
//"link-m[.]xyz",
//"link-protection[.]com",
//"liponals[.]store",
//"livingwithbadkidny[.]xyz",
//"llinkedin[.]net",
//"lnkedin[.]org",
//"localegem[.]net",
//"lylink[.]online",
//"makeitshort[.]xyz",
//"mifcbook[.]link",
//"md-news-direct[.]com",
//"miniiosapps[.]xyz",
//"mitube1[.]link",
//"mlinks[.]ws",
//"mobnetlink1[.]com",
//"mobnetlink2[.]com",
//"mobnetlink3[.]com",
//"mozillaupdate[.]xyz",
//"msas[.]ws",
//"mycoffeeshop[.]shop",
//"myfcbk[.]net",
//"mytrips[.]quest",
//"myutbe[.]net",
//"mywebsitevpstest[.]xyz",
//"nabd[.]site",
//"nabde[.]app",
//"nassosblog[.]gr[.]com",
//"nemshi-news[.]xyz",
//"nemshi[.]net",
//"networkenterprise[.]net",
//"newsbeast[.]gr[.]com",
//"newslive2[.]xyz",
//"newzeto[.]xyz",
//"newzgroup[.]xyz",
//"niceonase[.]com",
//"niceonesa[.]net",
//"nikjol[.]xyz",
//"nissan[.]gr[.]com",
//"novosti[.]bid",
//"oilgy[.]xyz",
//"olexegy[.]com",
//"olxeg[.]com",
//"omanreal[.]net",
//"omeega[.]xyz",
//"onlineservices[.]gr[.]com",
//"orangegypt[.]co",
//"orchomenos[.]news",
//"otaupdatesios[.]com",
//"paok-24[.]com",
//"pastepast[.]net",
//"pdfviewer[.]app",
//"playestore[.]net",
//"pocopoc[.]xyz",

"politika[.]bid",
"politique-koaci[.]info",
"prmopromo[.]com",
"pronews[.]gr[.]com",
"protothema[.]live",
"proupload[.]xyz",
"ps1link[.]xyz",
"ps2link[.]xyz",
"quickupdates[.]xyz",
"qwert[.]xyz",
"qwxzyl[.]com",
"redeitt[.]com",
"safelyredirecting[.]com",
"safelyredirecting[.]digital",
"sepenet[.]gr[.]com",
"sephoragroup[.]com",
"servers-mobile[.]info",
"serviceupdaterequest[.]com",
"sextape225[.]me",
"shorten[.]fi",
"shortenurls[.]me",
"shortmee[.]one",
"shortwidgets[.]com",
"shortxyz[.]com",
"simetricode[.]uk",
"sinai-new[.]com",
"sitepref[.]xyz",
"smsuns[.]com",
"snapfire[.]xyz",
"sniper[.]pet",
"solargoup[.]xyz",
"solargroup[.]xyz",
"speedy[.]sbs",
"speedygonzales[.]xyz",
"speedymax[.]shop",
"sportsnewz[.]site",
"sports-mdg[.]xyz",
"static-graph[.]com",
"stonisi[.]news",
"supportset[.]net",
"suzuki[.]gr[.]com",
"svetovid[.]bid",
"symoty[.]com",
"syncservices[.]one",
"synctimestamp[.]com",
"syncupdate[.]site",
"telecomegy-ads[.]com",
"telenorconn[.]com",
"tesla-s[.]shop",
"teslal[.]shop",
"teslal[.]xyz",
"teslali[.]com",
"tgrthgsrgwrthwrtgwr[.]xyz",
"timestampsync[.]com",
"timeupdate[.]xyz",
"timeupdateservice[.]com",
"tiny[.]gr[.]com",
"tinylinks[.]live",
"tinyulrs[.]com",
"tinyurl[.]cloud",
"tiol[.]xyz",
"tly[.]gr[.]com",
"tly[.]link",
"tovima[.]live",
"trecv[.]xyz",
"trecvf[.]xyz",
"trkc[.]online",
"tsrt[.]xyz",
"tw[.]itter[.]me",
"twtter[.]net",
"ube[.]gr[.]com",
"uberegypt[.]cn[.]com",
"updates4you[.]xyz",
"updateservice[.]center",
"updatetime[.]zone",
"updatingnews[.]xyz",
"updete[.]xyz",
"url-promo[.]club",
"url-tiny[.]app",
"uservicescheck[.]com",
"uservicesforyou[.]com",
"utube[.]digital",
"viva[.]gr[.]com",
"vodafoneegypt[.]tech",
"vodafonegypt[.]com",
"wavekli[.]xyz",
"we-site[.]net",
"weathear[.]live",
"weathernewz[.]xyz",
"weathersite[.]online",
"webaffise[.]com",
"wha[.]tsapp[.]me",
"worldnws[.]xyz",
"wtc1111[.]com",
"wtc2222[.]com",
"wtc3333[.]com",
"xf[.]actor",
"xnxx-hub[.]com",
"xyvok[.]xyz",
"yallakora-egy[.]com",
"yo[.]utube[.]digital",
"yo[.]utube[.]to",
"youarefired[.]xyz",
"yout[.]ube[.]gr[.]com",

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
