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

namespace Nepttune\Component;

use \Nette\Application\UI\Form;

final class LoginForm extends BaseFormComponent
{
    const SAVE_NEXT = false;
    const SAVE_LIST = false;

    /** @var  string */
    protected $redirectSignIn;

    /** @var  \Nepttune\Model\LoginLogModel */
    protected $loginLogModel;

    /** @var \Nette\Http\Request */
    protected $request;

    /** @var  \Nette\Security\User */
    protected $user;

    public function __construct(
        \Nepttune\Model\LoginLogModel $loginLogModel,
        \Nette\Http\Request $request,
        \Nette\Security\User $user)
    {
        parent::__construct();
        
        $this->loginLogModel = $loginLogModel;
        $this->request = $request;
        $this->user = $user;
    }

    public function setRedirect(string $redirect)
    {
        $this->redirectSignIn = $redirect;
    }

    protected function modifyForm(Form $form) : Form
    {
        $form->addText('username', 'admin.username')->setRequired();
        $form->addPassword('password', 'admin.password')->setRequired();

        $ids = $this->loginLogModel->findAll()
            ->where('ip_address', inet_pton($this->request->getRemoteAddress()))
            ->order('id DESC')
            ->limit(5)
            ->fetchPairs(null, 'id');

        if ($this->loginLogModel->findAll()->where('id', $ids)->where('result', 'failure')->count() === 5)
        {
            $form->addReCaptcha('recaptcha', 'form.recaptcha', 'form.error.recaptcha');
        }

        return $form;
    }

    public function formSuccess(\Nette\Application\UI\Form $form, \stdClass $values) : void
    {
        $failure = false;

        try
        {
            $this->user->login($values->username, $values->password);
            $this->user->setExpiration(0, TRUE);
        }
        catch (\Nette\Security\AuthenticationException $e)
        {
            $failure = $e->getMessage();
        }

        $this->loginLogModel->insert([
            'datetime' => new \Nette\Utils\DateTime(),
            'result' => (bool) $failure ? 'failure' : 'success',
            'ip_address' => inet_pton($this->request->getRemoteAddress()),
            'username' => $values->username
        ]);

        if ($failure)
        {
            $this->getPresenter()->flashMessage($this->translator->translate($failure), 'danger');
            $this->getPresenter()->redirect('this');
        }

        $this->getPresenter()->flashMessage($this->translator->translate('admin.flash.sign_in'), 'success');
        $this->getPresenter()->restoreRequest($this->getPresenter()->getParameter('backlink'));
        $this->getPresenter()->redirect($this->redirectSignIn);
    }
}
