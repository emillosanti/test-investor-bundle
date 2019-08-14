<?php

namespace SAM\InvestorBundle\Manager;

use SAM\CommonBundle\Manager\AbstractManager;
use SAM\InvestorBundle\Entity\InvestorLegalEntity;
use SAM\SearchBundle\Manager\SearchEngineManager;
use SAM\InvestorBundle\Repository\InvestorLegalEntityRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class InvestorManager
 */
class InvestorManager extends AbstractManager
{
    /**
     * @var SearchEngineManager
     */
    protected $searchEngineManager;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * InvestorManager constructor.
     *
     * @param EntityManagerInterface $em
     * @param TokenStorageInterface $tokenStorage
     * @param string $projectDirectory
     * @param string $siteName
     * @param SearchEngineManager $searchEngineManager
     * @param TranslatorInterface $translator
     */
    public function __construct(
        EntityManagerInterface $em,
        TokenStorageInterface $tokenStorage,
        string $projectDirectory,
        string $siteName,
        SearchEngineManager $searchEngineManager,
        TranslatorInterface $translator
    )
    {
        parent::__construct($em, $tokenStorage, $projectDirectory, $siteName);

        $this->searchEngineManager = $searchEngineManager;
        $this->translator = $translator;
    }

    private $letters = [
        'A', 'B', 'C', 'D', 'E', 'F',
        'G', 'H', 'I', 'J', 'K', 'L',
        'M', 'N', 'O', 'P', 'Q', 'R',
        'S', 'T', 'U', 'V', 'W', 'X',
        'Y', 'Z',
        'AA', 'AB', 'AC', 'AD', 'AE', 'AF', 'AG', 'AH', 'AI', 'AJ', 'AK', 'AL'
    ];

