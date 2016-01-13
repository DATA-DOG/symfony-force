<?php

namespace AppBundle\Form\Type\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ConfirmType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'user.label.firstname',
                'required' => true,
            ])
            ->add('lastname', TextType::class, [
                'label' => 'user.label.lastname',
                'required' => true,
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => 'password',
                'invalid_message' => 'Passwords does not match',
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
            'data_class' => \AppBundle\Entity\User::class,
            'validation_groups' => 'confirm',
            'csrf_token_id' => 'confirm',
        ]);
    }
}
