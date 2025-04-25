from mysql.connector.cursor import MySQLCursorDict
import pytest

from const import (
    A_SLUG,
    A_COUNT,
    A_CSV,
    A_NODECOUNT,
    AB_CSV,
    B_COUNT,
    B_CSV,
    B_NODECOUNT,
    B_SLUG,
    TINY_CSV,
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
def test_create_tiny_menu(cursor: MySQLCursorDict):
    assert csv_exists(TINY_CSV)

    # reset
    plugin_reboot(cursor)
    assert tables_are_empty(cursor)

    # load success
    assert cli_success(impex_load(TINY_CSV))

    # import data
    assert job_exists(cursor, 1)
    assert cli_success(job_run(1))

    # menus should not exist
    assert menu_exists(cursor, "tiny")

    #  assert impex_count(cursor) == A_COUNT
    #


#
#
#  # menus should exist
#  assert menu_exists(cursor, A_SLUG)
#
#  # nodes should exist + root, 3 pages for each menu
#  assert node_count(cursor) == A_NODECOUNT
#
#
# assert node_count(cursor) == A_NODECOUNT + B_NODECOUNT