    /**
     * @param Spreadsheet $document
     * @param array $criterias
     *
     * @return Spreadsheet
     *
     * @throws Exception
     */
    protected function addRows(Spreadsheet $document, $criterias = [])
    {
        $sheet = $document->getSheet($this->defaultSheet);

        /** @var InvestorLegalEntity[] $investors */
        $investorLegalEntities = $this->searchEngineManager->getDoctrineRepository(InvestorLegalEntityRepositoryInterface::class)
            ->findInvestorLegalEntitiesWithoutPagination($criterias, $this->tokenStorage->getToken()->getUser());

        $rowNumber = 4;
        /** @var InvestorLegalEntity $investorLegalEntity */
        foreach ($investorLegalEntities as $investorLegalEntity) {
            $sheet->getRowDimension($rowNumber)->setRowHeight(25);
            $legalEntity = $investorLegalEntity->getLegalEntity();
            $investor = $investorLegalEntity->getInvestor();
            $sourcing = $investorLegalEntity->getSourcing();
            $fundraiser = $investorLegalEntity->getFundraiser();
            $primaryContact = $investorLegalEntity->getContactPrimary();
            $creator = $investor->getCreator();
            $users = $investorLegalEntity->getUsers();

            $sheet->getCell($this->letters[1] . $rowNumber)->setValue($investor->getId());
            $sheet->getCell($this->letters[2] . $rowNumber)->setValue($legalEntity->getName());
            $sheet->getCell($this->letters[3] . $rowNumber)->setValue($investorLegalEntity->getWarrantSignedAt() != null ? $investorLegalEntity->getWarrantSignedAt()->format('d/m/Y') : '');
            $sheet->getCell($this->letters[4] . $rowNumber)->setValue($investorLegalEntity->getClosing());
            if ($investor->getInteractedAt()) {
                $sheet->getCell($this->letters[5] . $rowNumber)->setValue($investor->getInteractedAt()->format('d/m/Y'));
            }
            $sheet->getCell($this->letters[6] . $rowNumber)->setValue($investor->getName());
            $sheet->getCell($this->letters[7] . $rowNumber)->setValue($this->translator->trans($investor->getTypeAsString(), [], 'SAMInvestorBundle'));
            $sheet->getCell($this->letters[8] . $rowNumber)->setValue($investor->getCategory()->getName());
            $sheet->getCell($this->letters[9] . $rowNumber)->setValue($investorLegalEntity->getInvestmentAmount());
            $sheet->getCell($this->letters[10] . $rowNumber)->setValue($investorLegalEntity->getInvestmentPercentage());

            if ($sourcing != null) {
                $sheet->getCell($this->letters[11] . $rowNumber)->setValue($sourcing->getCategory() != null ? $sourcing->getCategory()->getName() : '');
                $sheet->getCell($this->letters[12] . $rowNumber)->setValue($sourcing->getCompany() != null ? $sourcing->getCompany()->getName() : '');

                $contact = $sourcing->getContact();
                if ($contact != null) {
                    $sheet->getCell($this->letters[13] . $rowNumber)->setValue($contact->getLastName());
                    $sheet->getCell($this->letters[14] . $rowNumber)->setValue($contact->getFirstName());
                    $sheet->getCell($this->letters[15] . $rowNumber)->setValue($contact->getFirstEmail());
                    $sheet->getCell($this->letters[16] . $rowNumber)->setValue($contact->getFirstPhone());
                    $sheet->getCell($this->letters[17] . $rowNumber)->setValue($contact->getJob());
                }
            }

            if ($fundraiser != null) {
                $sheet->getCell($this->letters[18] . $rowNumber)->setValue((string)$fundraiser);
                $sheet->getCell($this->letters[19] . $rowNumber)->setValue($fundraiser->getFeesAmount());
                $sheet->getCell($this->letters[20] . $rowNumber)->setValue($fundraiser->getFeesPercentage());

                if ($fundraiser->getContact()) {
                    $sheet->getCell($this->letters[22] . $rowNumber)->setValue($fundraiser->getContact()->getLastName());
                    $sheet->getCell($this->letters[23] . $rowNumber)->setValue($fundraiser->getContact()->getFirstName());
                    $sheet->getCell($this->letters[24] . $rowNumber)->setValue($fundraiser->getContact()->getFirstEmail());
                    $sheet->getCell($this->letters[25] . $rowNumber)->setValue($fundraiser->getContact()->getFirstPhone());
                    $sheet->getCell($this->letters[26] . $rowNumber)->setValue($fundraiser->getContact()->getJob());
                }
            }

            $boardNames = '';
            if (count($investorLegalEntity->getBoards()) > 0) {
                $boardNames = implode(', ', $investorLegalEntity->getBoards()->toArray());
            }
            $sheet->getCell($this->letters[21] . $rowNumber)->setValue($boardNames);

            if ($primaryContact != null) {
                $sheet->getCell($this->letters[27] . $rowNumber)->setValue($primaryContact->getLastName());
                $sheet->getCell($this->letters[28] . $rowNumber)->setValue($primaryContact->getFirstName());
                $sheet->getCell($this->letters[29] . $rowNumber)->setValue($primaryContact->getEmailsAsString());
                $sheet->getCell($this->letters[30] . $rowNumber)->setValue($primaryContact->getPhonesAsString());
                $sheet->getCell($this->letters[31] . $rowNumber)->setValue($primaryContact->getJob());
            }

            if ($creator != null) {
                $sheet->getCell($this->letters[32] . $rowNumber)->setValue($creator->getCode());
            }

            if (count($users) > 0) {
                $letterNumber = 33;
                foreach ($users as $user) {
                    $sheet->getCell($this->letters[$letterNumber] . $rowNumber)->setValue($user->getCode());
                    $letterNumber++;
                    if ($letterNumber == 38)
                        break;
                }
            }

            $rowNumber++;
        }

        $sheet->getStyle('B4:AL' . ($rowNumber - 1))->applyFromArray($this->styles['body']);
        $sheet->getStyle('C4:D' . ($rowNumber - 1))->applyFromArray(['font' => ['bold' => true]]);

        return $document;
    }

