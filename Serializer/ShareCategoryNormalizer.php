<?php

namespace SAM\InvestorBundle\Serializer;

use AppBundle\Entity\ShareCategory;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class ShareCategoryNormalizer extends ObjectNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function normalize($object, $format = null, array $context = array())
    {
        /** @var $object ShareCategory */
        return [
            'id' => $object->getId(),
            'name' => $object->getName(),
            'legalEntity' => $this->normalizer->normalize($object->getLegalEntity(), $format, $context),
            'unitPrice' => $object->getUnitPrice(),
        ];
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof ShareCategory;
    }
}