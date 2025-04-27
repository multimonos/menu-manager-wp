from typing import TypedDict
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
from model import Job
from plugin import (
    csv_exists,
    impex_count,
    impex_load,
    impex_menu_count,
    job_count,
    job_exists,
    job_latest,
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
    assert job_count(cursor) == 0

    # load success
    assert cli_success(impex_load(A_CSV))

    # menus should not exist
    assert menu_exists(cursor, A_SLUG) == False

    # impex count for menu
    assert impex_count(cursor) == A_COUNT
    assert impex_menu_count(cursor, A_SLUG) == A_COUNT

    # job
    assert job_count(cursor) == 1
    assert job_latest()["post_name"] != ""


@pytest.mark.serial
def test_load_b(cursor: MySQLCursorDict):
    assert csv_exists(B_CSV)

    # reset
    plugin_reboot(cursor)
    assert tables_are_empty(cursor)
    assert job_count(cursor) == 0

    # load success
    assert cli_success(impex_load(B_CSV))

    # menus should not exist
    assert menu_exists(cursor, B_SLUG) == False

    # impex count for menu
    assert impex_count(cursor) == B_COUNT
    assert impex_menu_count(cursor, B_SLUG) == B_COUNT

    # job
    assert job_count(cursor) == 1
    assert job_latest()["post_name"] != ""


@pytest.mark.serial
def test_load_ab(cursor: MySQLCursorDict):
    assert csv_exists(AB_CSV)

    # reset
    plugin_reboot(cursor)
    assert tables_are_empty(cursor)
    assert job_count(cursor) == 0

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
    assert job_count(cursor) == 1
    assert job_latest()["post_name"] != ""
