from mysql.connector.cursor import MySQLCursorDict
import pytest

from const import (
    A_COUNT,
    A_CSV,
    A_SLUG,
    AB_CSV,
    B_COUNT,
    B_CSV,
    B_SLUG,
)
from plugin import (
    csv_exists,
    impex_count,
    impex_load,
    impex_menu_count,
    job_exists,
    menu_exists,
    plugin_reboot,
    tables_are_empty,
    cli_success,
)


@pytest.mark.serial
def test_load_a(cursor: MySQLCursorDict):
    assert csv_exists(A_CSV)

    # reset
    plugin_reboot(cursor)
    assert tables_are_empty(cursor)

    # load success
    assert cli_success(impex_load(A_CSV))

    # menus should not exist
    assert menu_exists(cursor, A_SLUG) == False

    # impex count for menu
    assert impex_count(cursor) == A_COUNT
    assert impex_menu_count(cursor, A_SLUG) == A_COUNT

    # job
    assert job_exists(cursor, 1)


@pytest.mark.serial
def test_load_b(cursor: MySQLCursorDict):
    assert csv_exists(B_CSV)

    # reset
    plugin_reboot(cursor)
    assert tables_are_empty(cursor)

    # load success
    assert cli_success(impex_load(B_CSV))

    # menus should not exist
    assert menu_exists(cursor, B_SLUG) == False

    # impex count for menu
    assert impex_count(cursor) == B_COUNT
    assert impex_menu_count(cursor, B_SLUG) == B_COUNT

    # job
    assert job_exists(cursor, 1)


@pytest.mark.serial
def test_load_ab(cursor: MySQLCursorDict):
    assert csv_exists(AB_CSV)

    # reset
    plugin_reboot(cursor)
    assert tables_are_empty(cursor)

    # load success
    assert cli_success(impex_load(AB_CSV))

    # menus exist
    assert menu_exists(cursor, A_SLUG) == False
    assert menu_exists(cursor, B_SLUG) == False

    # impex count for menu
    assert impex_menu_count(cursor, A_SLUG) == A_COUNT
    assert impex_menu_count(cursor, B_SLUG) == B_COUNT

    # impex total count
    assert impex_count(cursor) == A_COUNT + B_COUNT

    # job exists
    assert job_exists(cursor, 1)
