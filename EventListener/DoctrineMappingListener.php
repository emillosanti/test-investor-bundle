<?php

namespace SAM\InvestorBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;

/**
 * This class adds automatically the ManyToOne and OneToMany relations,
 * because it's normally impossible to do so in a mapped superclass.
 */
class DoctrineMappingListener implements EventSubscriber
{
    /**
     * @var string
     */
    private $investorClass;

    /**
     * @var string
     */
    private $investorStepClass;

    /**
     * @var string
     */
    private $investorStepUpdateClass;

    /**
     * @var string
     */
    private $investorCategoryClass;

    /**
     * @var string
     */
    private $boardClass;

    /**
     * @var string
     */
    private $userClass;

    /**
     * @var string
     */
    private $companyClass;

    /**
     * @var string
     */
    private $contactMergedClass;

    /**
     * @var string
     */
    private $sourcingClass;

    /**
     * @var string
     */
    private $documentClass;

    /**
     * @var string
     */
    private $interactionEmailClass;

    /**
     * @var string
     */
    private $interactionAppointmentClass;

    /**
     * @var string
     */
    private $interactionLetterClass;

    /**
     * @var string
     */
    private $interactionNoteClass;

    /**
     * @var string
     */
    private $interactionCallClass;

    /**
     * @var string
     */
    private $shareCategoryClass;

    /**
     * @var string
     */
    private $legalEntityClass;

    /**
     * @var string
     */
    private $investorLegalEntityClass;

    /**
     * @var string
     */
    private $investorLegalEntityDetailsClass;

    /**
     * @var string
     */
    private $provisionClass;

    /**
     * @var string
     */
    private $fundraiserClass;

    public function __construct($entities)
    {
        $this->investorClass = $entities['investor']['class']; 
        $this->investorStepClass = $entities['investor_step']['class'];   
        $this->investorStepUpdateClass = $entities['investor_step_update']['class'];
        $this->investorCategoryClass = $entities['investor_category']['class'];
        $this->boardClass = $entities['board']['class'];
        $this->userClass = $entities['user']['class'];
        $this->companyClass = $entities['company']['class'];
        $this->contactMergedClass = $entities['contact_merged']['class'];
        $this->sourcingClass = $entities['sourcing']['class'];
        $this->fundraiserClass = $entities['fundraiser']['class'];

        $this->documentClass = $entities['document']['class'];

        $this->interactionEmailClass = $entities['interaction_email']['class'];
        $this->interactionAppointmentClass = $entities['interaction_appointment']['class'];
        $this->interactionLetterClass = $entities['interaction_letter']['class'];
        $this->interactionNoteClass = $entities['interaction_note']['class'];
        $this->interactionCallClass = $entities['interaction_call']['class'];

        $this->shareCategoryClass = $entities['share_category']['class'];
        $this->legalEntityClass = $entities['legal_entity']['class'];
        $this->investorLegalEntityClass = $entities['investor_legal_entity']['class'];
        $this->investorLegalEntityDetailsClass = $entities['investor_legal_entity_details']['class'];
        $this->provisionClass = $entities['provision']['class'];
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [Events::loadClassMetadata];
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $eventArgs->getClassMetadata();

        $isInvestor = is_a($classMetadata->getName(), $this->investorClass, true);
        // $isInvestorStep = is_a($classMetadata->getName(), $this->investorStepClass, true);
        // $isInvestorStepUpdate = is_a($classMetadata->getName(), $this->investorStepUpdateClass, true);
        $isInvestorCategory = is_a($classMetadata->getName(), $this->investorCategoryClass, true);
        $isBoard = is_a($classMetadata->getName(), $this->boardClass, true);
        $isShareCategory = is_a($classMetadata->getName(), $this->shareCategoryClass, true);
        $isInvestorLegalEntity = is_a($classMetadata->getName(), $this->investorLegalEntityClass, true);
        $isInvestorLegalEntityDetails = is_a($classMetadata->getName(), $this->investorLegalEntityDetailsClass, true);
        $isProvision = is_a($classMetadata->getName(), $this->provisionClass, true);

        if ($isInvestor) {
            $this->processInvestorMetadata($classMetadata);
        }

        if ($isBoard) {
            $this->processBoardMetadata($classMetadata);
        }

        // if ($isInvestorStepUpdate) {
        //     $this->processInvestorStepUpdateMetadata($classMetadata);
        // }

        if ($isShareCategory) {
            $this->processShareCategoryMetadata($classMetadata);
        }

        if ($isInvestorLegalEntity) {
            $this->processInvestorLegalEntityMetadata($classMetadata);
        }

        if ($isInvestorLegalEntityDetails) {
            $this->processInvestorLegalEntityDetailsMetadata($classMetadata);
        }

        if ($isProvision) {
            $this->processProvisionMetadata($classMetadata);
        }
    }

