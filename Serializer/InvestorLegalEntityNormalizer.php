<?php

namespace SAM\InvestorBundle\Serializer;

use AppBundle\Entity\Board;
use AppBundle\Entity\InvestorLegalEntity;
use AppBundle\Entity\InvestorLegalEntityDetails;
use AppBundle\Entity\User;
use SAM\InvestorBundle\Entity\InvestorStepUpdate;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\PropertyInfo\PropertyTypeExtractorInterface;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class InvestorLegalEntityNormalizer extends ObjectNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function __construct(ClassMetadataFactoryInterface $classMetadataFactory = null, NameConverterInterface $nameConverter = null, PropertyAccessorInterface $propertyAccessor = null, PropertyTypeExtractorInterface $propertyTypeExtractor = null)
    {
        parent::__construct($classMetadataFactory, $nameConverter, $propertyAccessor, $propertyTypeExtractor);

        $this->setCircularReferenceHandler(function (InvestorLegalEntity $object) {
            return sprintf('%s %s', $object->getInvestor()->getName(), $object->getLegalEntity()->getName());
        });
    }

    public function normalize($object, $format = null, array $context = array())
    {
        /** @var $object InvestorLegalEntity */
        return [
            'id' => $object->getId(),
            'status' => $object->getStatus(),
            'investor' => $this->normalizer->normalize($object->getInvestor(), $format, $context),
            'legalEntity' => $this->normalizer->normalize($object->getLegalEntity(), $format, $context),
            'fundraiser' => $this->normalizer->normalize($object->getFundraiser(), $format, $context),
            'details' => array_map(function (InvestorLegalEntityDetails $details) use ($format, $context) {
                return $this->normalizer->normalize($details, $format, $context);
            }, $object->getDetails()->toArray()),
            'sourcing' => $this->normalizer->normalize($object->getSourcing(), $format, $context),
            'boards' => array_map(function (Board $board) use ($format, $context) {
                return $this->normalizer->normalize($board, $format, $context);
            }, $object->getBoards()->toArray()),
            'users' => array_map(function (User $user) use ($format, $context) {
                return $this->normalizer->normalize($user, $format, $context);
            }, $object->getUsers()->toArray()),
            'closing' => $object->getClosing(),
            'warrantSignedAt' => $object->getWarrantSignedAt() ?? 0, // Algolia 'null' not supported.
            'warrantSignedAt_timestamp' => $object->getWarrantSignedAt() ? $object->getWarrantSignedAt()->getTimestamp() : 0, // Algolia dates to timestamps: https://www.algolia.com/doc/guides/managing-results/refine-results/filtering/how-to/filter-by-date/?language=php#after
            'soldAt' => $object->getSoldAt() ?? 0, // Algolia 'null' not supported.
            'soldAt_timestamp' => $object->getSoldAt() ? $object->getSoldAt()->getTimestamp() : 0, // Algolia dates to timestamps: https://www.algolia.com/doc/guides/managing-results/refine-results/filtering/how-to/filter-by-date/?language=php#after
            'investmentAmount' => (float)$object->getInvestmentAmount(),
            'investmentRangeMin' => (float)$object->getInvestmentAmount(),
            'investmentRangeMax' => (float)$object->getInvestmentAmount(),
            'investmentPercentage' => (float)$object->getInvestmentPercentage(),
            'notes' => $object->getNotes(),
            'contactPrimary' => $object->getContactPrimary() ? $object->getContactPrimary()->getId() : 0,
            // 'investorStepUpdates' => array_map(function (InvestorStepUpdate $investorStepUpdate) use ($format, $context) {
            //     return $this->normalizer->normalize($investorStepUpdate, $format, $context);
            // }, $object->getInvestor()->getStepUpdates()->toArray()),

            // needed for sorting
            // 'currentStep' => $this->normalizer->normalize($object->getInvestor()->getCurrentStep(), $format, $context),
            // 'currentStepPosition' => $object->getInvestor()->getCurrentStep() ? $object->getInvestor()->getCurrentStep()->getPosition() : 0,
        ];
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof InvestorLegalEntity;
    }
}