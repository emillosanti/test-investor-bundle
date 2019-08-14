<?php

namespace SAM\InvestorBundle\Serializer;

use AppBundle\Entity\InvestorLegalEntityDetails;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class InvestorLegalEntityDetailsNormalizer extends ObjectNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(ClassMetadataFactoryInterface $classMetadataFactory = null, NameConverterInterface $nameConverter = null, PropertyAccessorInterface $propertyAccessor = null, PropertyTypeExtractorInterface $propertyTypeExtractor = null)
    {
        parent::__construct($classMetadataFactory, $nameConverter, $propertyAccessor, $propertyTypeExtractor);

        $this->setCircularReferenceHandler(function (InvestorLegalEntityDetails $object) {
            return $object->getId();
        });
    }

    public function normalize($object, $format = null, array $context = array())
    {
        /** @var $object InvestorLegalEntityDetails */
        return [
            'id' => $object->getId(),
            'shareCategory' => $this->normalizer->normalize($object->getShareCategory(), $format, $context),
            // 'investorStep' => $this->normalizer->normalize($object->getInvestorStep(), $format, $context),
            'amount' => $object->getAmount(),
        ];
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof InvestorLegalEntityDetails;
    }
}