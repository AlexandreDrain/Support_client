<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class SearcheController extends AbstractController
{
    /**
     * @Route("/what_I'll_search", name="searched")
     */
    public function index()
    {
        return $this->render('searche/index.html.twig', [
        ]);
    }
}
