<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * DefaultController for home- and about-pages
 */
class DefaultController
extends AbstractController
{
    /**
     * @Route("/", name="home", options={"sitemap" = true})
     */
    public function homeAction()
    {
        return $this->render('Default/home.html.twig');
    }

    /**
     * @Route("/about", name="about", options={"sitemap" = true})
     */
    public function aboutAction(Request $request)
    {
        return $this->render('Default/about.' . $request->getLocale() . '.html.twig');
    }


    /**
     * @Route("/imprint", name="imprint", options={"sitemap" = true})
     */
    public function imprintAction(Request $request)
    {
        return $this->render('Default/imprint.' . $request->getLocale() . '.html.twig');
    }
}
