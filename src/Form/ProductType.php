<?php

namespace App\Form;

use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;



class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                "attr" => [
                    "class" => "w-full border px-4 py-2 rounded-lg",
                ]
            ])
            ->add('content', TextareaType::class, [
                "attr" => [
                    "class" => "w-full border px-4 py-2 rounded-lg",
                ]
            ])
            ->add('price',
                NumberType::class, [
                    'html5' => true,
                    'attr' => [
                        'class' => 'w-full border px-4 py-2 rounded-lg',
                        'min' => 0,
                        'step' => '0.5',
                        'placeholder' => '0.00'
                    ]
                ]
            )->add('stock',
                NumberType::class, [
                    'html5' => true,
                    'attr' => [
                        'class' => 'w-full border px-4 py-2 rounded-lg',
                        'min' => 0,
                    ]
                ]
            )
            ->add('image',
                FileType::class, [
                'label' => 'Image (jpg, jpeg, png)',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpg',
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid image (jpg, jpeg, png)',
                    ])
                ],
            ])
            ->add('save', SubmitType::class, ['label' => 'Submit'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
