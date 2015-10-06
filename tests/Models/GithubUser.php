<?php

namespace Models;

use Annotations\Node;
use Annotations\GraphProperty;
use Annotations\Entity as GraphEntity;

/**
 * @Node(labels = {"GithubUser"})
 * @GraphEntity
 */
class GithubUser extends Entity{

    /**
     * @GraphProperty(type="string", key="userName")
     */
    protected $userName;

    /**
     * @GraphProperty(type="string", key="userName")
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
