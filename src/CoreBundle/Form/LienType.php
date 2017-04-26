<?php

namespace CoreBundle\Form;

use CoreBundle\CoreBundle;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LienType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, array(
                'label' => 'Titre du lien (visible par les visiteurs):',
                'attr'  => array('class' => 'form-control')
            ))
            ->add('type', ChoiceType::class, array(
                'label' => 'type de lien',
                'attr'  => array('class' => 'form-control'),
                'placeholder' => 'Choisir un type de lien',
                'choices' => array(
                    'externe' => 'EXTERNE',
                    'page' => 'PAGE',
                    'galerie' => 'GALERIE',
                )
            ))
            ->add('url', TextType::class, array(
                'label' => 'Adresse internet :',
                'attr'  => array(
                    'class' => 'form-control',
                    'required' => false,
                )
            ))
            ->add('page', EntityType::class, array(
                'attr'  => array(
                    'class' => 'form-control',
                    'required' => false,
                ),
                'class'        => 'CoreBundle:Page',
                'choice_label' => 'title',
                'placeholder' => 'Quelle page choisir',
                'empty_data'  => null
            ))
            ->add('galerie', EntityType::class, array(
                'attr'  => array(
                    'class' => 'form-control',
                    'required' => false,
                ),
                'class'        => 'CoreBundle:Galerie',
                'choice_label' => 'title',
                'placeholder' => 'Quelle galerie choisir',
                'empty_data'  => null
            ))
            ->add('save', SubmitType::class, array(
                'label' => 'Enregistrer',
                'attr'  => array('class' => 'btn btn-primary pull-right')
            ))
        ;
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CoreBundle\Entity\Lien'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'corebundle_lien';
    }


}
