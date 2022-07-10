<?php

namespace App\Controller;

use App\Response\ApiResponse;
use DateInterval;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use App\Entity\Secret;

class SecretController extends AbstractController
{
    #[Route('/secret/{hash}', name: 'app_secret_get', methods: ['GET'])]
    public function getSecret(Request $request, ManagerRegistry $doctrine, $hash): JsonResponse
    {
        $repository = $doctrine->getRepository(Secret::class);
        $secretModel = $repository->findOneBy(['hash' => $hash]);
        if ($secretModel) {
            $hash = $secretModel->getHash();
            $secretText = $secretModel->getSecretText();
            $createdAt = $secretModel->getCreatedAt();
            $expiresAt = $secretModel->getExpiresAt();
            $remainingViews = $secretModel->getRemainingViews();
            if ($remainingViews !== null || $expiresAt) {
                $entityManager = $doctrine->getManager();
                $now = new \DateTime("now");
                if ($remainingViews === 0 || ($expiresAt ? $expiresAt->format('c') : 0) > $now->format('c')) {
                    $entityManager->remove($secretModel);
                    $entityManager->flush();
                    throw $this->createNotFoundException('The secret does not exist');
                }
                $secretModel->setRemainingViews($remainingViews ? $remainingViews - 1 : null);
                $entityManager->flush();
            }
            //return  new ApiResponse($request,array($secretModel));

            return $this->json([
                "hash" => $secretModel->getHash(),
                "secretText" => $secretModel->getSecretText(),
                "createdAt" => $secretModel->getCreatedAt()->format('c'),
                "expiresAt" => $secretModel->getExpiresAt() ? $secretModel->getExpiresAt()->format('c') : $secretModel->getExpiresAt(),
                "remainingViews" => $secretModel->getRemainingViews()
            ]);
        } else {
            throw $this->createNotFoundException('The secret does not exist');
        }


    }

    #[Route('/secret', name: 'app_secret_post', methods: ['POST'])]
    public function createSecret(Request $request, ManagerRegistry $doctrine, ValidatorInterface $validator): JsonResponse
    {
        $entityManager = $doctrine->getManager();

        $expireAfter = $request->request->get('expireAfter');
        if ($expireAfter) {
            $expireAfter = new \DateTime("now");
            try {
                $expireAfter->add(new DateInterval('PT' . $request->request->get('expireAfter') . 'M'));
            } catch (\Exception $e) {
                $errors = "Exception happened while calculating the expire date.";
            };
        }

        //dd(new \DateTime("now"));
        $secretModel = new Secret();
        $secretModel->setHash(password_hash($request->request->get('secret'), PASSWORD_DEFAULT));
        $secretModel->setSecretText($request->request->get('secret'));
        //encryptelni kéne
        $secretModel->setCreatedAt(new \DateTime("now"));
        if ($expireAfter) {
            $secretModel->setExpiresAt($expireAfter);
        }

        if ($request->request->get('expireAfterViews')) {
            $secretModel->setRemainingViews($request->request->get('expireAfterViews'));
        }

        $errors = $validator->validate($secretModel);
        if (count($errors) > 0) {
            return json([
                "errors" => $errors,
                "message" => 'Validation error',]);
        }

        $entityManager->persist($secretModel);
        $entityManager->flush();

        $request->headers->get('accept');// ez alapján eldönteni hogy json vagy xml a respone

        return $this->json([
            "hash" => $secretModel->getHash(),
            "secretText" => $secretModel->getSecretText(),
            "createdAt" => $secretModel->getCreatedAt()->format('c'),
            "expiresAt" => $secretModel->getExpiresAt() ? $secretModel->getExpiresAt()->format('c') : $secretModel->getExpiresAt(),
            "remainingViews" => $secretModel->getRemainingViews()
        ]);
    }
}
