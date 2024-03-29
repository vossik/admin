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

class RoleForm extends BaseFormComponent implements \Nepttune\TI\IAccessForm
{
    use \Nepttune\TI\TAccessForm;

    public function __construct(
        \Nepttune\Model\RoleModel $roleModel,
        \Nepttune\Model\RoleAccessModel $roleAccessModel)
    {
        $this->repository = $roleModel;
        $this->roleAccessModel = $roleAccessModel;
    }

    protected function modifyForm(Form $form) : Form
    {
        $form->addText('name', 'list.column.name')
            ->setRequired();
        $form->addTextArea('description', 'list.column.description');

        $form = $this->addCheckboxes($form);

        return $form;
    }

    public function formSuccess(\Nette\Application\UI\Form $form, \stdClass $values) : void
    {
        $access = \array_filter((array) $values->access, function ($value) {return $value === true;});
        unset($values->access);

        $rowId = 0;

        if ($this->rowId)
        {
            $values->id = $this->rowId;
        }

        $this->roleAccessModel->transaction(function() use ($values, $access, &$rowId)
        {
            $row = $this->repository->upsert((array) $values);
            $this->roleAccessModel->deleteByArray(['role_id' => $row->id]);
            $this->roleAccessModel->insertMany(static::createInsertArray($row->id, $access));

            $rowId = $row->id;
        });

        if ($this->saveCallback)
        {
            $this->saveCallback($form, $values, $rowId);
        }
    }
}
