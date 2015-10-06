<?php

namespace Tests\Integration\Domain;

use Annotations\Node;
use Annotations\GraphProperty;
use Annotations\Entity as GraphEntity;
use Annotations\Id;

/**
 * @Node(labels = {"GithubUser"})
 * @GraphEntity
 */
class GithubUser
{

    /**
     * @Id
     * @var int
     */
    protected $id;

    /**
     * @GraphProperty(type="string", key="userName")
     */
    protected $userName;

    /**
     * @GraphProperty(type="string", key="realName")
     */
    protected $realName;

    /**
     * @param string $userName
     * @param string $realName
     */
    public function __construct($userName, $realName)
    {
        $this->userName = $userName;
        $this->realName = $realName;
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
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * @param string $userName
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;
    }

    /**
     * @return string
     */
    public function getRealName()
    {
        return $this->realName;
    }

    /**
     * @param string $realName
     */
    public function setRealName($realName)
    {
        $this->realName = $realName;
    }
}
