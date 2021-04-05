<?php

namespace App\Form;

use App\Entity\CategoryProduct;
use App\Entity\Product;
use App\Entity\Tag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('lead')
            ->add('description', TextareaType::class, [
                'attr' => array(
                    'class' => 'tinymce',
                    'data-theme' => 'bbcode', // Skip it if you want to use default theme
                    'rows' => 15
                )

            ])
            ->add('url', UrlType::class, [
                'attr' => [
                    'placeholder' => 'https://'
                ]
            ])
            ->add('price', MoneyType::class, [
                'currency' => 'USD'
            ])
            ->add('categoryProduct', EntityType::class, [
                'class' => CategoryProduct::class,
                'placeholder' => 'Seleccionar'
            ])
            ->add('tag', Select2EntityType::class, [
                'remote_route' => 'tag_json_list',
                'class' => Tag::class,
                'primary_key' => 'id',
                'text_property' => 'name',
                'minimum_input_length' => 0,
                'multiple' => true,
                'page_limit' => 0,
                'allow_clear' => true,
                'delay' => 250,
                'cache' => true,
                'cache_timeout' => 60000, // if 'cache' is true
                'language' => 'es',
                'placeholder' => 'Seleccionar',
                'allow_add' => [
                    'enabled' => true,
                    'new_tag_text' => ' (NEW)',
                    'new_tag_prefix' => '__',
                    'tag_separators' => '[",", " "]'
                ],

            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
