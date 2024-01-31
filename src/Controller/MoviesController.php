<?php

namespace App\Controller;

use App\Entity\Movie;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Id;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MoviesController extends AbstractController
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/', methods:['GET'], name: 'movies_app')]
    public function index(): Response
    {
        $repository = $this->em->getRepository(Movie::class);

        return $this->render('movies/index.html.twig',[
            'movies' => $repository->findAll()
        ]);
    }

    #[Route('/movies/{id}', methods:['GET'], name: 'movies_show')]
    public function show($id): Response
    {
        $repository = $this->em->getRepository(Movie::class);

        return $this->render('movies/show.html.twig',[
            'movie' => $repository->find($id)
        ]);
    }
}
