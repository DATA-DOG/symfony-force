<?php

namespace AppBundle\Form\Type\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', 'text', [
                'label' => 'Firstname',
                'required' => true,
            ])
            ->add('lastname', 'text', [
                'label' => 'Lastname',
                'required' => true,
            ])
            ->add('plainPassword', 'repeated', [
                'type' => 'password',
                'invalid_message' => 'Not match do passwords.',
                'required' => true,
                'first_options'  => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat password'],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'AppBundle\Entity\User',
            'validation_groups' => 'profile',
            'intention' => 'profile',
        ]);
    }

    public function getName()
    {
        return 'profile';
    }
}
