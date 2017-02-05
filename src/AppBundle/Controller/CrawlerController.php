<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Query\ResultSetMapping;

use AppBundle\Entity\Job;
use Symfony\Component\DomCrawler\Crawler;

class CrawlerController extends Controller
{
	
    public function indexAction(Request $request)
    {		
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.root_dir').'/..').DIRECTORY_SEPARATOR,
        ]);
    }
	
    public function doCrawlAction(Request $request)
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
		$em = $this->getDoctrine()->getManager();
		$crawler = new Crawler($pageToCrawl);		
		$jobsList = $crawler->filter('.card-list');
		$jobs = $crawler->filter('.job-card.card-item');
		$parsedJobs = array();
		if (iterator_count($jobs) > 1) {
			foreach ($jobs as $key => $job) {
				$title = new Crawler($job);
				$company = new Crawler($job);
				$company1Name = $company->filter('img')->attr('alt');
				$company2 = new Crawler($job);
				$company2Name = trim($company->filter('p.truncate-1-line')->text());
				$location = new Crawler($job);
				$description = new Crawler($job);
				$parsedJobs[] = array(
					'title' => trim($title->filter('p.job-title a')->text()),
					'company' => $company1Name ? $company1Name : $company2Name ? $company2Name : '',
					'location' => trim($location->filter('p.text-muted')->text()),
					'description' => implode(',',
							$description->filter('a.search-after-keyword')
									->each(function ($node, $i) { return trim($node->text()); })
						)
				);
			}
		}		
		if(count($parsedJobs)){
			foreach($parsedJobs as $newJ){	
				$newJob = new Job();
				$newJob->setTitle($newJ['title']);
				$newJob->setCompany($newJ['company']);
				$newJob->setLocation($newJ['location']);
				$newJob->setDescription($newJ['description']);
				$em->persist($newJob);
				$em->flush();		
			}
		}		
        return $this->render('crawler/crawl.html.twig', array('jobResults' => $parsedJobs));
    }
	
	
    public function searchAction(Request $request)
    {		
		if($criteria = $request->query->get('criteria')){
			$em = $this->getDoctrine()->getManager();
			$qb = $em->createQueryBuilder('jobs');
			$crawled = $qb->select('j')
					->from('AppBundle:Job', 'j')
					->where('j.title LIKE \'%'.$criteria.'%\'')
					->orWhere('j.location LIKE \'%'.$criteria.'%\'')
					->orWhere('j.description LIKE \'%'.$criteria.'%\'')
					->getQuery()
					->getResult();
		}else{
			$criteria='';
			$crawled = $this->getDoctrine()->getRepository('AppBundle:Job')->findAll();
		}
		return $this->render('crawler/search.html.twig', array('criteria' => $criteria,'crawled' => $crawled));
	}
    public function listAction(Request $request)
    {	
		$criteria='';		
        return $this->render('crawler/list.html.twig', array('criteria' => $criteria));
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
