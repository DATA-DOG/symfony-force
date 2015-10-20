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
                'label' => 'user.label.firstname',
                'required' => true,
            ])
            ->add('lastname', 'text', [
                'label' => 'user.label.lastname',
                'required' => true,
            ])
            ->add('plainPassword', 'repeated', [
                'type' => 'password',
                'invalid_message' => 'user.label.password_mismatch',
                'required' => true,
                'first_options'  => ['label' => 'user.label.password'],
                'second_options' => ['label' => 'user.label.repeat_password'],
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
