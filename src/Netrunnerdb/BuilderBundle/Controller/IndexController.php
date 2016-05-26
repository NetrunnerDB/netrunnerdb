<?php
namespace Netrunnerdb\BuilderBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class IndexController extends Controller
{
    public function indexAction(Request $request)
    {

        $response = new Response();
        $response->setPublic();
        $response->setMaxAge($this->container->getParameter('short_cache'));
        
        // decklist of the week
        $decklist = $this->get('highlight')->get();
        
        // recent decklists
        $decklists_recent = $this->get('decklists')->recent(0, 10, FALSE)['decklists'];
        
        
        
        return $this->render('NetrunnerdbBuilderBundle:Default:index.html.twig',
                array(
                        'pagetitle' => "Android:Netrunner Cards and Deckbuilder",
                        'pagedescription' => "Build your deck for Android:Netrunner, the LCG by Fantasy Flight Games. Browse the cards and the thousand of decklists submitted by the community. Publish your own decks and get feedback.",
                        'decklists' => $decklists_recent,
                        'decklist' => $decklist,
                        'url' => $request->getRequestUri()
                ), $response);
        
        
    }
}