    /**
     * @param Spreadsheet $document
     *
     * @return Spreadsheet
     *
     * @throws Exception
     */
    protected function addHeader(Spreadsheet $document)
    {
        // Respect order in array
        $headings = [
            ['width' => 10, 'content' => 'ID'],
            ['width' => 20, 'content' => $this->translator->trans('lps.export.legal_entity_name', [], 'SAMInvestorBundle')],
            ['width' => 20, 'content' => $this->translator->trans('lps.export.warrant_signed_at', [], 'SAMInvestorBundle')],
            ['width' => 15, 'content' => $this->translator->trans('lps.export.closing', [], 'SAMInvestorBundle')],
            ['width' => 15, 'content' => $this->translator->trans('lps.export.interacted_at', [], 'SAMInvestorBundle')],
            ['width' => 20, 'content' => $this->translator->trans('lps.export.investor_name', [], 'SAMInvestorBundle')],
            ['width' => 15, 'content' => $this->translator->trans('lps.export.investor_type', [], 'SAMInvestorBundle')],
            ['width' => 20, 'content' => $this->translator->trans('lps.export.investor_category', [], 'SAMInvestorBundle')],
            ['width' => 20, 'content' => $this->translator->trans('lps.export.investment_amount', [], 'SAMInvestorBundle')],
            ['width' => 20, 'content' => $this->translator->trans('lps.export.investment_amount_percentage', [], 'SAMInvestorBundle')],
            ['width' => 20, 'content' => $this->translator->trans('lps.export.sourcing_category', [], 'SAMInvestorBundle')],
            ['width' => 20, 'content' => $this->translator->trans('lps.export.sourcing_company_name', [], 'SAMInvestorBundle')],
            ['width' => 15, 'content' => $this->translator->trans('lps.export.sourcing_lastname', [], 'SAMInvestorBundle')],
            ['width' => 15, 'content' => $this->translator->trans('lps.export.sourcing_firstname', [], 'SAMInvestorBundle')],
            ['width' => 25, 'content' => $this->translator->trans('lps.export.sourcing_email', [], 'SAMInvestorBundle')],
            ['width' => 25, 'content' => $this->translator->trans('lps.export.sourcing_phone', [], 'SAMInvestorBundle')],
            ['width' => 20, 'content' => $this->translator->trans('lps.export.sourcing_job', [], 'SAMInvestorBundle')],
            ['width' => 20, 'content' => $this->translator->trans('lps.export.fundraiser', [], 'SAMInvestorBundle')],
            ['width' => 20, 'content' => $this->translator->trans('lps.export.fees_amount', [], 'SAMInvestorBundle')],
            ['width' => 20, 'content' => $this->translator->trans('lps.export.fees_percentage', [], 'SAMInvestorBundle')],
            ['width' => 20, 'content' => $this->translator->trans('lps.export.board', [], 'SAMInvestorBundle')],
            ['width' => 20, 'content' => $this->translator->trans('lps.export.contact_lastname', [], 'SAMInvestorBundle')],
            ['width' => 20, 'content' => $this->translator->trans('lps.export.contact_firstname', [], 'SAMInvestorBundle')],
            ['width' => 20, 'content' => $this->translator->trans('lps.export.contact_emails', [], 'SAMInvestorBundle')],
            ['width' => 20, 'content' => $this->translator->trans('lps.export.contact_phones', [], 'SAMInvestorBundle')],
            ['width' => 20, 'content' => $this->translator->trans('lps.export.contact_job', [], 'SAMInvestorBundle')],
            ['width' => 20, 'content' => $this->translator->trans('lps.export.contact_lastname', [], 'SAMInvestorBundle')],
            ['width' => 20, 'content' => $this->translator->trans('lps.export.contact_firstname', [], 'SAMInvestorBundle')],
            ['width' => 20, 'content' => $this->translator->trans('lps.export.contact_emails', [], 'SAMInvestorBundle')],
            ['width' => 20, 'content' => $this->translator->trans('lps.export.contact_phones', [], 'SAMInvestorBundle')],
            ['width' => 20, 'content' => $this->translator->trans('lps.export.contact_job', [], 'SAMInvestorBundle')],
            ['width' => 10, 'content' => $this->translator->trans('lps.export.creator', [], 'SAMInvestorBundle')],
            ['width' => 10, 'content' => $this->translator->trans('lps.export.users_label_letter', [], 'SAMInvestorBundle') . '1'],
            ['width' => 10, 'content' => $this->translator->trans('lps.export.users_label_letter', [], 'SAMInvestorBundle') . '2'],
            ['width' => 10, 'content' => $this->translator->trans('lps.export.users_label_letter', [], 'SAMInvestorBundle') . '3'],
            ['width' => 10, 'content' => $this->translator->trans('lps.export.users_label_letter', [], 'SAMInvestorBundle') . '4'],
            ['width' => 10, 'content' => $this->translator->trans('lps.export.users_label_letter', [], 'SAMInvestorBundle') . '5'],
        ];

        $settings = [
            [
                'begin' => 'B',
                'end' => 'K'
            ],
            [
                'begin' => 'L',
                'end' => 'R',
                'value' => $this->translator->trans('lps.export.sourcing', [], 'SAMInvestorBundle')
            ],
            [
                'begin' => 'W',
                'end' => 'AA',
                'value' => $this->translator->trans('lps.export.fundraiser_contact', [], 'SAMInvestorBundle')
            ],
            [
                'begin' => 'AB',
                'end' => 'AF',
                'value' => $this->translator->trans('lps.export.primary_contact', [], 'SAMInvestorBundle')
            ],
            [
                'begin' => 'S',
                'end' => 'V'
            ],
            [
                'begin' => 'AG',
                'end' => 'AL'
            ],
        ];

        $this->formatColumns($document->getSheet($this->defaultSheet), $settings, $headings);

        return $document;
    }
}
