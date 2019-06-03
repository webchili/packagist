<?php

/*
 * This file is part of Packagist.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *     Nils Adermann <naderman@naderman.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Packagist\WebBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * @ORM\Entity(repositoryClass="Packagist\WebBundle\Entity\UserRepository")
 * @ORM\Table(name="fos_user")
 * @ORM\AttributeOverrides({
 *     @ORM\AttributeOverride(name="username",
 *         column=@ORM\Column(
 *             name="username",
 *             type="string",
 *             length=191
 *         )
 *     ),
 *     @ORM\AttributeOverride(name="usernameCanonical",
 *         column=@ORM\Column(
 *             name="username_canonical",
 *             type="string",
 *             length=191,
 *             unique=true
 *         )
 *     ),
 *     @ORM\AttributeOverride(name="email",
 *         column=@ORM\Column(
 *             name="email",
 *             type="string",
 *             length=191
 *         )
 *     ),
 *     @ORM\AttributeOverride(name="emailCanonical",
 *         column=@ORM\Column(
 *             name="email_canonical",
 *             type="string",
 *             length=191,
 *             unique=true
 *         )
 *     )
 * })
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Assert\Length(
     *     min=8,
     *     max=180,
     *     groups={"Profile", "Registration"}
     * )
     * @Assert\Regex(
     *     pattern="{^[^/""\r\n><#\[\]]{2,100}$}",
     *     message="Username invalid, /""\r\n><#[] are not allowed",
     *     groups={"Profile", "Registration"}
     * )
     * @Assert\NotBlank(
     *     message="fos_user.username.blank",
     *     groups={"Profile", "Registration"}
     * )
     */
    protected $username;

    /**
     * @ORM\ManyToMany(targetEntity="Package", mappedBy="maintainers")
     */
    private $packages;

    /**
     * @ORM\OneToMany(targetEntity="Packagist\WebBundle\Entity\Author", mappedBy="owner")
     */
    private $authors;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     * @var string
     */
    private $apiToken;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    private $githubId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    private $githubToken;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    private $githubScope;

    /**
     * @ORM\Column(type="boolean", options={"default"=true})
     * @var string
     */
    private $failureNotifications = true;

    public function __construct()
    {
        $this->packages = new ArrayCollection();
        $this->authors = new ArrayCollection();
        $this->createdAt = new \DateTime();
        parent::__construct();
    }

    public function toArray()
    {
        return array(
            'name' => $this->getUsername(),
            'avatar_url' => $this->getGravatarUrl(),
        );
    }

    /**
     * Add packages
     *
     * @param Package $packages
     */
    public function addPackages(Package $packages)
    {
        $this->packages[] = $packages;
    }

    /**
     * Get packages
     *
     * @return Package[]
     */
    public function getPackages()
    {
        return $this->packages;
    }

    /**
     * Add authors
     *
     * @param Author $authors
     */
    public function addAuthors(Author $authors)
    {
        $this->authors[] = $authors;
    }

    /**
     * Get authors
     *
     * @return Author[]
     */
    public function getAuthors()
    {
        return $this->authors;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set apiToken
     *
     * @param string $apiToken
     */
    public function setApiToken($apiToken)
    {
        $this->apiToken = $apiToken;
    }

    /**
     * Get apiToken
     *
     * @return string
     */
    public function getApiToken()
    {
        return $this->apiToken;
    }

    /**
     * Get githubId.
     *
     * @return string
     */
    public function getGithubId()
    {
        return $this->githubId;
    }

    /**
     * Get githubId.
     *
     * @return string
     */
    public function getGithubUsername()
    {
        if ($this->githubId) {
            if (!$this->githubToken) {
                return false;
            }

            $ctxt = ['http' => ['header' => ['User-Agent: packagist.org']]];
            $res = @file_get_contents('https://api.github.com/user?access_token='.$this->githubToken, false, stream_context_create($ctxt));
            if (!$res || !($res = json_decode($res, true))) {
                return false;
            }

            return $res['login'];
        }

        return false;
    }

    /**
     * Set githubId.
     *
     * @param string $githubId
     */
    public function setGithubId($githubId)
    {
        $this->githubId = $githubId;
    }

    /**
     * Get githubToken.
     *
     * @return string
     */
    public function getGithubToken()
    {
        return $this->githubToken;
    }

    /**
     * Set githubToken.
     *
     * @param string $githubToken
     */
    public function setGithubToken($githubToken)
    {
        $this->githubToken = $githubToken;
    }

    /**
     * Get githubScope.
     *
     * @return string
     */
    public function getGithubScope()
    {
        return $this->githubScope;
    }

    /**
     * Set githubScope.
     *
     * @param string $githubScope
     */
    public function setGithubScope($githubScope)
    {
        $this->githubScope = $githubScope;
    }

    /**
     * Set failureNotifications
     *
     * @param Boolean $failureNotifications
     */
    public function setFailureNotifications($failureNotifications)
    {
        $this->failureNotifications = $failureNotifications;
    }

    /**
     * Get failureNotifications
     *
     * @return Boolean
     */
    public function getFailureNotifications()
    {
        return $this->failureNotifications;
    }

    /**
     * Get failureNotifications
     *
     * @return Boolean
     */
    public function isNotifiableForFailures()
    {
        return $this->failureNotifications;
    }

    /**
     * Get Gravatar Url
     *
     * @return string
     */
    public function getGravatarUrl()
    {
        return 'https://www.gravatar.com/avatar/'.md5(strtolower($this->getEmail())).'?d=identicon';
    }
}
