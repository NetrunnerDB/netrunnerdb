<?php

namespace Netrunnerdb\CardsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CardType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pack', 'entity', array('class' => 'NetrunnerdbCardsBundle:Pack', 'property' => 'name'))
            ->add('quantity')
            ->add('code')
            ->add('position')
            ->add('side', 'entity', array('class' => 'NetrunnerdbCardsBundle:Side', 'property' => 'name'))
            ->add('faction', 'entity', array('class' => 'NetrunnerdbCardsBundle:Faction', 'property' => 'name'))
            ->add('title')
            ->add('uniqueness', 'checkbox', array('required' => false))
            ->add('limited')
            ->add('type', 'entity', array('class' => 'NetrunnerdbCardsBundle:Type', 'property' => 'name'))
            ->add('keywords')
            ->add('factionCost')
            ->add('text', 'textarea', array('required' => false))
            ->add('flavor', 'textarea', array('required' => false))
            ->add('illustrator')
            ->add('cost', 'number', array('required' => false))
            ->add('strength')
            ->add('advancementCost')
            ->add('agendaPoints')
            ->add('minimumDeckSize')
            ->add('influenceLimit')
            ->add('baseLink')
            ->add('memoryUnits')
            ->add('trashCost')
            ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Netrunnerdb\CardsBundle\Entity\Card'
        ));
    }

    public function getName()
    {
        return 'netrunnerdb_cardsbundle_cardtype';
    }
}
