<?php

namespace App\Form;

use App\Entity\Blog;
use App\Entity\CategoryProduct;
use App\Entity\Tag;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tetranz\Select2EntityBundle\Form\Type\Select2EntityType;

class BlogType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title')
            ->add('lead')
            ->add('category', EntityType::class, [
                'class' => CategoryProduct::class,
                'query_builder' => function (EntityRepository $er){
                    return $er->createQueryBuilder('c')
                        ->where('c.active = 1');
                },
                'placeholder' => 'Seleccionar'
            ])
            ->add('description', TextareaType::class, [
                'attr' => [
                    'class' => 'tinymce',
                    'data-theme' => 'advanced',
                    'rows' => 15
                ]
            ])
            ->add('tags', Select2EntityType::class, [
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
            'data_class' => Blog::class,
        ]);
    }
}
