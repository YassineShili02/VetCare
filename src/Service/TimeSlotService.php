<?php

namespace App\Service;

use App\Entity\Veterinaire;
use App\Entity\Rendezvous;
use Doctrine\ORM\EntityManagerInterface;

class TimeSlotService
{
    private EntityManagerInterface $em;

    // Durée d'un rendez-vous en minutes
    private const SLOT_DURATION = 30;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Récupère les créneaux pour une date donnée
     */
    private function createDateTimeFromTime(\DateTime $date, \DateTime $time): \DateTime
    {
        return (clone $date)->setTime(
            (int)$time->format('H'),
            (int)$time->format('i')
        );
    }

    private function getSlotsForVet(\DateTime $date, Veterinaire $veterinaire): array
    {
        $slots = [];
        $dayOfWeek = (int)$date->format('N');

        // Trouver la dispo du jour
        $disponibilite = null;
        foreach ($veterinaire->getDisponibilites() as $dispo) {
            if ($dispo->getJourSemaine() === $dayOfWeek) {
                $disponibilite = $dispo;
                break;
            }
        }

        if (!$disponibilite) {
            return [];
        }

        // Convertir les TIME SQL en DateTime du jour courant
        $currentTime = $this->createDateTimeFromTime($date, $disponibilite->getHeureDebut());
        $endTime = $this->createDateTimeFromTime($date, $disponibilite->getHeureFin());

        // Générer les créneaux
        while ($currentTime < $endTime) {

            if ($this->isSlotAvailable($currentTime, $veterinaire)) {
                $slots[] = [
                    'datetime' => clone $currentTime,
                    'time' => $currentTime->format('H:i'),
                    'available' => true,
                    'veterinaire' => $veterinaire
                ];
            }

            // Passer au prochain créneau
            $currentTime->modify('+' . self::SLOT_DURATION . ' minutes');
        }

        return $slots;
    }

    /**
     * Vérifie si un créneau est libre
     */
    private function isSlotAvailable(\DateTime $dateTime, Veterinaire $veterinaire): bool
    {
        // Pas de RDV dans le passé
        if ($dateTime < new \DateTime()) {
            return false;
        }

        // Vérifier s'il existe déjà un RDV exactement à cette heure
        $existingRdv = $this->em->getRepository(Rendezvous::class)
            ->createQueryBuilder('r')
            ->where('r.veterinaire = :vet')
            ->andWhere('r.dateHeure = :dt')
            ->andWhere('r.statut NOT IN (:excluded)')
            ->setParameter('vet', $veterinaire)
            ->setParameter('dt', $dateTime)
            ->setParameter('excluded', ['annule', 'refuse'])
            ->getQuery()
            ->getOneOrNullResult();

        return $existingRdv === null;
    }

    public function getAvailableSlots(\DateTime $date, ?Veterinaire $veterinaire = null): array
    {
        if ($veterinaire) {
            return $this->getSlotsForVet($date, $veterinaire);
        }

        $slots = [];
        $vets = $this->em->getRepository(Veterinaire::class)->findBy(['actif' => true]);

        foreach ($vets as $vet) {
            $vetSlots = $this->getSlotsForVet($date, $vet);
            foreach ($vetSlots as $slot) {
                $slots[] = $slot;
            }
        }

        usort($slots, fn($a, $b) => $a['datetime'] <=> $b['datetime']);

        return $slots;
    }

    public function getUpcomingAvailableSlots(int $days = 7, ?Veterinaire $veterinaire = null): array
    {
        $all = [];
        $current = new \DateTime();

        for ($i = 0; $i < $days; $i++) {
            $day = (clone $current)->modify("+$i days");
            $daySlots = $this->getAvailableSlots($day, $veterinaire);

            if ($daySlots) {
                $all[$day->format('Y-m-d')] = [
                    'date' => $day,
                    'day_name' => $this->getFrenchDayName($day->format('N')),
                    'slots' => $daySlots
                ];
            }
        }

        return $all;
    }

    private function getFrenchDayName(string $n): string
    {
        return [
            '1' => 'Lundi',
            '2' => 'Mardi',
            '3' => 'Mercredi',
            '4' => 'Jeudi',
            '5' => 'Vendredi',
            '6' => 'Samedi',
            '7' => 'Dimanche'
        ][$n];
    }
}
