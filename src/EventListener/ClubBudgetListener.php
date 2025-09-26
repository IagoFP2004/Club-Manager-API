<?php

namespace App\EventListener;

use App\Entity\Club;
use App\Entity\Player;
use App\Entity\Coach;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\EntityManagerInterface;

class ClubBudgetListener
{
    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();
        
        if ($entity instanceof Player || $entity instanceof Coach) {
            error_log("ClubBudgetListener: prePersist ejecutado para " . get_class($entity));
            $this->updateClubBudget($entity->getClub(), $args->getObjectManager());
        }
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        
        if ($entity instanceof Player || $entity instanceof Coach) {
            error_log("ClubBudgetListener: preUpdate ejecutado para " . get_class($entity));
            $this->updateClubBudget($entity->getClub(), $args->getObjectManager());
        }
    }

    public function preRemove(PreRemoveEventArgs $args): void
    {
        $entity = $args->getObject();
        
        if ($entity instanceof Player || $entity instanceof Coach) {
            $this->updateClubBudget($entity->getClub(), $args->getObjectManager());
        }
    }

    private function updateClubBudget(?Club $club, EntityManagerInterface $entityManager): void
    {
        if ($club) {
            error_log("ClubBudgetListener: Actualizando presupuesto para club " . $club->getNombre());
            $club->guardarNuevoPresupuesto();
            $entityManager->persist($club);
            // En eventos pre, el flush se hace automáticamente después
        }
    }
}
