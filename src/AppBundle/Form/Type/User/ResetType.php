<?php

namespace AppBundle\Form\Type\User;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrue as RecaptchaTrue;

class ResetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'user.label.email',
                'required' => true,
                'constraints' => [
                    new NotBlank(['message' => 'Email address cannot be empty']),
                    new Email(['message' => 'Email address is not valid']),
                ],
            ])
            ->add('captcha', 'ewz_recaptcha', [
                'label' => 'user.reset.verification',
                'constraints' => [
                    new RecaptchaTrue(['message'=>'Invalid verification code'])
                ],
            ])
        ;

    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
            'csrf_token_id' => 'reset_password',
        ]);
    }
}
