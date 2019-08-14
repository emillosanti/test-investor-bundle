<?php

namespace SAM\InvestorBundle\Tests\Controller;

use SAM\CommonBundle\Tests\WebTestCase;

class MetricsControllerTest extends WebTestCase
{
    /**
     * @return array
     */
    public function successfulUrlsProvider(): array
    {
        return [
            [
                ['uri' => '/statistiques/lps/']
            ],
            [
                ['uri' => '/statistiques/lps/export']
            ],
            [
                ['uri' => '/statistiques/lps/?investor_metrics%5BlegalEntity%5D=&investor_metrics%5Buser%5D=&investor_metrics%5BinvestorCategory%5D=&investor_metrics%5BhasFundraiser%5D=0&investor_metrics%5BtotalInvestmentAmount%5D%5Bmin%5D=0&investor_metrics%5BtotalInvestmentAmount%5D%5Bmax%5D=40']
            ]
        ];
    }

    /**
     * @dataProvider successfulUrlsProvider
     */
    public function testPageIsSuccessful(array $request, array $roles = null)
    {
        $this->assertRequestSuccess($request, $roles);
    }
}
