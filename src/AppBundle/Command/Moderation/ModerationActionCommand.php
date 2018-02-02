<?php

namespace AppBundle\Command\Moderation;

use AppBundle\Entity\Decklist;
use AppBundle\Entity\User;
use AppBundle\Service\ModerationHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class ModerationActionCommand extends ContainerAwareCommand
{
    /** @var InputInterface $input */
    private $input;

    /** @var OutputInterface $output */
    private $output;

    /** @var QuestionHelper */
    private $helper;

    /** @var EntityManagerInterface $entityManager */
    private $entityManager;

    /** @var ModerationHelper $moderationHelper */
    private $moderationHelper;

    public function __construct(EntityManagerInterface $entityManager, ModerationHelper $moderationHelper)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->moderationHelper = $moderationHelper;
    }

    protected function configure()
    {
        $this
            ->setName('app:moderation:action')
            ->setDescription('Changes the moderation status of a decklist');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $this->helper = $this->getHelper('question');

        /** @var User $user */
        $user = $this->entityManager->getRepository('AppBundle:User')->find(1);

        $decklistId = $this->helper->ask($input, $output, new Question('Please enter the id of the decklist: '));
        $decklist = $this->getDecklist($decklistId);

        $this->showStatus($decklist);

        $newStatus = $this->getNewStatus();
        $this->moderationHelper->changeStatus($user, $decklist, $newStatus);

        $this->entityManager->flush();
        $this->entityManager->refresh($decklist);

        $this->showStatus($decklist);
    }

    protected function showStatus(Decklist $decklist)
    {
        $this->output->writeln('<info>Decklist: ' . $decklist->getName() . '. Current moderation status: ' . $this->moderationHelper->getLabel($decklist->getModerationStatus()) . '</info>');
    }


    /**
     *
     * @return integer
     */
    protected function getNewStatus()
    {
        $choices = ['Published', 'Restored', 'Trashed', 'Deleted'];
        $question = new ChoiceQuestion(
            'Which status do you want to set on the decklist? ',
            $choices
        );
        $question->setErrorMessage('Status %s is invalid.');

        $answer = $this->helper->ask($this->input, $this->output, $question);

        return array_search($answer, $choices);
    }

    /**
     *
     * @param integer $decklistId
     * @return Decklist
     */
    protected function getDecklist(int $decklistId)
    {
        $decklist = $this->entityManager->getRepository('AppBundle:Decklist')->find($decklistId);
        if ($decklist instanceof Decklist) {
            return $decklist;
        }

        $this->output->writeln('<error>Not Found</error>');
        die;
    }
}