    /**
     * Declare mapping for Investor entity.
     *
     * @param ClassMetadata $classMetadata
     */
    private function processInvestorMetadata(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasAssociation('company')) {
            $classMetadata->mapManyToOne([
                'fieldName'    => 'company',
                'targetEntity' => $this->companyClass,
                'cascade' => [ 'persist' ]
            ]);
        }

        if (!$classMetadata->hasAssociation('contactMerged')) {
            $classMetadata->mapManyToOne([
                'fieldName'    => 'contactMerged',
                'targetEntity' => $this->contactMergedClass,
                'cascade' => [ 'persist' ]
            ]);
        }

        if (!$classMetadata->hasAssociation('creator')) {
            $classMetadata->mapManyToOne([
                'fieldName'    => 'creator',
                'targetEntity' => $this->userClass
            ]);
        }

        // if (!$classMetadata->hasAssociation('currentStep')) {
        //     $classMetadata->mapManyToOne([
        //         'fieldName'    => 'currentStep',
        //         'targetEntity' => $this->investorStepClass,
        //     ]);
        // }

        if (!$classMetadata->hasAssociation('category')) {
            $classMetadata->mapManyToOne([
                'fieldName'    => 'category',
                'targetEntity' => $this->investorCategoryClass,
                'cascade' => [ 'persist' ]
            ]);
        }

        if (!$classMetadata->hasAssociation('investorLegalEntities')) {
            $classMetadata->mapOneToMany([
                'fieldName'    => 'investorLegalEntities',
                'targetEntity' => $this->investorLegalEntityClass,
                'cascade' => [ 'persist', 'remove' ],
                'mappedBy'   => 'investor',
                'orphanRemoval' => true,
            ]);
        }

