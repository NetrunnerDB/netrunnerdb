<?php

namespace Netrunnerdb\CardsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('code')
            ->add('name')
            ->add('dateRelease')
            ->add('size')
            ->add('position')
            ->add('cycle', 'entity', array('class' => 'NetrunnerdbCardsBundle:Cycle', 'property' => 'name'))
        ;
    }
    
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Netrunnerdb\CardsBundle\Entity\Pack'
        ));
    }

    public function getName()
    {
        return 'netrunnerdb_cardsbundle_packtype';
    }
}
