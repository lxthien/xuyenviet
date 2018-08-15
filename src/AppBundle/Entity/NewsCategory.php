<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\File;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * NewsCategory
 *
 * @ORM\Table(name="newscategory")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\NewsCategoryRepository")
 */
class NewsCategory
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * One Category has Many Categories.
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\NewsCategory", mappedBy="parentcat")
     */
    protected $children;

    /**
     * Many Categories have One Category.
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\NewsCategory", inversedBy="children")
     * @ORM\JoinColumn(name="parentcat_id", referencedColumnName="id", nullable=true)
     */
    private $parentcat;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255)
     */
    private $url;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description = null;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enable", type="boolean")
     */
    private $enable = true;

    /**
     * @var string
     *
     * @ORM\Column(name="page_title", type="string", length=255, nullable=true)
     */
    private $page_title = null;

    /**
     * @var string
     *
     * @ORM\Column(name="page_description", type="text", nullable=true)
     */
    private $page_description = null;

    /**
     * @var string
     *
     * @ORM\Column(name="page_keyword", type="string", length=255, nullable=true)
     */
    private $page_keyword = null;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime") 
     */
    private $created_at;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated_at", type="datetime")
     */
    private $updated_at;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;


    public function __construct()
    {
        $this->parentcat = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return NewsCategory
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set parentcat
     *
     * @param NewsCategory $parent
     *
     * @return NewsCategory
     */
    public function setParentcat(\AppBundle\Entity\NewsCategory $parent = null) {
        $this->parentcat = $parent;

        return $this;
    }

    /**
     * Get parentcat
     *
     * @return NewsCategory
     */
    public function getParentcat() {
        return $this->parentcat;
    }

    /**
     * Get children
     *
     * @return NewsCategory
     */
    public function getChildren() {
        return $this->children;
    }

    /**
     * Set url
     *
     * @param string $url
     *
     * @return NewsCategory
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return NewsCategory
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set enable
     *
     * @param \boolean $enable
     *
     * @return NewsCategory
     */
    public function setEnable($enable)
    {
        $this->enable = (bool) $enable;

        return $this;
    }

    /**
     * Get enable
     *
     * @return \boolean
     */
    public function getEnable()
    {
        return $this->enable;
    }

    /**
     * Set pageTitle
     *
     * @param string $pageTitle
     *
     * @return NewsCategory
     */
    public function setPageTitle($pageTitle)
    {
        $this->page_title = $pageTitle;

        return $this;
    }

    /**
     * Get pageTitle
     *
     * @return string
     */
    public function getPageTitle()
    {
        return $this->page_title;
    }

    /**
     * Set pageDescription
     *
     * @param string $pageDescription
     *
     * @return NewsCategory
     */
    public function setPageDescription($pageDescription)
    {
        $this->page_description = $pageDescription;

        return $this;
    }

    /**
     * Get pageDescription
     *
     * @return string
     */
    public function getPageDescription()
    {
        return $this->page_description;
    }

    /**
     * Set pageKeyword
     *
     * @param string $pageKeyword
     *
     * @return NewsCategory
     */
    public function setPageKeyword($pageKeyword)
    {
        $this->page_keyword = $pageKeyword;

        return $this;
    }

    /**
     * Get pageKeyword
     *
     * @return string
     */
    public function getPageKeyword()
    {
        return $this->page_keyword;
    }

    /**
     * Set createAt
     *
     * @param \DateTime $createdAt
     *
     * @return News
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return News
     */
    public function setUpdatedAt($updated)
    {
        $this->updated_at = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * Set Author
     * @param User $author
     */
    public function setAuthor(User $author)
    {
        $this->author = $author;
    }

    /**
     * Get Author
     *
     * @return User
     */
    public function getAuthor()
    {
        return $this->author;
    }
}