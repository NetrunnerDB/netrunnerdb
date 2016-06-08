<?php 

namespace Netrunnerdb\BuilderBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\HttpFoundation\Response;

class ApiDocController extends Controller
{
	public function docAction()
	{
		return $this->render('NetrunnerdbBuilderBundle:Default:apiIntro.html.twig');
	}
	
	public function redirectAction(Request $request)
	{
		$code = $request->get('code');
		
		$url = $this->get('router')->generate('fos_oauth_server_token', [
				'client_id' => $this->getParameter('oauth_test_client_id'),
				'client_secret' => $this->getParameter('oauth_test_client_secret'),
				'redirect_uri' => $this->getParameter('oauth_test_redirect_url'),
				'grant_type' => 'authorization_code',
				'code' => $code
		], UrlGenerator::ABSOLUTE_URL);
		
		$client = new \GuzzleHttp\Client();
		$res = $client->request('GET', $url);
		
		if($res->getStatusCode() == 200) {
			$response = json_decode($res->getBody(), TRUE);
			dump($response);
			die;
		}
		
		return new Response($res->getStatusCode());
	}
}