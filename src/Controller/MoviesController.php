<?php

namespace App\Controller;

use App\Entity\Movie;
use App\Form\MovieFormType;
use App\Repository\MovieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;

class MoviesController extends AbstractController
{
    private $em;
    private $movieRepository;
    private $serializer;
    public function __construct(MovieRepository $movieRepository, EntityManagerInterface $em, SerializerInterface $serializer)
    {
        $this->movieRepository = $movieRepository;
        $this->em = $em;
        $this->serializer = $serializer;
    }

    //GET ALL MOVIES API - TEMPLATE FORMAT RESPONSE
    #[Route('/', methods:['GET'], name: 'movies_app')]
    public function index(): Response
    {
        return $this->render('movies/index.html.twig',[
            'movies' => $this->movieRepository->findAll()
        ]);
    }
    
    //GET ALL MOVIES API - JSON FORMAT RESPONSE
    #[Route('/movies', methods:['GET'], name: 'movies_get')]
    public function getmovies(): JsonResponse
    {
        $movies = $this->movieRepository->findAll();
        $moviesJson = $this->serializer->serialize(
            $movies,'json',[
                //fixing circular ref error caused by ManyToMany relation
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                }
            ]);
        return new JsonResponse(
            $moviesJson,
            Response::HTTP_OK,
            ['Content-Type' => 'application/json'],
            true
        );
    }
    
/////// Return a JSON response
    #[Route('/test', methods:['GET'], name: 'movies_api')]
    public function hello(): JsonResponse
    {
        return $this->json(['message' => 'Hello from Symfony API!']);
    }

    //GET ALL MOVIES API - JSON FORMAT - NO ACTORS
    #[Route('/api/movies', methods:['GET'], name: 'movies_only')]
    public function getonlymovies(): JsonResponse
    {
        $movies = $this->movieRepository->findAll();
        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups('list_movies')
            ->toArray();
        //removed ManyToMany relation error with GROUPS and setting context
        $moviesJson = $this->serializer->serialize(
            $movies,'json', $context);
        return new JsonResponse(
            $moviesJson,
            Response::HTTP_OK,
            ['Content-Type' => 'application/json'],
            true
        );
    }

//////Create new movie api NO template
    #[Route('/api/create', methods:['POST'], name:'movie_create')]
    public function add(Request $request): JsonResponse
    {
        $movie= $this->serializer->deserialize(
            $request->getContent(),
            Movie::class, 'json');
        $this->em->persist($movie);
        $this->em->flush();

        $jsonMovie= $this->serializer->serialize(
            $movie,'json',['groups'=>'getMovies']); //context

        return new JsonResponse(
            $jsonMovie,
            Response::HTTP_CREATED,
            ['Content-Type' => 'application/json'],
            true
        );
    }

    
    //CREATE NEW MOVIE API
    #[Route('/movies/create', name: 'movies_create')]
    public function create(Request $request): Response
    {
        $movie = new Movie();
        $form = $this->createForm(MovieFormType::class, $movie);
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $newMovie = $form->getData();

            $imagePath = $form->get('imagePath')->getData();
            if ($imagePath) {
                $newFileName = uniqid(). '.' .$imagePath->guessExtension();

                try {
                    $imagePath->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads',
                        $newFileName
                    );
                }catch(FileException $e){
                    return new Response($e->getMessage());
                }
                
                $newMovie->setImagePath('/uploads/' . $newFileName);
            }
            $this->em->persist($newMovie);
            $this->em->flush();
            
            return $this->redirectToRoute('movies_app');
        }
        
        return $this->render('movies/create.html.twig',[
            'form' => $form->createView()
        ]);
    }

    //GET SPECIFIC MOVIE API
    #[Route('/movies/{id}', methods:['GET'], name: 'movies_show')]
    public function show($id): Response
    {
        return $this->render('movies/show.html.twig',[
            'movie' => $this->movieRepository->find($id)
        ]);
    }

