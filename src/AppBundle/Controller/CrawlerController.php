<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\DomCrawler\Crawler;

class CrawlerController extends Controller
{
	
    public function indexAction(Request $request)
    {		
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
        ]);
    }
	
    public function listAction(Request $request)
    {
		
		$url = 'https://www.bestjobs.eu/ro/locuri-de-munca?location=bucuresti&keyword=symfony';
		
//		$pageToCrawl = file_get_contents($url);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; rv:33.0) Gecko/20100101 Firefox/33.0");
		curl_setopt($ch, CURLOPT_COOKIE, 'CookieName1=Value;');
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 9);
		curl_setopt($ch, CURLOPT_REFERER, $url);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip,deflate');
		$pageToCrawl = curl_exec($ch);
		$status = curl_getinfo($ch);
		curl_close($ch);

//		$follow_allowed = ( ini_get('open_basedir') || ini_get('safe_mode')) ? false : true;
//		if ($follow_allowed) {
//			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
//		}
		
		$crawler = new Crawler($pageToCrawl);
		
		$jobsList = $crawler->filter('.card-list');
		$jobs = $crawler->filter('.job-card.card-item');

		$parsedJobs = array();
		if (iterator_count($jobs) > 1) {
			foreach ($jobs as $key => $job) {
	//			var_dump($domElement->nodeName);
				$title = new Crawler($job);
				$company = new Crawler($job);
				$location = new Crawler($job);
				$description = new Crawler($job);
				$parsedJobs[] = array(
					'title' => $title->filter('img')->attr('alt'),
					'company' => trim($company->filter('p.job-title a')->text()),
					'location' => trim($location->filter('p.text-muted')->text()),
					'description' => implode(',',
							$description->filter('a.search-after-keyword')
									->each(function ($node, $i) { return trim($node->text()); })
						)
				);
			}
		}
		
		var_dump($parsedJobs);
		die;
		
        return $this->render('crawler/list.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
        ]);
    }
	
    public function crawlAction(Request $request)
    {
        return $this->render('crawler/crawl.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
        ]);
    }
	
    public function detailAction(Request $request)
    {
        return $this->render('crawler/detail.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
        ]);
    }
	
}
