<?php

namespace AppBundle\Form\Type\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ConfirmType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', 'text', [
                'label' => 'First name',
                'required' => true,
            ])
            ->add('lastname', 'text', [
                'label' => 'Last name',
                'required' => true,
            ])
            ->add('plainPassword', 'repeated', [
                'type' => 'password',
                'invalid_message' => 'Passwords does not match',
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
            'validation_groups' => 'confirm',
            'intention' => 'confirm',
        ]);
    }

    public function getName()
    {
        return 'confirm';
    }
}
