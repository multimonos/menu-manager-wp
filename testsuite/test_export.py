from pathlib import Path
from mysql.connector.cursor import MySQLCursorDict
import pytest

from const import (
    A_COUNT,
    A_CSV,
    A_NODECOUNT,
    A_SLUG,
    AB_CSV,
    B_COUNT,
    B_NODECOUNT,
    B_SLUG,
    EXPORT_CSV,
)
from plugin import (
    csv_exists,
    csv_linecount,
    impex_count,
    impex_export,
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
def test_export(cursor: MySQLCursorDict):
    assert csv_exists(A_CSV)
    assert csv_exists(AB_CSV)

    # reset
    plugin_reboot(cursor)
    assert tables_are_empty(cursor)

    # load success
    assert cli_success(impex_load(AB_CSV))
    assert impex_count(cursor) == A_COUNT + B_COUNT
    assert impex_menu_count(cursor, A_SLUG) == A_COUNT

    # menus should not exist
    assert menu_exists(cursor, A_SLUG) == False
    assert menu_exists(cursor, B_SLUG) == False

    # import data
    assert job_exists(cursor, 1)
    assert cli_success(job_run(1))

    # menus should exist
    assert menu_exists(cursor, A_SLUG)
    assert menu_exists(cursor, B_SLUG)
    assert node_count(cursor) == A_NODECOUNT + B_NODECOUNT

    # export
    path = Path(EXPORT_CSV)
    assert cli_success(impex_export(A_SLUG, EXPORT_CSV))

    # file was written
    assert path.exists()

    # line count is correct
    assert csv_linecount(EXPORT_CSV) > 0
    assert csv_linecount(A_CSV) == csv_linecount(EXPORT_CSV)
