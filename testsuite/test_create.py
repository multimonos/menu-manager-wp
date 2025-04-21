from mysql.connector.cursor import MySQLCursorDict
import pytest

from const import (
    A_SLUG,
    A_COUNT,
    A_CSV,
    A_NODECOUNT,
    AB_CSV,
    AB_NODECOUNT,
    B_COUNT,
    B_CSV,
    B_NODECOUNT,
    B_SLUG,
)
from plugin import (
    csv_exists,
    impex_count,
    impex_load,
    impex_menu_count,
    job_exists,
    job_run,
    menu_exists,
    node_count,
    plugin_reboot,
    tables_are_empty,
    cli_success,
)


@pytest.mark.serial
def test_create_a(cursor: MySQLCursorDict):
    assert csv_exists(A_CSV)

    # reset
    plugin_reboot(cursor)
    assert tables_are_empty(cursor)

    # load success
    assert cli_success(impex_load(A_CSV))
    assert impex_count(cursor) == A_COUNT

    # menus should not exist
    assert menu_exists(cursor, A_SLUG) == False

    # import data
    assert job_exists(cursor, 1)
    assert cli_success(job_run(1))

    # menus should exist
    assert menu_exists(cursor, A_SLUG)

    # nodes should exist + root, 3 pages for each menu
    assert node_count(cursor) == A_NODECOUNT


@pytest.mark.serial
def test_create_b(cursor: MySQLCursorDict):
    assert csv_exists(B_CSV)

    # reset
    plugin_reboot(cursor)
    assert tables_are_empty(cursor)

    # load success
    assert cli_success(impex_load(B_CSV))
    assert impex_count(cursor) == B_COUNT

    # menus should not exist
    assert menu_exists(cursor, B_SLUG) == False

    # import data
    assert job_exists(cursor, 1)
    assert cli_success(job_run(1))

    # menus should exist
    assert menu_exists(cursor, B_SLUG)

    # nodes should exist + root, 3 pages for each menu
    assert node_count(cursor) == B_NODECOUNT


@pytest.mark.serial
def test_create_ab(cursor: MySQLCursorDict):
    assert csv_exists(AB_CSV)

    # reset
    plugin_reboot(cursor)
    assert tables_are_empty(cursor)

    # load success
    assert cli_success(impex_load(AB_CSV))
    assert impex_menu_count(cursor, A_SLUG) == A_COUNT
    assert impex_menu_count(cursor, B_SLUG) == B_COUNT
    assert impex_count(cursor) == A_COUNT + B_COUNT

    # menus should not exist
    assert menu_exists(cursor, A_SLUG) == False
    assert menu_exists(cursor, B_SLUG) == False

    # import data
    assert job_exists(cursor, 1)
    assert cli_success(job_run(1))

    # menus should exist
    assert menu_exists(cursor, A_SLUG)
    assert menu_exists(cursor, B_SLUG)

    # nodes should exist + root, 3 pages for each menu
    assert node_count(cursor) == A_NODECOUNT + B_NODECOUNT
