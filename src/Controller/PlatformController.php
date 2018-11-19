<?php
/**
 * Created by PhpStorm.
 * User: corentinboutillier
 * Date: 18/11/2018
 * Time: 00:57
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class PlatformController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function homeAction()
    {
        return $this->render('base.html.twig');
    }

    /**
     * @Route("/admin", name="admin")
     */
    public function adminAction()
    {
        return $this->render('base.html.twig');
    }
}