<?php

namespace CoreBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
                'label' => 'texte du lien :',
                'attr'  => array('class' => 'form-control')
            ))
            ->add('url', TextType::class, array(
                'label' => 'url :',
                'attr'  => array('class' => 'form-control')
            ))
            ->add('ordre', HiddenType::class, array(
                'label' => 'ordre dans le menu :',
                'attr'  => array('class' => 'form-control', 'value' => 0)
            ))
            ->add('type', ChoiceType::class, array(
                'label' => 'type de lien :',
                'choices'  => array(
                    'Lien' => 'Lien',
                    'Galerie' => 'Galerie',
                    'Page' => 'Page'
                ),
                'attr'  => array('class' => 'form-control')
            ))
            ->add('save', SubmitType::class, array(
                'label' => 'Enregistrer',
                'attr'  => array('class' => 'btn btn-primary pull-right')
            ));
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
