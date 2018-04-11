<?php

/**
 * This file is part of Nepttune (https://www.peldax.com)
 *
 * Copyright (c) 2018 Václav Pelíšek (info@peldax.com)
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <https://www.peldax.com>.
 */

declare(strict_types = 1);

namespace Nepttune\Model;

use Nette\Security as NS;

final class Authenticator extends UserModel implements NS\IAuthenticator
{
    public function authenticate(array $credentials)
    {
        list($username, $password) = $credentials;
        $row = $this->findAll()
            ->where('username', $username)
            ->where('active', 1)
            ->fetch();

        if (!$row)
        {
            throw new NS\AuthenticationException('User not found.');
        }

        if (!NS\Passwords::verify($password, $row->password))
        {
            throw new NS\AuthenticationException('Invalid password.');
        }

        $data = $row->toArray();
        unset($data['password']);
        return new \Nette\Security\Identity($row->id, $row->role, $data);
    }
}
