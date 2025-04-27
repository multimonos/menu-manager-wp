from mysql.connector.cursor import MySQLCursorDict
import pytest

from const import (
    A_COUNT,
    A_CSV,
    A_NODECOUNT,
    A_SLUG,
    C_SLUG,
)
from plugin import (
    csv_exists,
    impex_count,
    impex_load,
    job_count,
    job_exists,
    job_latest,
    job_run,
    menu_clone,
    menu_count,
    menu_exists,
    node_count,
    nodemeta_count,
    plugin_reboot,
    tables_are_empty,
    cli_success,
)


@pytest.mark.serial
def test_clone(cursor: MySQLCursorDict):
    # reset
    plugin_reboot(cursor)
    assert tables_are_empty(cursor)

    # load success
    assert csv_exists(A_CSV)
    assert cli_success(impex_load(A_CSV))
    assert impex_count(cursor) == A_COUNT

    # menus should not exist
    assert menu_count(cursor) == 0
    assert menu_exists(cursor, A_SLUG) == False

    # job
    assert job_count(cursor) == 1
    job = job_latest()
    assert job_exists(cursor, job["ID"])
    assert cli_success(job_run(job["ID"]))

    # menus should exist
    assert menu_count(cursor) == 1
    assert menu_exists(cursor, A_SLUG)

    # nodes should exist + root, 3 pages for each menu
    assert node_count(cursor) == A_NODECOUNT
    METACOUNT = nodemeta_count(cursor)

    # clone
    assert cli_success(menu_clone(A_SLUG, C_SLUG))

    # validate
    assert menu_count(cursor) == 2
    assert menu_exists(cursor, A_SLUG)
    assert menu_exists(cursor, C_SLUG)
    assert node_count(cursor) == A_NODECOUNT * 2
    assert nodemeta_count(cursor) == METACOUNT * 2
