<?php

declare(strict_types=1);

namespace TestApplication\Filters;

use Doctrine\ORM\QueryBuilder;
use SwagIndustries\Melodiia\Crud\FilterInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use TestApplication\Entity\Todo;

class TodoContainFilter implements FilterInterface
{
    /**
     * @param QueryBuilder $queryBuilder
     */
    public function filter($queryBuilder, FormInterface $form): void
    {
        $q = $form->get('q')->getData();
        if (!empty($q)) {
            $queryBuilder->andWhere($queryBuilder->expr()->like('item.content', ':like'));
            $queryBuilder->setParameter('like', '%' . $q . '%');
        }
    }

    public function supports(string $class): bool
    {
        return Todo::class === $class;
    }

    public function buildForm(FormBuilderInterface $formBuilder): void
    {
        $formBuilder->add('q', TextType::class);
    }
}
