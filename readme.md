# Menu Manager

A wordpress plugin for managing restaurant style menus via an impex workflow.

## Assumptions

- ...

## Impex file format

See `lib\MenuManager\Model\Impex.php`

## Guidelines

- don't mix menus when creating a new menu
- new menuitem should not have item_id
- easier to `clone -> update` than to `create`

## Actions

create

- create new menu
- if any `create` is found then the whole impex is considered `create`
- if menu value is not found then a new one will be created
- intitial sort order is based on insert order

update

- update an existing menuitem

insert

- create a new menuitem at position in impex 