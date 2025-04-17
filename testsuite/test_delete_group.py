from mysql.connector.cursor import MySQLCursorDict
import pytest

from const import (
    A_COUNT,
    A_CSV,
    A_NODECOUNT,
    PATCH_DELETE_GROUP_CSV,
    PATCH_GROUP_CHILDREN_IDS,
    PATCH_GROUP_ID,
)
from plugin import (
    csv_exists,
    impex_count,
    impex_load,
    job_exists,
    job_run,
    node_count,
    node_exists,
    plugin_reboot,
    tables_are_empty,
    cli_success,
)


@pytest.mark.serial
def test_delete_item(cursor: MySQLCursorDict):
    """delete an option-group node which should cascade delete it's children"""

    ids = [PATCH_GROUP_ID, *PATCH_GROUP_CHILDREN_IDS]

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
    for id in ids:
        assert node_exists(cursor, id)

    # check
    assert node_count(cursor) == A_NODECOUNT

    # load update
    assert csv_exists(PATCH_DELETE_GROUP_CSV)
    assert cli_success(impex_load(PATCH_DELETE_GROUP_CSV))

    # run update
    assert job_exists(cursor, 2)
    assert cli_success(job_run(2))

    # check
    assert node_count(cursor) == A_NODECOUNT - len(ids)
    for id in ids:
        assert node_exists(cursor, id) == False
