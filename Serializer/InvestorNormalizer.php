<?php

namespace SAM\InvestorBundle\Serializer;

use AppBundle\Entity\Investor;
use AppBundle\Entity\User;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class InvestorNormalizer extends ObjectNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(ClassMetadataFactoryInterface $classMetadataFactory = null, NameConverterInterface $nameConverter = null, PropertyAccessorInterface $propertyAccessor = null, PropertyTypeExtractorInterface $propertyTypeExtractor = null)
    {
        parent::__construct($classMetadataFactory, $nameConverter, $propertyAccessor, $propertyTypeExtractor);

        $this->setCircularReferenceHandler(function (Investor $object) {
            return $object->getName();
        });
    }

    public function normalize($object, $format = null, array $context = array())
    {
        /** @var $object Investor */
        return [
            'id' => $object->getId(),
            'company' => $this->normalizer->normalize($object->getCompany(), $format, $context),
            'contactMergedId' => $object->getContactMerged() ? $object->getContactMerged()->getId() : 0,
            'type' => $object->getType(),
            'typeAsString' => $object->getTypeAsString(),
            'creatorId' => $object->getCreator() ? $object->getCreator()->getId() : 0,
            'category' => $object->getCategory() ? $object->getCategory()->getId() : 0,
            // 'currentStep' => $this->normalizer->normalize($object->getCurrentStep(), $format, $context),
            // 'currentStepPosition' => $object->getCurrentStep() ? $object->getCurrentStep()->getPosition() : 0,
            'name' => $object->getName(),
            'investorLegalEntityUsers' => array_map(function (User $user) use ($format, $context) {
                return $this->normalizer->normalize($user, $format, $context);
            }, $object->getInvestorLegalEntityUsers()->toArray()),
            'totalInvestmentAmount' => (float) $object->getTotalInvestmentAmount(),
            'createdAt' => $object->getCreatedAt() ?? 0, // Algolia 'null' not supported.
            'createdAt_timestamp' => $object->getCreatedAt() ? $object->getCreatedAt()->getTimestamp() : 0, // Algolia dates to timestamps: https://www.algolia.com/doc/guides/managing-results/refine-results/filtering/how-to/filter-by-date/?language=php#after
        ];
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Investor;
    }
}