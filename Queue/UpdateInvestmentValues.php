<?php

namespace SAM\InvestorBundle\Queue;

use Enqueue\Util\JSON;

class UpdateInvestmentValues implements \JsonSerializable
{
    /**
     * UpdateInvestmentValues constructor.
     */
    public function __construct()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return [
        ];
    }

    /**
     * @param string $json
     *
     * @return self
     */
    public static function jsonDeserialize(string $json): self
    {
        $data = array_replace([
        ], JSON::decode($json));

        return new static();
    }
}
