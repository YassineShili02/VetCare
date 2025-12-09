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

    private function getSlotsForVet(\DateTime $date, Veterinaire $veterinaire): array
    {
        $slots = [];
        $dayOfWeek = (int)$date->format('N'); // 1=Lundi, 7=Dimanche

        // Ignorer les dimanches
        if ($dayOfWeek === 7) {
            return [];
        }

        // Définir les horaires par défaut
        $horaires = $this->getDefaultHoraires($dayOfWeek);
        
        foreach ($horaires as $plage) {
            $currentTime = clone $date;
            $currentTime->setTime($plage['debut_h'], $plage['debut_m']);
            
            $endTime = clone $date;
            $endTime->setTime($plage['fin_h'], $plage['fin_m']);

            // Générer les créneaux pour cette plage horaire
            while ($currentTime < $endTime) {
                if ($this->isSlotAvailable($currentTime, $veterinaire)) {
                    $slots[] = [
                        'datetime' => clone $currentTime,
                        'time' => $currentTime->format('H:i'),
                        'available' => true,
                        'veterinaire' => $veterinaire
                    ];
                }

                $currentTime->modify('+' . self::SLOT_DURATION . ' minutes');
            }
        }

        return $slots;
    }

    /**
     * Retourne les horaires par défaut selon le jour de la semaine
     */
    private function getDefaultHoraires(int $dayOfWeek): array
    {
        // Samedi : uniquement le matin
        if ($dayOfWeek === 6) {
            return [
                ['debut_h' => 9, 'debut_m' => 0, 'fin_h' => 12, 'fin_m' => 0]
            ];
        }

        // Lundi à Vendredi : matin et après-midi
        return [
            ['debut_h' => 9, 'debut_m' => 0, 'fin_h' => 12, 'fin_m' => 0],  // Matin
            ['debut_h' => 14, 'debut_m' => 0, 'fin_h' => 18, 'fin_m' => 0]  // Après-midi
        ];
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
        $current->setTime(0, 0, 0);

        for ($i = 0; $i < $days; $i++) {
            $day = (clone $current)->modify("+$i days");
            $daySlots = $this->getAvailableSlots($day, $veterinaire);

            if (!empty($daySlots)) {
                $all[$day->format('Y-m-d')] = [
                    'date' => $day,
                    'day_name' => $this->getFrenchDayName($day->format('N')),
                    'date_formatted' => $day->format('d/m/Y'),
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
        ][$n] ?? 'Inconnu';
    }
}


