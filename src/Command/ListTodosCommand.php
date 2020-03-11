<?php
/**
 * Gestion de la commande de liste des tâches en ligne de commande
 *
 * @copyright  2017-2018 Telecom SudParis
 * @license    "MIT/X" License - cf. LICENSE file at project root
 */

namespace App\Command;

use App\Entity\Todo;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Command\Command;
use Doctrine\Persistence\ManagerRegistry;


/**
 * Command ListTodo
 * 
 * cf. https://symfony.com/doc/current/console.html
 * 
 */
class ListTodosCommand extends Command
{    
    
    private $doctrineManager;
    private $todoRepository;
    
    public function __construct(ManagerRegistry $doctrineManager)
    {
        $this->doctrineManager = $doctrineManager;
        $this->todoRepository = $doctrineManager->getRepository(Todo::class);
        parent::__construct();
    }
    
    protected function configure()
    {
        $this
        // the name of the command (the part after "bin/console")
        ->setName('app:list-todos')
        
        // the short description shown while running "php bin/console list"
        ->setDescription('List the todos.')
        
        // the full command description shown when running the command with
        // the "--help" option
        ->setHelp('This command allows you to list the todos')
        ;
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $errOutput = $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output;
        
        // récupère une liste toutes les instances de la classe Todo 
        $todos = $this->todoRepository->findAll();
        
        if(! empty($todos)) {
            $output->writeln('list of todos:');
            foreach($todos as $todo) {
                //$output->writeln($todo->__toString());
                $output->writeln($todo);
            }
        } else {
            $errOutput->writeln('<error>no todos found!</error>');
            return 1;
        }
        return 0;
    }
}
