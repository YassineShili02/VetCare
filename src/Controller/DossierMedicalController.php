<?php
    namespace App\Controller;

    use App\Entity\DossierMedical;
    use App\Service\EmailJsService;
    use App\Service\PdfGenerator;
    use App\Entity\Animal;
    use Knp\Component\Pager\PaginatorInterface;
    use App\Form\DossierMedicalType;
    use App\Repository\DossierMedicalRepository;
    use App\Repository\AnimalRepository;
    use Doctrine\Persistence\ManagerRegistry;
    use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
    use Symfony\Component\HttpFoundation\Request;
    use Symfony\Component\HttpFoundation\Response;
    use Symfony\Component\Routing\Annotation\Route;
    use Symfony\Component\HttpFoundation\File\Exception\FileException;

    #[Route('/dossiermedical')]
    class DossierMedicalController extends AbstractController
    {
        private string $dossierMedicalImagesDir;

        public function __construct(string $dossierMedicalImagesDir)
        {
            $this->dossierMedicalImagesDir = $dossierMedicalImagesDir;
        }

        /**
         * Vérifie l'accès pour ROLE_ADMIN et ROLE_VETERINAIRE
         */
        private function denyAccessToUsers(): ?Response
        {
            if (!$this->isGranted('ROLE_ADMIN') && !$this->isGranted('ROLE_VET')) {
                return $this->render('security/access_denied.html.twig');
            }
            return null;
        }

        #[Route('/', name: 'app_dossier_medical')]
        public function index(): Response
        {
            return $this->render('dossier_medical/index.html.twig');
        }

        #[Route('/add/{animalId}', name: 'add_dossier_for_animal')]
        public function addForAnimal(
            int $animalId,
            AnimalRepository $animalRepository,
            DossierMedicalRepository $dossierRepository,
            Request $request,
            ManagerRegistry $doctrine,
            EmailJsService $emailJsService
        ): Response {
            if ($response = $this->denyAccessToUsers()) {
                return $response;
            }

            $animal = $animalRepository->find($animalId);
            if (!$animal) {
                throw $this->createNotFoundException('Animal introuvable');
            }

            // ❌ SUPPRIMER CETTE PARTIE
            // if ($animal->getDossierAnimal()) {
            //     $this->addFlash('warning', 'Cet animal a déjà un dossier médical.');
            //     return $this->redirectToRoute('lists_dossiers');
            // }

            $dossier = new DossierMedical();
            $dossier->setAnimal($animal);

            $form = $this->createForm(DossierMedicalType::class, $dossier);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $dossier->setNumeroDossier('D-' . uniqid());
                $dossier->setDateCreation(new \DateTime());

                $images = $form->get('images')->getData();
                if ($images) {
                    $imageNames = [];
                    foreach ($images as $image) {
                        $newFilename = uniqid() . '.' . $image->guessExtension();
                        $image->move($this->dossierMedicalImagesDir, $newFilename);
                        $imageNames[] = $newFilename;
                    }
                    $dossier->setImages($imageNames);
                }

                $em = $doctrine->getManager();
                $em->persist($dossier);
                $em->flush();

                // ================= ENVOI EMAIL AVEC EMAILJS =================
                try {
                    $owner = $animal->getOwner();

                    if ($owner) {
                        $emailJsService->sendDossierReadyEmail([
                            'nom_proprietaire' => $owner->getFullName(),
                            'nom_animal' => $animal->getNom(),
                            'numero_dossier' => $dossier->getNumeroDossier(),
                            'to_email' => $owner->getEmail(),
                            'email' => 'contact@vetcare.tn',
                        ]);

                        $this->addFlash('info', 'Un email de notification a été envoyé au propriétaire.');
                    } else {
                        $emailJsService->sendDossierReadyEmail([
                            'nom_proprietaire' => 'Client VetCare',
                            'nom_animal' => $animal->getNom(),
                            'numero_dossier' => $dossier->getNumeroDossier(),
                            'to_email' => 'yassine.shili@esprit.tn',
                            'email' => 'contact@vetcare.tn',
                        ]);

                        $this->addFlash('info', 'Un email de notification a été envoyé.');
                    }
                } catch (\Exception $e) {
                    $this->addFlash('warning', 'Le dossier a été créé mais l\'email n\'a pas pu être envoyé. Erreur: ' . $e->getMessage());
                }
                // ============================================================

                $this->addFlash('success', 'Dossier médical ajouté avec succès !');
                return $this->redirectToRoute('lists_dossiers');
            }

            return $this->render('dossier_medical/add.html.twig', [
                'formDossier' => $form->createView(),
                'animal' => $animal,
            ]);
        }

        #[Route('/add', name: 'addForm_dossier')]
        public function addWithForm(
            Request $request,
            ManagerRegistry $doctrine,
            EmailJsService $emailJsService
        ): Response {
            if ($response = $this->denyAccessToUsers()) {
                return $response;
            }

            $dossier = new DossierMedical();
            $form = $this->createForm(DossierMedicalType::class, $dossier);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $animal = $dossier->getAnimal();

                // ❌ SUPPRIMER CETTE PARTIE
                // if ($animal && $animal->getDossierAnimal()) {
                //     $this->addFlash('warning', 'Cet animal a déjà un dossier médical.');
                //     return $this->redirectToRoute('lists_dossiers');
                // }

                $dossier->setNumeroDossier('D-' . uniqid());
                $dossier->setDateCreation(new \DateTime());

                $images = $form->get('images')->getData();
                if ($images) {
                    $imageNames = [];
                    foreach ($images as $image) {
                        $newFilename = uniqid() . '.' . $image->guessExtension();
                        $image->move($this->dossierMedicalImagesDir, $newFilename);
                        $imageNames[] = $newFilename;
                    }
                    $dossier->setImages($imageNames);
                }

                $em = $doctrine->getManager();
                $em->persist($dossier);
                $em->flush();

                // ================= ENVOI EMAIL =================
                try {
                    if ($animal) {
                        $owner = $animal->getOwner();

                        if ($owner) {
                            $emailJsService->sendDossierReadyEmail([
                                'nom_proprietaire' => $owner->getFullName(),
                                'nom_animal' => $animal->getNom(),
                                'numero_dossier' => $dossier->getNumeroDossier(),
                                'to_email' => $owner->getEmail(),
                                'email' => 'contact@vetcare.tn',
                            ]);

                            $this->addFlash('info', 'Un email de notification a été envoyé au propriétaire.');
                        }
                    }
                } catch (\Exception $e) {
                    $this->addFlash('warning', 'Le dossier a été créé mais l\'email n\'a pas pu être envoyé. Erreur: ' . $e->getMessage());
                }
                // ===============================================

                $this->addFlash('success', 'Dossier médical ajouté avec succès !');
                return $this->redirectToRoute('lists_dossiers');
            }

            return $this->render('dossier_medical/add.html.twig', [
                'formDossier' => $form->createView(),
            ]);
        }

        #[Route('/list', name: 'lists_dossiers')]
        public function list(DossierMedicalRepository $repository, Request $request, PaginatorInterface $paginator): Response
        {
            $sort = $request->query->get('sort', 'dateCreation');
            $order = strtoupper($request->query->get('order', 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

            $allowedSorts = [
                'idDossier' => 'd.id_dossier',
                'numeroDossier' => 'd.numero_dossier',
                'dateCreation' => 'd.date_creation',
                'poids' => 'd.poids',
                'etat' => 'd.etat',
                'animal' => 'a.nom'
            ];

            $queryBuilder = $repository->createQueryBuilder('d')
                ->leftJoin('d.animal', 'a')
                ->addSelect('a');

            $sortField = $allowedSorts[$sort] ?? 'd.date_creation';
            $queryBuilder->orderBy($sortField, $order);

            $tabdossiers = $paginator->paginate(
                $queryBuilder,
                $request->query->getInt('page', 1),
                10,
                [
                    'wrap-queries' => true,
                    'sortFieldParameterName' => null,
                    'sortDirectionParameterName' => null,
                ]
            );

            return $this->render('dossier_medical/list.html.twig', [
                'tabdossiers' => $tabdossiers,
                'sort' => $sort,
                'order' => $order,
            ]);
        }

        #[Route('/dossier/show/{id}', name: 'show_dossier')]
        public function show(DossierMedical $dossier): Response
        {
            return $this->render('dossier_medical/show_dossier.html.twig', [
                'dossier' => $dossier,
            ]);
        }

        #[Route('/pdf/{id}', name: 'dossier_pdf')]
        public function generatePdf(DossierMedical $dossier, PdfGenerator $pdfGenerator, Request $request): Response
        {
            if ($response = $this->denyAccessToUsers()) {
                return $response;
            }

            $basePath = $request->getSchemeAndHttpHost();
            $html = $this->renderView('dossier_medical/pdf.html.twig', [
                'dossier' => $dossier,
                'base_path' => $basePath,
            ]);

            $pdfContent = $pdfGenerator->generatePdf($html);

            return new Response(
                $pdfContent,
                200,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="dossier_' . $dossier->getNumeroDossier() . '.pdf"',
                ]
            );
        }

        #[Route('/update/{id}', name: 'update_dossier')]
        public function update(int $id, DossierMedicalRepository $repository, Request $request, ManagerRegistry $doctrine): Response
        {
            if ($response = $this->denyAccessToUsers()) {
                return $response;
            }

            $dossier = $repository->find($id);
            if (!$dossier) {
                throw $this->createNotFoundException('Dossier médical introuvable');
            }

            $form = $this->createForm(DossierMedicalType::class, $dossier);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $images = $form->get('images')->getData();
                if ($images) {
                    $imageNames = $dossier->getImages() ?? [];
                    foreach ($images as $image) {
                        $newFilename = uniqid() . '.' . $image->guessExtension();
                        try {
                            $image->move($this->dossierMedicalImagesDir, $newFilename);
                            $imageNames[] = $newFilename;
                        } catch (FileException $e) {
                            $this->addFlash('error', 'Erreur lors de l\'upload de l\'image.');
                        }
                    }
                    $dossier->setImages($imageNames);
                }

                $doctrine->getManager()->flush();
                $this->addFlash('success', 'Dossier médical modifié avec succès !');
                return $this->redirectToRoute('lists_dossiers');
            }

            return $this->render('dossier_medical/update.html.twig', [
                'formDossier' => $form->createView(),
                'dossier' => $dossier,
            ]);
        }

        #[Route('/remove/{id}', name: 'remove_dossier')]
        public function remove(int $id, DossierMedicalRepository $repository, ManagerRegistry $doctrine): Response
        {
            if ($response = $this->denyAccessToUsers()) {
                return $response;
            }

            $dossier = $repository->find($id);
            if (!$dossier) {
                throw $this->createNotFoundException('Dossier médical introuvable');
            }

            $em = $doctrine->getManager();
            $em->remove($dossier);
            $em->flush();

            $this->addFlash('success', 'Dossier médical supprimé avec succès !');
            return $this->redirectToRoute('lists_dossiers');
        }
    }