///////GET SPECIFIC MOVIE API NO TEMPLATE
    #[Route('/api/find/{id}', methods:['GET'], name: 'movies_show')]
    public function showMovie($id): Response
    {
        $movie = $this->movieRepository->find($id);
        if (!$movie) {
            return $this->json('No movie found for id ' . $id, 404);
        }
        $data =  [
            'title' => $movie->getTitle(),
            'releaseYear' => $movie->getReleaseYear(),
            'description' => $movie->getDescription(),
            'imagePath' => $movie->getImagePath(),
        ];
        return $this->json($data);
    }

/////////Update movie api NO template
    #[Route('/api/update/{id}', name:"movie_update", methods:['PUT'])]
    public function updateMovie($id, Request $request): JsonResponse 
    {
        $movie = $this->movieRepository->findOneBy(['id' => $id]);
        if (!$movie) {
            return $this->json('No movie found for id ' . $id, 404);
        }
        $data= json_decode($request->getContent(),true);

        empty($data['title'])? true : $movie->setTitle($data['title']);
        empty($data['releaseYear'])? true : $movie->setReleaseYear($data['releaseYear']);
        empty($data['description'])? true : $movie->setDescription($data['description']);
        empty($data['imagePath'])? true : $movie->setImagePath($data['imagePath']);
        $this->em->persist($movie);
        $this->em->flush();
            
        $data =  [
            'title' => $movie->getTitle(),
            'releaseYear' => $movie->getReleaseYear(),
            'description' => $movie->getDescription(),
            'imagePath' => $movie->getImagePath(),
        ];
        return $this->json($data);
    }

    //UPDATE A SPECIFIC MOVIE
    #[Route('/movies/edit/{id}', name:'movies_edit')]
    public function edit($id, Request $request): Response
    {
        $movie = $this->movieRepository->find($id);
        $form = $this->createForm(MovieFormType::class, $movie);

        $form->handleRequest($request);
        $imagePath = $form->get('imagePath')->getData();

        if ($form->isSubmitted() && $form->isValid()) {
            if ($imagePath) {
                if ($movie->getImagePath() !== null) {
                    if (file_exists(
                        $this->getParameter('kernel.project_dir') . $movie->getImagePath()
                        )) {
                            $this->GetParameter('kernel.project_dir') . $movie->getImagePath();
                        }
                        $newFileName = uniqid() . '.' . $imagePath->guessExtension();
                        try {
                            $imagePath->move(
                                $this->getParameter('kernel.project_dir') . '/public/uploads',
                                $newFileName
                            );
                        }catch(FileException $e){
                            return new Response($e->getMessage());
                        }
                        $movie->setImagePath('/uploads/' . $newFileName);
                        $this->em->flush();

                        return $this->redirectToRoute('movies_app');
                }
            }else{
                $movie->setTitle($form->get('title')->getData());
                $movie->setReleaseYear($form->get('releaseYear')->getData());
                $movie->setDescription($form->get('description')->getData());

                $this->em->flush();
                return $this->redirectToRoute('movies_app');
            }
        }
        return $this->render('movies/edit.html.twig',[
            'movie'=> $movie,
            'form' => $form->createView(),
        ]);
    }

//////DELETE A MOVIE API NO TEMPLATE
    #[Route('/api/delete/{id}', methods:['GET','DELETE'], name:'movie_delete')]
    public function deleteMovie($id): JsonResponse
    {
        $movie = $this->movieRepository->find($id);
        if (!$movie) {
            return $this->json('No movie found for id ' . $id, 404);
        }
        $this->em->remove($movie);
        $this->em->flush();

        return $this->json('Deleted a project successfully with id ' . $id);
    }
    //DELETE A MOVIE API
    #[Route('/movies/delete/{id}', methods:['GET','DELETE'], name:'movies_delete')]
    public function delete($id): Response
    {
        $movie = $this->movieRepository->find($id);
        $this->em->remove($movie);
        $this->em->flush();

        return $this->redirectToRoute('movies_app');
    }

}
