<?php

namespace App\Controller;

use App\Response\ApiResponse;
use DateInterval;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Validator\Validator\ValidatorInterface;

use App\Entity\Secret;

class SecretController extends AbstractController
{
    #[Route('/secret/{hash}', name: 'app_secret_get', methods: ['GET'])]
    public function getSecret(Request $request, ManagerRegistry $doctrine, string $hash): ApiResponse
    {
        /** @var Secret $secret */
        $secret = $doctrine->getRepository(Secret::class)->findOneBy(['hash' => $hash]);

        if (!$secret) {
            throw $this->createNotFoundException('The secret does not exist');
        }

        $expiresAt = $secret->getExpiresAt();
        $remainingViews = $secret->getRemainingViews();

        if (null !== $remainingViews || $expiresAt) {
            $entityManager = $doctrine->getManager();

            // Töröljük, ha lejárt a TTL vagy elfogytak a megtekintések
            if ($remainingViews === 0 || $secret->isExpired()) {
                $entityManager->remove($secret);
                $entityManager->flush();
                throw $this->createNotFoundException('The secret does not exist');
            }

            if (null !== $remainingViews) {
                $secret->setRemainingViews($remainingViews - 1);
                $entityManager->persist($secret);
                $entityManager->flush();
            }
        }
        return new ApiResponse(json_encode($secret->json()), 200, ['Accept' => $request->headers->get('Accept')]);
    }

    #[Route('/secret', name: 'app_secret_post', methods: ['POST'])]
    public function createSecret(Request $request, ManagerRegistry $doctrine, ValidatorInterface $validator): ApiResponse
    {
        $entityManager = $doctrine->getManager();

        $expireAfter = $request->request->get('expireAfter');

        if (is_numeric($expireAfter)) {
            $expireAfter = new DateTime("now");
            try {
                $expireAfter->add(new DateInterval('PT' . $request->request->get('expireAfter') . 'M'));
            } catch (Exception $e) {
                $errors = $e;
            }
        }

        $secretModel = new Secret();
        $secretModel->setHash(hash('sha256', $request->request->get('secret')));
        $secretModel->setSecretText($request->request->get('secret'));
        $secretModel->setCreatedAt(new DateTime("now"));
        if ($expireAfter instanceof DateTime) {
            $secretModel->setExpiresAt($expireAfter);
        }

        if (is_numeric($request->request->get('expireAfterViews'))) {
            $secretModel->setRemainingViews($request->request->get('expireAfterViews'));
        }

        $errors = $errors ?? $validator->validate($secretModel);
        if (count($errors) > 0) {
            return $this->json([
                "errors" => $errors,
                "message" => 'Validation error',]);
        }

        $entityManager->persist($secretModel);
        $entityManager->flush();

        return new ApiResponse(json_encode($secretModel->json()), 200, ['Accept' => $request->headers->get('Accept')]);
    }
}
