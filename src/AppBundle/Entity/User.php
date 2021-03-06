<?php
/*
  ÁTICA - Aplicación web para la gestión documental de centros educativos

  Copyright (C) 2015-2017: Luis Ramón López López

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU Affero General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU Affero General Public License for more details.

  You should have received a copy of the GNU Affero General Public License
  along with this program.  If not, see [http://www.gnu.org/licenses/].
*/

namespace AppBundle\Entity;

use AppBundle\Entity\Traits\UserBlameableTrait;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\UserRepository")
 * @UniqueEntity("loginUsername")
 * @UniqueEntity("emailAddress")
 */
class User implements AdvancedUserInterface
{
    use TimestampableEntity;
    use UserBlameableTrait;

    const GENDER_NEUTRAL = 0;
    const GENDER_MALE = 1;
    const GENDER_FEMALE = 2;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="string", unique=true, nullable=true)
     * @Assert\Regex(pattern="/[@ ]{1,}/", match=false, message="login_username.invalid_chars", htmlPattern=false)
     * @var string
     */
    private $loginUsername;

    /**
     * @ORM\Column(type="string", unique=true, nullable=true)
     * @var string
     */
    private $password;

    /**
     * @var \DateTime
     * @Gedmo\Timestampable(on="change", field="password")
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $passwordChangedAt;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     * @var string
     */
    private $firstName;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     * @var string
     */
    private $lastName;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private $enabled;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private $globalAdministrator;

    /**
     * @ORM\Column(type="string", unique=true, nullable=true)
     * @Assert\Email
     * @var string
     */
    private $emailAddress;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    private $internalCode;

    /**
     * @ORM\Column(type="integer")
     * @var int
     */
    private $gender;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    private $token;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    private $tokenType;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $tokenExpiration;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $lastAccess;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $blockedUntil;

    /**
     * @ORM\OneToMany(targetEntity="Membership", mappedBy="user")
     * @var Collection
     */
    private $memberships;

    /**
     * @ORM\ManyToMany(targetEntity="Organization", mappedBy="administrators")
     * @var Collection
     */
    private $managedOrganizations;

    /**
     * @ORM\ManyToOne(targetEntity="Organization")
     * @ORM\JoinColumn(nullable=true)
     * @var Organization|null
     */
    protected $defaultOrganization;

    /**
     * @ORM\OneToMany(targetEntity="Role", mappedBy="user")
     * @var Collection
     */
    private $assignedRoles;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    private $externalCheck;

    /**
     * @ORM\Column(type="boolean")
     * @var bool
     */
    protected $allowExternalCheck;

    /**
     * Convertir usuario en cadena
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getFirstName().' '.$this->getLastName();
    }

    /**
     * Convertir usuario en cadena
     *
     * @return string
     */
    public function getFullName()
    {
        return (string) $this.' ('.$this->getUsernameAndEmailAddress().')';
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->memberships = new \Doctrine\Common\Collections\ArrayCollection();
        $this->managedOrganizations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->assignedRoles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->externalCheck = false;
        $this->allowExternalCheck = false;
    }

    /**
     * @return string
     */
    public function getUsernameAndEmailAddress()
    {
        return $this->loginUsername.(($this->loginUsername && $this->emailAddress) ? ' - ' : '').$this->emailAddress;
    }
    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set userName
     *
     * @param string $loginUsername
     *
     * @return User
     */
    public function setLoginUsername($loginUsername)
    {
        $this->loginUsername = $loginUsername;

        return $this;
    }

    /**
     * Get userName
     *
     * @return string
     */
    public function getLoginUsername()
    {
        return $this->loginUsername;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     *
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     *
     * @return User
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }


    /**
     * Set globalAdmin
     *
     * @param boolean $globalAdministrator
     *
     * @return User
     */
    public function setGlobalAdministrator($globalAdministrator)
    {
        $this->globalAdministrator = $globalAdministrator;

        return $this;
    }

    /**
     * Get globalAdmin
     *
     * @return boolean
     */
    public function isGlobalAdministrator()
    {
        return $this->globalAdministrator;
    }

    /**
     * Get internal code
     *
     * @return string
     */
    public function getInternalCode()
    {
        return $this->internalCode;
    }

    /**
     * Set internal code
     *
     * @param string $internalCode
     * @return User
     */
    public function setInternalCode($internalCode)
    {
        $this->internalCode = $internalCode;
        return $this;
    }

    /**
     * Set emailAddress
     *
     * @param string $emailAddress
     *
     * @return User
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    /**
     * Get emailAddress
     *
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * Set gender
     *
     * @param integer $gender
     *
     * @return User
     */
    public function setGender($gender)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return integer
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Set token
     *
     * @param string $token
     *
     * @return User
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set tokenType
     *
     * @param string $tokenType
     *
     * @return User
     */
    public function setTokenType($tokenType)
    {
        $this->tokenType = $tokenType;

        return $this;
    }

    /**
     * Get tokenType
     *
     * @return string
     */
    public function getTokenType()
    {
        return $this->tokenType;
    }

    /**
     * Set tokenExpiration
     *
     * @param \DateTime $tokenExpiration
     *
     * @return User
     */
    public function setTokenExpiration($tokenExpiration)
    {
        $this->tokenExpiration = $tokenExpiration;

        return $this;
    }

    /**
     * Get tokenExpiration
     *
     * @return \DateTime
     */
    public function getTokenExpiration()
    {
        return $this->tokenExpiration;
    }

    /**
     * Set lastAccess
     *
     * @param \DateTime $lastAccess
     *
     * @return User
     */
    public function setLastAccess($lastAccess)
    {
        $this->lastAccess = $lastAccess;

        return $this;
    }

    /**
     * Get lastAccess
     *
     * @return \DateTime
     */
    public function getLastAccess()
    {
        return $this->lastAccess;
    }

    /**
     * Set blockedUntil
     *
     * @param \DateTime $blockedUntil
     *
     * @return User
     */
    public function setBlockedUntil($blockedUntil)
    {
        $this->blockedUntil = $blockedUntil;

        return $this;
    }

    /**
     * Get blockedUntil
     *
     * @return \DateTime
     */
    public function getBlockedUntil()
    {
        return $this->blockedUntil;
    }

    /**
     * Add membership
     *
     * @param Membership $membership
     *
     * @return User
     */
    public function addMembership(Membership $membership)
    {
        $this->memberships[] = $membership;

        return $this;
    }

    /**
     * Remove membership
     *
     * @param Membership $membership
     */
    public function removeMembership(Membership $membership)
    {
        $this->memberships->removeElement($membership);
    }

    /**
     * Get memberships
     *
     * @return Collection
     */
    public function getMemberships()
    {
        return $this->memberships;
    }

    /**
     * Set defaultOrganization
     *
     * @param Organization $defaultOrganization
     *
     * @return User
     */
    public function setDefaultOrganization(Organization $defaultOrganization = null)
    {
        $this->defaultOrganization = $defaultOrganization;

        return $this;
    }

    /**
     * Get defaultOrganization
     *
     * @return Organization|null
     */
    public function getDefaultOrganization()
    {
        return $this->defaultOrganization;
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        // comprobar si se ha especificado al menos el nombre de usuario o el correo electrónico
        if (!$this->getLoginUsername() && !$this->getEmailAddress()) {
            $context->buildViolation('user.id.not_found')
                ->atPath('userName')
                ->addViolation();
            $context->buildViolation('user.id.not_found')
                ->atPath('emailAddress')
                ->addViolation();
        }
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->getLoginUsername() ?: $this->getEmailAddress();
    }

    /**
     * Checks whether the user's account has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw an AccountExpiredException and prevent login.
     *
     * @return bool true if the user's account is non expired, false otherwise
     *
     * @see AccountExpiredException
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * Checks whether the user is locked.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a LockedException and prevent login.
     *
     * @return bool true if the user is not locked, false otherwise
     *
     * @see LockedException
     */
    public function isAccountNonLocked()
    {
        return $this->getBlockedUntil() ? ($this->getBlockedUntil() <= new \DateTime()) : true;
    }

    /**
     * Checks whether the user's credentials (password) has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a CredentialsExpiredException and prevent login.
     *
     * @return bool true if the user's credentials are non expired, false otherwise
     *
     * @see CredentialsExpiredException
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * Checks whether the user is enabled.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a DisabledException and prevent login.
     *
     * @return bool true if the user is enabled, false otherwise
     *
     * @see DisabledException
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return string[] The user roles
     */
    public function getRoles()
    {
        $roles = ['ROLE_USER'];
        if ($this->isGlobalAdministrator()) {
            $roles[] = 'ROLE_ADMIN';
        }
        return $roles;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
    }

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->loginUsername,
            $this->emailAddress,
            $this->password,
            $this->enabled
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->loginUsername,
            $this->emailAddress,
            $this->password,
            $this->enabled
            ) = unserialize($serialized);
    }

    /**
     * Add managedOrganization
     *
     * @param Organization $managedOrganization
     *
     * @return User
     */
    public function addManagedOrganization(Organization $managedOrganization)
    {
        $this->managedOrganizations[] = $managedOrganization;

        return $this;
    }

    /**
     * Remove managedOrganization
     *
     * @param Organization $managedOrganization
     */
    public function removeManagedOrganization(Organization $managedOrganization)
    {
        $this->managedOrganizations->removeElement($managedOrganization);
    }

    /**
     * Get managedOrganizations
     *
     * @return Collection
     */
    public function getManagedOrganizations()
    {
        return $this->managedOrganizations;
    }

    /**
     * Add assigned role
     *
     * @param Role $assignedRole
     *
     * @return User
     */
    public function addAssignedRole(Role $assignedRole)
    {
        if (!$this->assignedRoles->contains($assignedRole)) {
            $this->assignedRoles[] = $assignedRole;
        }

        return $this;
    }

    /**
     * Remove assigned role
     *
     * @param Element $assignedRole
     */
    public function removeRole(Role $assignedRole)
    {
        if ($this->assignedRoles->contains($assignedRole)) {
            $this->assignedRoles->removeElement($assignedRole);
        }
    }

    /**
     * Get assigned roles
     *
     * @return Collection
     */
    public function getAssignedRoles()
    {
        return $this->assignedRoles;
    }

    /**
     * Set externalCheck
     *
     * @param boolean $externalCheck
     *
     * @return User
     */
    public function setExternalCheck($externalCheck)
    {
        $this->externalCheck = $this->allowExternalCheck && $externalCheck;

        return $this;
    }

    /**
     * Get externalCheck
     *
     * @return boolean
     */
    public function getExternalCheck()
    {
        return $this->externalCheck;
    }

    /**
     * Set allowExternalCheck
     *
     * @param boolean $allowExternalCheck
     *
     * @return User
     */
    public function setAllowExternalCheck($allowExternalCheck)
    {
        $this->allowExternalCheck = $allowExternalCheck;

        if (!$allowExternalCheck) {
            $this->externalCheck = false;
        }

        return $this;
    }

    /**
     * Get allowExternalCheck
     *
     * @return boolean
     */
    public function getAllowExternalCheck()
    {
        return $this->allowExternalCheck;
    }
}
