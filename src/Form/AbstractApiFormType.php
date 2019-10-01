<?php declare(strict_types=1);

namespace Intergalactic\ApiBundle\Form;

use Symfony\Component\Form\AbstractType as SymfonyAbstractFormType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractApiFormType extends SymfonyAbstractFormType
{
    abstract public function getDataClass(): ?string;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // If you don't set any transformers then Symfony will convert empty
        // strings to null, skipping most validation. Grr!
        /** @var \Symfony\Component\Form\FormBuilderInterface $formChild */
        foreach ($builder->all() as $formChild) {
            $formChild->addViewTransformer($this->getNonTransformer());
        }
        parent::buildForm($builder, $options);
    }

    private function getNonTransformer(): DataTransformerInterface
    {
        // Return an anonymous transformer that performs no operations on form data.
        return new class implements DataTransformerInterface {
            /** {@inheritdoc} */
            public function transform($value)
            {
                return $value;
            }

            /** {@inheritdoc} */
            public function reverseTransform($value)
            {
                return $value;
            }
        };
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('data_class', $this->getDataClass());
        parent::configureOptions($resolver);
    }

    public function getBlockPrefix(): ?string
    {
        // Since this is an API, there's no need to prefix form elements.
        return null;
    }
}