        // if (!$classMetadata->hasAssociation('stepUpdates')) {
        //     $classMetadata->mapOneToMany([
        //         'fieldName'    => 'stepUpdates',
        //         'targetEntity' => $this->investorStepUpdateClass,
        //         'mappedBy'   => 'investor',
        //         'cascade' => [ 'persist', 'remove' ],
        //         'orphanRemoval' => true
        //     ]);
        // }
    }

    // /**
    //  * Declare mapping for InvestorStepUpdate entity.
    //  *
    //  * @param ClassMetadata $classMetadata
    //  */
    // private function processInvestorStepUpdateMetadata(ClassMetadata $classMetadata)
    // {
    //     if (!$classMetadata->hasAssociation('investor')) {
    //         $classMetadata->mapManyToOne([
    //             'fieldName'    => 'investor',
    //             'targetEntity' => $this->investorClass,
    //             'inversedBy'   => 'stepUpdates',
    //         ]);
    //     }

    //     if (!$classMetadata->hasAssociation('previousStep')) {
    //         $classMetadata->mapManyToOne([
    //             'fieldName'    => 'previousStep',
    //             'targetEntity' => $this->investorStepClass,
    //         ]);
    //     }

    //     if (!$classMetadata->hasAssociation('nextStep')) {
    //         $classMetadata->mapManyToOne([
    //             'fieldName'    => 'nextStep',
    //             'targetEntity' => $this->investorStepClass,
    //         ]);
    //     }

    //     if (!$classMetadata->hasAssociation('author')) {
    //         $classMetadata->mapManyToOne([
    //             'fieldName'    => 'author',
    //             'targetEntity' => $this->userClass,
    //         ]);
    //     }
    // }

    /**
     * Declare mapping for Board entity.
     *
     * @param ClassMetadata $classMetadata
     */
    private function processBoardMetadata(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasAssociation('investorLegalEntities')) {
            $classMetadata->mapManyToMany([
                'fieldName'    => 'investorLegalEntities',
                'targetEntity' => $this->investorLegalEntityClass,
                'mappedBy'   => 'boards',
            ]);
        }
    }

    /**
     * Declare mapping for ShareCategory entity.
     *
     * @param ClassMetadata $classMetadata
     */
    private function processShareCategoryMetadata(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasAssociation('legalEntity')) {
            $classMetadata->mapManyToOne([
                'fieldName'    => 'legalEntity',
                'targetEntity' => $this->legalEntityClass,
                'cascade' => [ 'persist' ]
            ]);
        }
    }

    /**
     * Declare mapping for InvestorLegalEntity entity.
     *
     * @param ClassMetadata $classMetadata
     */
    private function processInvestorLegalEntityMetadata(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasAssociation('investor')) {
            $classMetadata->mapManyToOne([
                'fieldName'    => 'investor',
                'targetEntity' => $this->investorClass,
                'cascade' => [ 'persist' ],
                'inversedBy' => 'investorLegalEntities'
            ]);
        }

        if (!$classMetadata->hasAssociation('legalEntity')) {
            $classMetadata->mapManyToOne([
                'fieldName'    => 'legalEntity',
                'targetEntity' => $this->legalEntityClass,
                'cascade' => [ 'persist' ],
            ]);
        }

        if (!$classMetadata->hasAssociation('details')) {
            $classMetadata->mapOneToMany([
                'fieldName'    => 'details',
                'targetEntity' => $this->investorLegalEntityDetailsClass,
                'mappedBy'   => 'investorLegalEntity',
                'cascade' => [ 'persist', 'remove' ],
                'orphanRemoval' => true
            ]);
        }

        if (!$classMetadata->hasAssociation('fundraiser')) {
            $classMetadata->mapOneToOne([
                'fieldName'    => 'fundraiser',
                'targetEntity' => $this->fundraiserClass,
                'cascade' => [ 'persist', 'remove' ],
                'inversedBy'   => 'investorLegalEntity',
            ]);
        }

        if (!$classMetadata->hasAssociation('boards')) {
            $classMetadata->mapManyToMany([
                'fieldName'    => 'boards',
                'targetEntity' => $this->boardClass,
                'inversedBy' => 'investorLegalEntities'
            ]);
        }

        if (!$classMetadata->hasAssociation('sourcing')) {
            $classMetadata->mapOneToOne([
                'fieldName'    => 'sourcing',
                'targetEntity' => $this->sourcingClass,
                'cascade' => [ 'persist', 'remove' ],
                'mappedBy'   => 'investorLegalEntity',
            ]);
        }

        if (!$classMetadata->hasAssociation('users')) {
            $classMetadata->mapManyToMany([
                'fieldName'    => 'users',
                'targetEntity' => $this->userClass,
                'joinTable' => [
                    'name' => 'investor_legal_entity_user',
                    'joinColumns' => [[
                        'name' => 'investor_legal_entity_id',
                        'referencedColumnName' => 'id'
                    ]],
                    'inverseJoinColumns' => [[
                        'name' => 'user_id',
                        'referencedColumnName' => 'id'
                    ]],
                ],
            ]);
        }

        if (!$classMetadata->hasAssociation('documents')) {
            $classMetadata->mapManyToMany([
                'fieldName'    => 'documents',
                'targetEntity' => $this->documentClass,
                'cascade' => [ 'persist', 'remove' ],
                'orphanRemoval' => true,
                'joinTable' => [
                    'name' => 'investor_legal_entity_x_document',
                    'joinColumns' => [[
                        'name' => 'investor_legal_entity_id',
                        'referencedColumnName' => 'id'
                    ]],
                    'inverseJoinColumns' => [[
                        'name' => 'document_id',
                        'referencedColumnName' => 'id'
                    ]],
                ],
            ]);
        }

        if (!$classMetadata->hasAssociation('interactionEmails')) {
            $classMetadata->mapManyToMany([
                'fieldName'    => 'interactionEmails',
                'targetEntity' => $this->interactionEmailClass,
                'cascade' => [ 'persist', 'remove' ],
                'orphanRemoval' => true,
                'joinTable' => [
                    'name' => 'investor_legal_entity_x_interaction_email',
                    'joinColumns' => [[
                        'name' => 'investor_legal_entity_id',
                        'referencedColumnName' => 'id'
                    ]],
                    'inverseJoinColumns' => [[
                        'name' => 'interaction_email_id',
                        'referencedColumnName' => 'id'
                    ]],
                ],
            ]);
        }

        if (!$classMetadata->hasAssociation('interactionNotes')) {
            $classMetadata->mapManyToMany([
                'fieldName'    => 'interactionNotes',
                'targetEntity' => $this->interactionNoteClass,
                'cascade' => [ 'persist', 'remove' ],
                'orphanRemoval' => true,
                'joinTable' => [
                    'name' => 'investor_legal_entity_x_interaction_note',
                    'joinColumns' => [[
                        'name' => 'investor_legal_entity_id',
                        'referencedColumnName' => 'id'
                    ]],
                    'inverseJoinColumns' => [[
                        'name' => 'interaction_note_id',
                        'referencedColumnName' => 'id'
                    ]],
                ],
            ]);
        }

        if (!$classMetadata->hasAssociation('interactionCalls')) {
            $classMetadata->mapManyToMany([
                'fieldName'    => 'interactionCalls',
                'targetEntity' => $this->interactionCallClass,
                'cascade' => [ 'persist', 'remove' ],
                'orphanRemoval' => true,
                'joinTable' => [
                    'name' => 'investor_legal_entity_x_interaction_call',
                    'joinColumns' => [[
                        'name' => 'investor_legal_entity_id',
                        'referencedColumnName' => 'id'
                    ]],
                    'inverseJoinColumns' => [[
                        'name' => 'interaction_call_id',
                        'referencedColumnName' => 'id'
                    ]],
                ],
            ]);
        }

        if (!$classMetadata->hasAssociation('interactionLetters')) {
            $classMetadata->mapManyToMany([
                'fieldName'    => 'interactionLetters',
                'targetEntity' => $this->interactionLetterClass,
                'cascade' => [ 'persist', 'remove' ],
                'orphanRemoval' => true,
                'joinTable' => [
                    'name' => 'investor_legal_entity_x_interaction_letter',
                    'joinColumns' => [[
                        'name' => 'investor_legal_entity_id',
                        'referencedColumnName' => 'id'
                    ]],
                    'inverseJoinColumns' => [[
                        'name' => 'interaction_letter_id',
                        'referencedColumnName' => 'id'
                    ]],
                ],
            ]);
        }

        if (!$classMetadata->hasAssociation('interactionAppointments')) {
            $classMetadata->mapManyToMany([
                'fieldName'    => 'interactionAppointments',
                'targetEntity' => $this->interactionAppointmentClass,
                'cascade' => [ 'persist', 'remove' ],
                'orphanRemoval' => true,
                'joinTable' => [
                    'name' => 'investor_legal_entity_x_interaction_appointment',
                    'joinColumns' => [[
                        'name' => 'investor_legal_entity_id',
                        'referencedColumnName' => 'id'
                    ]],
                    'inverseJoinColumns' => [[
                        'name' => 'interaction_appointment_id',
                        'referencedColumnName' => 'id'
                    ]],
                ],
            ]);
        }

        if (!$classMetadata->hasAssociation('provisions')) {
            $classMetadata->mapOneToMany([
                    'fieldName'    => 'provisions',
                    'targetEntity' => $this->provisionClass,
                    'cascade' => [ 'persist', 'remove' ],
                    'mappedBy'   => 'investorLegalEntity',
                    // @TODO do we need it?
                    'orphanRemoval' => true,
            ]);
        }

        if (!$classMetadata->hasAssociation('contactPrimary')) {
            $classMetadata->mapManyToOne([
                'fieldName'    => 'contactPrimary',
                'targetEntity' => $this->contactMergedClass,
                'cascade' => [ 'persist' ]
            ]);
        }

        if (!$classMetadata->hasAssociation('contacts')) {
            $classMetadata->mapManyToMany([
                'fieldName'    => 'contacts',
                'targetEntity' => $this->contactMergedClass,
                'joinTable' => [
                    'name' => 'investor_legal_entity_x_contact_merged',
                    'joinColumns' => [[
                        'name' => 'investor_legal_entity_id',
                        'referencedColumnName' => 'id'
                    ]],
                    'inverseJoinColumns' => [[
                        'name' => 'contact_merged_id',
                        'referencedColumnName' => 'id'
                    ]],
                ],
            ]);
        }
    }

    /**
     * Declare mapping for InvestorLegalEntityDetails entity.
     *
     * @param ClassMetadata $classMetadata
     */
    private function processInvestorLegalEntityDetailsMetadata(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasAssociation('investorLegalEntity')) {
            $classMetadata->mapManyToOne([
                'fieldName'    => 'investorLegalEntity',
                'targetEntity' => $this->investorLegalEntityClass,
                'inversedBy'   => 'details',
                'cascade' => [ 'persist' ]
            ]);
        }

        if (!$classMetadata->hasAssociation('shareCategory')) {
            $classMetadata->mapManyToOne([
                'fieldName'    => 'shareCategory',
                'targetEntity' => $this->shareCategoryClass,
                'cascade' => [ 'persist' ]
            ]);
        }

        if (!$classMetadata->hasAssociation('investorStep')) {
            $classMetadata->mapManyToOne([
                'fieldName'    => 'investorStep',
                'targetEntity' => $this->investorStepClass,
                'cascade' => [ 'persist' ]
            ]);
        }
    }

    /**
     * Declare mapping for Provision entity.
     *
     * @param ClassMetadata $classMetadata
     */
    private function processProvisionMetadata(ClassMetadata $classMetadata)
    {
        if (!$classMetadata->hasAssociation('creator')) {
            $classMetadata->mapManyToOne([
                'fieldName'    => 'creator',
                'targetEntity' => $this->userClass
            ]);
        }

        if (!$classMetadata->hasAssociation('investorLegalEntity')) {
            $classMetadata->mapManyToOne([
                    'fieldName'    => 'investorLegalEntity',
                    'targetEntity' => $this->investorLegalEntityClass,
                    'cascade' => [ 'persist' ],
                    'inversedBy' => 'provisions'
            ]);
        }
    }
}
