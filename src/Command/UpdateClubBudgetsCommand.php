<?php

namespace App\Command;

use App\Entity\Club;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:update-club-budgets',
    description: 'Actualiza el presupuesto restante de todos los clubs',
)]
class UpdateClubBudgetsCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Actualizando presupuestos de clubs...');

        $clubs = $this->entityManager->getRepository(Club::class)->findAll();
        
        if (empty($clubs)) {
            $io->warning('No se encontraron clubs en la base de datos.');
            return Command::SUCCESS;
        }

        $updated = 0;
        foreach ($clubs as $club) {
            $club->guardarNuevoPresupuesto(); // ← Usar tu método
            $this->entityManager->persist($club);
            $updated++;
            
            $io->text(sprintf(
                'Club: %s - Presupuesto Total: %s€ - Presupuesto Restante: %s€',
                $club->getNombre(),
                $club->getPresupuesto(),
                $club->getPresupuestoRestante()
            ));
        }

        $this->entityManager->flush();

        $io->success(sprintf('Se actualizaron %d clubs correctamente.', $updated));

        return Command::SUCCESS;
    }
}
