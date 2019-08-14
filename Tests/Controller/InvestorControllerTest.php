<?php

namespace SAM\InvestorBundle\Tests\Controller;

use AppBundle\Entity\Company;
use AppBundle\Entity\Investor;
use SAM\AddressBookBundle\Entity\ContactMerged;
use AppBundle\Entity\InvestorLegalEntity;
use AppBundle\Entity\TimelineEntry;
use AppBundle\Entity\InvestorCancelStatus;
use SAM\CommonBundle\Tests\WebTestCase;

class InvestorControllerTest extends WebTestCase
{
    public function successfulUrlsProvider(): array
    {
        return [
            [
                ['uri' => '/lps/']
            ],
            [
                ['uri' => '/lps/?export=1']
            ],
            [
                ['uri' => '/lps/?investor_search[query]=&investor_search[totalInvestmentAmount][min]=0.00&investor_search[totalInvestmentAmount][max]=50000.00&daterange=24%2F06%2F2018 - 24%2F06%2F2019&investor_search[dateRange][start]=24%2F06%2F2018&investor_search[dateRange][end]=24%2F06%2F2019&investor_search[category]=&investor_search[sourcingType]=&investor_search[legalEntity]=&investor_search[shareCategory]=&investor_search[myInvestor]=0&investor_search[layout]=table']
            ],
            [
                ['uri' => '/lps/nouveau/etape-1']
            ],
        ];
    }

    /**
     * @dataProvider successfulUrlsProvider
     */
    public function testIsSuccessful(array $request, array $roles = null)
    {
        $this->assertRequestSuccess($request, $roles);
    }

    public function testCreateAction()
    {
        $request = ['uri' => '/lps/nouveau/etape-1'];
        $this->logIn();
        $response = $this->sendRequest($request, $crawler);
        $this->assertTrue($response->isSuccessful());

        $company = $this->getFactory()->create(Company::class);
        $formNode = $crawler->filter('form[name="investor_import"]');
        $this->assertGreaterThan(0, $formNode->count(), 'Le formulaire de creation de LP n\'existe pas');

        $form = $formNode->form(['investor_import[companyOrContact]' => $company->getId(), 'investor_import[type]' => Investor::TYPE_LEGAL_PERSON]);
        $response = $this->submit($form);

        $this->assertTrue($response->isRedirect(), 'Aucune redirection');
        $this->assertRegExp("@/lps/nouveau/etape-2/".Investor::TYPE_LEGAL_PERSON."/{$company->getId()}$@", $response->headers->get('location'), 'Erreur dans la redirection');

        $contact = $this->getFactory()->create(ContactMerged::class);
        $formNode = $crawler->filter('form[name="investor_import"]');
        $this->assertGreaterThan(0, $formNode->count(), 'Le formulaire de creation de LP n\'existe pas');

        $form = $formNode->form(['investor_import[companyOrContact]' => $contact->getId(), 'investor_import[type]' => Investor::TYPE_NATURAL_PERSON]);
        $response = $this->submit($form);

        $this->assertTrue($response->isRedirect(), 'Aucune redirection');
        $this->assertRegExp("@/lps/nouveau/etape-2/".Investor::TYPE_NATURAL_PERSON."/{$contact->getId()}$@", $response->headers->get('location'), 'Erreur dans la redirection');
    }

     public function testCreateStep2Action()
     {
         $company = $this->getFactory()->create(Company::class);
         $request = ['uri' => "/lps/nouveau/etape-2/".Investor::TYPE_LEGAL_PERSON."/{$company->getId()}"];

         $this->logIn();
         $response = $this->sendRequest($request, $crawler);
         $this->assertTrue($response->isSuccessful());

         $formNode = $crawler->filter('form[name="investor"]');
         $this->assertGreaterThan(0, $formNode->count());

         $contact = $this->getFactory()->create(ContactMerged::class);
         $request = ['uri' => "/lps/nouveau/etape-2/".Investor::TYPE_NATURAL_PERSON."/{$contact->getId()}"];

         $this->logIn();
         $response = $this->sendRequest($request, $crawler);
         $this->assertTrue($response->isSuccessful());

         $formNode = $crawler->filter('form[name="investor"]');
         $this->assertGreaterThan(0, $formNode->count());
     }
    
    public function testShowAction()
    {
        $investor = $this->getFactory()->create(Investor::class);
        $request = ['uri' => "/lps/{$investor->getId()}"];

        $this->logIn();
        $response = $this->sendRequest($request, $crawler);
        $this->assertTrue($response->isSuccessful(), 'Impossible de consulter la fiche d\'un LP');

        $legalEntityTab = $crawler->filter('a:contains("'.$investor->getInvestorLegalEntities()[0]->getLegalEntity()->getName().'")');
        $this->assertGreaterThan(0, $legalEntityTab->count(), 'Ne contient pas l\'onglet véhicule');

        $heading1 = $crawler->filter('a:contains("'.$investor->getName().'")');
        $this->assertGreaterThan(0, $heading1->count(), 'Ne contient pas le bon titre');

        $category = $crawler->filter('div.investor-category > .big-text:contains("'.$investor->getCategory()->getName().'")');
        $this->assertGreaterThan(0, $category->count(), 'Ne contient pas la bonne catégorie');

        $formNode = $crawler->filter('form[name="interaction_note"]');
        $this->assertGreaterThan(0, $formNode->count());

        $formNode = $crawler->filter('form[name="interaction_email"]');
        $this->assertGreaterThan(0, $formNode->count());

        $formNode = $crawler->filter('form[name="interaction_call"]');
        $this->assertGreaterThan(0, $formNode->count());

        $formNode = $crawler->filter('form[name="interaction_letter"]');
        $this->assertGreaterThan(0, $formNode->count());

        $formNode = $crawler->filter('form[name="interaction_appointment"]');
        $this->assertGreaterThan(0, $formNode->count());
    }
}