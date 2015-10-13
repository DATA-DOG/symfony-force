<?php

namespace AppBundle\Entity;

use AppBundle\Mailer\ContactInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 * @UniqueEntity("email")
 */
class User implements UserInterface, \Serializable, ContactInterface
{
    static public $roleMap = [
        'ROLE_USER' => 1,
        'ROLE_ADMIN' => 2,
    ];

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(length=255, unique=true)
     * @Assert\NotBlank(message="Email address cannot be empty", groups={"signup", "reset"})
     * @Assert\Email(message="Email address is not valid", groups={"signup", "reset"})
     */
    private $email;

    /**
     * @ORM\Column(length=48)
     */
    private $salt;

    /**
     * @ORM\Column(length=64, nullable=true)
     * @Assert\NotBlank(message="First Name cannot be empty", groups={"confirm", "profile"})
     */
    private $firstname;

    /**
     * @ORM\Column(length=64, nullable=true)
     * @Assert\NotBlank(message="Last name cannot be empty", groups={"confirm", "profile"})
     */
    private $lastname;

    /**
     * @ORM\Column(name="confirmation_token", length=48, nullable=true)
     */
    private $confirmationToken;

    /**
     * @ORM\Column(type="integer")
     */
    private $roles = 0; // has no roles by default

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", name="created_at")
     */
    private $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", name="updated_at")
     */
    private $updatedAt;

    /**
     * @ORM\Column(length=64, nullable=true)
     */
    private $password;

    /**
     * Plain password. Used for model validation. Must not be persisted.
     *
     * @Assert\NotBlank(message="Password cannot be empty", groups={"confirm"})
     * @Assert\Length(
     *   min=8,
     *   max=4096,
     *   minMessage="Password is too short",
     *   maxMessage="Password is too long",
     *   groups={"confirm", "profile"}
     * )
     */
    private $plainPassword;

    public function __construct()
    {
        $this->salt = base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    public function setPlainPassword($plainPassword)
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }

    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    public function getRoles()
    {
        $roles = [];
        foreach (self::$roleMap as $role => $flag) {
            if ($flag === ($this->roles & $flag)) {
                $roles[] = $role;
            }
        }
        return $roles;
    }

    public function hasRole($role)
    {
        return in_array($role, $this->getRoles(), true);
    }

    public function removeRole($role)
    {
        $role = strtoupper($role);
        if (array_key_exists($role, self::$roleMap)) {
            $this->roles ^= self::$roleMap[$role];
        }
        return $this;
    }

    public function setRoles(array $roles)
    {
        $this->roles = 0;
        foreach ($roles as $role) {
            $this->addRole($role);
        }
        return $this;
    }

    public function addRole($role)
    {
        $role = strtoupper($role);
        if (!array_key_exists($role, self::$roleMap)) {
            return $this;
        }
        $this->roles |= self::$roleMap[$role];
        return $this;
    }

    public function setPassword($password)
    {
        $this->password = $password;
        return $this;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function getFirstname()
    {
        return $this->firstname;
    }

    public function getLastname()
    {
        return $this->lastname;
    }

    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function setConfirmationToken($confirmationToken)
    {
        $this->confirmationToken = $confirmationToken;
        return $this;
    }

    public function getConfirmationToken()
    {
        return $this->confirmationToken;
    }

    public function regenerateConfirmationToken()
    {
        if (function_exists('openssl_random_pseudo_bytes')) {
            $bytes = openssl_random_pseudo_bytes(32, $strong);

            if (false !== $bytes && true === $strong) {
                $num = $bytes;
            } else {
                $num = hash('sha256', uniqid(mt_rand(), true), true);
            }
        } else {
            $num = hash('sha256', uniqid(mt_rand(), true), true);
        }

        $this->confirmationToken = rtrim(strtr(base64_encode($num), '+/', '-_'), '=');
        return $this;
    }

    public function isConfirmed()
    {
        return $this->password !== null;
    }

    public function confirm()
    {
        $this->addRole('ROLE_USER');
        $this->confirmationToken = null;
    }

    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function __toString()
    {
        return $this->firstname ? trim($this->firstname . ' ' . $this->lastname) : $this->email;
    }

    public function serialize()
    {
        return serialize([
            $this->email,
            $this->id,
        ]);
    }

    public function unserialize($serialized)
    {
        // add a few extra elements in the array to ensure that we have enough keys when unserializing
        // older data which does not include all properties.
        $data = array_merge(unserialize($serialized), array_fill(0, 2, null));

        list($this->email, $this->id) = $data;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return trim($this->firstname . ' ' . $this->lastname);
    }
}
