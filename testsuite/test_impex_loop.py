from mysql.connector.cursor import MySQLCursorDict
import pytest

from const import A_SLUG, AB_COUNT, AB_CSV, AB_NODECOUNT, B_SLUG
from plugin import (
    csv_exists,
    impex_count,
    impex_load,
    job_exists,
    job_run,
    menu_exists,
    node_count,
    plugin_reboot,
    tables_are_empty,
    cli_success,
)


@pytest.mark.serial
def test_impex_loop(cursor: MySQLCursorDict):
    """
    Given
        csv A is imported
        And A is exported as B
        And B is imported
    When B is exported as C
    Then C == B

    Notes,
    - before first import no internal item id's in impex tables
    - after first import item id's exist in nodes
    - create an export with item id's (first export)
    - import that first export ( impex now has item id's)
    - export again ( impex has item id's)
    - first export should equal the last export
    """

    pass
    """@todo this test"""
    return
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

    # import data
    assert job_exists(cursor, 1)
    assert cli_success(job_run(1))

    # menus should exist
    assert menu_exists(cursor, A_SLUG)
    assert menu_exists(cursor, B_SLUG)

    # nodes should exist + root, 3 pages for each menu
    assert node_count(cursor) == AB_NODECOUNT
