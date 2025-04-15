from mysql.connector.cursor import MySQLCursorDict
import pytest

from const import A_SLUG, AB_COUNT, AB_CSV, B_SLUG
from plugin import (
    csv_exists,
    impex_count,
    impex_load,
    job_exists,
    job_run,
    menu_exists,
    node_count,
    plugin_activate,
    plugin_clean,
    plugin_deactivate,
    task_success,
)


@pytest.mark.serial
def test_load(cursor: MySQLCursorDict):
    assert csv_exists(AB_CSV)

    plugin_clean(cursor)
    plugin_deactivate()
    plugin_activate()

    # no impex tables
    assert impex_count(cursor) == 0

    # load success
    assert task_success(impex_load(AB_CSV))
    assert impex_count(cursor) == AB_COUNT

    # menus should not exist
    assert menu_exists(cursor, A_SLUG) == False
    assert menu_exists(cursor, B_SLUG) == False

    # job should exist
    assert job_exists(cursor, 1)

    # run job
    assert task_success(job_run(1))

    # menus should exist
    assert menu_exists(cursor, A_SLUG)
    assert menu_exists(cursor, B_SLUG)

    # nodes should exist + root, 3 pages for each menu
    assert node_count(cursor) == AB_COUNT + (4 * 2)
