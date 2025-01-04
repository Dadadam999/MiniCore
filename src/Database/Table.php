<?php

namespace MiniCore\Database;

use MiniCore\Database\DataBase;
use MiniCore\Database\DefaultAction\DeleteAction;
use MiniCore\Database\DefaultAction\InsertAction;
use MiniCore\Database\DefaultAction\SelectAction;
use MiniCore\Database\DefaultAction\UpdateAction;

abstract class Table
{
    protected array $actions = [];

    public function __construct(
        protected string $name,
        protected array $scheme,
    ) {
        $this->actions = [
            new InsertAction($this->name),
            new SelectAction($this->name),
            new UpdateAction($this->name),
            new DeleteAction($this->name),
        ];
    }

    public function create(): void
    {
        $fields = $this->getSchemeToString();

        DataBase::query(
            "CREATE TABLE {$this->name} ({$fields})"
        );
    }

    public function drop(): void
    {
        DataBase::query(
            "DROP TABLE `{$this->name}`"
        );
    }

    public function exist(): bool
    {
        $query = "SHOW TABLES LIKE :table_name";
        $result = DataBase::query($query, ['table_name' => $this->name]);
        return !empty($result);
    }

    public function getSchemeToString(): string
    {
        $fields = '';

        foreach ($this->scheme as $fieldName => $fieldDefinition) {
            $fields .= "$fieldName $fieldDefinition, ";
        }

        return rtrim($fields, ', ');
    }

    public function addAction(ActionInterface $action): void
    {
        $this->actions[] = $action;
    }

    public function removeAction(string $actionName): void
    {
        foreach ($this->actions as $key => $action) {
            if ($action->getName() === $actionName) {
                unset($this->actions[$key]);
                break;
            }
        }
    }

    public function execute(string $actionName, DataAction $data): mixed
    {
        foreach ($this->actions as $action) {
            if ($action->getName() === $actionName) {
                return $action->execute($data);
            }
        }

        return null;
    }
}
