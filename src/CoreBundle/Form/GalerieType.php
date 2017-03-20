<?php

namespace CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GalerieType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, array(
                'label' => 'Titre de la galerie :',
                'attr'  => array('class' => 'form-control')
            ))
            ->add('description', TextareaType::class, array(
                'label' => 'Description :',
                'attr'  => array('class' => 'form-control')
            ))
            ->add('private', CheckboxType::class, array(
                'label' => 'Rendre la galerie privée : ',
                'required' => false
            ))
            ->add('images', CollectionType::class, array(
                'entry_type'   => ImageType::class,
                'allow_add'    => true,
                'allow_delete' => true
            ))
            ->add('save', SubmitType::class, array(
                'label' => 'Enregistrer',
                'attr'  => array('class' => 'btn btn-primary')
            ));
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CoreBundle\Entity\Galerie'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'corebundle_galerie';
    }


}
