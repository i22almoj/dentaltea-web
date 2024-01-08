<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class PanelController extends AbstractController
{

    public function index(): Response
    {  
        
        return $this->render('admin/dashboard.html.twig', [
            
        ]);
    }
}