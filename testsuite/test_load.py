from mysql.connector.cursor import MySQLCursorDict
import pytest

from const import A_SLUG, AB_COUNT, AB_CSV, AB_NODECOUNT, B_SLUG
from plugin import (
    csv_exists,
    impex_count,
    impex_load,
    job_exists,
    menu_exists,
    plugin_reboot,
    tables_are_empty,
    cli_success,
)


@pytest.mark.serial
def test_load(cursor: MySQLCursorDict):
    assert csv_exists(AB_CSV)

    # reset
    plugin_reboot(cursor)
    assert tables_are_empty(cursor)

    # load success
    assert cli_success(impex_load(AB_CSV))
    assert impex_count(cursor) == AB_COUNT

    # menus should not exist
    assert menu_exists(cursor, A_SLUG) == False
    assert menu_exists(cursor, B_SLUG) == False

    # job
    assert job_exists(cursor, 1)
