<?php

namespace SAM\InvestorBundle\Serializer;

use AppBundle\Entity\InvestorStepUpdate;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class InvestorStepUpdateNormalizer extends ObjectNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function normalize($object, $format = null, array $context = array())
    {
        /** @var $object InvestorStepUpdate */
        return [
            'id' => $object->getId(),
            'previousStep' => $this->normalizer->normalize($object->getPreviousStep(), $format, $context),
            'nextStep' => $this->normalizer->normalize($object->getNextStep(), $format, $context),
            'updateDate' => $object->getUpdateDate() ?? 0, // Algolia 'null' not supported.
            'updateDate_timestamp' => $object->getUpdateDate() ? $object->getUpdateDate()->getTimestamp() : 0, // Algolia dates to timestamps: https://www.algolia.com/doc/guides/managing-results/refine-results/filtering/how-to/filter-by-date/?language=php#after
            'author' => $this->normalizer->normalize($object->getAuthor(), $format, $context),

        ];
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof InvestorStepUpdate;
    }
}