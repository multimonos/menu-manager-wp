from mysql.connector.cursor import MySQLCursorDict
import pytest

from const import (
    A_COUNT,
    A_CSV,
    A_NODECOUNT,
    PATCH_ID,
    PATCH_UPDATE_CSV,
)
from plugin import (
    csv_exists,
    impex_count,
    impex_load,
    is_node,
    job_exists,
    job_run,
    node_count,
    node_exists,
    node_get,
    plugin_reboot,
    tables_are_empty,
    cli_success,
)


@pytest.mark.serial
def test_update_item(cursor: MySQLCursorDict):
    """update a single 'item' node"""

    title = "updated-item-title"

    # setup
    plugin_reboot(cursor)
    assert tables_are_empty(cursor)

    # load
    assert csv_exists(A_CSV)
    assert cli_success(impex_load(A_CSV))
    assert impex_count(cursor) == A_COUNT

    # run
    assert job_exists(cursor, 1)
    assert cli_success(job_run(1))

    # check
    assert node_exists(cursor, PATCH_ID)
    node = node_get(PATCH_ID)
    assert node is not None
    assert is_node(node)
    assert node["title"] != title

    # check
    assert node_count(cursor) == A_NODECOUNT

    # load update
    assert csv_exists(PATCH_UPDATE_CSV)
    assert cli_success(impex_load(PATCH_UPDATE_CSV))

    # run update
    assert job_exists(cursor, 2)
    assert cli_success(job_run(2))

    # check
    assert node_exists(cursor, PATCH_ID)
    new_node = node_get(PATCH_ID)
    assert new_node is not None
    assert is_node(new_node)
    assert new_node["title"] == title
