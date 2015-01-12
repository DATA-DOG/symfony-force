<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class User implements UserInterface, \Serializable
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
     * @Assert\NotBlank(message="entity.user.email.blank", groups={"signup"})
     * @Assert\Email(message="entity.user.email.invalid", groups={"signup"})
     */
    private $email;

    /**
     * @ORM\Column(length=48)
     */
    private $salt;

    /**
     * @ORM\Column(length=64, nullable=true)
     * @Assert\NotBlank(message="entity.user.firstname.blank", groups={"signup"})
     */
    private $firstname;

    /**
     * @ORM\Column(length=64, nullable=true)
     * @Assert\NotBlank(message="entity.user.lastname.blank", groups={"signup"})
     */
    private $lastname;

    /**
     * @ORM\Column(length=64)
     */
    private $password;

    /**
     * Plain password. Used for model validation. Must not be persisted.
     *
     * @Assert\NotBlank(message="entity.user.password.blank", groups={"reset"})
     * @Assert\Length(
     *   min=8,
     *   max=4096,
     *   minMessage="entity.user.password.short",
     *   maxMessage="entity.user.password.long",
     *   groups={"reset"}
     * )
     */
    private $plainPassword;

    /**
     * @ORM\Column(type="integer")
     */
    private $roles = 0;

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
        $roles = ['ROLE_USER'];
        foreach (self::$roleMap as $role => $flag) {
            if ($flag === $this->roles && $flag) {
                $roles[] = $role;
            }
        }
        return array_unique($roles);
    }

    public function removeRole($role)
    {
        $role = strtoupper($role);
        if (array_key_exists($role, self::$roleMap)) {
            $this->roles = $this->roles ^ self::$roleMap[$role];
        }
        return $this;
    }

    public function hasRole($role)
    {
        $role = strtoupper($role);
        if ($role === 'ROLE_USER') {
            return true;
        }
        if (array_key_exists($role, self::$roleMap)) {
            return self::$roleMap[$role] === $this->roles & self::$roleMap[$role];
        }
        return false;
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
        if ($this->hasRole($role)) {
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

    public function __toString()
    {
        return $this->firstname ? trim($this->firstname . ' ' . $this->lastname) : $this->email;
    }

    public function serialize()
    {
        return serialize(array(
            $this->email,
            $this->id,
        ));
    }

    public function unserialize($serialized)
    {
        $data = unserialize($serialized);
        // add a few extra elements in the array to ensure that we have enough keys when unserializing
        // older data which does not include all properties.
        $data = array_merge($data, array_fill(0, 2, null));

        list(
            $this->email,
            $this->id
        ) = $data;
    }
}
