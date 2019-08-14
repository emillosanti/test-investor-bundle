<?php

namespace SAM\InvestorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\MappedSuperclass(repositoryClass="SAM\InvestorBundle\Repository\InvestorStepRepository")
 * @ORM\Table(name="investor_step")
 */
abstract class InvestorStep
{
    use TimestampableEntity;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string")
     */
    protected $name;

    /**
     * @var int
     *
     * @Gedmo\SortablePosition
     *
     * @ORM\Column(name="position", type="integer")
     */
    protected $position;

    /**
     * @var string
     *
     * @ORM\Column(name="backgroundColor", type="string", nullable=true)
     */
    protected $backgroundColor;

    /**
     * @var string
     *
     * @ORM\Column(name="textColor", type="string", nullable=true)
     */
    protected $textColor = '#FFFFFF';

    /**
     * @var string
     *
     * @ORM\Column(name="recommended_documents", type="string", nullable=true)
     */
    protected $recommendedDocuments;

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->name;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     *
     * @return $this
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return string
     */
    public function getBackgroundColor()
    {
        return $this->backgroundColor;
    }

    /**
     * @param string $color
     *
     * @return $this
     */
    public function setBackgroundColor($backgroundColor)
    {
        $this->backgroundColor = $backgroundColor;

        return $this;
    }

    /**
     * @return string
     */
    public function getTextColor()
    {
        return $this->textColor;
    }

    /**
     * @param string $textColor
     * @return $this
     */
    public function setTextColor(string $textColor)
    {
        $this->textColor = $textColor;

        return $this;
    }

    /**
     * @return string
     */
    public function getRecommendedDocuments()
    {
        return $this->recommendedDocuments;
    }

    /**
     * @param string $recommendedDocuments
     *
     * @return $this
     */
    public function setRecommendedDocuments($recommendedDocuments)
    {
        $this->recommendedDocuments = $recommendedDocuments;

        return $this;
    }
}
