<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use App\Entity\Contact;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {

        $builder
            ->add("user_name", TextType::class, 
                [   "label" => "Votre nom",
                    "attr" => [
                    "class" => "form-control"
                    ] 
                ]
            )
            ->add("user_email", EmailType::class, 
                [   "label" => "Votre Email",
                    "attr" => [
                    "class" => "form-control"
                    ] 
                ]
            )
            ->add("message_object", TextType::class, 
                [   "label" => "Objet de votre message",
                    "attr" => [
                    "class" => "form-control"
                    ] 
                ]
            )
            ->add('message_date', DateType::class, [
                'label'     =>  'Date',
                'widget'    => 'single_text',
                'input'     =>  'datetime',
                'data'      => new \DateTime('now'),
                'disabled'  =>  true,
                    "attr"  => [
                    "class" => "form-control"  
                    ]

              ]
            )
            ->add("message_content", TextareaType::class, 
                [   "label" => "Votre message",
                    "attr" => [
                    "class" => "form-control"
                    ] 
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver) {

        $resolver->setDefaults(
            ['data_class' => Contact::class]
        );
    }
}