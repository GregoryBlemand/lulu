<?php

namespace CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PageType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, array(
                'label' => 'Titre de la page :',
                'attr'  => array('class' => 'form-control')
            ))
            ->add('slug', TextType::class, array(
                'label' => 'lien vers la page :',
                'attr'  => array('class' => 'form-control')
            ))
            ->add('content', TextareaType::class, array(
                'label' => 'Contenu :',
                'required' => false,
                'attr'  => array('class' => 'form-control tinymce')
            ))
            ->add('tags', TextType::class, array(
                'label' => 'Mots Clés de recherche :',
                'required' => false,
                'attr'  => array('class' => 'form-control')
            ))
            ->add('publication')
            ->add('save', SubmitType::class, array(
                'label' => 'envoyer',
                'attr' => array('class' => 'btn btn-primary')
            ));
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CoreBundle\Entity\Page'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'corebundle_page';
    }


}
