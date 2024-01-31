<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Id;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MoviesController extends AbstractController
{
    private $movieRepository;
    public function __construct(MovieRepository $movieRepository)
    {
        $this->movieRepository = $movieRepository;
    }

    #[Route('/', methods:['GET'], name: 'movies_app')]
    public function index(): Response
    {
        return $this->render('movies/index.html.twig',[
            'movies' => $this->movieRepository->findAll()
        ]);
    }

    #[Route('/movies/{id}', methods:['GET'], name: 'movies_show')]
    public function show($id): Response
    {
        return $this->render('movies/show.html.twig',[
            'movie' => $this->movieRepository->find($id)
        ]);
    }
}
