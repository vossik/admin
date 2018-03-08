<?php

namespace Nepttune\Component;

use \Ublaboo\DataGrid\DataGrid;

final class UserList extends BaseListComponent
{
    public function __construct(\Nepttune\Model\UserModel $userModel)
    {
        $this->repository = $userModel;
    }

    protected function modifyList(DataGrid $grid) : DataGrid
    {
        $grid->addColumnText('username', 'Přihlašovací jméno')
            ->setSortable();

        $grid->addToolbarButton(':add', 'Přidat');

        return $grid;
    }
}