<?php

namespace SAM\InvestorBundle\Serializer;

use AppBundle\Entity\InvestorStep;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class InvestorStepNormalizer extends ObjectNormalizer
{
    public function normalize($object, $format = null, array $context = array())
    {
        /** @var $object InvestorStep */
        return [
            'id' => $object->getId(),
            'name' => $object->getName(),
            'backgroundColor' => $object->getBackgroundColor(),
        ];
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof InvestorStep;
    }
}